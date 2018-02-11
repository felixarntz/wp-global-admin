<?php
/**
 * Compatibility with WP Network Roles plugin
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Adjusts the user count option to only include users in the network.
 *
 * This is a hacky way to fix `wp_update_network_user_counts()` which does not count users in the current network,
 * but instead counts all users in the entire setup which is incorrect.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int    $user_count     The original number of users.
 * @param int    $old_user_count The old number of users.
 * @param string $option         The option name.
 * @param int    $network_id     Network ID for which the option is updated.
 * @return int The modified number of users.
 */
function _ga_fix_network_user_counts( $user_count, $old_user_count, $option, $network_id ) {
	if ( ! is_multinetwork() || ! nr_is_user_network_migration_done() ) {
		return $user_count;
	}

	// TODO: check for 'spam' and 'deleted'.
	$user_query = new WP_User_Query( array(
		'number'      => 20,
		'network_id'  => $network_id,
		'count_total' => true,
	) );

	return $user_query->total_users;
}
add_filter( 'pre_update_site_option_user_count', '_ga_fix_network_user_counts', 10, 4 );

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
	if ( ! is_multinetwork() || ! nr_is_user_network_migration_done() || ! is_network_admin() ) {
		return $args;
	}

	$args['network_id'] = get_current_network_id();

	return $args;
}
add_filter( 'users_list_table_query_args', '_ga_adjust_users_list_table_query_args', 10, 1 );
