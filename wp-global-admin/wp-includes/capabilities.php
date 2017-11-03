<?php
/**
 * Core User Role & Capabilities API
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Filters the meta map process to handle complex capability checks.
 *
 * Global admins have all capabilities. This function ensures that as well (in the rare case that
 * a global admin is not technically a super admin for a specific network). Regarding this hacky
 * approach in the function, there might be a better alternative...
 *
 * @since 1.0.0
 *
 * @param array  $caps    Returns the user's actual capabilities.
 * @param string $cap     Capability name.
 * @param int    $user_id The user ID.
 * @param array  $args    Adds the context to the cap. Typically the object ID.
 * @return array The mapped capabilities.
 */
function ga_map_meta_cap( $caps, $cap, $user_id, $args ) {
	if ( ! is_multinetwork() ) {
		return $caps;
	}

	$global_capabilities = array(
		'manage_global',
		'manage_networks',
		'manage_global_users',
		'manage_global_themes',
		'manage_global_plugins',
		'manage_global_options',
		// The following capabilities are part of WP Spider Cache, WP User Signups and WP Encrypt respectively.
		'manage_cache',
		'manage_user_signups',
		'manage_certificates',
	);

	/**
	 * Filters the capabilities that only global administrators should have in a multinetwork.
	 *
	 * @since 1.0.0
	 *
	 * @param array Array of global capabilities.
	 */
	$global_capabilities = apply_filters( 'global_admin_capabilities', $global_capabilities );

	switch ( $cap ) {
		case 'list_networks':
		case 'create_networks':
		case 'delete_networks':
			if ( in_array( 'manage_networks', $global_capabilities, true ) && ! is_global_administrator( $user_id ) ) {
				$caps = array( 'do_not_allow' );
			} else {
				$caps = array( 'manage_networks' );
			}
			break;
			break;
		case 'create_user_signups':
		case 'edit_user_signups':
		case 'activate_signup':
		case 'delete_signup':
		case 'edit_signup':
		case 'resend_signup':
			if ( in_array( 'manage_user_signups', $global_capabilities, true ) && ! is_global_administrator( $user_id ) ) {
				$caps = array( 'do_not_allow' );
			} else {
				$caps = array( 'manage_user_signups' );
			}
			break;
		case 'edit_user':
			if ( ! current_user_can( 'manage_global_users' ) && isset( $args[0] ) ) {
				$user = get_userdata( $args[0] );
				if ( $user->has_cap( 'manage_global_users' ) ) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
		default:
			if ( in_array( $cap, $global_capabilities, true ) && ! is_global_administrator( $user_id ) ) {
				$caps[] = 'do_not_allow';
			}
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'ga_map_meta_cap', 10, 4 );

if ( ! function_exists( 'get_global_administrators' ) ) :

/**
 * Retrieves a list of global administrators.
 *
 * @since 1.0.0
 *
 * @return array List of global administrator logins.
 */
function get_global_administrators() {
	global $global_administrators;

	if ( isset( $global_administrators ) ) {
		return $global_administrators;
	}

	return get_global_option( 'global_administrators', array() );
}

endif;

if ( ! function_exists( 'is_global_administrator' ) ) :

/**
 * Determines if a user is a global administrator.
 *
 * @since 1.0.0
 *
 * @param int $user_id Optional. ID of a user. Default is the current user.
 * @return bool True if the user is a global administrator, false otherwise.
 */
function is_global_administrator( $user_id = false ) {
	if ( ! $user_id || $user_id == get_current_user_id() ) {
		$user = wp_get_current_user();
	} else {
		$user = get_userdata( $user_id );
	}

	if ( ! $user || ! $user->exists() ) {
		return false;
	}

	if ( is_multinetwork() ) {
		$global_admins = get_global_administrators();

		return is_array( $global_admins ) && in_array( $user->user_login, $global_admins, true );
	}

	return is_super_admin( $user_id );
}

endif;
