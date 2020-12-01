<?php
/**
 * VK Showcase Post Types
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Post Types
 */
class VK_Showcase_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 0 );
		add_action( 'admin_init', array( __CLASS__, 'role_setting' ) );
	}

	/**
	 * Add Showcase Post Type
	 */
	public static function register_post_type() {

		global $vkfs_prefix;
		register_post_type(
			'showcase',
			array(
				'label'        => __( '制作実績', 'vk-showcase' ),
				'public'       => true,
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'editor' ),
				'map_meta_cap' => false,
				'capabilities' => array(
					'edit_post'              => 'edit_showcase',
					'read_post'              => 'read_showcase',
					'delete_post'            => 'delete_showcase',
					'create_posts'           => 'edit_showcases',
					'edit_posts'             => 'edit_showcases',
					'edit_others_posts'      => 'edit_others_posts',
					'publish_posts'          => 'publish_posts',
					'read_private_posts'     => 'read_private_posts',
					'delete_posts'           => 'delete_showcases',
					'delete_private_posts'   => 'delete_private_posts',
					'delete_published_posts' => 'delete_published_posts',
					'delete_others_posts'    => 'delete_others_posts',
					'edit_private_posts'     => 'edit_private_posts',
					'edit_published_posts'   => 'edit_published_posts',
				),
			)
		);

		register_taxonomy(
			'site_type',
			'showcase',
			array(
				'label'             => __( 'サイト種別・機能', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'capabilities'      => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'edit_showcases',
				),
			)
		);

		register_taxonomy(
			'in_charge',
			'showcase',
			array(
				'label'             => __( '担当範囲', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'capabilities'      => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'edit_showcases',
				),
			)
		);

		register_taxonomy(
			'industry',
			'showcase',
			array(
				'label'             => __( '業種', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
				'capabilities'      => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'manage_categories',
					'delete_terms' => 'manage_categories',
					'assign_terms' => 'edit_showcases',
				),
			)
		);
	}

	/**
	 * Role Setting
	 */
	public static function role_setting() {
		global $wp_roles;
		$wp_roles->add_cap( 'administrator', 'read_showcase' );
		$wp_roles->add_cap( 'administrator', 'edit_showcase' );
		$wp_roles->add_cap( 'administrator', 'delete_showcase' );
		$wp_roles->add_cap( 'administrator', 'edit_showcases' );
		$wp_roles->add_cap( 'administrator', 'delete_showcases' );
		$wp_roles->add_cap( 'editor', 'read_showcase' );
		$wp_roles->add_cap( 'editor', 'edit_showcase' );
		$wp_roles->add_cap( 'editor', 'delete_showcase' );
		$wp_roles->add_cap( 'editor', 'edit_showcases' );
		$wp_roles->add_cap( 'editor', 'delete_showcases' );
		$wp_roles->add_cap( 'author', 'read_showcase' );
		$wp_roles->add_cap( 'author', 'edit_showcase' );
		$wp_roles->add_cap( 'author', 'delete_showcase' );
		$wp_roles->add_cap( 'author', 'edit_showcases' );
		$wp_roles->add_cap( 'author', 'delete_showcases' );
		$wp_roles->add_cap( 'contributor', 'read_showcase' );
		$wp_roles->add_cap( 'contributor', 'edit_showcase' );
		$wp_roles->add_cap( 'contributor', 'delete_showcase' );
		$wp_roles->add_cap( 'contributor', 'edit_showcases' );
		$wp_roles->add_cap( 'contributor', 'delete_showcases' );
	}
}

new VK_Showcase_Post_Types();
