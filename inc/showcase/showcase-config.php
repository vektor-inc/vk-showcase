<?php
/**
 * Showcace Config File
 *
 * @package VK Showcace
 */

if ( ! class_exists( 'VK_Showcase_Post_Types' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-post-types.php';
}

if ( ! class_exists( 'VK_Showcase_Users' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-users.php';
}

if ( ! class_exists( 'VK_Showcase_Contact_Form' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-contact-form.php';
}

if ( ! class_exists( 'VK_Showcase_Author_Archive' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-author-archive.php';
}
