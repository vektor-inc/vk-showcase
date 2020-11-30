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
	}

	/**
	 * Theme Hook Array.
	 */
	public static function theme_hook_array() {
		return apply_filters( 'vksc_theme_hook_array', array() );
	}



	/**
	 * Change Author Archive
	 */
	public static function change_author_archive( $query ) {
		if ( is_author() && $query->is_main_query() ) {
			$query->set( 'post_type', array( 'post', 'showcase' ) );
		}
	}

	/**
	 * Display Author Info on Loop
	 */
	public static function display_author_info_on_loop() {
		if ( class_exists( 'Vk_Post_Author_Box' ) ) {
			echo Vk_Post_Author_Box::pad_get_author_profile();
		}
	}
}

new VK_Showcase_Author_Archive();