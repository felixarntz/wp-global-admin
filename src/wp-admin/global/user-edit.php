<?php
/**
 * Edit user global administration panel.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'global-admin' ) );
}

//TODO: grant global admin privileges checkbox

require( ABSPATH . 'wp-admin/user-edit.php' );
