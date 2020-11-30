<?php
/**
 * VK Showcase Contact Form
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Contact Form
 */
class VK_Showcase_Contact_Form {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'the_content', array( __CLASS__, 'add_contact_button' ) );
	}

	/**
	 * Add Contact Button
	 */
	public static function add_contact_button( $content ) {
		global $post;
		global $form_url;
		$contact_button = '';

		$user_id       = get_userdata( $post->post_author )->ID;
		$user_email    = get_the_author_meta( 'user_email', $user_id );
		$allow_contact = get_the_author_meta( 'allow_contact', $user_id );

		if ( 'showcase' === get_post_type() ) {
			if ( '1' === $allow_contact ) {
				$contact_button .= '<a class="btn btn-primary text-center" href="' . $form_url . '?user_mail_address=' . $user_email . '">';
				$contact_button .= __( 'この製作者に制作依頼をする', 'vk-showcase' );
				$contact_button .= '</a>';
			} else {
				$contact_button .= '<div class="alert alert-warning text-center">';
				$contact_button .= __( '現在この制作者は依頼を受け付けていません。', 'vk-showcase' );
				$contact_button .= '</div>';

			}
		}
		return $content . $contact_button;
	}
}
new VK_Showcase_Contact_Form();