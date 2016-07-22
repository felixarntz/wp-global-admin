<?php
/**
 * Core User API
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the user query to not display all users unless the current user is a global administrator.
 *
 * It is usually undesirable to have a network administrator see all users regardless of whether
 * they're part of his/her network or not. This function ensures that only global administrators
 * are able to see all users.
 *
 * @since 1.0.0
 *
 * @param WP_User_Query &$user_query The original query instance.
 */
function ga_adjust_user_query( &$user_query ) {
	global $wpdb;

	if ( ! is_multinetwork() || is_global_admin() ) {
		return;
	}

	if ( current_user_can( 'manage_global_users' ) ) {
		return;
	}

	if ( 0 < absint( $user_query->query_vars['blog_id'] ) ) {
		return;
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

	if ( empty( $user_query->meta_query->queries ) ) {
		$user_query->meta_query->queries = $site_queries;
	} else {
		$user_query->meta_query->queries = array(
			'relation' => 'AND',
			array( $user_query->meta_query->queries, $site_queries ),
		);
	}
}
add_action( 'pre_user_query', 'ga_adjust_user_query', 10, 1 );
