<?php
/**
 * Compatibility with WP Multi Network plugin
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Whenever someone creates a new network, they are assumed to be a global admin.
 *
 * If they're not technically a global admin yet, let's make them one. This function
 * basically triggers the global admin functionality and backend to be available if it isn't yet.
 *
 * TODO: The WPMN filter should pass the arguments as well so we can actually detect the real user ID
 * and not base it on the assumption that it's the current user.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int $new_network_id The ID of the newly created network.
 */
function _ga_set_global_admin_on_network_creation( $new_network_id, $args = array() ) {
	if ( isset( $args['user_id'] ) ) {
		$user_id = $args['user_id'];
	} else {
		$user_id = get_current_user_id();
	}

	if ( $user_id == get_current_user_id() ) {
		$user = wp_get_current_user();
	} else {
		$user = get_userdata( $user_id );
	}

	if ( ! has_global_admin() ) {
		update_global_option( 'global_admins', array( $user->user_login ) );
	}
}
add_action( 'add_network', '_ga_set_global_admin_on_network_creation', 10, 2 );

//TODO: move networks menu from network admin to global admin backend, remove it from regular admin menu
/**
 * Adjusts the network menus for WP Multi Network to be in the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_adjust_network_menus() {
	if ( ! has_global_admin() ) {
		return;
	}

	// This does not work yet, currently cannot access the admin instance of WP Multi Network.
	$admin = wpmn()->admin;
	if ( is_null( $admin ) ) {
		return;
	}

	remove_action( 'admin_menu', array( $admin, 'admin_menu' ) );
	remove_action( 'network_admin_menu', array( $admin, 'network_admin_menu' ) );
	remove_action( 'network_admin_menu', array( $admin, 'network_admin_menu_separator' ) );
}
add_action( 'init', '_ga_adjust_network_menus' );

/**
 * Adjusts the URL to the networks admin page to be part of the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $url The original URL.
 * @return string The adjusted URL.
 */
function _ga_adjust_networks_admin_url( $url, $args ) {
	if ( ! has_global_admin() ) {
		return $url;
	}

	$args = wp_parse_args( $args, array( 'page' => 'networks' ) );

	return add_query_arg( $args, global_admin_url( 'admin.php' ) );
}
add_filter( 'edit_networks_screen_url', '_ga_adjust_networks_admin_url', 10, 2 );
