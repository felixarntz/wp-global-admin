<?php
/**
 * Compatibility with WP User Signups plugin
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the global administrator capabilities.
 *
 * @since 1.0.0
 * @access private
 *
 * @param array $global_capabilities List of global capabilities.
 * @return array Modified list of global capabilities.
 */
function _ga_add_global_multinetwork_capabilities( $global_capabilities ) {
	$global_capabilities[] = 'manage_signups';

	return $global_capabilities;
}
add_filter( 'global_admin_capabilities', '_ga_add_global_multinetwork_capabilities' );

/**
 * Adjusts the network menus for WP User Signups to be in the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_adjust_user_signups_menus() {
	if ( ! is_multinetwork() ) {
		return;
	}

	remove_filter( 'map_meta_cap', 'wp_user_signups_map_meta_cap', 10 );

	remove_action( 'network_admin_menu', 'wp_user_signups_add_menu_item', 20 );
	remove_action( 'admin_menu', 'wp_user_signups_add_menu_item', 20 );

	add_action( 'global_admin_menu', '_ga_adjust_user_signups_menu_item', 20 );
}
add_action( 'init', '_ga_adjust_user_signups_menus' );

/**
 * Adds the User Signups menu to the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_adjust_user_signups_menu_item() {
	$hooks = array();

	$hooks[] = add_menu_page( esc_html__( 'Sign ups', 'wp-user-signups' ), esc_html__( 'Sign ups', 'wp-user-signups' ), 'manage_user_signups', 'user_signups', 'wp_user_signups_output_list_page', 'dashicons-flag', 11 );
	$hooks[] = add_submenu_page( 'user_signups', esc_html__( 'Add New Signup', 'wp-user-signups' ), esc_html__( 'Add New', 'wp-user-signups' ), 'edit_user_signups', 'user_signup_edit', 'wp_user_signups_output_edit_page' );

	if ( ! current_user_can( 'create_user_signups' ) ) {
		remove_submenu_page( 'user_signups', 'user_signup_edit' );
	}

	foreach ( $hooks as $hook ) {
		add_action( "load-{$hook}", 'wp_user_signups_handle_actions' );
		add_action( "load-{$hook}", 'wp_user_signups_load_list_table' );
		add_action( "load-{$hook}", 'wp_user_signups_add_screen_options' );
	}
}

/**
 * Adjusts the URL to the networks admin page to be part of the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $url       The original URL.
 * @param string $admin_url The base URL to the admin area.
 * @param array  $args      Additional query arguments for the URL.
 * @return string The adjusted URL.
 */
function _ga_adjust_user_signups_admin_url( $url, $admin_url, $args ) {
	if ( ! is_multinetwork() ) {
		return $url;
	}

	$admin_url = global_admin_url( 'admin.php' );

	return add_query_arg( $args, $admin_url );
}
add_filter( 'wp_user_signups_admin_url', '_ga_adjust_user_signups_admin_url', 10, 3 );
