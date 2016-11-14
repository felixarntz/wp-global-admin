<?php
/**
 * Core User Role & Capabilities API
 *
 * @package GlobalAdmin
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

	switch ( $cap ) {
		case 'list_networks':
		case 'create_networks':
		case 'delete_networks':
			$caps = array( 'manage_networks' );
			break;
		case 'edit_user':
			if ( ! current_user_can( 'manage_global_users' ) && isset( $args[0] ) ) {
				$user = get_userdata( $args[0] );
				if ( $user->has_cap( 'manage_global_users' ) ) {
					$caps[] = 'do_not_allow';
				}
			}
			break;
	}

	return $caps;
}
add_filter( 'map_meta_cap', 'ga_map_meta_cap', 10, 4 );

/**
 * Retrieves the global WP_Global_Roles instance and instantiates it if necessary.
 *
 * @since 1.0.0
 *
 * @global WP_Global_Roles $wp_global_roles WP_Global_Roles global instance.
 *
 * @return WP_Global_Roles WP_Global_Roles global instance if not already instantiated.
 */
if ( ! function_exists( 'wp_global_roles' ) ) :
function wp_global_roles() {
	global $wp_global_roles;

	if ( ! isset( $wp_global_roles ) ) {
		$wp_global_roles = new WP_Global_Roles();
	}
	return $wp_global_roles;
}
endif;

/**
 * Retrieve global role object.
 *
 * @since 1.0.0
 *
 * @param string $role Network role name.
 * @return WP_Global_Role|null WP_Global_Role object if found, null if the role does not exist.
 */
if ( ! function_exists( 'get_global_role' ) ) :
function get_global_role( $role ) {
	return wp_global_roles()->get_role( $role );
}
endif;

/**
 * Add global role, if it does not exist.
 *
 * @since 1.0.0
 *
 * @param string $role Network role name.
 * @param string $display_name Display name for role.
 * @param array $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
 * @return WP_Global_Role|null WP_Global_Role object if role is added, null if already exists.
 */
if ( ! function_exists( 'add_global_role' ) ) :
function add_global_role( $role, $display_name, $capabilities = array() ) {
	if ( empty( $role ) ) {
		return;
	}
	return wp_global_roles()->add_role( $role, $display_name, $capabilities );
}
endif;

/**
 * Remove global role, if it exists.
 *
 * @since 1.0.0
 *
 * @param string $role Network role name.
 */
if ( ! function_exists( 'remove_global_role' ) ) :
function remove_global_role( $role ) {
	wp_global_roles()->remove_role( $role );
}
endif;
