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

function ga_init() {
	define( 'GA_PATH', plugin_dir_path( __FILE__ ) );
	define( 'GA_URL', plugin_dir_url( __FILE__ ) );

	require_once( dirname( __FILE__ ) . '/src/wp-includes/load.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-includes/option.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-includes/capabilities.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-includes/user.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-includes/link-template.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-includes/admin-bar.php' );

	require_once( dirname( __FILE__ ) . '/src/wp-admin/includes/schema.php' );
	require_once( dirname( __FILE__ ) . '/src/wp-admin/includes/hacks.php' );

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'wp-multi-network/wpmn-loader.php' ) ) {
		require_once( dirname( __FILE__ ) . '/src/multi-network-compat.php' );
	}

	if ( is_multinetwork() ) {
		ga_register_table();
	}

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'global-options', 'global-transient' ) );
	}

	register_global_cap( array(
		'edit_user',
		'manage_global_options',
		'manage_global_users',
		'manage_networks',
		'create_networks',
		'delete_networks',
		'manage_cache',
		'manage_certificates',
	) );
}

function ga_requirements_notice() {
	$plugin_file = plugin_basename( __FILE__ );
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php printf(
				__( 'Please note: Global Admin requires WordPress 4.6-beta3 or higher. <a href="%s">Deactivate plugin</a>.' ),
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

if ( version_compare( $GLOBALS['wp_version'], '4.6-beta3', '<' ) ) {
	add_action( 'admin_notices', 'ga_requirements_notice' );
	add_action( 'network_admin_notices', 'ga_requirements_notice' );
} else {
	add_action( 'plugins_loaded', 'ga_init' );
}
