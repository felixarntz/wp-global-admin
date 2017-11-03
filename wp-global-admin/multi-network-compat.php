<?php
/**
 * Compatibility with WP Multi Network plugin
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the network menus for WP Multi Network to be in the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_adjust_network_menus() {
	$admin = wpmn()->admin;
	if ( is_null( $admin ) ) {
		return;
	}

	remove_action( 'admin_menu', array( $admin, 'admin_menu' ) );
	remove_action( 'network_admin_menu', array( $admin, 'network_admin_menu' ) );
	remove_action( 'network_admin_menu', array( $admin, 'network_admin_menu_separator' ) );

	if ( is_multinetwork() ) {
		add_action( 'global_admin_menu', array( $admin, 'network_admin_menu' ) );
		add_action( 'global_admin_menu', '_ga_adjust_networks_menu_position', 11 );
	}
}
add_action( 'init', '_ga_adjust_network_menus' );

/**
 * Adjusts the position of the Networks admin menu in the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_adjust_networks_menu_position() {
	global $menu;

	if ( ! isset( $menu[-1] ) ) {
		return;
	}

	$networks_menu = $menu[-1];
	if ( 'networks' !== $networks_menu[2] ) {
		return;
	}

	unset( $menu[-1] );

	if ( isset( $menu[5] ) ) {
		$position = 5 + substr( base_convert( md5( $networks_menu[2] . $networks_menu[0] ), 16, 10 ) , -5 ) * 0.00001;
		$menu[ "$position" ] = $networks_menu;
	} else {
		$menu[5] = $networks_menu;
	}
}

/**
 * Adjusts the URL to the networks admin page to be part of the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $url  The original URL.
 * @param array  $args Additional query arguments for the URL.
 * @return string The adjusted URL.
 */
function _ga_adjust_networks_admin_url( $url, $args ) {
	if ( ! is_multinetwork() ) {
		return $url;
	}

	$args = wp_parse_args( $args, array(
		'page' => 'networks',
	) );

	return add_query_arg( $args, global_admin_url( 'admin.php' ) );
}
add_filter( 'edit_networks_screen_url', '_ga_adjust_networks_admin_url', 10, 2 );

/**
 * Adjusts the detection of which networks belong to a user.
 *
 * Users who are a global admin have full capabilities on all networks.
 *
 * @since 1.0.0
 * @access private
 *
 * @param array|null $networks Original array of network IDs or null.
 * @param int        $user_id  User ID to get networks for.
 * @return array|false Array of network IDs or false if no IDs.
 */
function _ga_user_has_networks( $networks, $user_id ) {
	if ( ! is_multinetwork() ) {
		return $networks;
	}

	$all_networks = get_networks( array(
		'fields' => 'ids',
	) );

	$user = get_user_by( 'id', $user_id );

	if ( $user->has_cap( 'manage_networks' ) ) {
		$user_networks = $all_networks;
	} else {
		$user = get_userdata( $user_id );
		$user_networks = array();
		foreach ( $all_networks as $network_id ) {
			$network_admins = get_network_option( $network_id, 'site_admins', array() );
			if ( in_array( $user->user_login, $network_admins, true ) ) {
				$user_networks[] = $network_id;
			}
		}
	}

	if ( empty( $user_networks ) ) {
		$user_networks = false;
	}

	return $user_networks;
}
add_filter( 'networks_pre_user_is_network_admin', '_ga_user_has_networks', 10, 2 );

// Internal filter.
add_filter( '_global_admin_show_admin_bar_networks', '__return_true' );
