<?php
require_once DINAMIZE_PLUGIN_DIR . '/includes/capabilities.php';

add_action( 'plugins_loaded', 'dinamize' );

function dinamize() {
	/* Shortcodes */
	add_shortcode( 'dinamize-form', 'dinamize_form_tag_func' );
}
?>