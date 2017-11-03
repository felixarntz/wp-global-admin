<?php
/**
 * Sets up the default filters and actions for Multisite.
 *
 * If you need to remove a default hook, this file will give you the priority
 * for which to use to remove the hook.
 *
 * Not all of the Multisite default hooks are found in ms-default-filters.php
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

if ( is_multinetwork() ) {
	add_action( 'admin_init', 'wp_schedule_update_global_counts');
	add_action( 'update_global_counts', 'wp_update_global_counts');
	foreach ( array( 'user_register', 'deleted_user', 'wpmu_new_user', 'make_spam_user', 'make_ham_user' ) as $action ) {
		add_action( $action, 'wp_maybe_update_global_user_counts' );
	}
	foreach ( array( 'make_spam_blog', 'make_ham_blog', 'archive_blog', 'unarchive_blog', 'make_delete_blog', 'make_undelete_blog' ) as $action ) {
		add_action( $action, 'wp_maybe_update_global_site_counts' );
	}
	unset( $action );
}
