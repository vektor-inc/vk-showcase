<?php
/**
 * VK Showcase Custom Fields
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Custom Fields
 */
class VK_Showcase_Custom_Fields {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_fields' ) );
		add_filter( 'the_content', array( __CLASS__, 'display_fields'), 10 );
	}

	/**
	 * Add Meta Box
	 */
	public static function add_meta_box() {
		add_meta_box(
			'vk_showcase_site_infomation',
			__( '制作したサイトの情報', 'vk-showcase' ),
			array( __CLASS__,  'add_fields' ),
			'showcase',
			'normal',
			'default'
		);
	}

	/**
	 * Add Fileds
	 */
	public static function add_fields() {
		global $post;
		$fields  = '<label>';
		$fields .= __( 'サイト URL', 'vk-showcase' );
		$fields .= '<input type="text" name="showcase_site_url" value="' . get_post_meta( $post->ID, 'showcase_site_url', true ) . '" size="50" />';
		$fields .= '</label>';

		echo $fields;
	}

	/**
	 * Save Fields
	 */
	public static function save_fields( $post_id ) {
		if ( ! empty( $_POST['showcase_site_url'] ) ) {
			update_post_meta( $post_id, 'showcase_site_url', $_POST['showcase_site_url'] );
		} else {
			delete_post_meta( $post_id, 'showcase_site_url' );
		}
	}

	/**
	 * Display Fields
	 */
	public static function display_fields( $content ) {
		global $post;
		$site_button = '';
		$site_url    = ! empty( get_post_meta( $post->ID, 'showcase_site_url', true ) ) ? get_post_meta( $post->ID, 'showcase_site_url', true ) : '';

		if ( ! empty( $site_url ) ) {
			$site_button  = '<a class="btn btn-danger text-center btn-block btn-lg" href="' . $site_url . '">';
			$site_button .= __( 'このサイトを見に行く', 'vk-showcase' );
			$site_button .= '</a>';
		}

		return $content . $site_button;
	}

}

new VK_Showcase_Custom_Fields();
