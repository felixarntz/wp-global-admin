<?php
/**
 * Action handler for Multisite Global Administration panels.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'wp-global-admin' ) );
}

if ( empty( $_GET['action'] ) ) {
	wp_redirect( global_admin_url() );
	exit;
}

/**
 * Fires the requested handler action in the Global Administration panel.
 *
 * The dynamic portion of the hook name, `$_GET['action']`, refers to the name
 * of the requested action.
 *
 * @since 1.0.0
 */
do_action( 'global_admin_edit_' . $_GET['action'] );

wp_redirect( global_admin_url() );
exit();
