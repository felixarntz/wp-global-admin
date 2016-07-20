<?php
/**
 * Option API
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Retrieves a global option value based on an option name.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * If the option was serialized then it will be unserialized when it is returned.
 *
 * Any scalar values will be returned as strings. You may coerce the return type of
 * a given option by registering an {@see 'option_$option'} filter callback.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $option  Name of option to retrieve. Expected to not be SQL-escaped.
 * @param mixed  $default Optional. Default value to return if the option does not exist.
 * @return mixed Value set for the option.
 */
if ( ! function_exists( 'get_global_option' ) ) :
function get_global_option( $option, $default = false ) {
	global $wpdb;

	$option = trim( $option );
	if ( empty( $option ) ) {
		return false;
	}

	/**
	 * Filters the value of an existing global option before it is retrieved.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * Passing a truthy value to the filter will short-circuit retrieving
	 * the option value, returning the passed value instead.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|mixed $pre_option Value to return instead of the option value.
	 *                               Default false to skip it.
	 * @param string     $option     Option name.
	 */
	$pre = apply_filters( 'pre_global_option_' . $option, false, $option );
	if ( false !== $pre ) {
		return $pre;
	}

	if ( ! is_multisite() ) {
		/** This filter is documented in wp-includes/option.php */
		$default = apply_filters( 'default_global_option_' . $option, $default, $option );
		$value = get_option( $option, $default );
	} elseif ( ! wp_installing() ) {
		// prevent non-existent options from triggering multiple queries
		$notoptions = wp_cache_get( 'notoptions', 'global-options' );
		if ( isset( $notoptions[ $option ] ) ) {
			/**
			 * Filters the default value for a global option.
			 *
			 * The dynamic portion of the hook name, `$option`, refers to the option name.
			 *
			 * @since 1.0.0
			 *
			 * @param mixed  $default The default value to return if the option does not exist
			 *                        in the database.
			 * @param string $option  Option name.
			 */
			return apply_filters( 'default_global_option_' . $option, $default, $option );
		}

		$alloptions = wp_load_global_alloptions();

		if ( isset( $alloptions[ $option ] ) ) {
			$value = $alloptions[ $option ];
		} else {
			$value = wp_cache_get( $option, 'global-options' );

			if ( false === $value ) {
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->global_options WHERE option_name = %s LIMIT 1", $option ) );

				// Has to be get_row instead of get_var because of funkiness with 0, false, null values
				if ( is_object( $row ) ) {
					$value = $row->option_value;
					wp_cache_add( $option, $value, 'global-options' );
				} else { // option does not exist, so we must cache its non-existence
					if ( ! is_array( $notoptions ) ) {
						 $notoptions = array();
					}
					$notoptions[ $option ] = true;
					wp_cache_set( 'notoptions', $notoptions, 'global-options' );

					/** This filter is documented in wp-includes/option.php */
					return apply_filters( 'default_global_option_' . $option, $default, $option );
				}
			}
		}
	} else {
		$suppress = $wpdb->suppress_errors();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->global_options WHERE option_name = %s LIMIT 1", $option ) );
		$wpdb->suppress_errors( $suppress );
		if ( is_object( $row ) ) {
			$value = $row->option_value;
		} else {
			/** This filter is documented in wp-includes/option.php */
			return apply_filters( 'default_global_option_' . $option, $default, $option );
		}
	}

	/**
	 * Filters the value of an existing global option.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value  Value of the option. If stored serialized, it will be
	 *                       unserialized prior to being returned.
	 * @param string $option Option name.
	 */
	return apply_filters( 'global_option_' . $option, maybe_unserialize( $value ), $option );
}
endif;

/**
 * Loads and caches all autoloaded global options, if available or all options.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return array List of all options.
 */
