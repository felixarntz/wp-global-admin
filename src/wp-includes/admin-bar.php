<?php
/**
 * Toolbar API: Top-level Toolbar functionality
 *
 * @package GlobalAdmin
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
function _ga_adjust_admin_bar( $admin_bar ) {
	if ( ! is_user_logged_in() || ! is_multinetwork() ) {
		return;
	}

	if ( ! is_user_global_admin() ) {
		return;
	}

	$wp_admin_bar->add_group( array(
		'parent' => 'my-sites',
		'id'     => 'my-sites-global-admin',
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'my-sites-global-admin',
		'id'     => 'global-admin',
		'title'  => __( 'Global Admin', 'global-admin' ),
		'href'   => global_admin_url(),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-d',
		'title'  => __( 'Dashboard' ),
		'href'   => global_admin_url(),
	) );

	/**
	 * Filters whether the Networks item should be shown in the Global Admin menu of the Toolbar.
	 *
	 * @private
	 * @since 1.0.0
	 *
	 * @param bool Whether to show the item. Defaults to false.
	 */
	if ( apply_filters( '_global_admin_show_admin_bar_networks', false ) ) {
		$wp_admin_bar->add_menu( array(
			'parent' => 'global-admin',
			'id'     => 'global-admin-d',
			'title'  => __( 'Dashboard' ),
			'href'   => add_query_arg( array( 'page' => 'networks' ), global_admin_url( 'admin.php' ) ),
		) );
	}

	$wp_admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-u',
		'title'  => __( 'Users' ),
		'href'   => global_admin_url( 'users.php' ),
	) );

	$wp_admin_bar->add_menu( array(
		'parent' => 'global-admin',
		'id'     => 'global-admin-o',
		'title'  => __( 'Settings' ),
		'href'   => global_admin_url( 'settings.php' ),
	) );
}
add_action( 'admin_bar_menu', '_ga_adjust_admin_bar', 19, 1 );
