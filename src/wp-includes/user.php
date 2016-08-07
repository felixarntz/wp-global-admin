<?php
/**
 * Core User API
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the query arguments for the Network Users list table to only show users in the network.
 *
 * It is usually undesirable to have a network administrator see all users regardless of whether
 * they're part of his/her network or not. This function ensures that only the global admin exposes
 * all users.
 *
 * @since 1.0.0
 * @access private
 *
 * @param array $args Original query arguments.
 * @return array Modified query arguments.
 */
function _ga_adjust_users_list_table_query_args( $args ) {
	global $wpdb;

	if ( ! is_multinetwork() || ! is_network_admin() ) {
		return $args;
	}

	$site_ids = get_sites( array( 'fields' => 'ids', 'network_id' => get_current_network_id() ) );

	// That's a large meta query, but it's all we can do here at this point.
	$site_queries = array();
	foreach ( $site_ids as $site_id ) {
		$site_queries[] = array(
			'key'		=> $wpdb->get_blog_prefix( $site_id ) . 'capabilities',
			'compare'	=> 'EXISTS',
		);
	}

	$site_queries['relation'] = 'OR';

	if ( empty( $args['meta_query'] ) ) {
		$args['meta_query'] = $site_queries;
	} else {
		$args['meta_query'] = array(
			'relation' => 'AND',
			array( $args['meta_query'], $site_queries ),
		);
	}

	return $args;
}
add_filter( 'users_list_table_query_args', '_ga_adjust_users_list_table_query_args', 10, 1 );

/**
 * Adjusts the user count option to only include users in the network.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int $user_count The original number of users.
 * @return int The modified number of users.
 */
function _ga_fix_network_user_counts( $user_count ) {
	if ( ! is_multinetwork() ) {
		return $user_count;
	}

	$site_ids = get_sites( array( 'fields' => 'ids', 'network_id' => get_current_network_id() ) );

	$args = array(
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
