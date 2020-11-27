<?php
/**
 * Showcace Config File
 *
 * @package VK Showcace
 */

if ( ! class_exists( 'VK_Showcase_Post_Types' ) &&  ! class_exists( 'VK_Showcase_Users' )  ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-post-types.php';
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-users.php';
}
