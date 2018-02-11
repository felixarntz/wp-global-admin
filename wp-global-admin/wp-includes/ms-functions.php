<?php
/**
 * Multisite WordPress API
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

if ( ! function_exists( 'get_global_user_count' ) ) :

	/**
	 * The number of active users for the setup.
	 *
	 * The count is cached and updated twice daily. This is not a live count.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function get_global_user_count() {
		return get_global_option( 'user_count' );
	}

endif;

if ( ! function_exists( 'get_global_network_count' ) ) :

	/**
	 * The number of active networks for the setup.
	 *
	 * The count is cached and updated twice daily. This is not a live count.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function get_global_network_count() {
		return get_global_option( 'network_count' );
	}

endif;

if ( ! function_exists( 'get_global_site_count' ) ) :

	/**
	 * The number of active sites for the setup.
	 *
	 * The count is cached and updated twice daily. This is not a live count.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function get_global_site_count() {
		return get_global_option( 'site_count' );
	}

endif;

if ( ! function_exists( 'wp_schedule_update_global_counts' ) ) :

	/**
	 * Schedules update of the global counts for the setup.
	 *
	 * @since 1.0.0
	 */
	function wp_schedule_update_global_counts() {
		if ( ! is_main_network() || ! is_main_site() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'update_global_counts' ) && ! wp_installing() ) {
			wp_schedule_event( time(), 'twicedaily', 'update_global_counts' );
		}
	}

endif;

if ( ! function_exists( 'wp_update_global_counts' ) ) :

	/**
	 * Updates the global counts for the setup.
	 *
	 * @since 1.0.0
	 */
	function wp_update_global_counts() {
		wp_update_global_user_counts();
		wp_update_global_network_counts();
		wp_update_global_site_counts();
	}

endif;

if ( ! function_exists( 'wp_maybe_update_global_user_counts' ) ) :

	/**
	 * Updates the global users count.
	 *
	 * If enabled through the {@see 'enable_live_global_counts'} filter, update the users count
	 * on the setup when a user is created or its status is updated.
	 *
	 * @since 1.0.0
	 */
	function wp_maybe_update_global_user_counts() {
		$is_small_setup = ! wp_is_large_setup( 'users' );

		/**
		 * Filters whether to update global site or user counts when a new site is created.
		 *
		 * @since 1.0.0
		 *
		 * @see wp_is_large_setup()
		 *
		 * @param bool   $small_setup Whether the setup is considered small.
		 * @param string $context     Context. Either 'users', 'networks' or 'sites'.
		 */
		if ( ! apply_filters( 'enable_live_global_counts', $is_small_setup, 'users' ) ) {
			return;
		}

		wp_update_global_user_counts();
	}

endif;

if ( ! function_exists( 'wp_maybe_update_global_site_counts' ) ) :

	/**
	 * Updates the count of networks for the setup.
	 *
	 * If enabled through the {@see 'enable_live_global_counts'} filter, update the networks count
	 * on the setup when a network is created or its status is updated.
	 *
	 * @since 1.0.0
	 */
	function wp_maybe_update_global_network_counts() {
		$is_small_setup = ! wp_is_large_setup( 'networks' );

		/** This filter is documented in wp-includes/ms-functions.php */
		if ( ! apply_filters( 'enable_live_global_counts', $is_small_setup, 'networks' ) ) {
			return;
		}

		wp_update_global_network_counts();
	}

endif;

if ( ! function_exists( 'wp_maybe_update_global_site_counts' ) ) :

	/**
	 * Updates the count of sites for the setup.
	 *
	 * If enabled through the {@see 'enable_live_global_counts'} filter, update the sites count
	 * on the setup when a site is created or its status is updated.
	 *
	 * @since 1.0.0
	 */
	function wp_maybe_update_global_site_counts() {
		$is_small_setup = ! wp_is_large_setup( 'sites' );

		/** This filter is documented in wp-includes/ms-functions.php */
		if ( ! apply_filters( 'enable_live_global_counts', $is_small_setup, 'sites' ) ) {
			return;
		}

		wp_update_global_site_counts();
	}

endif;

if ( ! function_exists( 'wp_update_global_user_counts' ) ) :

	/**
	 * Updates the global user count.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	function wp_update_global_user_counts() {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(ID) as c FROM $wpdb->users WHERE spam = '0' AND deleted = '0'" );
		update_global_option( 'user_count', $count );
	}

endif;

if ( ! function_exists( 'wp_update_global_network_counts' ) ) :

	/**
	 * Updates the global network count.
	 *
	 * @since 1.0.0
	 */
	function wp_update_global_network_counts() {
		$count = get_networks( array(
			'number' => 0,
			'count'  => true,
		) );

		update_global_option( 'network_count', $count );
	}

endif;

if ( ! function_exists( 'wp_update_global_site_counts' ) ) :

	/**
	 * Updates the global site count.
	 *
	 * @since 1.0.0
	 */
	function wp_update_global_site_counts() {
		$count = get_sites( array(
			'number'   => 0,
			'spam'     => 0,
			'deleted'  => 0,
			'archived' => 0,
			'count'    => true,
		) );

		update_global_option( 'site_count', $count );
	}

endif;

if ( ! function_exists( 'wp_is_large_setup' ) ) :

	/**
	 * Whether or not we have a large setup.
	 *
	 * The default criteria for a large setup is either more than 10,000 users, more than 10,000 networks, or more than 10,000 sites.
	 * Plugins can alter this criteria using the {@see 'wp_is_large_setup'} filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $using 'sites', 'networks' or 'users'. Default is 'sites'.
	 * @return bool True if the setup meets the criteria for large. False otherwise.
	 */
	function wp_is_large_setup( $using = 'sites' ) {
		switch ( $using ) {
			case 'users':
				$count = get_global_user_count();
				break;
			case 'networks':
				$count = get_global_network_count();
				break;
			default:
				$using = 'sites';
				$count = get_global_site_count();
		}

		/**
		 * Filters whether the setup is considered large.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $is_large_setup Whether the setup has more than 10000 users, networks or sites.
		 * @param string $component      The component to count. Accepts 'users', 'networks', or 'sites'.
		 * @param int    $count          The count of items for the component.
		 */
		return apply_filters( 'wp_is_large_setup', $count > 10000, $using, $count );
	}

endif;
