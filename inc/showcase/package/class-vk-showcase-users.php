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
		add_action( 'show_password_fields', array( __CLASS__, 'add_allow_contact' ) );
		add_action( 'profile_update', array( __CLASS__, 'update_allow_contact' ), 10, 2 );
		// add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
		// add_filter( 'woocommerce_disable_admin_bar', '__return_false' );
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

	/**
	 * Add Allow Contact on Profile
	 */
	public static function add_allow_contact( $bool ) {
		global $profileuser;
		$user_meta = get_user_meta( $profileuser->ID, 'allow_contact', true );

		if ( current_user_can( 'creator' ) && defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			?>
			<tr>
				<th scope="row"><?php _e( 'Allow contact from customor', 'vk-showcase' ); ?></th>
				<td>
					<label><input type="checkbox" name="allow_contact" <?php checked( $user_meta, true, true ) ?>><?php _e( 'Allow contact from customor', 'vk-showcase' ); ?></label>
				</td>
			</tr>
			<?php
		}

		return $bool;
	}

	/**
	 * Update Allow Contact on Profile
	 */
	public static function update_allow_contact( $user_id, $old_user_data ) {
		if ( isset( $_POST['allow_contact'] ) ) {
			$allow_contact = true;
		} else {
			$allow_contact = false;
		}
		update_user_meta( $user_id, 'allow_contact', $allow_contact );
	}
}

new VK_Showcase_Users();
