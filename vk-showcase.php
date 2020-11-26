<?php
/**
 * Plugin Name: VK Showcase
 * Plugin URI: https://github.com/vektor-inc/vk-showcase
 * Description: This plugin for filter search.
 * Version: 0.0.1
 * Author:  Vektor,Inc.
 * Author URI: https://vektor-inc.co.jp
 * Text Domain: vk-showcase
 * License: GPL 2.0 or Later
 *
 * @package VK Showcase
 */

defined( 'ABSPATH' ) || exit;

define( 'VKSC_PATH', plugin_dir_path( __FILE__ ) );

$data = get_file_data( __FILE__, array( 'version' => 'Version' ) );
define( 'VKSC_VERSION', $data['version'] );

global $vkfs_prefix;
$vkfs_prefix = apply_filters( 'vkfs_prefix', 'VK ' );

load_plugin_textdomain( 'vk-showcase', false, VKSC_PATH . '/languages/' );

require_once VKSC_PATH . 'inc/showcase/showcase-config.php';