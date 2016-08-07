<?php
/**
 * Hack functions.
 *
 * Global Admin integrates deeply into WordPress Core.
 * Unfortunately we need to hacks to make it happen in a plugin.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * This is an incredibly hacky attempt to work with menus in the Global Admin.
 * Looks like there's no other way to do it from a plugin.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_create_global_admin_menu() {
	global $menu, $submenu, $_wp_real_parent_file;

	if ( ! is_global_admin() ) {
		return;
	}

	if ( isset( $_wp_real_parent_file ) ) {
		unset( $_wp_real_parent_file );
	}

	$menu = array();
	$submenu = array();

	remove_all_actions( '_admin_menu' );

	require_once( GA_PATH . 'src/wp-admin/global/menu.php' );

	/**
	 * Fires before the administration menu loads in the Global Admin.
	 *
	 * The hook fires before menus and sub-menus are removed based on user privileges.
	 *
	 * @private
	 * @since 1.0.0
	 */
	do_action( '_global_admin_menu' );
}
add_action( '_admin_menu', '_ga_create_global_admin_menu', 1 );

/**
 * This is another incredibly hacky attempt to work with menus in the Global Admin.
 * Looks like there's no other way to do it from a plugin.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_trigger_global_admin_menu_hook() {
	if ( ! is_global_admin() ) {
		return;
	}

	remove_all_actions( 'admin_menu' );

	/**
	 * Fires before the administration menu loads in the Global Admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Empty context.
	 */
	do_action( 'global_admin_menu', '' );
}
add_action( 'admin_menu', '_ga_trigger_global_admin_menu_hook', 1 );

/**
 * Adds the Global Setup screen to the network administration menu if necessary.
 *
 * If it was in Core, that would happen directly in `wp-admin/network/menu.php`.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_add_global_setup_menu_item() {
	if ( ! defined( 'WP_ALLOW_MULTINETWORK' ) || ! WP_ALLOW_MULTINETWORK ) {
		return;
	}

	add_submenu_page( 'settings.php', __( 'Global Setup', 'global-admin' ), __( 'Global Setup', 'global-admin' ), 'manage_networks', GA_PATH . 'src/wp-admin/network/global.php' );
}
add_action( 'network_admin_menu', '_ga_add_global_setup_menu_item' );