if ( ! function_exists( 'wp_load_global_alloptions' ) ) :
function wp_load_global_alloptions() {
	global $wpdb;

	if ( ! wp_installing() ) {
		$alloptions = wp_cache_get( 'alloptions', 'global-options' );
	} else {
		$alloptions = false;
	}

	if ( ! $alloptions ) {
		$suppress = $wpdb->suppress_errors();
		if ( ! $alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->global_options WHERE autoload = 'yes'" ) ) {
			$alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->global_options" );
		}

		$wpdb->suppress_errors( $suppress );

		$alloptions = array();
		foreach ( (array) $alloptions_db as $o ) {
			$alloptions[ $o->option_name ] = $o->option_value;
		}

		if ( ! wp_installing() ) {
			wp_cache_add( 'alloptions', $alloptions, 'global-options' );
		}
	}

	return $alloptions;
}
endif;

/**
 * Update the value of a global option that was already added.
 *
 * You do not need to serialize values. If the value needs to be serialized, then
 * it will be serialized before it is inserted into the database. Remember,
 * resources can not be serialized or added as an option.
 *
 * If the option does not exist, then the option will be added with the option value,
 * with an `$autoload` value of 'yes'.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string      $option   Option name. Expected to not be SQL-escaped.
 * @param mixed       $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up. For existing options,
 *                              `$autoload` can only be updated using `update_option()` if `$value` is also changed.
 *                              Accepts 'yes'|true to enable or 'no'|false to disable. For non-existent options,
 *                              the default value is 'yes'. Default null.
 * @return bool False if value was not updated and true if value was updated.
 */
if ( ! function_exists( 'update_global_option' ) ) :
function update_global_option( $option, $value, $autoload = null ) {
	global $wpdb;

	$option = trim($option);
	if ( empty($option) )
		return false;

	wp_protect_special_option( $option );

	if ( is_object( $value ) )
		$value = clone $value;

	$value = sanitize_option( $option, $value );
	$old_value = get_global_option( $option );

	/**
	 * Filters a specific global option before its value is (maybe) serialized and updated.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param mixed  $old_value The old option value.
	 * @param string $option    Option name.
	 */
	$value = apply_filters( 'pre_update_global_option_' . $option, $value, $old_value, $option );

	/**
	 * Filters a global option before its value is (maybe) serialized and updated.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value     The new, unserialized option value.
	 * @param string $option    Name of the option.
	 * @param mixed  $old_value The old option value.
	 */
	$value = apply_filters( 'pre_update_global_option', $value, $option, $old_value );

	// If the new and old values are the same, no need to update.
	if ( $value === $old_value )
		return false;

	/** This filter is documented in wp-includes/option.php */
	if ( apply_filters( 'default_global_option_' . $option, false, $option ) === $old_value ) {
		// Default setting for new options is 'yes'.
		if ( null === $autoload ) {
			$autoload = 'yes';
		}

		return add_global_option( $option, $value, $autoload );
	}

	$serialized_value = maybe_serialize( $value );

	/**
	 * Fires immediately before a global option value is updated.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option    Name of the option to update.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 */
	do_action( 'update_global_option', $option, $old_value, $value );

	if ( ! is_multisite() ) {
		$result = update_option( $option, $value, 'no' );
		if ( ! $result ) {
			return false;
		}
	} else {
		$update_args = array(
			'option_value' => $serialized_value,
		);

		if ( null !== $autoload ) {
			$update_args['autoload'] = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';
		}

		$result = $wpdb->update( $wpdb->global_options, $update_args, array( 'option_name' => $option ) );
		if ( ! $result )
			return false;

		$notoptions = wp_cache_get( 'notoptions', 'global-options' );
		if ( is_array( $notoptions ) && isset( $notoptions[ $option ] ) ) {
			unset( $notoptions[ $option ] );
			wp_cache_set( 'notoptions', $notoptions, 'global-options' );
		}

		if ( ! wp_installing() ) {
			$alloptions = wp_load_global_alloptions();
			if ( isset( $alloptions[ $option ] ) ) {
				$alloptions[ $option ] = $serialized_value;
				wp_cache_set( 'alloptions', $alloptions, 'global-options' );
			} else {
				wp_cache_set( $option, $serialized_value, 'global-options' );
			}
		}
	}

	/**
	 * Fires after the value of a specific global option has been successfully updated.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 * @param string $option    Option name.
	 */
	do_action( "update_global_option_{$option}", $old_value, $value, $option );

	/**
	 * Fires after the value of a global option has been successfully updated.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option    Name of the updated option.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $value     The new option value.
	 */
	do_action( 'updated_global_option', $option, $old_value, $value );
	return true;
}
endif;

