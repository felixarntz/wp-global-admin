<?php
/**
 * Add new user global administration panel.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() ) {
	wp_die( __( 'Multisite support is not enabled.' ) );
}

//TODO: implement this, allow to grant global admin privileges
