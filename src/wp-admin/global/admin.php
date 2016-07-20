<?php
/**
 * WordPress Global Administration Bootstrap
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

function _ga_detect_abspath() {
	$current = __FILE__;

	for ( $i = 0; $i < 7; $i++ ) {
		$current = dirname( $current );
	}

	if ( file_exists( $current . '/wp-settings.php' ) ) {
		return $current;
	}

	// Let's assume that most people use these names when having WP in a subdirectory separate from
	// the wp-content directory.
	$wp_core_dirs = array( 'wordpress', 'wp', 'core' );

	foreach ( $wp_core_dirs as $wp_core_dir ) {
		if ( file_exists( $current . '/' . $wp_core_dir . '/wp-settings.php' ) ) {
			return $current . '/' . $wp_core_dir;
		}
	}

	die( 'WordPress Core directory could not be detected.' );
}

define( 'WP_GLOBAL_ADMIN', true );

/** Load WordPress Administration Bootstrap */
require_once( _ga_detect_abspath() . '/wp-admin/admin.php' );

// This would be it if it was part of Core.
//require_once( dirname( dirname( __FILE__ ) ) . '/admin.php' );

if ( ! is_multisite() ) {
	wp_die( __( 'Multisite support is not enabled.' ) );
}

if ( ! has_global_admin() ) {
	wp_die( __( 'The global admin backend is not enabled.', 'global-admin' ) );
}

$current_network = get_network();
$main_network = get_network( get_main_network_id() );

$redirect_global_admin_request = 0 !== strcasecmp( $current_network->domain, $main_network->domain ) || 0 !== strcasecmp( $current_network->path, $main_network->path );

/**
 * Filters whether to redirect the request to the Global Admin.
 *
 * @since 1.0.0
 *
 * @param bool $redirect_global_admin_request Whether the request should be redirected.
 */
$redirect_global_admin_request = apply_filters( 'redirect_global_admin_request', $redirect_global_admin_request );
if ( $redirect_global_admin_request ) {
	wp_redirect( global_admin_url() );
	exit;
}

unset( $current_network );
unset( $main_network );
unset( $redirect_global_admin_request );
