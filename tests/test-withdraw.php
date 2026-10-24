<?php
/**
 * Class WithdrawTest
 *
 * @package Vk_Showcase
 */

require_once dirname( __DIR__ ) . '/inc/showcase/package/class-vk-showcase-users.php';
require_once dirname( __DIR__ ) . '/inc/showcase/package/class-vk-showcase-withdraw.php';
require_once dirname( __DIR__ ) . '/inc/mail-notice.php';

/**
 * 退会機能のテスト
 */
class WithdrawTest extends WP_UnitTestCase {

	const PASSWORD = 'correct-password';

	/**
	 * 各テストの前に creator ロールを用意する
	 */
	public function set_up() {
		parent::set_up();
		VK_Showcase_Users::add_creator_role();
	}

	/**
	 * 指定ロールのユーザーを作る
	 *
	 * @param string $role ロール.
	 * @return int ユーザー ID.
	 */
	private function create_user( $role ) {
		return self::factory()->user->create(
			array(
				'role'      => $role,
				'user_pass' => self::PASSWORD,
			)
		);
	}

	/**
	 * ユーザーの投稿（公開・承認待ち・下書き・ゴミ箱）と添付ファイルを作る
	 *
	 * @param int $user_id ユーザー ID.
	 * @return array array( 'posts' => 投稿 ID の配列, 'attachment' => 添付 ID ).
	 */
	private function create_contents( $user_id ) {
		$posts = array();
		foreach ( array( 'publish', 'pending', 'draft', 'trash' ) as $status ) {
			$posts[] = self::factory()->post->create(
				array(
					'post_author' => $user_id,
					'post_status' => $status,
				)
			);
		}
		$attachment = self::factory()->attachment->create_object(
			array(
				'file'           => 'vksc-test.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_author'    => $user_id,
			)
		);
		return array(
			'posts'      => $posts,
			'attachment' => $attachment,
		);
	}

	/**
	 * 現在のユーザーを設定して退会の送信値を作る
	 *
	 * @param int    $user_id     ユーザー ID.
	 * @param string $nonce_state valid / invalid.
	 * @param bool   $confirm     チェックの有無.
	 * @param string $password    パスワード.
	 * @return array 送信値.
	 */
	private function build_request( $user_id, $nonce_state, $confirm, $password ) {
		wp_set_current_user( $user_id );
		$request = array(
			'_wpnonce'               => 'valid' === $nonce_state ? wp_create_nonce( VK_Showcase_Withdraw::NONCE_ACTION ) : 'invalid',
			'vksc_withdraw_password' => $password,
		);
		if ( $confirm ) {
			$request['vksc_withdraw_confirm'] = '1';
		}
		return $request;
	}

	/**
	 * 退会できる条件のテスト
	 */
	public function test_can_withdraw() {
		$test_cases = array(
			array(
				'test_condition_name' => '制作者は退会できる',
				'role'                => 'creator',
				'expected'            => true,
			),
			array(
				'test_condition_name' => '管理者は退会できない',
				'role'                => 'administrator',
				'expected'            => false,
			),
			array(
				'test_condition_name' => '編集者（制作者ではない）は退会できない',
				'role'                => 'editor',
				'expected'            => false,
			),
		);

		foreach ( $test_cases as $case ) {
			$user_id = $this->create_user( $case['role'] );
			$this->assertSame( $case['expected'], VK_Showcase_Withdraw::can_withdraw( $user_id ), $case['test_condition_name'] );
		}

		// ユーザー削除権限を足した制作者は退会できない。
		$user_id = $this->create_user( 'creator' );
		get_userdata( $user_id )->add_cap( 'delete_users' );
		$this->assertFalse( VK_Showcase_Withdraw::can_withdraw( $user_id ), 'delete_users 権限を持つ制作者は退会できない' );

		// サイト設定権限を足した制作者は退会できない。
		$user_id = $this->create_user( 'creator' );
		get_userdata( $user_id )->add_cap( 'manage_options' );
		$this->assertFalse( VK_Showcase_Withdraw::can_withdraw( $user_id ), 'manage_options 権限を持つ制作者は退会できない' );
	}

	/**
	 * パスワードを連続して間違えたら受け付けなくなるテスト
	 */
	public function test_validate_request_password_lock() {
		$user_id = $this->create_user( 'creator' );
		wp_set_current_user( $user_id );
		$nonce = wp_create_nonce( VK_Showcase_Withdraw::NONCE_ACTION );

		// 上限回数まで間違える。
		for ( $i = 0; $i < VK_Showcase_Withdraw::MAX_PASSWORD_FAILURES; $i++ ) {
			$this->assertSame( 'wrong_password', VK_Showcase_Withdraw::validate_request( $user_id, $nonce, true, 'wrong' ), ( $i + 1 ) . ' 回目の失敗は wrong_password' );
		}
		$this->assertSame( 'too_many_attempts', VK_Showcase_Withdraw::validate_request( $user_id, $nonce, true, self::PASSWORD ), '上限後は正しいパスワードでも受け付けない' );

		// 失敗回数を消せば、また受け付ける。
		delete_transient( VK_Showcase_Withdraw::get_failure_transient_key( $user_id ) );
		$this->assertSame( '', VK_Showcase_Withdraw::validate_request( $user_id, $nonce, true, self::PASSWORD ), '失敗回数が消えれば正しいパスワードで通る' );
	}

