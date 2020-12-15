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
		add_action( 'init', array( __CLASS__, 'add_creator_role' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'remove_menu_link' ), 10, 2 );
		add_action( 'show_password_fields', array( __CLASS__, 'add_allow_contact' ) );
		add_action( 'profile_update', array( __CLASS__, 'update_allow_contact' ), 10, 2 );
		// add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
		// add_filter( 'woocommerce_disable_admin_bar', '__return_false' );
	}

	/**
	 * Add Creator Role
	 */
	public static function add_creator_role() {

		// 権限を変更する時は一旦削除
		// remove_role( 'creator' );

		add_role(
			'creator',
			__( '制作者', 'vk-showcase' ),
			array(
				'read'         => true,
				'edit_posts'   => true,
				'edit_published_posts'   => true,
				'delete_posts' => true,
				'upload_files' => true,
			)
		);
	}

	/**
	 * Remove Menu Link
	 */
	public static function remove_menu_link() {
		if ( current_user_can( 'creator' ) ) {
			remove_menu_page( 'edit-comments.php' );
			remove_menu_page( 'tools.php' );
		}
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
				<th scope="row"><?php _e( '制作問い合わせを受け付ける', 'vk-showcase' ); ?></th>
				<td>
					<label><input type="checkbox" name="allow_contact" <?php checked( $user_meta, true, true ) ?>><?php _e( '制作問い合わせを受け付ける', 'vk-showcase' ); ?></label>
<?php
// role確認
// if ( current_user_can( 'administrator' ) ){
// 	$role = get_role( 'creator' );
// 	print '<pre style="text-align:left">';print_r($role);print '</pre>';
// }
?>
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
		if ( current_user_can( 'creator' ) && defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			if ( isset( $_POST['allow_contact'] ) ) {
				$allow_contact = true;
			} else {
				$allow_contact = false;
			}
			update_user_meta( $user_id, 'allow_contact', $allow_contact );
		}
	}
}

new VK_Showcase_Users();
