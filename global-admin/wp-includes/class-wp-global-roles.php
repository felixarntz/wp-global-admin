<?php
/**
 * User API: WP_Global_Roles class
 *
 * @package WordPress
 * @subpackage Users
 * @since 4.8.0
 */

/**
 * Core class used to implement a global user roles API.
 *
 * The role option is simple, the structure is organized by role name that store
 * the name in value of the 'name' key. The capabilities are stored as an array
 * in the value of the 'capability' key.
 *
 *     array (
 *    		'rolename' => array (
 *    			'name' => 'rolename',
 *    			'capabilities' => array()
 *    		)
 *     )
 *
 * TODO: If it was in Core, the retrieving/updating option processes of WP_Roles would be
 * moved into a separate protected method for easier replacement and less duplicate code
 * in a subclass like WP_Global_Roles.
 *
 * @since 4.8.0
 */
class WP_Global_Roles extends WP_Roles {
	/**
	 * Set up the object properties.
	 *
	 * The role key is set to 'user_roles' and retrieved from the global options database table.
	 * If the $wp_global_user_roles global is set, then it will
	 * be used and the global role option will not be updated or used.
	 *
	 * @since 4.8.0
	 * @access protected
	 *
	 * @global array $wp_global_user_roles Used to set the 'roles' property value.
	 */
	protected function _init() {
		global $wp_global_user_roles;

		$this->role_key = 'user_roles';
		if ( ! empty( $wp_global_user_roles ) ) {
			$this->roles = $wp_global_user_roles;
			$this->use_db = false;
		} else {
			$this->roles = get_global_option( $this->role_key );
		}

		if ( empty( $this->roles ) )
			return;

		$this->role_objects = array();
		$this->role_names =  array();
		foreach ( array_keys( $this->roles ) as $role ) {
			$this->role_objects[ $role ] = new WP_Global_Role( $role, $this->roles[ $role ]['capabilities'] );
			$this->role_names[ $role ] = $this->roles[ $role ]['name'];
		}
	}

	/**
	 * Reinitialize the object
	 *
	 * Recreates the global role objects.
	 *
	 * @since 4.8.0
	 * @access public
	 */
	public function reinit() {
		// There is no need to reinit global roles ever.
	}

	/**
	 * Add role name with capabilities to list.
	 *
	 * Updates the list of roles, if the role doesn't already exist.
	 *
	 * The capabilities are defined in the following format `array( 'read' => true );`
	 * To explicitly deny a role a capability you set the value for that capability to false.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $display_name Role display name.
	 * @param array $capabilities List of role capabilities in the above format.
	 * @return WP_Role|void WP_Role object, if role is added.
	 */
	public function add_role( $role, $display_name, $capabilities = array() ) {
		if ( empty( $role ) || isset( $this->roles[ $role ] ) ) {
			return;
		}

		$this->roles[ $role ] = array(
			'name' => $display_name,
			'capabilities' => $capabilities
		);
		if ( $this->use_db ) {
			update_global_option( $this->role_key, $this->roles );
		}
		$this->role_objects[ $role ] = new WP_Global_Role( $role, $capabilities );
		$this->role_names[ $role ] = $display_name;
		return $this->role_objects[ $role ];
	}

	/**
	 * Remove role by name.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Role name.
	 */
	public function remove_role( $role ) {
		if ( ! isset( $this->role_objects[ $role ] ) )
			return;

		unset( $this->role_objects[ $role ] );
		unset( $this->role_names[ $role ] );
		unset( $this->roles[ $role ] );

		if ( $this->use_db ) {
			update_global_option( $this->role_key, $this->roles );
		}

		if ( get_global_option( 'default_role' ) == $role ) {
			update_global_option( 'default_role', 'subscriber' );
		}
	}

	/**
	 * Add capability to role.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $cap Capability name.
	 * @param bool $grant Optional, default is true. Whether role is capable of performing capability.
	 */
	public function add_cap( $role, $cap, $grant = true ) {
		if ( ! isset( $this->roles[ $role ] ) )
			return;

		$this->roles[ $role ]['capabilities'][ $cap ] = $grant;
		if ( $this->use_db ) {
			update_global_option( $this->role_key, $this->roles );
		}
	}

	/**
	 * Remove capability from role.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Role name.
	 * @param string $cap Capability name.
	 */
	public function remove_cap( $role, $cap ) {
		if ( ! isset( $this->roles[$role] ) )
			return;

		unset( $this->roles[$role]['capabilities'][$cap] );
		if ( $this->use_db ) {
			update_global_option( $this->role_key, $this->roles );
		}
	}
}
