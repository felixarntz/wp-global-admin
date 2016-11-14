<?php
/**
 * Global installation administration panel.
 *
 * A multi-step process allowing the user to enable a multinetwork setup of WordPress.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

define( 'WP_INSTALLING_GLOBAL', true );

/** WordPress Administration Bootstrap */
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( __FILE__ ) . '/admin.php' );
}

if ( ! current_user_can( 'manage_network_options' ) ) {
	wp_die( __( 'Sorry, you are not allowed to manage options for this network.', 'global-admin' ) );
}

if ( is_multinetwork() && ! is_global_admin() ) {
	wp_redirect( global_admin_url( 'setup.php' ) );
	exit;
}

require_once( GA_PATH . 'global-admin/wp-admin/includes/global.php' );

// This would be it if it was part of Core.
//require_once( ABSPATH . 'wp-admin/includes/global.php' );

// We need to create references to ms global tables to enable Network.
ga_register_table();

if ( ! global_table_check() && ( ! defined( 'WP_ALLOW_MULTINETWORK' ) || ! WP_ALLOW_MULTINETWORK ) ) {
	wp_die(
		printf(
			/* translators: 1: WP_ALLOW_MULTINETWORK 2: wp-config.php */
			__( 'You must define the %1$s constant as true in your %2$s file to allow creation of a Multinetwork.', 'global-admin' ),
			'<code>WP_ALLOW_MULTINETWORK</code>',
			'<code>wp-config.php</code>'
		)
	);
}

if ( is_global_admin() ) {
	$title = __( 'Global Setup', 'global-admin' );
} else {
	$title = __( 'Create a Multinetwork', 'global-admin' );
}

$parent_file = 'settings.php';

$global_help = '<p>' . __( 'This screen allows you to configure a multinetwork setup which will provide you with a Global Admin panel to add further networks.', 'global-admin' ) . '</p>' .
	'<p>' . __( 'The next screen for Global Setup will give you individually-generated lines of code to add to your wp-config.php file. Make a backup copy of that file.', 'global-admin' ) . '</p>' .
	'<p>' . __( 'Add the designated lines of code to wp-config.php (just before <code>/*...stop editing...*/</code>).', 'global-admin' ) . '</p>' .
	'<p>' . __( 'Once you add this code and refresh your browser, multinetwork should be enabled. This screen, now in the Global Admin navigation menu, will keep an archive of the added code. You can toggle between Global Admin and Network Admin by clicking on the Global Admin or an individual network name under the My Networks dropdown in the Toolbar.', 'global-admin' ) . '</p>' .
	'<p><strong>' . __( 'For more information:', 'global-admin' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://github.com/felixarntz/global-admin/wiki/Create-A-Multinetwork" target="_blank">Documentation on Creating a Multinetwork</a>', 'global-admin' ) . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'global',
	'title'   => __( 'Multinetwork', 'global-admin' ),
	'content' => $global_help,
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:', 'global-admin' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://github.com/felixarntz/global-admin/wiki/Create-A-Multinetwork" target="_blank">Documentation on Creating a Multinetwork</a>', 'global-admin' ) . '</p>'
);

// Only load admin header and footer files when loaded directly. Otherwise they are included by WP Core.
$_ga_load_admin_files = ! did_action( 'in_admin_header' );

if ( $_ga_load_admin_files ) {
	include( ABSPATH . 'wp-admin/admin-header.php' );
}
?>
<div class="wrap">
<h1><?php echo esc_html( $title ); ?></h1>

<?php
if ( $_POST ) {
	check_admin_referer( 'install-global-1' );

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Create global table.
	install_global();

	$result = populate_global( sanitize_email( $_POST['email'] ), wp_unslash( $_POST['global_name'] ) );
	if ( is_wp_error( $result ) ) {
		global_step1( $result );
	} else {
		global_step2();
	}
} elseif ( is_multinetwork() || global_table_check() ) {
	global_step2();
} else {
	global_step1();
}
?>
</div>

<?php if ( $_ga_load_admin_files ) {
	include( ABSPATH . 'wp-admin/admin-footer.php' );
} ?>
