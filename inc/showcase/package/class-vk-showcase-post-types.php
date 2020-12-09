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
		add_filter( 'wpcf7_map_meta_cap', array( __CLASS__, 'wpcf7_capabilities' ) );
	}

	/**
	 * Add Showcase Post Type
	 */
	public static function register_post_type() {

		global $vkfs_prefix;
		register_post_type(
			'information',
			array(
				'label'        => __( 'お知らせ', 'vk-showcase' ),
				'public'       => true,
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-screenoptions',
				'show_in_rest' => true,
				'supports'     => array( 'title', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'editor' ),
				'map_meta_cap' => true,
				'capabilities' => array(
					'create_posts'           => 'create_pages',
					'edit_posts'             => 'edit_pages',
					'edit_others_posts'      => 'edit_others_pages',
					'publish_posts'          => 'publish_pages',
					'read_private_posts'     => 'read_private_pages',
					'delete_posts'           => 'delete_pages',
					'delete_private_posts'   => 'delete_private_pages',
					'delete_published_posts' => 'delete_published_pages',
					'delete_others_posts'    => 'delete_others_pages',
					'edit_private_posts'     => 'edit_private_pages',
					'edit_published_posts'   => 'edit_published_pages',
				),
			)
		);

		register_taxonomy(
			'themes_and_skins',
			'post',
			array(
				'label'             => __( 'テーマ・スキン', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'site_type',
			'post',
			array(
				'label'             => __( 'サイト種別・機能', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'in_charge',
			'post',
			array(
				'label'             => __( '担当範囲', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'industry',
			'post',
			array(
				'label'             => __( '業種', 'vk-showcase' ),
				'public'            => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => true,
			)
		);

		unregister_taxonomy_for_object_type( 'category', 'post' );
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}

	public static function wpcf7_capabilities( $meta_caps ) {
		$meta_caps = array(
			'wpcf7_edit_contact_form'    => 'edit_pages',
			'wpcf7_edit_contact_forms'   => 'edit_pages',
			'wpcf7_read_contact_form'    => 'edit_pages',
			'wpcf7_read_contact_forms'   => 'edit_pages',
			'wpcf7_delete_contact_form'  => 'delete_pages',
			'wpcf7_delete_contact_forms' => 'delete_pages',
			'wpcf7_manage_integration'   => 'manage_options',
			'wpcf7_submit'               => 'read',
		);

		return $meta_caps;
	}

}

new VK_Showcase_Post_Types();
