<?php
/**
 * Multisite Global Administration panel.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

/** Load WordPress dashboard API */
require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );
require_once dirname( dirname( __FILE__ ) ) . '/includes/dashboard.php';

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'wp-global-admin' ) );
}

if ( ! current_user_can( 'manage_networks' ) ) {
	wp_die( __( 'You do not have permission to access this page.' ), 403 );
}

$title = __( 'Dashboard' );
$parent_file = 'index.php';

$overview = '<p>' . __( 'Welcome to your Global Admin. This area of the Administration Screens is used for managing all aspects of your entire setup and your networks.', 'wp-global-admin' ) . '</p>';
$overview .= '<p>' . __( 'From here you can:', 'wp-global-admin' ) . '</p>';
$overview .= '<ul><li>' . __( 'Add and manage networks or users', 'wp-global-admin' ) . '</li>';
//TODO: later
//$overview .= '<li>' . __( 'Install and activate themes or plugins', 'wp-global-admin' ) . '</li>';
//$overview .= '<li>' . __( 'Update your network', 'wp-global-admin' ) . '</li>';
$overview .= '<li>' . __( 'Modify global settings', 'wp-global-admin' ) . '</li></ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __( 'Overview', 'wp-global-admin' ),
	'content' => $overview
) );

//TODO: investigate what of the following information makes sense here
$quick_tasks = '<p>' . __( 'The Right Now widget on this screen provides current user and site counts on your network.' ) . '</p>';
$quick_tasks .= '<ul><li>' . __( 'To add a new user, <strong>click Create a New User</strong>.' ) . '</li>';
$quick_tasks .= '<li>' . __( 'To add a new site, <strong>click Create a New Site</strong>.' ) . '</li></ul>';
$quick_tasks .= '<p>' . __( 'To search for a user or site, use the search boxes.' ) . '</p>';
$quick_tasks .= '<ul><li>' . __( 'To search for a user, <strong>enter an email address or username</strong>. Use a wildcard to search for a partial username, such as user&#42;.' ) . '</li>';
$quick_tasks .= '<li>' . __( 'To search for a site, <strong>enter the path or domain</strong>.' ) . '</li></ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'quick-tasks',
	'title'   => __( 'Quick Tasks' ),
	'content' => $quick_tasks
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:', 'wp-global-admin' ) . '</strong></p>' .
	'<p>' . __('<a href="https://github.com/felixarntz/wp-global-admin/wiki/Global-Admin" target="_blank">Documentation on the Global Admin</a>', 'wp-global-admin' ) . '</p>'
);

wp_global_dashboard_setup();

wp_enqueue_script( 'dashboard' );
wp_enqueue_script( 'plugin-install' );
add_thickbox();

require_once( ABSPATH . 'wp-admin/admin-header.php' );

?>

<div class="wrap">
<h1><?php echo esc_html( $title ); ?></h1>

<div id="dashboard-widgets-wrap">

<?php wp_dashboard(); ?>

<div class="clear"></div>
</div><!-- dashboard-widgets-wrap -->

</div><!-- wrap -->

<?php
wp_print_community_events_templates();

include( ABSPATH . 'wp-admin/admin-footer.php' );
