<?php
/*
Plugin Name: Dinamize
Plugin URI: http://www.dinamize.com.br/wordpress_plugin
Description: Para agilizar seu tempo a Dinamize criou um plugin que possibilita a integração da ferramenta com seu site.
Version: 1.0.0
Author: Dinamize
Author URI: http://www.dinamize.com.br/
Text Domain: dinamize
Domain Path: /languages/
License: GPLv2
*/

defined( 'ABSPATH' ) or die( 'Esse código não foi feito para todos' );
define( 'DINAMIZE_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'DINAMIZE_ADMIN_READ_CAPABILITY' ) ) {
	define( 'DINAMIZE_ADMIN_READ_CAPABILITY', 'edit_posts' );
}

if ( ! defined( 'DINAMIZE_ADMIN_READ_WRITE_CAPABILITY' ) ) {
	define( 'DINAMIZE_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );
}

require_once DINAMIZE_PLUGIN_DIR . '/settings.php';
require_once DINAMIZE_PLUGIN_DIR . '/class.forms.php';
require_once DINAMIZE_PLUGIN_DIR . '/class.widget.php';

if ( is_admin() ) {
	require_once DINAMIZE_PLUGIN_DIR . "/admin/admin.php";
} else {
	dinamize_add_scripts();
}

function dinamize_init() {
	if(!session_id()) {
		session_start();
	}	
}

function dinamize_end() {
	session_destroy();
}

function dinamize_enqueue_style() {
	wp_enqueue_style( 'core', plugins_url('/css/dinamize.css', __FILE__), false );
}

function dinamize_enqueue_script() {
	wp_enqueue_script( 'my-js', plugins_url('/js/dinamize.js', __FILE__), array("jquery") );
}

function dinamize_add_scripts() {
	add_action( 'wp_enqueue_scripts', 'dinamize_enqueue_style' );
	add_action( 'wp_enqueue_scripts', 'dinamize_enqueue_script' );
} 

function dinamize_load_textdomain() {
	load_plugin_textdomain( 'dinamize', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action('init', 'dinamize_init', 1);
add_action('wp_logout', 'dinamize_end');
add_action('wp_login', 'dinamize_end');
// Translate
add_action( 'plugins_loaded', 'dinamize_load_textdomain' );
// Shortcode
add_shortcode( 'dinamize-form', 'dinamize_form_shortcode_handle' );
// Widget
add_action('widgets_init', create_function('', 'return register_widget("DinamizeWidget");'));
?>