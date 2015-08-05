<?php
require_once DINAMIZE_PLUGIN_DIR . '/includes/capabilities.php';

add_action( 'plugins_loaded', 'dinamize' );

function dinamize() {
	/* Shortcodes */
	add_shortcode( 'dinamize-form', 'dinamize_form_tag_func' );
}

/*
add_action( 'init', 'dinamize_init' );

function dinamize_init() {
	do_action( 'dinamize_init' );
}
add_action( 'admin_init', 'dinamize_upgrade' );

function dinamize_upgrade() {
	$opt = get_option( 'dinamize' );

	if ( ! is_array( $opt ) )
		$opt = array();

	$old_ver = isset( $opt['version'] ) ? (string) $opt['version'] : '0';
	$new_ver = DINAMIZE_VERSION;

	if ( $old_ver == $new_ver )
		return;

	do_action( 'dinamize_upgrade', $new_ver, $old_ver );

	$opt['version'] = $new_ver;

	update_option( 'dinamize', $opt );
}

add_action( 'activate_' . DINAMIZE_PLUGIN_BASENAME, 'dinamize_install' );

function dinamize_install() {
	if ( $opt = get_option( 'dinamize' ) )
		return;

	if ( get_posts( array( 'post_type' => 'dinamize_form' ) ) )
		return;

	$form = Dinamize_Forms::get_template( array(
			'title' => sprintf( __( 'Dinamize %d', 'dinamize' ), 1 ) ) );

	$form->save();
}
*/
?>