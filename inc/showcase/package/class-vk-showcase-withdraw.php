<?php
/**
 * VK Showcase Withdraw
 *
 * 制作者（creator ロール）が自分で退会するための機能。
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Withdraw
 */
class VK_Showcase_Withdraw {

	/**
	 * 退会手続きページのスラッグ
	 */
	const PAGE_SLUG = 'vksc-withdraw';

	/**
	 * 退会手続きの nonce アクション名
	 */
	const NONCE_ACTION = 'vksc_withdraw';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'add_withdraw_page' ) );
		add_action( 'admin_footer-profile.php', array( __CLASS__, 'render_profile_entry' ) );
		add_action( 'admin_post_vksc_withdraw', array( __CLASS__, 'handle_withdraw_request' ) );
		add_filter( 'login_message', array( __CLASS__, 'add_login_message' ) );
	}

	/**
	 * 退会手続きページの URL を返す
	 *
	 * @param string $error_code エラーコード（任意）。
	 * @return string URL.
	 */
	public static function get_withdraw_page_url( $error_code = '' ) {
		$url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		if ( '' !== $error_code ) {
			$url = add_query_arg( 'vksc_withdraw_error', $error_code, $url );
		}
		return $url;
	}

	/**
	 * 退会手続きページを管理画面に登録する（左メニューには出さない）
	 */
	public static function add_withdraw_page() {
		// 親を options.php にすると、メニューに出ずにページだけ開ける。
		add_submenu_page(
			'options.php',
			__( '退会手続き', 'vk-showcase' ),
			__( '退会手続き', 'vk-showcase' ),
			'read',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_withdraw_page' )
		);
	}

	/**
	 * 退会を実行できる本人かどうかを判定する
	 *
	 * @param int $user_id 対象ユーザー ID.
	 * @return bool 制作者のみ true。ユーザー削除権限・サイト設定権限を持つ者と特権管理者は false.
	 */
	public static function can_withdraw( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user || ! in_array( 'creator', (array) $user->roles, true ) ) {
			return false;
		}
		if ( user_can( $user, 'delete_users' ) || user_can( $user, 'manage_options' ) || is_super_admin( $user_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * プロフィール画面の最下部に退会への入口を出力する
	 */
	public static function render_profile_entry() {
		if ( ! self::can_withdraw( get_current_user_id() ) ) {
			return;
		}
		?>
		<div id="vksc-withdraw-entry" class="vksc-withdraw-entry">
			<h2><?php esc_html_e( '退会', 'vk-showcase' ); ?></h2>
			<p><?php esc_html_e( 'ショーケースの利用をやめる場合は、退会手続きへ進んでください。', 'vk-showcase' ); ?></p>
			<p><?php esc_html_e( '手続きは別のページで行います。', 'vk-showcase' ); ?></p>
			<p><a href="<?php echo esc_url( self::get_withdraw_page_url() ); ?>"><?php esc_html_e( '退会手続きへ進む', 'vk-showcase' ); ?></a></p>
		</div>
		<script>
		( function () {
			// プロフィール更新フォームの外、ページ本体の最下部へ移す。
			var entry = document.getElementById( 'vksc-withdraw-entry' );
			var wrap = document.querySelector( '#wpbody-content .wrap' );
			if ( entry && wrap ) {
				wrap.appendChild( entry );
			}
		} )();
		</script>
		<?php
	}

	/**
	 * 退会手続きページを出力する
	 */
	public static function render_withdraw_page() {
		if ( ! self::can_withdraw( get_current_user_id() ) ) {
			wp_die( esc_html__( 'このアカウントでは退会手続きを行えません。', 'vk-showcase' ), '', array( 'response' => 403 ) );
		}

		$error_code = isset( $_GET['vksc_withdraw_error'] ) ? sanitize_key( wp_unslash( $_GET['vksc_withdraw_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error_list = self::get_error_messages();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( '退会手続き', 'vk-showcase' ); ?></h1>
			<?php if ( isset( $error_list[ $error_code ] ) ) : ?>
				<div class="notice notice-error" role="alert">
					<p>
						<?php echo esc_html( $error_list[ $error_code ]['message'] ); ?>
						<?php if ( '' !== $error_list[ $error_code ]['target'] ) : ?>
							<a href="#<?php echo esc_attr( $error_list[ $error_code ]['target'] ); ?>"><?php esc_html_e( '入力欄へ移動', 'vk-showcase' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<p><?php esc_html_e( '退会すると、次のようになります。', 'vk-showcase' ); ?></p>
			<p><?php esc_html_e( '内容を確認のうえ、手続きを進めてください。', 'vk-showcase' ); ?></p>
			<ul style="list-style: disc; padding-left: 1.5em;">
				<li><?php esc_html_e( 'アカウントが削除され、ログインできなくなります。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( '掲載中・承認待ちの制作実績と画像は、すべて削除されます。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( '制作者プロフィールと「依頼の問い合わせ」ボタンは表示されなくなります。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( '削除した実績と画像は元に戻せません。', 'vk-showcase' ); ?></li>
			</ul>
			<p><strong><?php esc_html_e( '退会後は、ご自身で元に戻すことはできません。', 'vk-showcase' ); ?></strong></p>
			<p><?php esc_html_e( '再び利用するには、改めて申請と承認が必要です。', 'vk-showcase' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="vksc_withdraw">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<p>
					<label for="vksc-withdraw-confirm">
						<input type="checkbox" name="vksc_withdraw_confirm" id="vksc-withdraw-confirm" value="1">
						<?php esc_html_e( '上記の内容を確認しました', 'vk-showcase' ); ?>
					</label>
				</p>
				<p>
					<label for="vksc-withdraw-password"><?php esc_html_e( '確認のため、現在のパスワードを入力してください', 'vk-showcase' ); ?></label><br>
					<input type="password" name="vksc_withdraw_password" id="vksc-withdraw-password" class="regular-text" autocomplete="current-password">
				</p>
				<p>
					<button type="submit" class="button vksc-withdraw-submit"><?php esc_html_e( '退会する', 'vk-showcase' ); ?></button>
				</p>
			</form>
			<style>
				.vksc-withdraw-submit.button {
					min-height: 44px;
					padding: 0 24px;
					color: #fff;
					background: #b32d2e;
					border-color: #b32d2e;
				}
				.vksc-withdraw-submit.button:hover,
				.vksc-withdraw-submit.button:focus {
					color: #fff;
					background: #8a2424;
					border-color: #8a2424;
				}
			</style>
		</div>
		<?php
	}

	/**
	 * エラーコードごとの表示文言とリンク先を返す
	 *
	 * @return array エラーコード => array( 'message' => 文言, 'target' => リンク先の id ).
	 */
	public static function get_error_messages() {
		return array(
			'not_confirmed'  => array(
				'message' => __( '確認のチェックが入っていません。', 'vk-showcase' ),
				'target'  => 'vksc-withdraw-confirm',
			),
			'wrong_password' => array(
				'message' => __( 'パスワードが正しくありません。', 'vk-showcase' ),
				'target'  => 'vksc-withdraw-password',
			),
			'invalid_nonce'  => array(
				'message' => __( '画面の有効期限が切れました。もう一度お試しください。', 'vk-showcase' ),
				'target'  => '',
			),
			'failed'         => array(
				'message' => __( '退会手続きを完了できませんでした。', 'vk-showcase' ),
				'target'  => '',
			),
		);
	}

	/**
	 * 退会の送信内容を検証する
	 *
	 * @param int    $user_id   対象ユーザー ID（ログイン中の本人）.
	 * @param string $nonce     送信された nonce.
	 * @param bool   $confirmed 確認のチェックが入っているか.
	 * @param string $password  入力された現在のパスワード.
	 * @return string 問題が無ければ空文字、あればエラーコード.
	 */
	public static function validate_request( $user_id, $nonce, $confirmed, $password ) {
		if ( ! self::can_withdraw( $user_id ) ) {
			return 'forbidden';
		}
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return 'invalid_nonce';
		}
		if ( ! $confirmed ) {
			return 'not_confirmed';
		}
		$user = get_userdata( $user_id );
		if ( '' === $password || ! wp_check_password( $password, $user->user_pass, $user_id ) ) {
			return 'wrong_password';
		}
		return '';
	}

	/**
	 * 本人の投稿と添付ファイルを、ゴミ箱を経由せず完全に削除する
	 *
	 * @param int $user_id 対象ユーザー ID.
	 * @return int 削除した投稿（post）の件数.
	 */
	public static function delete_user_contents( $user_id ) {
		$deleted_post_count = 0;

		// 投稿（ゴミ箱内も含む）を完全削除する。
		$post_ids = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => array_merge( get_post_stati(), array( 'trash' ) ),
				'author'         => $user_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $post_ids as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) {
				++$deleted_post_count;
			}
		}

		// 本人が持ち主の添付ファイルを、ファイルごと完全削除する。
		$attachment_ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => array_merge( get_post_stati(), array( 'inherit', 'trash' ) ),
				'author'         => $user_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);
		foreach ( $attachment_ids as $attachment_id ) {
			wp_delete_post( $attachment_id, true );
		}

		return $deleted_post_count;
	}

	/**
	 * 退会処理を実行する（投稿・添付ファイル削除 → ユーザー削除 → vksc_after_withdraw）
	 *
	 * @param int $user_id 対象ユーザー ID.
	 * @return int|false 削除した投稿の件数。ユーザー削除に失敗したら false.
	 */
	public static function withdraw( $user_id ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// 削除後は参照できないため、通知用にユーザー情報を控える。
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		$deleted_post_count = self::delete_user_contents( $user_id );

		if ( ! wp_delete_user( $user_id ) ) {
			return false;
		}

		/**
		 * 退会処理が完了した後に実行される。
		 *
		 * @param WP_User $user               退会したユーザー（削除前の情報）.
		 * @param int     $deleted_post_count 削除した投稿の件数.
		 */
		do_action( 'vksc_after_withdraw', $user, $deleted_post_count );

		return $deleted_post_count;
	}

	/**
	 * 退会の送信を処理する。リクエスト値の検証と退会実行までを行い、結果を返す
	 *
	 * @param int   $user_id ログイン中のユーザー ID.
	 * @param array $request 送信値（$_POST 相当・unslash 済み）.
	 * @return string 退会できたら空文字、できなければエラーコード.
	 */
	public static function process_request( $user_id, $request ) {
		$nonce     = isset( $request['_wpnonce'] ) ? (string) $request['_wpnonce'] : '';
		$confirmed = ! empty( $request['vksc_withdraw_confirm'] );
		$password  = isset( $request['vksc_withdraw_password'] ) ? (string) $request['vksc_withdraw_password'] : '';

		$error_code = self::validate_request( $user_id, $nonce, $confirmed, $password );
		if ( '' !== $error_code ) {
			return $error_code;
		}
		if ( false === self::withdraw( $user_id ) ) {
			return 'failed';
		}
		return '';
	}

	/**
	 * Admin-post.php からの退会送信を受ける
	 */
	public static function handle_withdraw_request() {
		// 対象はログイン中の本人のみ。ID は送信値から受け取らない。
		$error_code = self::process_request( get_current_user_id(), wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( 'forbidden' === $error_code ) {
			wp_die( esc_html__( 'このアカウントでは退会手続きを行えません。', 'vk-showcase' ), '', array( 'response' => 403 ) );
		}
		if ( '' !== $error_code ) {
			wp_safe_redirect( self::get_withdraw_page_url( $error_code ) );
			exit;
		}

		// 退会完了: ログアウトしてログイン画面へ。
		wp_logout();
		wp_safe_redirect( add_query_arg( 'vksc_withdrawn', '1', wp_login_url() ) );
		exit;
	}

	/**
	 * ログイン画面に退会完了の文を出す
	 *
	 * @param string $message 既存のメッセージ.
	 * @return string メッセージ.
	 */
	public static function add_login_message( $message ) {
		if ( isset( $_GET['vksc_withdrawn'] ) && '1' === $_GET['vksc_withdrawn'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$message .= '<p class="message" role="status">' . esc_html__( '退会手続きが完了しました。ご利用ありがとうございました。', 'vk-showcase' ) . '</p>';
		}
		return $message;
	}
}

new VK_Showcase_Withdraw();
