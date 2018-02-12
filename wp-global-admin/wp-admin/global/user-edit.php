<?php
/**
 * Edit user global administration panel.
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

/** Load WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multinetwork() ) {
	wp_die( __( 'Multinetwork support is not enabled.', 'wp-global-admin' ) );
}

/**
 * Handles changes to the global administrator privileges of a user.
 *
 * @since 1.0.0
 * @access private
 *
 * @param int $user_id ID of the user that is being edited.
 */
function _ga_maybe_modify_global_administrator_setting( $user_id ) {
	if ( is_multinetwork() && is_global_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_global_options' ) ) {
		$global_administrators = get_global_option( 'global_administrators', array() );
		$user                  = get_userdata( $user_id );

		if ( ! empty( $_POST['global_administrator'] ) ) {
			if ( ! in_array( $user->user_login, $global_administrators, true ) ) {
				$global_administrators[] = $user->user_login;
				update_global_option( 'global_administrators', $global_administrators );

				/**
				 * Fires after a user has been granted global administrator privileges.
				 *
				 * @since 1.0.0
				 *
				 * @param int $user_id User ID.
				 */
				do_action( 'granted_global_administrator', $user->ID );
			}
		} else {
			$key = array_search( $user->user_login, $global_administrators, true );
			if ( false !== $key ) {
				array_splice( $global_administrators, $key, 1 );
				update_global_option( 'global_administrators', $global_administrators );

				/**
				 * Fires after a user's global administrator privileges have been revoked.
				 *
				 * @since 1.0.0
				 *
				 * @param int $user_id User ID.
				 */
				do_action( 'revoked_global_administrator', $user->ID );
			}
		}
	}
}
add_action( 'personal_options_update', '_ga_maybe_modify_global_administrator_setting', 10, 1 );
add_action( 'edit_user_profile_update', '_ga_maybe_modify_global_administrator_setting', 10, 1 );

/**
 * Displays a checkbox to grant or revoke global administrator privileges.
 *
 * This function MUST be hooked into 'personal_options' as its HTML will only be valid there.
 *
 * @since 1.0.0
 * @access private
 *
 * @param WP_User $user User that is being edited.
 */
function _ga_maybe_display_global_administrator_checkbox( $user ) {
	if ( is_multinetwork() && is_global_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_global_options' ) ) {
		?>
		</table>

		<style type="text/css">
			.user-role-wrap {
				display: none;
			}
		</style>

		<h2><?php _e( 'Permissions', 'wp-global-admin' ); ?></h2>

		<table class="form-table">
			<tr class="user-global-admin-wrap">
				<th>
					<?php _e( 'Global Administrator', 'wp-global-admin' ); ?>
				</th>
				<td>
					<?php if ( $user->user_email != get_global_option( 'admin_email' ) || ! is_global_administrator( $user->ID ) ) : ?>
						<p><label><input type="checkbox" id="global_administrator" name="global_administrator"<?php checked( is_global_administrator( $user->ID ) ); ?> /> <?php _e( 'Grant this user global administrator privileges.', 'wp-global-admin' ); ?></label></p>
					<?php else : ?>
						<p><?php _e( 'Global administrator privileges cannot be removed because this user has the global admin email.', 'wp-global-admin' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php
	}
}
add_action( 'personal_options', '_ga_maybe_display_global_administrator_checkbox', 9999, 1 );

require( ABSPATH . 'wp-admin/user-edit.php' );
