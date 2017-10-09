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

	//TODO: When the time is right, this function should use the network_id argument that WP Network Roles introduces.

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
