<?php
/**
 * These functions are needed to load WordPress.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

if ( ! function_exists( 'is_multinetwork' ) ) :

	/**
	 * If this is a multinetwork setup.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if a multinetwork, false otherwise.
	 */
	function is_multinetwork() {
		if ( ! is_multisite() ) {
			return false;
		}

		if ( defined( 'MULTINETWORK' ) ) {
			return MULTINETWORK;
		}

		$is_multinetwork = get_global_transient( 'is_multinetwork' );
		if ( false === $is_multinetwork ) {
			$networks = get_networks( array(
				'fields' => 'ids',
				'number' => 2,
			) );

			// Something other than booleans must be used here.
			$is_multinetwork = count( $networks ) > 1 ? 1 : 0;

			set_global_transient( 'is_multinetwork', $is_multinetwork, WEEK_IN_SECONDS );
		}

		return (bool) $is_multinetwork;
	}

endif;

if ( ! function_exists( 'is_global_admin' ) ) :

	/**
	 * Whether the current request is for the global administrative interface, e.g. `/wp-admin/global/`.
	 *
	 * Does not check if the user is a global administrator. Use is_global_administrator() for that.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if inside WordPress global administration panel.
	 */
	function is_global_admin() {
		// Unfortunately it's not possible to use the $current_screen global here since it cannot be adjusted.
		if ( defined( 'WP_GLOBAL_ADMIN' ) ) {
			return (bool) WP_GLOBAL_ADMIN;
		}

		return false;
	}

endif;
