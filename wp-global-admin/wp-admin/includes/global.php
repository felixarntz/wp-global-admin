<?php
/**
 * WordPress Global Administration API.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/**
 * Prints step 1 for Global installation process.
 *
 * @since 1.0.0
 *
 * @param WP_Error|bool $errors
 */
if ( ! function_exists( 'global_step1' ) ) :
function global_step1( $errors = false ) {
	$main_network_id = get_main_network_id();
	$main_network = get_network( $main_network_id );
	$hostname = $main_network->domain;
	?>

	<form method="post">

		<?php
		wp_nonce_field( 'install-global-1' );

		$error_codes = array();
		if ( is_wp_error( $errors ) ) {
			echo '<div class="error"><p><strong>' . __( 'ERROR: The multinetwork could not be created.', 'wp-global-admin' ) . '</strong></p>';
			foreach ( $errors->get_error_messages() as $error ) {
				echo "<p>$error</p>";
			}
			echo '</div>';
			$error_codes = $errors->get_error_codes();
		}

		$global_name = ( ! empty( $_POST['global_name'] ) && ! in_array( 'empty_global_name', $error_codes ) ) ? $_POST['global_name'] : sprintf( _x( '%s Networks', 'Default global name', 'wp-global-admin' ), get_option( 'blogname' ) );
		$admin_email = ( ! empty( $_POST['email'] ) && ! in_array( 'invalid_email', $error_codes ) ) ? $_POST['email'] : get_option( 'admin_email' );
		?>

		<p><?php _e( 'Welcome to the Multinetwork installation process!', 'wp-global-admin' ); ?></p>
		<p><?php _e( 'Fill in the information below and you&#8217;ll be on your way to creating a global multinetwork setup. We will create configuration files in the next step.' ); ?></p>

		<h3><?php _e( 'Multinetwork Details', 'wp-global-admin' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Server Address', 'wp-global-admin' ); ?></th>
				<td>
					<?php printf(
						/* translators: %s: host name */
						__( 'The internet address of your global admin panel will be %s.' ),
						'<code>' . $hostname . '</code>'
					); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Multinetwork Title', 'wp-global-admin' ); ?></th>
				<td>
					<input name="global_name" type="text" size="45" value="<?php echo esc_attr( $global_name ); ?>" />
					<p class="description">
						<?php _e( 'What would you like to call your multinetwork?', 'wp-global-admin' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Global Admin Email', 'wp-global-admin' ); ?></th>
				<td>
					<input name="email" type="text" size="45" value="<?php echo esc_attr( $admin_email ); ?>" />
					<p class="description">
						<?php _e( 'Your email address.', 'wp-global-admin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Install' ), 'primary', 'submit' ); ?>

	</form>

	<?php
}
endif;

/**
 * Prints step 2 for Global installation process.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'global_step2' ) ) :
function global_step2() {
	$main_network_id = get_main_network_id();
	$main_network = get_network( $main_network_id );
	$hostname = $main_network->domain;

	$abspath_fix = str_replace( '\\', '/', ABSPATH );
	$location_of_wp_config = $abspath_fix;
	if ( ! file_exists( ABSPATH . 'wp-config.php' ) && file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
		$location_of_wp_config = dirname( $abspath_fix );
	}
	$location_of_wp_config = trailingslashit( $location_of_wp_config );

	if ( $_POST || ! is_multinetwork() ) {
		?>
		<h3><?php esc_html_e( 'Enabling the Multinetwork' ); ?></h3>
		<p><?php _e( 'Complete the following steps to enable the global admin panel for creating multiple networks.' ); ?></p>
		<div class="updated inline"><p>
			<?php printf(
				/* translators: 1: wp-config.php */
				__( 'We recommend you back up your existing %s file.', 'wp-global-admin' ),
				'<code>wp-config.php</code>'
			); ?>
		</p></div>
		<?php
	} else {
		?>
		<p><?php _e( 'The original configuration steps are shown here for reference.', 'wp-global-admin' ); ?></p>
		<?php
	}
	?>
	<ol>
		<li>
			<p><?php printf(
				/* translators: 1: wp-config.php 2: location of wp-config file, 3: translated version of "That's all, stop editing! Happy blogging." */
				__( 'Add the following to your %1$s file in %2$s <strong>above</strong> the line reading %3$s:', 'wp-global-admin' ),
				'<code>wp-config.php</code>',
				'<code>' . $location_of_wp_config . '</code>',
				/*
				 * translators: This string should only be translated if wp-config-sample.php is localized.
				 * You can check the localized release package or
				 * https://i18n.svn.wordpress.org/<locale code>/branches/<wp version>/dist/wp-config-sample.php
				 */
				'<code>/* ' . __( 'That&#8217;s all, stop editing! Happy blogging.', 'wp-global-admin' ) . ' */</code>'
			); ?></p>
			<textarea class="code" readonly="readonly" cols="100" rows="2">
define('MULTINETWORK', true);
</textarea>
		</li>
	</ol>
	<?php

	if ( ! is_multinetwork() ) { ?>
		<p><?php _e( 'Once you complete these steps, your multinetwork is enabled and configured.', 'wp-global-admin' ); ?></p>
	<?php }
}
endif;
