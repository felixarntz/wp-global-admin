<?php
/**
 * These functions are needed to load WordPress.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * If this is a Multinetwork setup.
 *
 * @since 1.0.0
 *
 * @return bool True if a Multinetwork, false otherwise.
 */
if ( !function_exists( 'is_multinetwork' ) ) :
function is_multinetwork() {
	if ( ! is_multisite() ) {
		return false;
	}

	if ( defined( 'MULTINETWORK' ) ) {
		return MULTINETWORK;
	}

	return false;
}
endif;

/**
 * Whether the current request is for the global administrative interface.
 *
 * e.g. `/wp-admin/global/`
 *
 * Does not check if the user is an administrator; current_user_can()
 * for checking roles and capabilities.
 *
 * @since 1.0.0
 *
 * @global WP_Screen $current_screen
 *
 * @return bool True if inside WordPress global administration pages.
 */
if ( ! function_exists( 'is_global_admin' ) ) :
function is_global_admin() {
	// It's not possible in a plugin to override this, therefore skip this check.
	// Unfortunately this can also cause issues with `is_blog_admin()` because it will return
	// true if we're in the global admin.
	/*if ( isset( $GLOBALS['current_screen'] ) ) {
		return $GLOBALS['current_screen']->in_admin( 'global' );
	}*/

	if ( defined( 'WP_GLOBAL_ADMIN' ) ) {
		return WP_GLOBAL_ADMIN;
	}

	return false;
}
endif;
