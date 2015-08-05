<?php

add_filter( 'map_meta_cap', 'dinamize_map_meta_cap', 10, 4 );

function dinamize_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'dinamize_edit_form' => DINAMIZE_ADMIN_READ_WRITE_CAPABILITY,
		'dinamize_edit_forms' => DINAMIZE_ADMIN_READ_WRITE_CAPABILITY,
		'dinamize_read_forms' => DINAMIZE_ADMIN_READ_CAPABILITY,
		'dinamize_delete_form' => DINAMIZE_ADMIN_READ_WRITE_CAPABILITY );

	$meta_caps = apply_filters( 'dinamize_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

?>