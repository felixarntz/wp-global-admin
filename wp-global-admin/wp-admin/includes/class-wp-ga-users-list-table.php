<?php
/**
 * List Table API: WP_GA_Users_List_Table class
 *
 * @package WPGlobalAdmin
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
			'number'  => $users_per_page,
			'offset'  => ( $paged - 1 ) * $users_per_page,
			'search'  => $usersearch,
			'blog_id' => 0,
			'fields'  => 'all_with_meta',
		);

		if ( wp_is_large_setup( 'users' ) ) {
			$args['search'] = ltrim( $args['search'], '*' );
		} elseif ( '' !== $args['search'] ) {
			$args['search'] = trim( $args['search'], '*' );
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( 'global' === $role ) {
			$args['login__in'] = get_global_administrators();
		}

		/*
		 * If the network is large and a search is not being performed,
		 * show only the latest users with no paging in order to avoid
		 * expensive count queries.
		 */
		if ( ! $usersearch && wp_is_large_network( 'users' ) ) {
			if ( ! isset( $_REQUEST['orderby'] ) ) {
				$_GET['orderby']     = 'id';
				$_REQUEST['orderby'] = 'id';
			}
			if ( ! isset( $_REQUEST['order'] ) ) {
				$_GET['order']     = 'DESC';
				$_REQUEST['order'] = 'DESC';
			}
			$args['count_total'] = false;
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$args['order'] = $_REQUEST['order'];
		}

		if ( ! empty( $_REQUEST['mode'] ) ) {
			$mode = 'excerpt' === $_REQUEST['mode'] ? 'excerpt' : 'list';
			set_user_setting( 'global_users_list_mode', $mode );
		} else {
			$mode = get_user_setting( 'global_users_list_mode', 'list' );
		}

		/** This filter is documented in wp-admin/includes/class-wp-users-list-table.php */
		$args = apply_filters( 'users_list_table_query_args', $args );

		// Query the user IDs for this page.
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page'    => $users_per_page,
		) );
	}

	/**
	 * Gets the columns for the global users list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of $column_slug => $column_label pairs.
	 */
	public function get_columns() {
		$users_columns = array(
			'cb'         => '<input type="checkbox" />',
			'username'   => __( 'Username' ),
			'name'       => __( 'Name' ),
			'email'      => __( 'Email' ),
			'registered' => _x( 'Registered', 'user' ),
		);

		if ( function_exists( 'get_networks_of_user' ) ) {
			$users_columns['networks'] = __( 'Networks', 'wp-global-admin' );
		}

		/**
		 * Filters the columns displayed in the Network Admin Users list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $users_columns An array of user columns. Default 'cb', 'username',
		 *                             'name', 'email', 'registered', and possibly 'networks'.
		 */
		return apply_filters( 'global_users_columns', $users_columns );
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

		$total_users = get_global_user_count();

		$global_admins = get_global_administrators();
		$total_admins  = count( $global_admins );

		$role_links = array();

		$class             = 'global' !== $role ? ' class="current"' : '';
		$role_links['all'] = "<a href='" . global_admin_url( 'users.php' ) . "'$class>" . sprintf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_users, 'users', 'wp-global-admin' ), number_format_i18n( $total_users ) ) . '</a>';

		$class                = 'global' === $role ? ' class="current"' : '';
		$role_links['global'] = "<a href='" . global_admin_url( 'users.php?role=global' ) . "'$class>" . sprintf( _n( 'Global Administrator <span class="count">(%s)</span>', 'Global Administrators <span class="count">(%s)</span>', $total_admins, 'wp-global-admin' ), number_format_i18n( $total_admins ) ) . '</a>';

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
		$avatar = get_avatar( $user->user_email, 32 );

		echo $avatar;

		if ( current_user_can( 'edit_user', $user->ID ) ) {
			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) ) );
			$edit      = "<a href=\"{$edit_link}\">{$user->user_login}</a>";
		} else {
			$edit = $user->user_login;
		}

		?>
		<strong>
			<a href="<?php echo $edit_link; ?>" class="edit"><?php echo $user->user_login; ?></a>
			<?php
			if ( is_global_administrator( $user->ID ) ) {
				echo ' - ' . __( 'Global Administrator' );
			}
			?>
		</strong>
		<?php
	}

	/**
	 * Handles the wrapper output of the networks column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user    The current WP_User object.
	 * @param string  $classes Column CSS classes.
	 * @param string  $data    Column data attributes.
	 * @param string  $primary Primary column name.
	 */
	protected function _column_networks( $user, $classes, $data, $primary ) {
		echo '<td class="', $classes, ' has-row-actions" ', $data, '>';
		echo $this->column_networks( $user );
		echo $this->handle_row_actions( $user, 'networks', $primary );
		echo '</td>';
	}

	/**
	 * Handles the networks column output.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user The current WP_User object.
	 */
	public function column_networks( $user ) {
		if ( ! function_exists( 'get_networks_of_user' ) ) {
			return;
		}

		$networks = get_networks_of_user( $user->ID, true );
		if ( ! is_array( $networks ) ) {
			return;
		}

		foreach ( $networks as $network ) {
			if ( ! can_edit_network( $network->id ) ) {
				continue;
			}

			/**
			 * Filters the URL to edit a network in the list of a user's networks.
			 *
			 * @since 1.0.0
			 *
			 * @param string $edit_url   Network edit URL. By default it is empty, as in not set.
			 * @param int    $network_id Network ID.
			 */
			$edit_network_url = apply_filters( 'global_user_list_edit_network_url', '', $network->id );

			$path = '/' === $network->path ? '' : $network->path;

			echo '<span class="network-' . $network->id . '" >';
			echo '<a href="' . esc_url( network_admin_url( 'site-info.php?id=' . $network->id ) ) . '">' . $network->domain . $path . '</a>';
			echo ' <small class="row-actions">';

			$actions = array();
			if ( ! empty( $edit_network_url ) ) {
				$actions['edit'] = '<a href="' . esc_url( $edit_network_url ) . '">' . __( 'Edit' ) . '</a>';
			}
			$actions['view'] = '<a href="' . esc_url( get_home_url( $network->site_id ) ) . '">' . __( 'View' ) . '</a>';

			/**
			 * Filters the action links displayed next the networks a user belongs to
			 * in the Global Admin Users list table.
			 *
			 * @since 1.0.0
			 *
			 * @param array $actions    An array of action links to be displayed.
			 *                          Default 'Edit', 'View'.
			 * @param int   $network_id The network ID.
			 */
			$actions = apply_filters( 'global_user_list_network_actions', $actions, $network->id );

			$i            = 0;
			$action_count = count( $actions );
			foreach ( $actions as $action => $link ) {
				++$i;
				$sep = ( $i === $action_count ) ? '' : ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			echo '</small></span><br/>';
		}
	}

	/**
	 * Displays the list table rows.
	 *
	 * This method is in here to remove unnecessary logic from the parent class.
	 *
	 * @since 1.0.0
	 */
	public function display_rows() {
		foreach ( $this->items as $user ) {
			?>
			<tr>
				<?php $this->single_row_columns( $user ); ?>
			</tr>
			<?php
		}
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user        User being acted upon.
	 * @param string  $column_name Current column name.
	 * @param string  $primary     Primary column name.
	 * @return string Row actions output for users in Multisite.
	 */
	protected function handle_row_actions( $user, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$global_administrators = get_global_administrators();

		$actions = array();

		if ( current_user_can( 'edit_user', $user->ID ) ) {
			$edit_link       = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) ) );
			$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
		}

		if ( current_user_can( 'delete_user', $user->ID ) && ! in_array( $user->user_login, $global_administrators, true ) ) {
			$delete_link       = esc_url( global_admin_url( add_query_arg( '_wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), wp_nonce_url( 'users.php', 'deleteuser' ) . '&amp;action=deleteuser&amp;id=' . $user->ID ) ) );
			$actions['delete'] = '<a href="' . $delete_link . '" class="delete">' . __( 'Delete' ) . '</a>';
		}

		/**
		 * Filters the action links displayed under each user in the Global Admin Users list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $actions An array of action links to be displayed.
		 *                         Default 'Edit', 'Delete'.
		 * @param WP_User $user    WP_User object.
		 */
		$actions = apply_filters( 'global_user_row_actions', $actions, $user );

		return $this->row_actions( $actions );
	}
}
