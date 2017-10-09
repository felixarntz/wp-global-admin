<?php
/**
 * Multisite WordPress API
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * The number of active users for the setup.
 *
 * The count is cached and updated twice daily. This is not a live count.
 *
 * @since 1.0.0
 *
 * @return int
 */
if ( ! function_exists( 'get_global_user_count' ) ) :
function get_global_user_count() {
	return get_global_option( 'user_count' );
}
endif;

/**
 * The number of active sites for the setup.
 *
 * The count is cached and updated twice daily. This is not a live count.
 *
 * @since 1.0.0
 *
 * @return int
 */
if ( ! function_exists( 'get_global_site_count' ) ) :
function get_global_site_count() {
	return get_global_option( 'site_count' );
}
endif;

/**
 * Schedules update of the global counts for the setup.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wp_schedule_update_global_counts' ) ) :
function wp_schedule_update_global_counts() {
	if ( ! is_main_network() || ! is_main_site() ) {
		return;
	}

	if ( ! wp_next_scheduled( 'update_global_counts' ) && ! wp_installing() ) {
		wp_schedule_event( time(), 'twicedaily', 'update_global_counts' );
	}
}
endif;

/**
 * Updates the global counts for the setup.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wp_update_global_counts' ) ) :
function wp_update_global_counts() {
	wp_update_global_user_counts();
	wp_update_global_site_counts();
}
endif;

/**
 * Updates the count of sites for the setup.
 *
 * If enabled through the {@see 'enable_live_global_counts'} filter, update the sites count
 * on the setup when a site is created or its status is updated.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wp_maybe_update_global_site_counts' ) ) :
function wp_maybe_update_global_site_counts() {
	$is_small_setup = ! wp_is_large_setup( 'sites' );

	/**
	 * Filters whether to update global site or user counts when a new site is created.
	 *
	 * @since 3.7.0
	 *
	 * @see wp_is_large_setup()
	 *
	 * @param bool   $small_setup Whether the setup is considered small.
	 * @param string $context     Context. Either 'users' or 'sites'.
	 */
	if ( ! apply_filters( 'enable_live_global_counts', $is_small_setup, 'sites' ) )
		return;

	wp_update_global_site_counts();
}
endif;

/**
 * Updates the global users count.
 *
 * If enabled through the {@see 'enable_live_global_counts'} filter, update the users count
 * on the setup when a user is created or its status is updated.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wp_maybe_update_global_user_counts' ) ) :
function wp_maybe_update_global_user_counts() {
	$is_small_setup = ! wp_is_large_setup( 'users' );

	/** This filter is documented in wp-includes/ms-functions.php */
	if ( ! apply_filters( 'enable_live_global_counts', $is_small_setup, 'users' ) )
		return;

	wp_update_global_user_counts();
}
endif;

/**
 * Updates the global site count.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'wp_update_global_site_counts' ) ) :
function wp_update_global_site_counts() {
	$count = get_sites( array(
		'spam'       => 0,
		'deleted'    => 0,
		'archived'   => 0,
		'count'      => true,
	) );

	update_global_option( 'site_count', $count );
}
endif;

/**
 * Updates the global user count.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
if ( ! function_exists( 'wp_update_global_user_counts' ) ) :
function wp_update_global_user_counts() {
	global $wpdb;

	$count = $wpdb->get_var( "SELECT COUNT(ID) as c FROM $wpdb->users WHERE spam = '0' AND deleted = '0'" );
	update_global_option( 'user_count', $count );
}
endif;

/**
 * Whether or not we have a large setup.
 *
 * The default criteria for a large setup is either more than 10,000 users or more than 10,000 sites.
 * Plugins can alter this criteria using the {@see 'wp_is_large_setup'} filter.
 *
 * @since 1.0.0
 *
 * @param string $using 'sites or 'users'. Default is 'sites'.
 * @return bool True if the setup meets the criteria for large. False otherwise.
 */
if ( ! function_exists( 'wp_is_large_setup' ) ) :
function wp_is_large_setup( $using = 'sites' ) {
	if ( 'users' == $using ) {
		$count = get_global_user_count();

		/**
		 * Filters whether the setup is considered large.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $is_large_setup   Whether the setup has more than 10000 users or sites.
		 * @param string $component        The component to count. Accepts 'users', or 'sites'.
		 * @param int    $count            The count of items for the component.
		 */
		return apply_filters( 'wp_is_large_setup', $count > 10000, 'users', $count );
	}

	$count = get_global_site_count();

	/** This filter is documented in wp-includes/ms-functions.php */
	return apply_filters( 'wp_is_large_setup', $count > 10000, 'sites', $count );
}
endif;

/**
 * Adjusts the user count option to only include users in the network.
 *
 * This is a hacky way to fix `wp_update_network_user_counts()` which does not count users in the current network,
 * but instead counts all users in the entire setup which is incorrect.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int $user_count The original number of users.
 * @return int The modified number of users.
 */
function _ga_fix_network_user_counts( $user_count ) {
	global $wpdb;

	if ( ! is_multinetwork() ) {
		return $user_count;
	}

	//TODO: If there's ever a user-to-network association, that should be used here.
	$site_ids = get_sites( array( 'fields' => 'ids', 'network_id' => get_current_network_id() ) );
	if ( count( $site_ids ) > 20 ) {
		// This query is really terrible, and with two many site IDs it just becomes too much to handle.
		return $user_count;
	}

	$args = array(
		'number'     => 20,
		'meta_query' => array( 'relation' => 'OR' ),
		//TODO: check for 'spam' and 'deleted'
	);
	foreach ( $site_ids as $site_id ) {
		$args['meta_query'][] = array(
			'key'		=> $wpdb->get_blog_prefix( $site_id ) . 'capabilities',
			'compare'	=> 'EXISTS',
		);
	}

	$user_query = new WP_User_Query( $args );

	return $user_query->total_users;
}
add_filter( 'pre_update_site_option_user_count', '_ga_fix_network_user_counts', 10, 1 );
