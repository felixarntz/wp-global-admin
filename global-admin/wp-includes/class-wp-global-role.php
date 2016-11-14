<?php
/**
 * User API: WP_Global_Role class
 *
 * @package WordPress
 * @subpackage Users
 * @since 4.8.0
 */

/**
 * Core class used to extend the global user roles API.
 *
 * @since 4.8.0
 */
class WP_Global_Role extends WP_Role {
	/**
	 * Assign global role a capability.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $cap Capability name.
	 * @param bool $grant Whether global role has capability privilege.
	 */
	public function add_cap( $cap, $grant = true ) {
		$this->capabilities[$cap] = $grant;
		wp_global_roles()->add_cap( $this->name, $cap, $grant );
	}

	/**
	 * Removes a capability from a global role.
	 *
	 * This is a container for WP_Global_Roles::remove_cap() to remove the
	 * capability from the role. That is to say, that WP_Global_Roles::remove_cap()
	 * implements the functionality, but it also makes sense to use this class,
	 * because you don't need to enter the role name.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $cap Capability name.
	 */
	public function remove_cap( $cap ) {
		unset( $this->capabilities[$cap] );
		wp_global_roles()->remove_cap( $this->name, $cap );
	}

	/**
	 * Determines whether the  global role has the given capability.
	 *
	 * The capabilities is passed through the {@see 'global_role_has_cap'} filter.
	 * The first parameter for the hook is the list of capabilities the class
	 * has assigned. The second parameter is the capability name to look for.
	 * The third and final parameter for the hook is the role name.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $cap Capability name.
	 * @return bool True if the global role has the given capability. False otherwise.
	 */
	public function has_cap( $cap ) {
		/**
		 * Filters which capabilities a global role has.
		 *
		 * @since 4.8.0
		 *
		 * @param array  $capabilities Array of role capabilities.
		 * @param string $cap          Capability name.
		 * @param string $name         Network role name.
		 */
		$capabilities = apply_filters( 'global_role_has_cap', $this->capabilities, $cap, $this->name );

		if ( ! empty( $capabilities[$cap] ) ) {
			return $capabilities[$cap];
		}

		return false;
	}
}
