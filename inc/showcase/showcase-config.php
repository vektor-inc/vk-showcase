<?php
/**
 * Showcace Config File
 *
 * @package VK Showcace
 */

if ( ! function_exists( 'vksc_themes_hook' ) ) {
	/**
	 * Theme Hook Array
	 *
	 * @param array $theme_hook_array themes and hooks of the themes.
	 */
	function vksc_themes_hook( $theme_hook_array ) {
		$theme_hooks      = array(
			'lightning'     => 'lightning_loop_before',
			'lightning-pro' => 'lightning_loop_before',
			'katawara'      => 'katawara_loop_before',
		);
		$theme_hook_array = array_merge( $theme_hook_array, $theme_hooks );
		return $theme_hook_array;
	}
	add_filter( 'vksc_theme_hook_array', 'vksc_themes_hook' );
}

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

if ( ! class_exists( 'VK_Showcase_Author_Archive' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'package/class-vk-showcase-author-archive.php';
}
