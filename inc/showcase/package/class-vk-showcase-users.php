<?php
/**
 * VK Showcase Users
 *
 * @package VK Showcase
 */

/**
 * VK Showcase Users
 */
class VK_Showcase_Users {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'add_role' ) );
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
				'read'             => true,
				'read_showcase'    => true,
				'edit_showcase'    => true,
				'delete_showcase'  => true,
				'edit_showcases'   => true,
				'delete_showcases' => true,
			)
		);
	}
}

new VK_Showcase_Users();
