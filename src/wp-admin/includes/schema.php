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
 * Installs the global table.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'install_global' ) ) :
function install_global() {
	if ( ! defined( 'WP_INSTALLING_GLOBAL' ) ) {
		define( 'WP_INSTALLING_GLOBAL', true );
	}

	$queries = ga_get_db_schema( 'global' );
	if ( empty( $queries ) ) {
		return;
	}

	dbDelta( $queries );
}
endif;

/**
 * Populate global settings.
 *
 * @since 1.0.0
 *
 * @global wpdb       $wpdb
 *
 * @param string $email       Email address for the network administrator.
 * @param string $global_name The name of the network.
 * @return bool|WP_Error True on success, or WP_Error on failure.
 */
if ( ! function_exists( 'populate_global' ) ) :
function populate_global( $email = '', $global_name = '' ) {
	global $wpdb;

	$errors = new WP_Error();

	if ( empty( $global_name ) ) {
		$errors->add( 'empty_global_name', __( 'You must provide a name for your global multinetwork.', 'global-admin' ) );
	}

	if ( ! is_email( $email ) ) {
		$errors->add( 'invalid_email', __( 'You must provide a valid email address.', 'global-admin' ) );
	}

	if ( $errors->get_error_code() ) {
		return $errors;
	}

	$user = get_user_by( 'email', $email );
	if ( false === $user ) {
		$user = wp_get_current_user();
	}

	$global_admins = array( $user->user_login );
	if ( is_multinetwork() ) {
		$global_admins = get_global_option( 'global_admins', array() );
	}

	$global_options = array(
		'global_name'   => $global_name,
		'admin_email'   => $email,
		'admin_user_id' => $user->ID,
		'global_admins' => $global_admins,
	);

	/**
	 * Filters options for the global admin on creation.
	 *
	 * @since 1.0.0
	 *
	 * @param array $global_options Associative array of global keys and values to be inserted.
	 */
	$global_options = apply_filters( 'populate_global_options', $global_options );

	$insert = '';
	foreach ( $global_options as $key => $value ) {
		if ( is_array( $value ) ) {
			$value = serialize( $value );
		}
		if ( ! empty( $insert ) ) {
			$insert .= ', ';
		}
		$insert .= $wpdb->prepare( "( %s, %s, %s)", $key, $value, 'yes' );
	}

	$wpdb->query( "INSERT INTO $wpdb->global_options ( option_name, option_value, autoload ) VALUES " . $insert );

	return true;
}
endif;

/**
 * Registers the global database table.
 *
 * @since 1.0.0
 */
function ga_register_table() {
	global $wpdb;

	if ( isset( $wpdb->global_options ) ) {
		return;
	}

	// In Core the property would be called `mn_global_tables`
	$wpdb->ms_global_tables[] = 'global_options';
	$wpdb->global_options = $wpdb->base_prefix . 'global_options';
}

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

	// Engage multinetwork if in the middle of turning it on from global.php.
	$is_multinetwork = is_multinetwork() || ( defined( 'WP_INSTALLING_GLOBAL' ) && WP_INSTALLING_GLOBAL );

	$max_index_length = 191;

	$mn_global_tables = "CREATE TABLE $wpdb->global_options (
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
			if ( $is_multinetwork ) {
				$queries .= $mn_global_tables;
			}
			break;
		case 'mn_global' :
			$queries = $mn_global_tables;
			break;
		case 'all' :
		default:
			$queries = '';
			if ( $is_multinetwork ) {
				$queries .= $mn_global_tables;
			}
			break;
	}

	return $queries;
}
