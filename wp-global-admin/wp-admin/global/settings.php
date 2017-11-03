<?php
/**
 * Multisite global settings administration panel.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'wp-global-admin' ) );
}

if ( ! current_user_can( 'manage_global_options' ) ) {
	wp_die( __( 'You do not have permission to access this page.' ), 403 );
}

$title = __( 'Global Settings', 'wp-global-admin' );
$parent_file = 'settings.php';

get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'wp-global-admin' ),
		'content' =>
			'<p>' . __( 'This screen sets and changes options for the entire setup as a whole. The settings on this page will affect all networks and sites.', 'wp-global-admin' ) . '</p>'
			//TODO: what do we need here? What makes sense?
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:', 'wp-global-admin' ) . '</strong></p>' .
	'<p>' . __('<a href="https://github.com/felixarntz/wp-global-admin/wiki/Global-Admin-Settings-Screen" target="_blank">Documentation on Global Settings</a>', 'wp-global-admin' ) . '</p>'
);

if ( $_POST ) {
	check_admin_referer( 'global-options' );

	$options = array(
		'global_name',
		'admin_email',
	);

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

		<table class="form-table">
			<tr>
				<th scope="row"><label for="global_name"><?php _e( 'Global Title', 'wp-global-admin' ) ?></label></th>
				<td>
					<input name="global_name" type="text" id="global_name" class="regular-text" value="<?php echo esc_attr( get_global_option( 'global_name' ) ) ?>" />
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="admin_email"><?php _e( 'Global Admin Email', 'wp-global-admin' ) ?></label></th>
				<td>
					<input name="admin_email" type="email" id="admin_email" aria-describedby="admin-email-desc" class="regular-text" value="<?php echo esc_attr( get_global_option( 'admin_email' ) ) ?>" />
					<p class="description" id="admin-email-desc">
						<?php _e( 'This address is used for admin purposes.', 'wp-global-admin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>

<?php include( ABSPATH . 'wp-admin/admin-footer.php' ); ?>
