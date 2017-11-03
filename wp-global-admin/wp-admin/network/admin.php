<?php
/**
 * WordPress Network Administration Bootstrap
 *
 * Helper file to load the WordPress Core Network Administration Bootstrap file
 *
 * @package WPGlobalAdmin
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

/** Load WordPress Administration Bootstrap */
require_once( _ga_detect_abspath() . '/wp-admin/network/admin.php' );
