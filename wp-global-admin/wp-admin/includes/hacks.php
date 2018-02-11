<?php
/**
 * Hack functions.
 *
 * Global Admin integrates deeply into WordPress Core.
 * Unfortunately we need to hacks to make it happen in a plugin.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the ID and base of the current screen if in the global admin.
 *
 * This is very hacky, but the class is final and just not extendable at all.
 *
 * @since 1.0.0
 * @access private
 *
 * @param WP_Screen $wp_screen Current screen.
 */
function _ga_adjust_global_admin_screen( $wp_screen ) {
	if ( defined( 'WP_GLOBAL_ADMIN' ) && WP_GLOBAL_ADMIN ) {
		$wp_screen->id   .= '-global';
		$wp_screen->base .= '-global';
	}
}
add_action( 'current_screen', '_ga_adjust_global_admin_screen' );

/**
 * Adjusts the $self global to be set correctly in the global admin.
 *
 * The 'parent_file' filter is simply used because it fires in the right location.
 * The filter value is passed through without being modified.
 *
 * @since 1.0.0
 * @access private
 *
 * @global string $self Current admin file.
 *
 * @param string $parent_file Parent file.
 * @return string Unmodified parent file.
 */
function _ga_adjust_global_admin_self( $parent_file ) {
	global $self;

	if ( defined( 'WP_GLOBAL_ADMIN' ) && WP_GLOBAL_ADMIN ) {
		$self = preg_replace( '|^.*/wp-admin/global/|i', '', $_SERVER['PHP_SELF'] );
	}

	return $parent_file;
}
add_filter( 'parent_file', '_ga_adjust_global_admin_self' );

/**
 * This is an incredibly hacky attempt to work with menus in the global admin.
 * Looks like there's no other way to do it from a plugin.
 *
 * @since 1.0.0
 * @access private
 */
function _ga_create_global_admin_menu() {
	global $menu, $submenu, $_wp_real_parent_file, $pagenow;

	if ( ! is_global_admin() ) {
		return;
	}

	if ( isset( $_wp_real_parent_file ) ) {
		unset( $_wp_real_parent_file );
	}

	$menu    = array();
	$submenu = array();

	remove_all_actions( '_admin_menu' );

	if ( 'global.php' === $pagenow ) {
		preg_match( '#/wp-admin/global/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches );

		$pagenow = $self_matches[1];
		$pagenow = trim( $pagenow, '/' );
		$pagenow = preg_replace( '#\?.*?$#', '', $pagenow );
		if ( '' === $pagenow || 'index' === $pagenow || 'index.php' === $pagenow ) {
			$pagenow = 'index.php';
		} else {
			preg_match( '#(.*?)(/|$)#', $pagenow, $self_matches );

			$pagenow = strtolower( $self_matches[1] );
			if ( '.php' !== substr( $pagenow, -4, 4 ) ) {
				$pagenow .= '.php';
			}
		}
	}

	require_once( GA_PATH . 'wp-global-admin/wp-admin/global/menu.php' );

	/**
	 * Fires before the administration menu loads in the global admin.
	 *
	 * The hook fires before menus and sub-menus are removed based on user privileges.
	 *
	 * @private
	 * @since 1.0.0
	 */
	do_action( '_global_admin_menu' );
}
add_action( '_admin_menu', '_ga_create_global_admin_menu', 0 );

/**
 * This is another incredibly hacky attempt to work with menus in the global admin.
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
	 * Fires before the administration menu loads in the global admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context Empty context.
	 */
	do_action( 'global_admin_menu', '' );
}
add_action( 'admin_menu', '_ga_trigger_global_admin_menu_hook', 0 );

/**
 * Adjusts the title for the title tag in the global administration panel.
 *
 * If it was in Core, it would happen directly in `wp-admin/admin-header.php`.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $admin_title Original admin title.
 * @param string $title       Original title.
 * @return string Modified admin title if in global administration panel.
 */
function _ga_adjust_admin_title( $admin_title, $title ) {
	if ( ! is_global_admin() ) {
		return $admin_title;
	}

	$new_admin_title = __( 'Global Admin', 'wp-global-admin' );

	if ( false === strpos( $admin_title, '&lsaquo;' ) ) {
		$admin_title = sprintf( __( '%1$s &#8212; WordPress' ), $new_admin_title );
	} else {
		$admin_title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), $title, $new_admin_title );
	}

	return $admin_title;
}
add_filter( 'admin_title', '_ga_adjust_admin_title', 10, 2 );
