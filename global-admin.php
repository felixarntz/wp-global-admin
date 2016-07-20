<?php
/*
Plugin Name: Global Admin
Plugin URI:  https://wordpress.org/plugins/global-admin/
Description: Introduces a global admin in WordPress. Works best with WP Multi Network.
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

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'wp-multi-network/wpmn-loader.php' ) ) {
		require_once( dirname( __FILE__ ) . '/src/multi-network-compat.php' );
	}

	require_once( dirname( __FILE__ ) . '/src/wp-admin/includes/hacks.php' );

	register_global_cap( array(
		'manage_cache',
		'manage_networks',
		'create_networks',
		'delete_networks',
		'delete_network',
		'manage_global_options',
		'manage_global_users',
		'edit_user',
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
						admin_url( 'plugins.php' )
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
} else {
	require_once( dirname( __FILE__ ) . '/src/wp-admin/includes/schema.php' );

	ga_register_table();
	register_activation_hook( __FILE__, 'ga_install' );

	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'global-options', 'global-transient' ) );
	}

	add_action( 'plugins_loaded', 'ga_init' );
}
