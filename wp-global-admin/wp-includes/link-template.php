<?php
/**
 * WordPress Link Template Functions
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

if ( ! function_exists( 'global_site_url' ) ) :

	/**
	 * Retrieves the global site URL.
	 *
	 * Returns the site URL with the appropriate protocol, 'https' if
	 * is_ssl() and 'http' otherwise. If $scheme is 'http' or 'https', is_ssl() is
	 * overridden.
	 *
	 * @since 1.0.0
	 *
	 * @see set_url_scheme()
	 *
	 * @param string $path   Optional. Path relative to the site URL. Default empty.
	 * @param string $scheme Optional. Scheme to give the site URL context. Accepts
	 *                       'http', 'https', or 'relative'. Default null.
	 * @return string Site URL link with optional path appended.
	 */
	function global_site_url( $path = '', $scheme = null ) {
		if ( ! is_multinetwork() ) {
			return network_site_url( $path, $scheme );
		}

		$main_network_id = get_main_network_id();

		$main_network = get_network( $main_network_id );

		if ( 'relative' == $scheme ) {
			$url = $main_network->path;
		} else {
			$url = set_url_scheme( 'http://' . $main_network->domain . $main_network->path, $scheme );
		}

		if ( $path && is_string( $path ) ) {
			$url .= ltrim( $path, '/' );
		}

		/**
		 * Filters the global site URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $url    The complete global site URL including scheme and path.
		 * @param string      $path   Path relative to the global site URL. Blank string if
		 *                            no path is specified.
		 * @param string|null $scheme Scheme to give the URL context. Accepts 'http', 'https',
		 *                            'relative' or null.
		 */
		return apply_filters( 'global_site_url', $url, $path, $scheme );
	}

endif;

if ( ! function_exists( 'global_home_url' ) ) :

	/**
	 * Retrieves the global home URL.
	 *
	 * Returns the home URL with the appropriate protocol, 'https' is_ssl()
	 * and 'http' otherwise. If `$scheme` is 'http' or 'https', `is_ssl()` is
	 * overridden.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $path   Optional. Path relative to the home URL. Default empty.
	 * @param  string $scheme Optional. Scheme to give the home URL context. Accepts
	 *                        'http', 'https', or 'relative'. Default null.
	 * @return string Home URL link with optional path appended.
	 */
	function global_home_url( $path = '', $scheme = null ) {
		if ( ! is_multinetwork() ) {
			return network_home_url( $path, $scheme );
		}

		$main_network_id = get_main_network_id();

		$main_network = get_network( $main_network_id );
		$orig_scheme = $scheme;

		if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ) ) ) {
			$scheme = is_ssl() && ! is_admin() ? 'https' : 'http';
		}

		if ( 'relative' == $scheme ) {
			$url = $main_network->path;
		} else {
			$url = set_url_scheme( 'http://' . $main_network->domain . $main_network->path, $scheme );
		}

		if ( $path && is_string( $path ) ) {
			$url .= ltrim( $path, '/' );
		}

		/**
		 * Filters the global home URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string      $url         The complete global home URL including scheme and path.
		 * @param string      $path        Path relative to the global home URL. Blank string
		 *                                 if no path is specified.
		 * @param string|null $orig_scheme Scheme to give the URL context. Accepts 'http', 'https',
		 *                                 'relative' or null.
		 */
		return apply_filters( 'global_home_url', $url, $path, $orig_scheme);
	}

endif;

if ( ! function_exists( 'global_admin_url' ) ) :

	/**
	 * Retrieves the URL to the global admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path   Optional path relative to the admin URL. Default empty.
	 * @param string $scheme Optional. The scheme to use. Default is 'admin', which obeys force_ssl_admin()
	 *                       and is_ssl(). 'http' or 'https' can be passed to force those schemes.
	 * @return string Admin URL link with optional path appended.
	 */
	function global_admin_url( $path = '', $scheme = 'admin' ) {
		if ( ! is_multinetwork() ) {
			return network_admin_url( $path, $scheme );
		}

		// This would be different if it was part of core.
		$plugin_dir_relative = str_replace( home_url( '/' ), '', GA_URL );
		$url                 = global_home_url( $plugin_dir_relative . 'wp-global-admin/wp-admin/global/', $scheme );

		if ( $path && is_string( $path ) ) {
			$url .= ltrim( $path, '/' );
		}

		/**
		 * Filters the global admin URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url  The complete global admin URL including scheme and path.
		 * @param string $path Path relative to the global admin URL. Blank string if
		 *                     no path is specified.
		 */
		return apply_filters( 'global_admin_url', $url, $path );
	}

endif;

/**
 * Adjusts the self admin URL to consider the Global Administration panel.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $url    The complete URL including scheme and path.
 * @param string $path   Path relative to the URL. Blank string if no path is specified.
 * @param string $scheme The scheme to use.
 * @return string Possibly modified URL.
 */
function _ga_adjust_self_admin_url( $url, $path, $scheme ) {
	if ( ! is_global_admin() ) {
		return $url;
	}

	return global_admin_url( $path, $scheme );
}
add_filter( 'self_admin_url', '_ga_adjust_self_admin_url', 10, 3 );

/**
 * Adjusts the edit profile URL to consider the Global Administration panel as well.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $url     The complete URL including scheme and path.
 * @param int    $user_id The user ID.
 * @param string $scheme  Scheme to give the URL context. Accepts 'http', 'https', 'login',
 *                        'login_post', 'admin', 'relative' or null.
 * @return string The adjusted URL.
 */
function _ga_adjust_edit_profile_url( $url, $user_id, $scheme ) {
	if ( ! is_global_admin() ) {
		return $url;
	}

	return global_admin_url( 'profile.php', $scheme );
}
add_filter( 'edit_profile_url', '_ga_adjust_edit_profile_url', 10, 3 );

/**
 * Adjusts the edit user link to consider the Global Administration panel as well.
 *
 * @since 1.0.0
 * @access private
 *
 * @param string $link    The edit user link.
 * @param int    $user_id The user ID.
 * @return string The adjusted link.
 */
function _ga_adjust_edit_user_link( $link, $user_id ) {
	if ( get_current_user_id() == $user_id || ! is_global_admin() ) {
		return $link;
	}

	return add_query_arg( 'user_id', $user_id, global_admin_url( 'user-edit.php' ) );
}
add_filter( 'get_edit_user_link', '_ga_adjust_edit_user_link', 10, 2 );
