<?php
/**
 * Edit user global administration panel.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() ) {
	wp_die( __( 'Multisite support is not enabled.' ) );
}

//TODO: grant global admin privileges checkbox

require( ABSPATH . 'wp-admin/user-edit.php' );
