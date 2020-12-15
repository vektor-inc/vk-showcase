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