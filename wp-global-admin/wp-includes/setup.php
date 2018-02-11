<?php
/**
 * Sets up the initial global options.
 *
 * @package WPGlobalOptions
 * @since 1.0.0
 */

/**
 * Gets the initial global options to populate the setup with.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int $network_id Optional. ID of the network to generate global options from. Default is the main network.
 * @return array Array of $option => $value pairs.
 */
function _ga_get_initial_global_options( $network_id = null ) {
	if ( ! $network_id ) {
		$network_id = get_main_network_id();
	}

	wp_installing( true );

	$global_name = get_network_option( $network_id, 'site_name', '' );
	$global_name = str_replace( __( 'Network', 'wp-global-admin' ), __( 'Setup', 'wp-global-admin' ), $global_name );
	$global_name = str_replace( __( 'Sites', 'wp-global-admin' ), __( 'Networks', 'wp-global-admin' ), $global_name );

	$options = array(
		'global_name'           => $global_name,
		'admin_email'           => get_network_option( $network_id, 'admin_email', '' ),
		'global_administrators' => get_network_option( $network_id, 'site_admins', array() ),
	);

	/**
	 * Filters the initial global options to set when populating the global environment.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options    Array of $option => $value pairs.
	 * @param int   $network_id ID of the network to generate global options from.
	 */
	$options = apply_filters( 'initial_global_options', $options, $network_id );

	wp_installing( false );

	return $options;
}

/**
 * Maybe installs the global options database table and sets the installed flag.
 *
 * @since 1.0.0
 * @access private
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function _ga_maybe_populate_global() {
	global $wpdb;

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! is_multinetwork() ) {
		return;
	}

	$initial_options = _ga_get_initial_global_options();
	$alloptions      = wp_load_global_alloptions();

	$new_options = array_diff_key( $initial_options, $alloptions );

	if ( ! empty( $new_options ) ) {
		$insert = array();
		foreach ( $new_options as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = serialize( $value );
			}

			$insert[] = $wpdb->prepare( '( %s, %s, %s)', $key, $value, 'yes' );
		}

		$wpdb->query( "INSERT INTO $wpdb->global_options ( option_name, option_value, autoload ) VALUES " . implode( ', ', $insert ) );
	}

	if ( ! isset( $alloptions['user_count'] ) ) {
		wp_update_global_user_counts();
	}
	if ( ! isset( $alloptions['network_count'] ) ) {
		wp_update_global_network_counts();
	}
	if ( ! isset( $alloptions['site_count'] ) ) {
		wp_update_global_site_counts();
	}
}
add_action( 'init', '_ga_maybe_populate_global', 2, 0 );
