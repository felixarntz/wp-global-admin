<?php
/**
 * Core User API
 *
 * @package WPGlobalAdmin
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

	/** This filter is documented in wp-includes/ms-functions.php */
	$supports_user_network_query = apply_filters( 'global_admin_supports_user_network_query', false );

	if ( $supports_user_network_query ) {
		$args['network_id'] = get_current_network_id();
	} else {
		//TODO: If there's ever a user-to-network association, that should be used here.
		$site_ids = get_sites( array( 'fields' => 'ids', 'network_id' => get_current_network_id() ) );
		if ( count( $site_ids ) > 20 ) {
			// This query is really terrible, and with two many site IDs it just becomes too much to handle.
			return $args;
		}

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
	}

	return $args;
}
add_filter( 'users_list_table_query_args', '_ga_adjust_users_list_table_query_args', 10, 1 );
