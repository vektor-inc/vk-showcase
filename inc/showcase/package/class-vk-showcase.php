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
		add_action( 'admin_init', array( __CLASS__, 'role_setting' ) );
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
				'read'    => true,
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
				'capabilities' => array(
					'edit_posts'   => 'showcase_capabilities',
					'delete_posts' => 'showcase_capabilities',
					'read'         => 'showcase_capabilities',
				),
				'map_meta_cap' => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
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
				'capabilities' => array(
					'assign_terms' => 'showcase_capabilities',
				),
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
				'capabilities' => array(
					'assign_terms' => 'showcase_capabilities',
				),
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
				'capabilities' => array(
					'assign_terms' => 'showcase_capabilities',
				),
			)
		);

	}

	/**
	 * Role Setting
	 */
	public static function role_setting() {
		global $wp_roles;
		$wp_roles->add_cap( 'administrator', 'showcase_capabilities' );
		$wp_roles->add_cap( 'editor', 'showcase_capabilities' );
		$wp_roles->add_cap( 'author', 'showcase_capabilities' );
		$wp_roles->add_cap( 'contributor', 'showcase_capabilities' );
		$wp_roles->add_cap( 'creator', 'showcase_capabilities' );
	}
}
new VK_Showcase();

