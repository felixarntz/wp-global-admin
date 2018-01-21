<?php
/**
 * WordPress Administration Scheme API
 *
 * Here we keep the DB structure and option values.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

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
		$errors->add( 'empty_global_name', __( 'You must provide a name for your global multinetwork.', 'wp-global-admin' ) );
	}

	if ( ! is_email( $email ) ) {
		$errors->add( 'invalid_email', __( 'You must provide a valid email address.', 'wp-global-admin' ) );
	}

	if ( $errors->get_error_code() ) {
		return $errors;
	}

	$user = get_user_by( 'email', $email );
	if ( false === $user ) {
		$user = wp_get_current_user();
	}

	$global_options = array(
		'global_name'           => $global_name,
		'admin_email'           => $email,
		'global_administrators' => array( $user->user_login ),
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
