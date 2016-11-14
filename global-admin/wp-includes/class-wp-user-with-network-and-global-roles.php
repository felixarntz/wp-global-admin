<?php
/**
 * User API: WP_User_With_Network_And_Global_Roles class
 *
 * @package WordPress
 * @subpackage Users
 * @since 4.8.0
 */

/**
 * Core class used to implement the WP_User object with support for network roles.
 *
 * @since 4.8.0
 */
class WP_User_With_Network_And_Global_Roles extends WP_User_With_Network_Roles {
	/**
	 * The individual global capabilities the user has been given.
	 *
	 * @since 4.8.0
	 * @access public
	 * @var array
	 */
	public $global_caps = array();

	/**
	 * User metadata option name for global caps.
	 *
	 * @since 4.8.0
	 * @access public
	 * @var string
	 */
	public $global_cap_key;

	/**
	 * The global roles the user is part of.
	 *
	 * @since 4.8.0
	 * @access public
	 * @var array
	 */
	public $global_roles = array();

	/**
	 * Constructor.
	 *
	 * Retrieves the userdata and passes it to WP_User::init().
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int|string|stdClass|WP_User $id User's ID, a WP_User object, or a user object from the DB.
	 * @param string $name Optional. User's username
	 * @param int $blog_id Optional Site ID, defaults to current site.
	 */
	public function __construct( $id = 0, $name = '', $blog_id = '', $network_id = '' ) {
		parent::__construct( $id, $name, $blog_id, $network_id );

		$this->_init_global_caps();
	}

	/**
	 * Set up capability object properties.
	 *
	 * Will set the value for the 'global_cap_key' property to the database table
	 * base prefix, followed by 'global_capabilities'.
	 * Will then check to see if the property matching the 'global_cap_key' exists
	 * and is an array. If so, it will be used.
	 *
	 * @since 4.8.0
	 * @access protected
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $cap_key Optional capability key
	 */
	protected function _init_global_caps( $cap_key = '' ) {
		global $wpdb;

		if ( empty( $cap_key ) ) {
			$this->global_cap_key = $wpdb->base_prefix . 'global_capabilities';
		} else {
			$this->global_cap_key = $cap_key;
		}

		$this->global_caps = get_user_meta( $this->ID, $this->global_cap_key, true );

		if ( ! is_array( $this->global_caps ) )
			$this->global_caps = array();

		$this->get_role_caps();
	}

	/**
	 * Retrieve all of the role capabilities and merge with individual capabilities.
	 *
	 * All of the capabilities of the roles the user belongs to are merged with
	 * the users individual roles. This also means that the user can be denied
	 * specific roles that their role might have, but the specific user isn't
	 * granted permission to.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @return array List of all capabilities for the user.
	 */
	public function get_role_caps() {
		$this->allcaps = parent::get_role_caps();

		//TODO: There is a bug in Core in this function, and it is explicitly a bug here either.
		// For discussion about a fix, see https://core.trac.wordpress.org/ticket/36961
		// The fix here will happen after the fix is in place in Core.

		$wp_global_roles = wp_global_roles();

		//Filter out caps that are not role names and assign to $this->global_roles
		if ( is_array( $this->global_caps ) ) {
			$this->global_roles = array_filter( array_keys( $this->global_caps ), array( $wp_global_roles, 'is_role' ) );
		}

		//Build $allcaps from role caps, overlay user's $caps
		$this->allcaps = array();
		foreach ( (array) $this->global_roles as $role ) {
			$the_role = $wp_global_roles->get_role( $role );
			$this->allcaps = array_merge( (array) $this->allcaps, (array) $the_role->capabilities );
		}
		$this->allcaps = array_merge( (array) $this->allcaps, (array) $this->global_caps );

		return $this->allcaps;
	}

	/**
	 * Add global role to user.
	 *
	 * Updates the user's meta data option with capabilities and roles.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Network role name.
	 */
	public function add_global_role( $role ) {
		if ( empty( $role ) ) {
			return;
		}

		$this->global_caps[ $role ] = true;
		update_user_meta( $this->ID, $this->global_cap_key, $this->global_caps );
		$this->get_role_caps();

		/**
		 * Fires immediately after the user has been given a new global role.
		 *
		 * @since 4.8.0
		 *
		 * @param int    $user_id The user ID.
		 * @param string $role    The new role.
		 */
		do_action( 'add_global_user_role', $this->ID, $role );
	}

	/**
	 * Remove global role from user.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Network role name.
	 */
	public function remove_global_role( $role ) {
		if ( ! in_array( $role, $this->global_roles ) ) {
			return;
		}

		unset( $this->global_caps[ $role ] );
		update_user_meta( $this->ID, $this->global_cap_key, $this->global_caps );
		$this->get_role_caps();

		/**
		 * Fires immediately after a global role as been removed from a user.
		 *
		 * @since 4.8.0
		 *
		 * @param int    $user_id The user ID.
		 * @param string $role    The removed role.
		 */
		do_action( 'remove_global_user_role', $this->ID, $role );
	}

	/**
	 * Set the global role of the user.
	 *
	 * This will remove the previous global roles of the user and assign the user the
	 * new one. You can set the global role to an empty string and it will remove all
	 * of the global roles from the user.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $role Network role name.
	 */
	public function set_global_role( $role ) {
		if ( 1 == count( $this->global_roles ) && $role == current( $this->global_roles ) ) {
			return;
		}

		foreach ( (array) $this->global_roles as $oldrole ) {
			unset( $this->global_caps[ $oldrole ] );
		}

		$old_roles = $this->global_roles;
		if ( ! empty( $role ) ) {
			$this->global_caps[ $role ] = true;
			$this->global_roles = array( $role => true );
		} else {
			$this->global_roles = false;
		}
		update_user_meta( $this->ID, $this->global_cap_key, $this->global_caps );
		$this->get_role_caps();

		/**
		 * Fires after the user's global role has changed.
		 *
		 * @since 4.8.0
		 *
		 * @param int    $user_id   The user ID.
		 * @param string $role      The new role.
		 * @param array  $old_roles An array of the user's previous roles.
		 */
		do_action( 'set_global_user_role', $this->ID, $role, $old_roles );
	}

	/**
	 * Add global capability and grant or deny access to capability.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $cap Network capability name.
	 * @param bool $grant Whether to grant capability to user.
	 */
	public function add_global_cap( $cap, $grant = true ) {
		$this->global_caps[ $cap ] = $grant;
		update_user_meta( $this->ID, $this->global_cap_key, $this->global_caps );
		$this->get_role_caps();
	}

	/**
	 * Remove global capability from user.
	 *
	 * @since 4.8.0
	 * @access public
	 *
	 * @param string $cap Capability name.
	 */
	public function remove_global_cap( $cap ) {
		if ( ! isset( $this->global_caps[ $cap ] ) ) {
			return;
		}
		unset( $this->global_caps[ $cap ] );
		update_user_meta( $this->ID, $this->global_cap_key, $this->global_caps );
		$this->get_role_caps();
	}

	/**
	 * Remove all of the capabilities of the user.
	 *
	 * @since 4.8.0
	 * @access public
	 */
	public function remove_all_global_caps() {
		$this->global_caps = array();
		delete_user_meta( $this->ID, $this->global_cap_key );
		$this->get_role_caps();
	}
}
