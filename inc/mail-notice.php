<?php 
function vk_sc_mail_for_pending( $new_status, $old_status, $post ) {
	// 投稿がレビュー待ち以外からレビュー待ちに変わった(新規の場合は$old_statusが'new'、$new_statusが'pending'になります)
	if ( $old_status != 'pending' && $new_status == 'pending' ) {
		// ブログ名(サイト名)
		$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES);
		// 投稿名
		$post_title = wp_specialchars_decode( $post->post_title, ENT_QUOTES);
		
		// 送信先(管理者)
		$to = get_option('admin_email');
		// 件名
		$subject = "[{$blogname}] 承認待ちの実績が投稿されました({$post_title})";
		// 本文
		$message = "承認待ちの実績「{$post_title}」が投稿されました。確認をお願いします。\r\n";
		$message .= "\r\n";
		$message .= "編集および公開: \r\n";
		$message .= wp_specialchars_decode(get_edit_post_link( $post->ID ), ENT_QUOTES) . "\r\n";
 
		// メールを送信
		$r = wp_mail( $to, $subject, $message );
	}
}
add_action( 'transition_post_status', 'vk_sc_mail_for_pending', 10, 3 );

/**
 * 制作者の退会を、運営（管理者メール）と本人へプレーンテキストメールで知らせる
 *
 * @param WP_User $user               退会したユーザー（削除前の情報）.
 * @param int     $deleted_post_count 削除した実績（投稿）の件数.
 */
function vk_sc_mail_for_withdraw( $user, $deleted_post_count ) {
	$blogname     = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$display_name = wp_specialchars_decode( $user->display_name, ENT_QUOTES );
	$date_time    = wp_date( 'Y-m-d H:i:s' );

	// 運営宛て（削除後のためリンクは載せない）。
	$subject  = "[{$blogname}] 制作者が退会しました({$display_name})";
	$message  = "制作者が退会しました。\r\n";
	$message .= "\r\n";
	$message .= "表示名: {$display_name}\r\n";
	$message .= "ユーザー名: {$user->user_login}\r\n";
	$message .= "メールアドレス: {$user->user_email}\r\n";
	$message .= "削除した実績の件数: {$deleted_post_count}\r\n";
	$message .= "日時: {$date_time}\r\n";
	wp_mail( get_option( 'admin_email' ), $subject, $message );

	// 本人宛て。
	$subject  = "[{$blogname}] 退会手続きが完了しました";
	$message  = "{$display_name} 様\r\n";
	$message .= "\r\n";
	$message .= "退会手続きが完了しました。ご利用ありがとうございました。\r\n";
	$message .= "アカウントと、掲載していた制作実績・画像はすべて削除しました。\r\n";
	$message .= "\r\n";
	$message .= "このメールに心当たりがない場合は、お手数ですがサイトの運営者までご連絡ください。\r\n";
	$message .= "\r\n";
	$message .= "{$date_time}\r\n";
	wp_mail( $user->user_email, $subject, $message );
}
add_action( 'vksc_after_withdraw', 'vk_sc_mail_for_withdraw', 10, 2 );
