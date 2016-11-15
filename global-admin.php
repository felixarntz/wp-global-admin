<?php
/*
Plugin Name: Global Admin
Plugin URI:  https://github.com/felixarntz/global-admin
Description: Introduces a global admin panel in WordPress. Works best with WP Multi Network.
Version:     1.0.0
Author:      Felix Arntz
Author URI:  https://leaves-and-love.net
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: global-admin
Network:     true
Tags:        global admin, network, multisite, multinetwork
*/
/**
 * Plugin initialization file
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Initializes the plugin.
 *
 * Loads the required files, registers the new DB table, global cache groups and global capabilities.
 *
 * @since 1.0.0
 */
function ga_init() {
	if ( ! function_exists( 'wp_network_roles' ) ) {
		add_action( 'admin_notices', 'ga_requirements_notice_network_roles' );
		add_action( 'network_admin_notices', 'ga_requirements_notice_network_roles' );
		return;
	}

	define( 'GA_PATH', plugin_dir_path( __FILE__ ) );
	define( 'GA_URL', plugin_dir_url( __FILE__ ) );

	require_once( GA_PATH . 'global-admin/wp-includes/load.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/option.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/class-wp-global-role.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/class-wp-global-roles.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/capabilities.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/class-wp-user-with-network-and-global-roles.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/user.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/link-template.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/admin-bar.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/ms-functions.php' );
	require_once( GA_PATH . 'global-admin/wp-includes/ms-default-filters.php' );

	require_once( GA_PATH . 'global-admin/wp-admin/includes/schema.php' );
	require_once( GA_PATH . 'global-admin/wp-admin/includes/hacks.php' );

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'wp-multi-network/wpmn-loader.php' ) ) {
		require_once( GA_PATH . 'global-admin/multi-network-compat.php' );
	}
	if ( is_plugin_active( 'wp-user-signups/wp-user-signups.php' ) ) {
		require_once( GA_PATH . 'global-admin/user-signups-compat.php' );
	}

	if ( is_multinetwork() ) {
		ga_register_table();
		add_action( 'setup_theme', 'ga_setup_wp_global_roles', 1 );
	}

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'global-options', 'global-transient' ) );
	}
}

/**
 * Instantiates the WP_Global_Roles class.
 *
 * @since 1.0.0
 */
function ga_setup_wp_global_roles() {
	$GLOBALS['wp_global_roles'] = new WP_Global_Roles();

	ga_populate_roles();
}

/**
 * Populates the default global roles.
 *
 * @since 1.0.0
 */
function ga_populate_roles() {
	if ( get_global_role( 'administrator' ) ) {
		return;
	}

	$network_administrator = get_network_role( 'administrator' );

	$global_administrator_capabilities = array_merge( $network_administrator->capabilities, array_fill_keys( array(
		'manage_global',
		'manage_networks',
		'manage_global_users',
		'manage_global_themes',
		'manage_global_plugins',
		'manage_global_options',
		// The following capabilities are part of WP Spider Cache, WP User Signups and WP Encrypt respectively.
		'manage_cache',
		'manage_user_signups',
		'manage_certificates',
	), true ) );

	add_global_role( 'administrator', __( 'Global Administrator' ), $global_administrator_capabilities );
}

/**
 * Shows an admin notice if the WordPress version installed is not supported.
 *
 * @since 1.0.0
 */
function ga_requirements_notice_wordpress() {
	$plugin_file = plugin_basename( __FILE__ );
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php printf(
				__( 'Please note: Global Admin requires WordPress 4.7-beta3 or higher. <a href="%s">Deactivate plugin</a>.' ),
				wp_nonce_url(
					add_query_arg(
						array(
							'action'        => 'deactivate',
							'plugin'        => $plugin_file,
							'plugin_status' => 'all',
						),
						self_admin_url( 'plugins.php' )
					),
					'deactivate-plugin_' . $plugin_file
				)
			); ?>
		</p>
	</div>
	<?php
}

/**
 * Shows an admin notice if the WP Network Roles plugin is not active.
 *
 * @since 1.0.0
 */
function ga_requirements_notice_network_roles() {
	$plugin_file = plugin_basename( __FILE__ );
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php printf(
				__( 'Please note: Global Admin requires the plugin WP Network Roles to be installed and activated. <a href="%s">Deactivate plugin</a>.' ),
				wp_nonce_url(
					add_query_arg(
						array(
							'action'        => 'deactivate',
							'plugin'        => $plugin_file,
							'plugin_status' => 'all',
						),
						self_admin_url( 'plugins.php' )
					),
					'deactivate-plugin_' . $plugin_file
				)
			); ?>
		</p>
	</div>
	<?php
}

/**
 * Ensures that this plugin gets activated in every new network by filtering the `active_sitewide_plugins` option.
 *
 * @since 1.0.0
 *
 * @param array $plugins Array of plugin basenames as keys and time() as values.
 * @return array Modified plugins array.
 */
function ga_activate_everywhere( $plugins ) {
	if ( isset( $plugins['global-admin/global-admin.php'] ) ) {
		return $plugins;
	}

	$plugins['global-admin/global-admin.php'] = time();

	return $plugins;
}

if ( version_compare( $GLOBALS['wp_version'], '4.7-beta3', '<' ) ) {
	add_action( 'admin_notices', 'ga_requirements_notice_wordpress' );
	add_action( 'network_admin_notices', 'ga_requirements_notice_wordpress' );
} else {
	add_action( 'plugins_loaded', 'ga_init' );
	add_filter( 'pre_update_site_option_active_sitewide_plugins', 'ga_activate_everywhere', 10, 1 );
}
