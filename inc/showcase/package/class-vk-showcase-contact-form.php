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
		add_filter( 'the_content', array( __CLASS__, 'add_contact_button' ), 11 );
		add_filter( 'wpcf7_form_tag', array( __CLASS__, 'set_form' ), 11 );
	}

	/**
	 * Contact Form URL
	 */
	public static function contact_form_url() {
		return apply_filters( 'vksc_form_url', 'https://showcase.vektor-inc.co.jp/production-request/' );
	}

	/**
	 * Add Contact Button
	 */
	public static function add_contact_button( $content ) {
		global $post;
		$contact_button = '';

		$form_url      = self::contact_form_url();
		$user_id       = get_userdata( $post->post_author )->ID;
		$user_email    = get_the_author_meta( 'user_email', $user_id );
		$user_name     = get_the_author_meta( 'nickname', $user_id );
		$allow_contact = get_the_author_meta( 'allow_contact', $user_id );

		if ( 'showcase' === get_post_type() ) {
			if ( '1' === $allow_contact ) {
				$contact_button .= '<a class="btn btn-primary text-center btn-block btn-lg" href="' . $form_url . '?creator-name=' . $user_name . '&creator-email=' . $user_email . '"><i class="fas fa-arrow-circle-right"></i> ';
				$contact_button .= __( 'この制作者に依頼の問い合わせ', 'vk-showcase' );
				$contact_button .= '</a>';
			} else {
				$contact_button .= '<div class="alert alert-warning text-center">';
				$contact_button .= __( '現在この制作者は依頼を受け付けていません。', 'vk-showcase' );
				$contact_button .= '</div>';

			}
		}
		return $content . $contact_button;
	}

	public static function set_form( $tag ) {
		if ( ! is_array( $tag ) ) {
			return $tag;
		}
		// ユーザー（顧客）のメールアドレス.
		if( isset( $_GET['creator-name'] ) ) {
			$name = $tag['name'];
			if( $name == 'creator-name' ) {
				$tag['values'] = (array) $_GET['creator-name'];
			}
		}
		// ユーザー（顧客）のメールアドレス.
		if( isset( $_GET['creator-email'] ) ) {
			$name = $tag['name'];
			if( $name == 'creator-email' ) {
				$tag['values'] = (array) $_GET['creator-email'];
			}
		}
		return $tag;
	}
}
new VK_Showcase_Contact_Form();