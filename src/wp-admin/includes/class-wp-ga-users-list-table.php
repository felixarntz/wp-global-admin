<?php
/**
 * List Table API: WP_GA_Users_List_Table class
 *
 * @package GlobalAdmin
 * @since 1.0.0
 */

/**
 * Core class used to implement displaying users in a list table for the global admin.
 *
 * @since 1.0.0
 * @access private
 *
 * @see WP_MS_Users_List_Table
 * @see WP_List_Table
 */
class WP_GA_Users_List_Table extends WP_MS_Users_List_Table {
	/**
	 * Checks the user capability for AJAX requests.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'manage_global_users' );
	}

	/**
	 * Prepares the query arguments
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @global string $usersearch
	 * @global string $role
	 * @global wpdb   $wpdb
	 * @global string $mode
	 */
	public function prepare_items() {
		global $usersearch, $role, $wpdb, $mode;

		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$users_per_page = $this->get_items_per_page( 'users_global_per_page' );

		$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

		$paged = $this->get_pagenum();

		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged-1 ) * $users_per_page,
			'search' => $usersearch,
			'blog_id' => 0,
			'fields' => 'all_with_meta'
		);

		if ( wp_is_large_network( 'users' ) ) {
			$args['search'] = ltrim( $args['search'], '*' );
		} else if ( '' !== $args['search'] ) {
			$args['search'] = trim( $args['search'], '*' );
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( $role === 'global' ) {
			$logins = implode( "', '", get_global_admins() );
			$args['include'] = $wpdb->get_col( "SELECT ID FROM $wpdb->users WHERE user_login IN ('$logins')" );
		}

		/*
		 * If the network is large and a search is not being performed,
		 * show only the latest users with no paging in order to avoid
		 * expensive count queries.
		 */
		if ( !$usersearch && wp_is_large_network( 'users' ) ) {
			if ( !isset($_REQUEST['orderby']) )
				$_GET['orderby'] = $_REQUEST['orderby'] = 'id';
			if ( !isset($_REQUEST['order']) )
				$_GET['order'] = $_REQUEST['order'] = 'DESC';
			$args['count_total'] = false;
		}

		if ( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby'];

		if ( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];

		if ( ! empty( $_REQUEST['mode'] ) ) {
			$mode = $_REQUEST['mode'] === 'excerpt' ? 'excerpt' : 'list';
			set_user_setting( 'global_users_list_mode', $mode );
		} else {
			$mode = get_user_setting( 'global_users_list_mode', 'list' );
		}

		/** This filter is documented in wp-admin/includes/class-wp-users-list-table.php */
		$args = apply_filters( 'users_list_table_query_args', $args );

		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page' => $users_per_page,
		) );
	}

	/**
	 * Renders the list table views.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @global string $role
	 *
	 * @return array
	 */
	protected function get_views() {
		global $role;

		$total_users = get_user_count();
		$global_admins = get_global_admins();
		$total_admins = count( $global_admins );

		$class = $role != 'global' ? ' class="current"' : '';
		$role_links = array();
		$role_links['all'] = "<a href='" . global_admin_url( 'users.php' ) . "'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users', 'global-admin' ), number_format_i18n( $total_users ) ) . '</a>';
		$class = $role === 'global' ? ' class="current"' : '';
		$role_links['global'] = "<a href='" . global_admin_url( 'users.php?role=global' ) . "'$class>" . sprintf( _n( 'Global Admin <span class="count">(%s)</span>', 'Global Admins <span class="count">(%s)</span>', $total_admins, 'global-admin' ), number_format_i18n( $total_admins ) ) . '</a>';

		return $role_links;
	}

	/**
	 * Handles the username column output.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_User $user The current WP_User object.
	 */
	public function column_username( $user ) {
		$global_admins = get_global_admins();
		$avatar	= get_avatar( $user->user_email, 32 );
		$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) ) );

		echo $avatar;

		?><strong><a href="<?php echo $edit_link; ?>" class="edit"><?php echo $user->user_login; ?></a><?php
		if ( in_array( $user->user_login, $global_admins ) ) {
			echo ' - ' . __( 'Global Admin', 'global-admin' );
		}
		?></strong>
	<?php
	}
}
