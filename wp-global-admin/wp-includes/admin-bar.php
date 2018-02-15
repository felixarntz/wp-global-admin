<?php
/**
 * Toolbar API: Top-level Toolbar functionality
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Adds links to the Global Administration panel to the Toolbar as necessary.
 *
 * @since 1.0.0
 * @access private
 *
 * @param WP_Admin_Bar $admin_bar The Toolbar instance.
 */
function _ga_adjust_admin_bar_my_sites_menu( $admin_bar ) {
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_global' ) ) {
		return;
	}

	$admin_bar->add_group( array(
		'parent' => 'my-sites',
		'id'     => 'my-sites-global-admin',
	) );

	$admin_bar->add_menu( array(
		'parent' => 'my-sites-global-admin',
		'id'     => 'global-admin',
		'title'  => __( 'Global Admin', 'wp-global-admin' ),
		'href'   => global_admin_url(),
	) );

	$admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-d',
		'title'  => __( 'Dashboard' ),
		'href'   => global_admin_url(),
	) );

	/**
	 * Filters whether the Networks item should be shown in the Global Admin menu of the Toolbar.
	 *
	 * @since 1.0.0
	 *
	 * @param bool Whether to show the item. Defaults to false.
	 */
	if ( apply_filters( '_global_admin_show_admin_bar_networks', false ) ) {
		$admin_bar->add_menu( array(
			'parent' => 'global-admin',
			'id'     => 'global-admin-n',
			'title'  => __( 'Networks', 'wp-global-admin' ),
			'href'   => add_query_arg( array( 'page' => 'networks' ), global_admin_url( 'admin.php' ) ),
		) );
	}

	$admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-u',
		'title'  => __( 'Users' ),
		'href'   => global_admin_url( 'users.php' ),
	) );

	$admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-o',
		'title'  => __( 'Settings' ),
		'href'   => global_admin_url( 'settings.php' ),
	) );
}

/**
 * Adjusts the View Site link in the Toolbar as necessary.
 *
 * @since 1.0.0
 * @access private
 *
 * @param WP_Admin_Bar $admin_bar The Toolbar instance.
 */
function _ga_adjust_admin_bar_site_menu( $admin_bar ) {
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_global' ) ) {
		return;
	}

	$blogname = sprintf( __( 'Global Admin: %s' ), esc_html( get_global_option( 'global_name' ) ) );

	$title = wp_html_excerpt( $blogname, 40, '&hellip;' );

	$admin_bar->add_menu( array(
		'id'    => 'site-name',
		'title' => $title,
		'href'  => home_url( '/' ),
	) );

	$admin_bar->add_menu( array(
		'parent' => 'site-name',
		'id'     => 'view-site',
		'title'  => __( 'Visit Site' ),
		'href'   => home_url( '/' ),
	) );
}

/**
 * Adjusts the Toolbar output if the current setup is a multinetwork.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_initialize_admin_bar_changes() {
	if ( ! is_multinetwork() ) {
		return;
	}

	add_action( 'admin_bar_menu', '_ga_adjust_admin_bar_my_sites_menu', 19, 1 );

	// Only change the following if we're in the global administration panel.
	if ( ! is_global_admin() ) {
		return;
	}

	remove_action( 'admin_bar_menu', 'wp_admin_bar_site_menu', 30 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
	remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 70 );

	add_action( 'admin_bar_menu', '_ga_adjust_admin_bar_site_menu', 30, 1 );
}
add_action( 'add_admin_bar_menus', '_ga_initialize_admin_bar_changes' );
