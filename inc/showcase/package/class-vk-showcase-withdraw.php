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
	 * パスワードを連続して間違えられる回数（この回数に達したら受け付けない）
	 */
	const MAX_PASSWORD_FAILURES = 5;

	/**
	 * パスワード失敗回数を覚えておく秒数（受け付けない時間も兼ねる）
	 */
	const PASSWORD_FAILURE_LOCK_SECONDS = 900;

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
			<p><?php esc_html_e( '退会すると、アカウントと制作実績がすべて削除されます。', 'vk-showcase' ); ?></p>
			<p><a href="<?php echo esc_url( self::get_withdraw_page_url() ); ?>"><?php esc_html_e( '退会手続きへ進む', 'vk-showcase' ); ?></a></p>
		</div>
		<style>
			.vksc-withdraw-entry {
				margin-top: 40px;
				padding-top: 24px;
				border-top: 1px solid #c3c4c7;
			}
		</style>
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
		$error      = isset( $error_list[ $error_code ] ) ? $error_list[ $error_code ] : null;

		// エラーのあった入力欄に付ける、通知との関連付け。
		$invalid_attr = ' aria-invalid="true" aria-describedby="vksc-withdraw-notice"';
		$confirm_attr = ( $error && 'vksc-withdraw-confirm' === $error['target'] ) ? $invalid_attr : '';
		$pass_attr    = ( $error && 'vksc-withdraw-password' === $error['target'] ) ? $invalid_attr : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( '退会手続き', 'vk-showcase' ); ?></h1>
			<?php if ( $error ) : ?>
				<div id="vksc-withdraw-notice" class="notice notice-error" role="alert" tabindex="-1">
					<p>
						<?php echo esc_html( implode( '', $error['messages'] ) ); ?>
						<?php if ( '' !== $error['target'] ) : ?>
							<a href="#<?php echo esc_attr( $error['target'] ); ?>"><?php esc_html_e( '入力欄へ移動', 'vk-showcase' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php endif; ?>

			<p><?php esc_html_e( '退会すると、次のようになります。', 'vk-showcase' ); ?></p>
			<ul class="ul-disc">
				<li><?php esc_html_e( 'アカウントが削除され、ログインできなくなります。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( 'あなたが投稿したすべての制作実績（下書き・非公開・ゴミ箱の中を含む）と、アップロードした画像は、すべて削除されます。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( '制作者プロフィールと「依頼の問い合わせ」ボタンは表示されなくなります。', 'vk-showcase' ); ?></li>
				<li><?php esc_html_e( '再び利用するには、改めて申請と承認が必要です。', 'vk-showcase' ); ?></li>
			</ul>
			<p><strong><?php esc_html_e( '削除したアカウント・制作実績・画像は、元に戻せません。', 'vk-showcase' ); ?></strong></p>

			<form id="vksc-withdraw-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="vksc_withdraw">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<p>
					<label for="vksc-withdraw-confirm">
						<input type="checkbox" name="vksc_withdraw_confirm" id="vksc-withdraw-confirm" value="1"<?php echo $confirm_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<?php esc_html_e( '上記の内容を確認しました', 'vk-showcase' ); ?>
					</label>
				</p>
				<p>
					<label for="vksc-withdraw-password"><?php esc_html_e( '確認のため、現在のパスワードを入力してください', 'vk-showcase' ); ?></label><br>
					<input type="password" name="vksc_withdraw_password" id="vksc-withdraw-password" class="regular-text" autocomplete="current-password"<?php echo $pass_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<br>
					<small><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'パスワードを忘れた場合', 'vk-showcase' ); ?></a></small>
				</p>
				<p class="vksc-withdraw-actions">
					<button type="submit" class="button vksc-withdraw-submit"><?php esc_html_e( '退会する', 'vk-showcase' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>"><?php esc_html_e( '退会しない（プロフィールに戻る）', 'vk-showcase' ); ?></a>
				</p>
			</form>
			<style>
				.vksc-withdraw-actions {
					display: flex;
					flex-wrap: wrap;
					align-items: center;
					gap: 16px;
				}
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
			<script>
			( function () {
				// エラー通知があれば読み込み時にフォーカスを移す。
				var notice = document.getElementById( 'vksc-withdraw-notice' );
				if ( notice ) {
					notice.focus();
				}
				// 送信後はボタンを無効化して二重送信を防ぐ（送信処理は止めない）。
				var form = document.getElementById( 'vksc-withdraw-form' );
				if ( form ) {
					form.addEventListener( 'submit', function () {
						var button = form.querySelector( '.vksc-withdraw-submit' );
						if ( button ) {
							window.setTimeout( function () {
								button.disabled = true;
							}, 0 );
						}
					} );
				}
			} )();
			</script>
		</div>
		<?php
	}

	/**
	 * エラーコードごとの表示文言とリンク先を返す
	 *
	 * @return array エラーコード => array( 'messages' => 1 文ごとの文言の配列, 'target' => リンク先の id ).
	 */
	public static function get_error_messages() {
		return array(
			'not_confirmed'      => array(
				'messages' => array(
					__( '確認のチェックが入っていません。', 'vk-showcase' ),
					__( '内容を確認して、チェックを入れてください。', 'vk-showcase' ),
				),
				'target'   => 'vksc-withdraw-confirm',
			),
			'wrong_password'     => array(
				'messages' => array(
					__( 'パスワードが正しくありません。', 'vk-showcase' ),
					__( 'もう一度入力してください。', 'vk-showcase' ),
					__( '確認のチェックも、もう一度入れてください。', 'vk-showcase' ),
				),
				'target'   => 'vksc-withdraw-password',
			),
			'too_many_attempts'  => array(
				'messages' => array(
					__( 'パスワードの入力に続けて失敗したため、しばらく退会手続きを受け付けられません。', 'vk-showcase' ),
					__( '時間をおいてから、もう一度お試しください。', 'vk-showcase' ),
				),
				'target'   => '',
			),
			'invalid_nonce'      => array(
				'messages' => array(
					__( '画面の有効期限が切れました。', 'vk-showcase' ),
					__( 'もう一度お試しください。', 'vk-showcase' ),
				),
				'target'   => '',
			),
			'failed'             => array(
				'messages' => array(
					__( '退会手続きを完了できませんでした。', 'vk-showcase' ),
					__( '一部のデータが削除されている可能性があります。', 'vk-showcase' ),
					__( 'サイトの運営者までご連絡ください。', 'vk-showcase' ),
				),
				'target'   => '',
			),
		);
	}

	/**
	 * パスワード失敗回数を覚えておく transient のキーを返す
	 *
	 * @param int $user_id ユーザー ID.
	 * @return string transient キー.
	 */
	public static function get_failure_transient_key( $user_id ) {
		return 'vksc_withdraw_fail_' . absint( $user_id );
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

		// 連続して失敗している間は、パスワードを照合せず受け付けない。
		$failure_key   = self::get_failure_transient_key( $user_id );
		$failure_count = (int) get_transient( $failure_key );
		if ( $failure_count >= self::MAX_PASSWORD_FAILURES ) {
			return 'too_many_attempts';
		}

		$user = get_userdata( $user_id );
		if ( '' === $password || ! wp_check_password( $password, $user->user_pass, $user_id ) ) {
			set_transient( $failure_key, $failure_count + 1, self::PASSWORD_FAILURE_LOCK_SECONDS );
			do_action( 'wp_login_failed', $user->user_login, new WP_Error( 'incorrect_password', 'Incorrect password for withdrawal.' ) );
			return 'wrong_password';
		}

		delete_transient( $failure_key );
		return '';
	}

	/**
	 * 本人の投稿と添付ファイルを、ゴミ箱を経由せず完全に削除する
	 *
	 * @param int $user_id 対象ユーザー ID.
	 * @return int 削除した投稿（post）の件数.
	 */
	public static function delete_user_contents( $user_id ) {
		// author が 0 だと WP_Query が条件を無視して全員分になるため、実在するユーザーだけを対象にする。
		$user_id = absint( $user_id );
		if ( 0 === $user_id || ! get_userdata( $user_id ) ) {
			return 0;
		}

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
	 * @return int|false 削除した投稿の件数。削除前の事前確認またはユーザー削除に失敗したら false.
	 */
	public static function withdraw( $user_id ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// 削除後は参照できないため、通知用にユーザー情報を控える。
		$user = get_userdata( $user_id );

		// 投稿を消した後でユーザー削除だけ失敗する状態を避けるため、先に確認できることは確認する。
		if ( ! $user || ! function_exists( 'wp_delete_user' ) ) {
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
		// 文字列以外（配列など）で送られた値は空として扱う。
		$nonce     = ( isset( $request['_wpnonce'] ) && is_string( $request['_wpnonce'] ) ) ? $request['_wpnonce'] : '';
		$confirmed = ! empty( $request['vksc_withdraw_confirm'] );
		$password  = ( isset( $request['vksc_withdraw_password'] ) && is_string( $request['vksc_withdraw_password'] ) ) ? $request['vksc_withdraw_password'] : '';

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
			$message .= '<p class="message" role="status">' . esc_html__( '退会手続きが完了しました。', 'vk-showcase' ) . esc_html__( 'ご利用ありがとうございました。', 'vk-showcase' ) . '</p>';
		}
		return $message;
	}
}

new VK_Showcase_Withdraw();
