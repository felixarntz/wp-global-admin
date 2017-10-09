<?php
/**
 * Edit user global administration panel.
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'global-admin' ) );
}

// TODO: Form submission of this page does not work correctly.

function _ga_maybe_modify_global_administrator_setting( $user_id ) {
	if ( is_multinetwork() && is_global_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_global_options' ) && ! isset( $GLOBALS['global_administrators'] ) ) {
		$global_administrators = get_global_option( 'global_administrators', array() );
		$user = get_userdata( $user_id );

		if ( ! empty( $_POST['global_administrator'] ) ) {
			if ( ! in_array( $user->user_login, $global_administrators, true ) ) {
				$global_administrators[] = $user->user_login;
				update_global_option( 'global_administrators', $global_administrators );
			}
		} else {
			$key = array_search( $user->user_login, $global_administrators, true );
			if ( false !== $key ) {
				array_splice( $global_administrators, $key, 1 );
				update_global_option( 'global_administrators', $global_administrators );
			}
		}
	}
}
add_action( 'personal_options_update', '_ga_maybe_modify_global_administrator_setting', 10, 1 );
add_action( 'edit_user_profile_update', '_ga_maybe_modify_global_administrator_setting', 10, 1 );

function _ga_maybe_display_global_administrator_checkbox( $user ) {
	if ( is_multinetwork() && is_global_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_global_options' ) && ! isset( $GLOBALS['global_administrators'] ) ) {
		?>
		<table class="form-table">
			<tr class="user-super-admin-wrap">
				<th>
					<?php _e( 'Global Administrator', 'global-admin' ); ?>
				</th>
				<td>
					<?php if ( $user->user_email != get_global_option( 'admin_email' ) || ! is_global_administrator( $user->ID ) ) : ?>
						<p><label><input type="checkbox" id="global_administrator" name="global_administrator"<?php checked( is_global_administrator( $user->ID ) ); ?> /> <?php _e( 'Grant this user global administrator privileges.', 'global-admin' ); ?></label></p>
					<?php else : ?>
						<p><?php _e( 'Global administrator privileges cannot be removed because this user has the global admin email.', 'global-admin' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}
}
add_action( 'personal_options', '_ga_maybe_display_global_administrator_checkbox', 10, 1 );

require( ABSPATH . 'wp-admin/user-edit.php' );
