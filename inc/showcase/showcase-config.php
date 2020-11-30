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
	global $form_url;
	$form_url = apply_filters( 'vksc_form_url', 'https://showcase.vektor-inc.co.jp/production-request/' );
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-contact-form.php';
}
