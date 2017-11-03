<?php
/**
 * Add new user global administration panel.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'wp-global-admin' ) );
}

//TODO: implement this, allow to grant global admin privileges

wp_die( 'Work in Progress: This functionality is not yet implemented.' );
