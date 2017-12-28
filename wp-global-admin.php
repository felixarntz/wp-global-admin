<?php
/**
 * Plugin initialization file
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: WP Global Admin
 * Plugin URI:  https://github.com/felixarntz/wp-global-admin
 * Description: Introduces a global admin panel in WordPress. Works best with WP Multi Network.
 * Version:     1.0.0
 * Author:      Felix Arntz
 * Author URI:  https://leaves-and-love.net
 * License:     GNU General Public License v3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-global-admin
 * Network:     true
 * Tags:        global admin, network, multisite, multinetwork
 */

/**
 * Initializes the plugin.
 *
 * Loads the required files, registers the new DB table, global cache groups and global capabilities.
 *
 * @since 1.0.0
 */
function ga_init() {
	define( 'GA_PATH', plugin_dir_path( __FILE__ ) );
	define( 'GA_URL', plugin_dir_url( __FILE__ ) );

	require_once GA_PATH . 'wp-global-admin/wp-includes/load.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/option.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/capabilities.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/user.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/link-template.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/admin-bar.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/ms-functions.php';
	require_once GA_PATH . 'wp-global-admin/wp-includes/ms-default-filters.php';

	if ( is_admin() ) {
		require_once GA_PATH . 'wp-global-admin/wp-admin/includes/schema.php';
		require_once GA_PATH . 'wp-global-admin/wp-admin/includes/hacks.php';
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( is_plugin_active( 'wp-multi-network/wpmn-loader.php' ) ) {
		require_once GA_PATH . 'wp-global-admin/multi-network-compat.php';
	}
	if ( is_plugin_active( 'wp-user-signups/wp-user-signups.php' ) ) {
		require_once GA_PATH . 'wp-global-admin/user-signups-compat.php';
	}

	if ( is_multinetwork() ) {
		ga_register_table();
	}

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'global-options', 'global-transient' ) );
	}
}

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

	$wpdb->ms_global_tables[] = 'global_options';
	$wpdb->global_options = $wpdb->base_prefix . 'global_options';
}

/**
 * Shows an admin notice if the WordPress version installed is not supported.
 *
 * @since 1.0.0
 */
function ga_requirements_notice() {
	$plugin_file = plugin_basename( __FILE__ );

	if ( ! current_user_can( 'deactivate_plugin', $plugin_file ) ) {
		return;
	}

	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php printf(
				__( 'Please note: WP Global Admin requires WordPress 4.9 or higher. <a href="%s">Deactivate plugin</a>.', 'wp-global-admin' ),
				wp_nonce_url(
					add_query_arg(
						array(
							'action'        => 'deactivate',
							'plugin'        => $plugin_file,
							'plugin_status' => 'all',
						),
						network_admin_url( 'plugins.php' )
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
 * @param array $network_options All network options for the new network.
 * @return array Modified network options including the plugin.
 */
function ga_activate_on_new_network( $network_options ) {
	$plugin_file = plugin_basename( __FILE__ );

	if ( ! isset( $network_options['active_sitewide_plugins'][ $plugin_file ] ) ) {
		$network_options['active_sitewide_plugins'][ $plugin_file ] = time();
	}

	return $network_options;
}

if ( version_compare( $GLOBALS['wp_version'], '4.9', '<' ) ) {
	add_action( 'admin_notices', 'ga_requirements_notice' );
	add_action( 'network_admin_notices', 'ga_requirements_notice' );
} else {
	add_action( 'plugins_loaded', 'ga_init' );

	if ( did_action( 'muplugins_loaded' ) ) {
		add_filter( 'populate_network_meta', 'ga_activate_on_new_network', 10, 1 );
	}
}