	/**
	 * 送信値が配列のときは空として扱うテスト
	 */
	public function test_process_request_array_values() {
		$user_id = $this->create_user( 'creator' );
		$request = $this->build_request( $user_id, 'valid', true, self::PASSWORD );

		$array_password                           = $request;
		$array_password['vksc_withdraw_password'] = array( self::PASSWORD );
		$this->assertSame( 'wrong_password', VK_Showcase_Withdraw::process_request( $user_id, $array_password ), 'パスワードが配列なら wrong_password' );

		$array_nonce             = $request;
		$array_nonce['_wpnonce'] = array( $request['_wpnonce'] );
		$this->assertSame( 'invalid_nonce', VK_Showcase_Withdraw::process_request( $user_id, $array_nonce ), 'nonce が配列なら invalid_nonce' );
		$this->assertNotFalse( get_userdata( $user_id ), '退会されていない' );
	}

	/**
	 * 退会の送信内容の検証のテスト
	 */
	public function test_validate_request() {
		$test_cases = array(
			array(
				'test_condition_name' => 'nonce・チェック・パスワードが正しい場合 => 空文字',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'confirm'             => true,
				'password'            => self::PASSWORD,
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'nonce が不正な場合 => invalid_nonce',
				'role'                => 'creator',
				'nonce'               => 'invalid',
				'confirm'             => true,
				'password'            => self::PASSWORD,
				'expected'            => 'invalid_nonce',
			),
			array(
				'test_condition_name' => 'チェックが無い場合 => not_confirmed',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'confirm'             => false,
				'password'            => self::PASSWORD,
				'expected'            => 'not_confirmed',
			),
			array(
				'test_condition_name' => 'パスワードが違う場合 => wrong_password',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'confirm'             => true,
				'password'            => 'wrong',
				'expected'            => 'wrong_password',
			),
			array(
				'test_condition_name' => 'パスワードが空の場合 => wrong_password',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'confirm'             => true,
				'password'            => '',
				'expected'            => 'wrong_password',
			),
			array(
				'test_condition_name' => '管理者は全て正しくても forbidden',
				'role'                => 'administrator',
				'nonce'               => 'valid',
				'confirm'             => true,
				'password'            => self::PASSWORD,
				'expected'            => 'forbidden',
			),
		);

		foreach ( $test_cases as $case ) {
			$user_id = $this->create_user( $case['role'] );
			$request = $this->build_request( $user_id, $case['nonce'], $case['confirm'], $case['password'] );
			$actual  = VK_Showcase_Withdraw::validate_request(
				$user_id,
				$request['_wpnonce'],
				! empty( $request['vksc_withdraw_confirm'] ),
				$request['vksc_withdraw_password']
			);
			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 退会の送信処理のテスト（退会できる場合・できない場合）
	 */
	public function test_process_request() {
		$test_cases = array(
			array(
				'test_condition_name' => '正しい送信 => 退会できてユーザー・投稿・添付が完全に消える',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'password'            => self::PASSWORD,
				'expected_error'      => '',
				'expected_exists'     => false,
			),
			array(
				'test_condition_name' => 'nonce 不正 => 退会せず、ユーザー・投稿・添付が残る',
				'role'                => 'creator',
				'nonce'               => 'invalid',
				'password'            => self::PASSWORD,
				'expected_error'      => 'invalid_nonce',
				'expected_exists'     => true,
			),
			array(
				'test_condition_name' => 'パスワード不正 => 退会せず、ユーザー・投稿・添付が残る',
				'role'                => 'creator',
				'nonce'               => 'valid',
				'password'            => 'wrong',
				'expected_error'      => 'wrong_password',
				'expected_exists'     => true,
			),
			array(
				'test_condition_name' => '管理者 => 退会せず、ユーザー・投稿・添付が残る',
				'role'                => 'administrator',
				'nonce'               => 'valid',
				'password'            => self::PASSWORD,
				'expected_error'      => 'forbidden',
				'expected_exists'     => true,
			),
		);

		foreach ( $test_cases as $case ) {
			$user_id  = $this->create_user( $case['role'] );
			$contents = $this->create_contents( $user_id );
			$request  = $this->build_request( $user_id, $case['nonce'], true, $case['password'] );

			$error_code = VK_Showcase_Withdraw::process_request( $user_id, $request );

			$this->assertSame( $case['expected_error'], $error_code, $case['test_condition_name'] );
			$this->assertSame( $case['expected_exists'], false !== get_userdata( $user_id ), $case['test_condition_name'] . '（ユーザー）' );

			// ゴミ箱の投稿も含め、完全削除されたか（get_post が null）で確認する。
			foreach ( $contents['posts'] as $post_id ) {
				$this->assertSame( $case['expected_exists'], null !== get_post( $post_id ), $case['test_condition_name'] . '（投稿 ' . $post_id . '）' );
			}
			$this->assertSame( $case['expected_exists'], null !== get_post( $contents['attachment'] ), $case['test_condition_name'] . '（添付）' );
		}
	}

	/**
	 * 本人の投稿・添付の削除のテスト（他人の投稿は消さない）
	 */
	public function test_delete_user_contents() {
		$creator_id = $this->create_user( 'creator' );
		$other_id   = $this->create_user( 'creator' );
		$mine       = $this->create_contents( $creator_id );
		$others     = $this->create_contents( $other_id );

		$deleted_post_count = VK_Showcase_Withdraw::delete_user_contents( $creator_id );

		$this->assertSame( 4, $deleted_post_count, '削除件数は本人の投稿 4 件（ゴミ箱内を含む）' );
		foreach ( $mine['posts'] as $post_id ) {
			$this->assertNull( get_post( $post_id ), '本人の投稿は完全に削除される' );
		}
		$this->assertNull( get_post( $mine['attachment'] ), '本人の添付は完全に削除される' );
		foreach ( $others['posts'] as $post_id ) {
			$this->assertNotNull( get_post( $post_id ), '他人の投稿は残る' );
		}
		$this->assertNotNull( get_post( $others['attachment'] ), '他人の添付は残る' );
	}

	/**
	 * ユーザー ID が 0・負の数・存在しない値のときは何も削除しないテスト
	 */
	public function test_delete_user_contents_invalid_user() {
		$other_id = $this->create_user( 'creator' );
		$others   = $this->create_contents( $other_id );
		// 負の ID が絶対値（ユーザー 1）に化けて、ユーザー 1 の投稿が消えないことも確認する。
		$first_user_contents = $this->create_contents( 1 );

		foreach ( array( 0, '0', -1, '-1', 99999999 ) as $invalid_user_id ) {
			$this->assertSame( 0, VK_Showcase_Withdraw::delete_user_contents( $invalid_user_id ), 'ID ' . $invalid_user_id . ' は何も削除しない' );
		}
		foreach ( $others['posts'] as $post_id ) {
			$this->assertNotNull( get_post( $post_id ), '他人の投稿が残る' );
		}
		$this->assertNotNull( get_post( $others['attachment'] ), '他人の添付が残る' );
		foreach ( $first_user_contents['posts'] as $post_id ) {
			$this->assertNotNull( get_post( $post_id ), 'ユーザー 1 の投稿が残る' );
		}
		$this->assertNotNull( get_post( $first_user_contents['attachment'] ), 'ユーザー 1 の添付が残る' );
		$this->assertFalse( VK_Showcase_Withdraw::withdraw( 99999999 ), '存在しないユーザーの退会は false' );
	}

	/**
	 * 他人がアップロードして本人の投稿に添付した画像は残るテスト
	 */
	public function test_delete_user_contents_keeps_others_attachment() {
		$creator_id  = $this->create_user( 'creator' );
		$uploader_id = $this->create_user( 'administrator' );
		$post_id     = self::factory()->post->create( array( 'post_author' => $creator_id ) );
		$attachment  = self::factory()->attachment->create_object(
			array(
				'file'           => 'vksc-others.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_author'    => $uploader_id,
				'post_parent'    => $post_id,
			)
		);

		VK_Showcase_Withdraw::delete_user_contents( $creator_id );

		$this->assertNull( get_post( $post_id ), '本人の投稿は削除される' );
		$this->assertNotNull( get_post( $attachment ), '他人がアップロードした添付は残る' );
	}

	/**
	 * 退会時に運営宛てと本人宛てのメールが送られるテスト
	 */
	public function test_vk_sc_mail_for_withdraw() {
		reset_phpmailer_instance();
		// 環境のホスト名が localhost だと送信元が不正アドレス扱いになるため固定する。
		add_filter(
			'wp_mail_from',
			function () {
				return 'wordpress@example.org';
			}
		);
		$user_id = $this->create_user( 'creator' );
		$this->create_contents( $user_id );
		$request = $this->build_request( $user_id, 'valid', true, self::PASSWORD );
		$email   = get_userdata( $user_id )->user_email;

		// 承認待ち投稿の作成で出たメールを除き、退会のメールだけを数える。
		reset_phpmailer_instance();
		VK_Showcase_Withdraw::process_request( $user_id, $request );

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertSame( 2, count( $mailer->mock_sent ), '運営宛てと本人宛ての 2 通が送られる' );
		$this->assertSame( get_option( 'admin_email' ), $mailer->mock_sent[0]['to'][0][0], '1 通目は運営宛て' );
		$this->assertStringContainsString( '削除した実績の件数: 4', $mailer->mock_sent[0]['body'], '運営宛て本文に削除件数がある' );
		$this->assertSame( $email, $mailer->mock_sent[1]['to'][0][0], '2 通目は本人宛て' );
		reset_phpmailer_instance();
	}
}
