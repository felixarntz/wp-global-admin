<?php
/**
 * Multisite global settings administration panel.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'global-admin' ) );
}

if ( ! current_user_can( 'manage_global_options' ) ) {
	wp_die( __( 'You do not have permission to access this page.' ), 403 );
}

$title = __( 'Global Settings', 'global-admin' );
$parent_file = 'settings.php';

get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'global-admin' ),
		'content' =>
			'<p>' . __( 'This screen sets and changes options for the entire setup as a whole. The settings on this page will affect all networks and sites.', 'global-admin' ) . '</p>'
			//TODO: what do we need here? What makes sense?
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:', 'global-admin' ) . '</strong></p>' .
	'<p>' . __('<a href="https://github.com/felixarntz/global-admin/wiki/Global-Admin-Settings-Screen" target="_blank">Documentation on Global Settings</a>', 'global-admin' ) . '</p>'
);

if ( $_POST ) {
	check_admin_referer( 'global-options' );

	//TODO: process global settings
	$options = array();

	foreach ( $options as $option_name ) {
		if ( ! isset( $_POST[ $option_name ] ) ) {
			continue;
		}

		$value = wp_unslash( $_POST[ $option_name ] );

		update_global_option( $option_name, $value );
	}

	wp_redirect( add_query_arg( 'updated', 'true', global_admin_url( 'settings.php' ) ) );
	exit();
}

include( ABSPATH . 'wp-admin/admin-header.php' );

if ( isset( $_GET['updated'] ) ) {
	?><div id="message" class="updated notice is-dismissible"><p><?php _e( 'Settings saved.' ) ?></p></div><?php
}
?>

<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>
	<form method="post" action="settings.php" novalidate="novalidate">
		<?php wp_nonce_field( 'global-options' ); ?>

		<!-- TODO: fields go here -->

		<?php submit_button(); ?>
	</form>
</div>

<?php include( ABSPATH . 'wp-admin/admin-footer.php' ); ?>
