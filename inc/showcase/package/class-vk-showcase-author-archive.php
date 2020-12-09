<?php
/**
 * VK Showcase Author Archive
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Author Archive
 */
class VK_Showcase_Author_Archive {

	/**
	 * Constructor
	 */
	public function __construct() {
		$theme_hook_array     = self::theme_hook_array();
		$current_parent_theme = get_template();

		add_action( 'pre_get_posts', array( __CLASS__, 'change_author_archive' ) );

		if ( array_key_exists( $current_parent_theme, $theme_hook_array ) ) {
			add_action( $theme_hook_array[ $current_parent_theme ], array( __CLASS__, 'display_author_info_on_loop' ) );
		} else {
			add_action( 'loop_start', array( __CLASS__, 'display_author_info_on_loop' ) );
		}

		add_filter( 'vk_post_options', array( __CLASS__, 'change_vk_posts_overlay' ) );
	}

	/**
	 * Theme Hook Array.
	 */
	public static function theme_hook_array() {
		$theme_hooks      = array(
			'lightning'     => 'lightning_loop_before',
			'lightning-pro' => 'lightning_loop_before',
			'katawara'      => 'katawara_loop_before',
		);
		return apply_filters( 'vksc_theme_hook_array', $theme_hooks );
	}

	/**
	 * Change Author Archive
	 */
	public static function change_author_archive( $query ) {
		if ( is_author() && $query->is_main_query() ) {
			$query->set( 'post_type', array( 'post' ) );
		}
	}

	/**
	 * Display Author Info on Loop
	 */
	public static function display_author_info_on_loop() {
		if ( class_exists( 'Vk_Post_Author_Box' ) && is_author() ) {
			echo Vk_Post_Author_Box::pad_get_author_box( 'author_archive' );
		}
	}

	/**
	 * Change VK Posts Overlay
	 */
	public static function change_vk_posts_overlay( $default_options ) {
		global $post;
		$outer_class = 'vk_post_imgOuter_singleTermLabel';
		$outer_style = 'color:#fff;background-color:#c00;';
		$user_id     = get_userdata( $post->post_author )->ID;
		$allow_contact = get_the_author_meta( 'allow_contact', $user_id );

		$default_options['display_image_overlay_term'] = false;

		if ( 'post' === get_post_type() && '1' === $allow_contact ) {
			$default_options['overlay'] = '<span class="' . $outer_class . '" style="' . $outer_style. '">' . __( '依頼受付中', 'vk-showcase' ) . '</span>';
		} else {
			$default_options['overlay'] ='';
		}

		return $default_options;
	}
}

new VK_Showcase_Author_Archive();