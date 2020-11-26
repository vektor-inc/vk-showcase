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
		add_action( 'init', array( __CLASS__, 'add_role' ) );
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
	}

	/**
	 * Add Role
	 */
	public static function add_role() {
		global $wp_roles;
		$wp_roles->add_role(
			'creator',
			__( 'Creator', 'vk-showcase' ),
			array(
				'read'         => true,
				'edit_posts'   => true,
				'delete_posts' => true,
			)
		);
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
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'      => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'editor' ),
			)
		);

		register_taxonomy(
			'site_type',
			'showcase',
			array(
				'label'             => __( 'Site Type', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest' => true,
			)
		);

		register_taxonomy(
			'in_charge',
			'showcase',
			array(
				'label'             => __( 'In Charge', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest' => true,
			)
		);

		register_taxonomy(
			'industry',
			'showcase',
			array(
				'label'             => __( 'Industry', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest' => true,
			)
		);
	}
}
new VK_Showcase();