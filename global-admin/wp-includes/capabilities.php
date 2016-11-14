<?php
/**
 * Core User Role & Capabilities API
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Filters the meta map process to distinguish between super admins and global admins.
 *
 * Capabilities that should only be available to global admins, but not to super admins must be
 * registered via `register_global_cap()`.
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
	global $_global_capabilities;

	if ( ! is_multinetwork() ) {
		return $caps;
	}

	// Global admins have all capabilities, so this hack ensures it.
	if ( is_user_global_admin( $user_id ) ) {
		return array( 'exist' );
	}

	if ( ! isset( $_global_capabilities ) ) {
		return $caps;
	}

	if ( ! in_array( $cap, $_global_capabilities ) ) {
		return $caps;
	}

	switch ( $cap ) {
		case 'edit_user':
			if ( ! is_user_global_admin( $user_id ) && isset( $args[0] ) && is_user_global_admin( $args[0] ) ) {
				$caps[] = 'do_not_allow';
			}
			break;
		default:
			if ( ! is_user_global_admin( $user_id ) ) {
				$caps[] = 'do_not_allow';
			}
			break;
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'ga_map_meta_cap', 10, 4 );

/**
 * Returns the global admins for this setup.
 *
 * In a Core implementation we could instead use a modified version of `get_super_admins()`.
 *
 * @since 1.0.0
 *
 * @return array Array of global admin logins.
 */
if ( ! function_exists( 'get_global_admins' ) ) :
function get_global_admins() {
	global $global_admins;

	if ( isset( $global_admins ) ) {
		return $global_admins;
	}

	return get_global_option( 'super_admins', array() );
}
endif;

/**
 * Checks whether a specific user is a global administrator.
 *
 * Naming of this function is sub-optimal. However it cannot be called `is_global_admin()`
 * since that function already exists to determine whether we are in the global admin backend.
 *
 * In a Core implementation we could instead use a modified version of `is_super_admin()`.
 *
 * @since 1.0.0
 *
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool True if the user is a global admin.
 */
if ( ! function_exists( 'is_user_global_admin' ) ) :
function is_user_global_admin( $user_id = false ) {
	if ( ! $user_id || $user_id == get_current_user_id() ) {
		$user = wp_get_current_user();
	} else {
		$user = get_userdata( $user_id );
	}

	if ( ! $user || ! $user->exists() ) {
		return false;
	}

	if ( ! is_multisite() ) {
		return $user->has_cap( 'delete_users' );
	}

	if ( ! is_multinetwork() ) {
		return $user->has_cap( 'manage_network' );
	}

	$global_admins = get_global_admins();

	return in_array( $user->user_login, $global_admins );
}
endif;

/**
 * Registers a global capability.
 *
 * Any capability registered here will only be available for global administrators. If no global
 * administrator is available, they will fallback to be granted to super admins instead.
 *
 * @since 1.0.0
 *
 * @param string|array $cap A single capability or an array of capabilities to register.
 */
if ( ! function_exists( 'register_global_cap' ) ) :
function register_global_cap( $cap ) {
	global $_global_capabilities;

	$cap = (array) $cap;

	if ( ! isset( $_global_capabilities ) ) {
		$_global_capabilities = array();
	}

	$_global_capabilities = array_unique( array_merge( $_global_capabilities, $cap ) );
}
endif;
