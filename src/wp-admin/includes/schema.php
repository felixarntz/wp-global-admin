<?php
/**
 * WordPress Administration Scheme API
 *
 * Here we keep the DB structure and option values.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Registers the global database table.
 *
 * @since 1.0.0
 */
function ga_register_table() {
	global $wpdb;

	$wpdb->ms_global_tables[] = 'global_options';
	$wpdb->global_options = $wpdb->base_prefix . 'global_options';
}

/**
 * Installs the global database table.
 *
 * @since 1.0.0
 */
function ga_install_table() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$queries = ga_get_db_schema( 'global' );
	if ( empty( $queries ) ) {
		return;
	}

	dbDelta( $queries );
}

/**
 * Install Network.
 *
 * Overrides pluggable function in WP Core.
 *
 * @since 1.0.0
 */
if ( !function_exists( 'install_network' ) ) :
function install_network() {
	if ( ! defined( 'WP_INSTALLING_NETWORK' ) ) {
		define( 'WP_INSTALLING_NETWORK', true );
	}

	dbDelta( wp_get_db_schema( 'global' ) );

	ga_install_table();
}
endif;

/**
 * Retrieve the SQL for creating database tables.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $scope Optional. The tables for which to retrieve SQL. Can be all, global, ms_global, or blog tables. Defaults to all.
 * @param int $blog_id Optional. The site ID for which to retrieve SQL. Default is the current site ID.
 * @return string The SQL needed to create the requested tables.
 */
function ga_get_db_schema( $scope = 'all', $blog_id = null ) {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	// Engage multisite if in the middle of turning it on from network.php.
	$is_multisite = is_multisite() || ( defined( 'WP_INSTALLING_NETWORK' ) && WP_INSTALLING_NETWORK );

	$max_index_length = 191;

	$ms_global_tables = "CREATE TABLE $wpdb->global_options (
  option_id bigint(20) unsigned NOT NULL auto_increment,
  option_name varchar(191) NOT NULL default '',
  option_value longtext NOT NULL,
  autoload varchar(20) NOT NULL default 'yes',
  PRIMARY KEY  (option_id),
  UNIQUE KEY option_name (option_name)
) $charset_collate;\n";

	switch ( $scope ) {
		case 'blog' :
			$queries = '';
			break;
		case 'global' :
			$queries = '';
			if ( $is_multisite ) {
				$queries .= $ms_global_tables;
			}
			break;
		case 'ms_global' :
			$queries = $ms_global_tables;
			break;
		case 'all' :
		default:
			$queries = '';
			if ( $is_multisite ) {
				$queries .= $ms_global_tables;
			}
			break;
	}

	return $queries;
}

/**
 * Installation callback for plugin activation hook.
 *
 * @since 1.0.0
 */
function ga_install() {
	ga_install_table();
}