/**
 * Add a new global option.
 *
 * You do not need to serialize values. If the value needs to be serialized, then
 * it will be serialized before it is inserted into the database. Remember,
 * resources can not be serialized or added as an option.
 *
 * You can create options without values and then update the values later.
 * Existing options will not be updated and checks are performed to ensure that you
 * aren't adding a protected WordPress option. Care should be taken to not name
 * options the same as the ones which are protected.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string         $option      Name of option to add. Expected to not be SQL-escaped.
 * @param mixed          $value       Optional. Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param string|bool    $autoload    Optional. Whether to load the option when WordPress starts up.
 *                                    Default is enabled. Accepts 'no' to disable for legacy reasons.
 * @return bool False if option was not added and true if option was added.
 */
if ( ! function_exists( 'add_global_option' ) ) :
function add_global_option( $option, $value = '', $autoload = 'yes' ) {
	global $wpdb;

	$option = trim($option);
	if ( empty($option) )
		return false;

	wp_protect_special_option( $option );

	if ( is_object($value) )
		$value = clone $value;

	if ( ! is_multisite() ) {
		$result = add_option( $option, $value, '', 'no' );
		if ( ! $result ) {
			return false;
		}
	} else {
		$value = sanitize_option( $option, $value );

		// Make sure the option doesn't already exist. We can check the 'notoptions' cache before we ask for a db query
		$notoptions = wp_cache_get( 'notoptions', 'global-options' );
		if ( !is_array( $notoptions ) || !isset( $notoptions[$option] ) )
			/** This filter is documented in wp-includes/option.php */
			if ( apply_filters( 'default_global_option_' . $option, false, $option ) !== get_global_option( $option ) )
				return false;

		$serialized_value = maybe_serialize( $value );
		$autoload = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';

		/**
		 * Fires before a global option is added.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the option to add.
		 * @param mixed  $value  Value of the option.
		 */
		do_action( 'add_global_option', $option, $value );

		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO `$wpdb->global_options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $autoload ) );
		if ( ! $result ) {
			return false;
		}

		if ( ! wp_installing() ) {
			if ( 'yes' == $autoload ) {
				$alloptions = wp_load_global_alloptions();
				$alloptions[ $option ] = $serialized_value;
				wp_cache_set( 'alloptions', $alloptions, 'global-options' );
			} else {
				wp_cache_set( $option, $serialized_value, 'global-options' );
			}
		}

		// This option exists now
		$notoptions = wp_cache_get( 'notoptions', 'global-options' ); // yes, again... we need it to be fresh
		if ( is_array( $notoptions ) && isset( $notoptions[ $option ] ) ) {
			unset( $notoptions[ $option ] );
			wp_cache_set( 'notoptions', $notoptions, 'global-options' );
		}
	}

	/**
	 * Fires after a specific global option has been added.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value  Value of the option.
	 */
	do_action( "add_global_option_{$option}", $option, $value );

	/**
	 * Fires after a global option has been added.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option Name of the added option.
	 * @param mixed  $value  Value of the option.
	 */
	do_action( 'added_global_option', $option, $value );
	return true;
}
endif;

/**
 * Removes a global option by name. Prevents removal of protected WordPress options.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $option Name of option to remove. Expected to not be SQL-escaped.
 * @return bool True, if option is successfully deleted. False on failure.
 */
if ( ! function_exists( 'delete_global_option' ) ) :
function delete_global_option( $option ) {
	global $wpdb;

	$option = trim( $option );
	if ( empty( $option ) )
		return false;

	wp_protect_special_option( $option );

	// Get the ID, if no ID then return
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT autoload FROM $wpdb->global_options WHERE option_name = %s", $option ) );
	if ( is_null( $row ) )
		return false;

	/**
	 * Fires immediately before a global option is deleted.
	 *
	 * The dynamic portion of the hook name, `$option`, refers to the option name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option Name of the option to delete.
	 */
	do_action( 'pre_delete_global_option_' . $option, $option );

	if ( ! is_multisite() ) {
		$result = delete_option( $option );
	} else {
		$result = $wpdb->delete( $wpdb->global_options, array( 'option_name' => $option ) );
		if ( ! wp_installing() ) {
			if ( 'yes' == $row->autoload ) {
				$alloptions = wp_load_global_alloptions();
				if ( is_array( $alloptions ) && isset( $alloptions[ $option ] ) ) {
					unset( $alloptions[ $option ] );
					wp_cache_set( 'alloptions', $alloptions, 'global-options' );
				}
			} else {
				wp_cache_delete( $option, 'global-options' );
			}
		}
	}

	if ( $result ) {

		/**
		 * Fires after a specific global option has been deleted.
		 *
		 * The dynamic portion of the hook name, `$option`, refers to the option name.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the deleted option.
		 */
		do_action( "delete_global_option_$option", $option );

		/**
		 * Fires after a global option has been deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param string $option Name of the deleted option.
		 */
		do_action( 'deleted_global_option', $option );
		return true;
	}
	return false;
}
endif;

/**
 * Delete a global transient.
 *
 * @since 1.0.0
 *
 * @param string $transient Transient name. Expected to not be SQL-escaped.
 * @return bool true if successful, false otherwise
 */
if ( ! function_exists( 'delete_global_transient' ) ) :
function delete_global_transient( $transient ) {

	/**
	 * Fires immediately before a specific global transient is deleted.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $transient Transient name.
	 */
	do_action( 'delete_global_transient_' . $transient, $transient );

	if ( wp_using_ext_object_cache() ) {
		$result = wp_cache_delete( $transient, 'global-transient' );
	} else {
		$option_timeout = '_transient_timeout_' . $transient;
		$option = '_transient_' . $transient;
		$result = delete_global_option( $option );
		if ( $result ) {
			delete_global_option( $option_timeout );
		}
	}

	if ( $result ) {

		/**
		 * Fires after a global transient is deleted.
		 *
		 * @since 1.0.0
		 *
		 * @param string $transient Deleted transient name.
		 */
		do_action( 'deleted_global_transient', $transient );
	}

	return $result;
}
endif;

/**
 * Get the value of a global transient.
 *
 * If the transient does not exist, does not have a value, or has expired,
 * then the return value will be false.
 *
 * @since 1.0.0
 *
 * @param string $transient Transient name. Expected to not be SQL-escaped.
 * @return mixed Value of transient.
 */
if ( ! function_exists( 'get_global_transient' ) ) :
function get_global_transient( $transient ) {

	/**
	 * Filters the value of an existing global transient.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * Passing a truthy value to the filter will effectively short-circuit retrieval
	 * of the transient, returning the passed value instead.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $pre_transient The default value to return if the transient does not exist.
	 *                              Any value other than false will short-circuit the retrieval
	 *                              of the transient, and return the returned value.
	 * @param string $transient     Transient name.
	 */
	$pre = apply_filters( 'pre_global_transient_' . $transient, false, $transient );
	if ( false !== $pre )
		return $pre;

	if ( wp_using_ext_object_cache() ) {
		$value = wp_cache_get( $transient, 'global-transient' );
	} else {
		$transient_option = '_transient_' . $transient;
		if ( ! wp_installing() ) {
			// If option is not in alloptions, it is not autoloaded and thus has a timeout
			$alloptions = wp_load_global_alloptions();
			if ( ! isset( $alloptions[ $transient_option ] ) ) {
				$transient_timeout = '_transient_timeout_' . $transient;
				$timeout = get_global_option( $transient_timeout );
				if ( false !== $timeout && $timeout < time() ) {
					delete_global_option( $transient_option  );
					delete_global_option( $transient_timeout );
					$value = false;
				}
			}
		}

		if ( ! isset( $value ) ) {
			$value = get_global_option( $transient_option );
		}
	}

	/**
	 * Filters an existing global transient's value.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value     Value of transient.
	 * @param string $transient Transient name.
	 */
	return apply_filters( 'global_transient_' . $transient, $value, $transient );
}
endif;

/**
 * Set/update the value of a global transient.
 *
 * You do not need to serialize values. If the value needs to be serialized, then
 * it will be serialized before it is set.
 *
 * @since 1.0.0
 *
 * @param string $transient  Transient name. Expected to not be SQL-escaped. Must be
 *                           172 characters or fewer in length.
 * @param mixed  $value      Transient value. Must be serializable if non-scalar.
 *                           Expected to not be SQL-escaped.
 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool False if value was not set and true if value was set.
 */
if ( ! function_exists( 'set_global_transient' ) ) :
function set_global_transient( $transient, $value, $expiration = 0 ) {

	$expiration = (int) $expiration;

	/**
	 * Filters a specific global transient before its value is set.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value      New value of transient.
	 * @param int    $expiration Time until expiration in seconds.
	 * @param string $transient  Transient name.
	 */
	$value = apply_filters( 'pre_set_global_transient_' . $transient, $value, $expiration, $transient );

	/**
	 * Filters the expiration for a global transient before its value is set.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $expiration Time until expiration in seconds. Use 0 for no expiration.
	 * @param mixed  $value      New value of transient.
	 * @param string $transient  Transient name.
	 */
	$expiration = apply_filters( 'expiration_of_global_transient_' . $transient, $expiration, $value, $transient );

	if ( wp_using_ext_object_cache() ) {
		$result = wp_cache_set( $transient, $value, 'global-transient', $expiration );
	} else {
		$transient_timeout = '_transient_timeout_' . $transient;
		$transient_option = '_transient_' . $transient;
		if ( false === get_global_option( $transient_option ) ) {
			$autoload = 'yes';
			if ( $expiration ) {
				$autoload = 'no';
				add_global_option( $transient_timeout, time() + $expiration, 'no' );
			}
			$result = add_global_option( $transient_option, $value, $autoload );
		} else {
			// If expiration is requested, but the transient has no timeout option,
			// delete, then re-create transient rather than update.
			$update = true;
			if ( $expiration ) {
				if ( false === get_global_option( $transient_timeout ) ) {
					delete_global_option( $transient_option );
					add_global_option( $transient_timeout, time() + $expiration, 'no' );
					$result = add_global_option( $transient_option, $value, 'no' );
					$update = false;
				} else {
					update_global_option( $transient_timeout, time() + $expiration );
				}
			}
			if ( $update ) {
				$result = update_global_option( $transient_option, $value );
			}
		}
	}

	if ( $result ) {

		/**
		 * Fires after the value for a specific global transient has been set.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value      Transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 * @param string $transient  The name of the transient.
		 */
		do_action( 'set_global_transient_' . $transient, $value, $expiration, $transient );

		/**
		 * Fires after the value for a global transient has been set.
		 *
		 * @since 1.0.0
		 *
		 * @param string $transient  The name of the transient.
		 * @param mixed  $value      Transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 */
		do_action( 'setted_global_transient', $transient, $value, $expiration );
	}
	return $result;
}
endif;
