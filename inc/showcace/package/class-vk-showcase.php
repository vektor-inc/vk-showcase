<?php
/**
 * VK Showcase Main
 *
 * @package VK Showcase
 */

/**
 * VK Showcase
 */
class VK_Showcase {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Add Showcase Post Type
	 */
	public static function register_post_type() {

		global $vkfs_prefix;
		register_post_type(
			'showcase',
			array(
				'label'        => $vkfs_prefix . __( 'Showcase', 'vk-showcase' ),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => true,
				'capabilities' => array(
					'edit_posts' => 'create_showcase',
				),
				'map_meta_cap' => true,
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor' ),
			)
		);
	}
}

