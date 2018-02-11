<?php
/**
 * WordPress Dashboard Widget Administration Screen API
 *
 * @package WPGlobalAdmin
 * @since 1.0.0
 */

if ( ! function_exists( 'wp_global_dashboard_setup' ) ) :

	/**
	 * Registers global dashboard widgets.
	 *
	 * Handles POST data, sets up filters.
	 *
	 * @since 1.0.0
	 *
	 * @global array $wp_registered_widgets
	 * @global array $wp_registered_widget_controls
	 * @global array $wp_dashboard_control_callbacks
	 */
	function wp_global_dashboard_setup() {
		global $wp_registered_widgets, $wp_registered_widget_controls, $wp_dashboard_control_callbacks;

		$wp_dashboard_control_callbacks = array();
		$screen                         = get_current_screen();

		/* Register Widgets and Controls */

		$response = wp_check_browser_version();

		if ( $response && $response['upgrade'] ) {
			add_filter( 'postbox_classes_dashboard_dashboard_browser_nag', 'dashboard_browser_nag_class' );
			if ( $response['insecure'] ) {
				wp_add_dashboard_widget( 'dashboard_browser_nag', __( 'You are using an insecure browser!' ), 'wp_dashboard_browser_nag' );
			} else {
				wp_add_dashboard_widget( 'dashboard_browser_nag', __( 'Your browser is out of date!' ), 'wp_dashboard_browser_nag' );
			}
		}

		// Right Now.
		wp_add_dashboard_widget( 'global_dashboard_right_now', __( 'Right Now' ), 'wp_global_dashboard_right_now' );

		// WordPress Events and News.
		wp_add_dashboard_widget( 'dashboard_primary', __( 'WordPress Events and News' ), 'wp_dashboard_events_news' );

		/**
		 * Fires after core widgets for the Global Admin dashboard have been registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wp_global_dashboard_setup' );

		/**
		 * Filters the list of widgets to load for the Global Admin dashboard.
		 *
		 * @since 1.0.0
		 *
		 * @param array $dashboard_widgets An array of dashboard widgets.
		 */
		$dashboard_widgets = apply_filters( 'wp_global_dashboard_widgets', array() );

		foreach ( $dashboard_widgets as $widget_id ) {
			$name = empty( $wp_registered_widgets[ $widget_id ]['all_link'] ) ? $wp_registered_widgets[ $widget_id ]['name'] : $wp_registered_widgets[ $widget_id ]['name'] . " <a href='{$wp_registered_widgets[$widget_id]['all_link']}' class='edit-box open-box'>" . __( 'View all' ) . '</a>';
			wp_add_dashboard_widget( $widget_id, $name, $wp_registered_widgets[ $widget_id ]['callback'], $wp_registered_widget_controls[ $widget_id ]['callback'] );
		}

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['widget_id'] ) ) {
			check_admin_referer( 'edit-dashboard-widget_' . $_POST['widget_id'], 'dashboard-widget-nonce' );
			ob_start(); // hack - but the same hack wp-admin/widgets.php uses
			wp_dashboard_trigger_widget_control( $_POST['widget_id'] );
			ob_end_clean();
			wp_redirect( remove_query_arg( 'edit' ) );
			exit;
		}

		/** This action is documented in wp-admin/edit-form-advanced.php */
		do_action( 'do_meta_boxes', $screen->id, 'normal', '' );

		/** This action is documented in wp-admin/edit-form-advanced.php */
		do_action( 'do_meta_boxes', $screen->id, 'side', '' );
	}

endif;

if ( ! function_exists( 'wp_global_dashboard_right_now' ) ) :

	/**
	 * Renders the Right Now widget for the Global Admin dashboard.
	 *
	 * @since 1.0.0
	 */
	function wp_global_dashboard_right_now() {
		/**
		 * Filters the URL to list networks in the global Right Now dashboard widget.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url Networks list URL. By default it is empty, as in not set.
		 */
		$networks_url = apply_filters( 'global_right_now_networks_url', '' );

		/**
		 * Filters the URL to add a network in the global Right Now dashboard widget.
		 *
		 * @since 1.0.0
		 *
		 * @param string $url Add Network URL. By default it is empty, as in not set.
		 */
		$add_network_url = apply_filters( 'global_right_now_add_network_url', '' );

		$actions = array();
		if ( ! empty( $add_network_url ) && current_user_can( 'create_networks' ) ) {
			$actions['create-network'] = '<a href="' . esc_url( $add_network_url ) . '">' . __( 'Create a New Network', 'wp-global-admin' ) . '</a>';
		}
		if ( current_user_can( 'create_users' ) ) {
			$actions['create-user'] = '<a href="' . global_admin_url( 'user-new.php' ) . '">' . __( 'Create a New User', 'wp-global-admin' ) . '</a>';
		}

		$c_users    = get_global_user_count();
		$c_networks = get_global_network_count();
		$c_sites    = get_global_site_count();

		/* translators: %s: number of users in the setup */
		$user_text = sprintf( _n( '%s user', '%s users', $c_users, 'wp-global-admin' ), number_format_i18n( $c_users ) );
		/* translators: %s: number of networks in the setup */
		$network_text = sprintf( _n( '%s network', '%s networks', $c_networks, 'wp-global-admin' ), number_format_i18n( $c_networks ) );
		/* translators: %s: number of sites in the setup */
		$site_text = sprintf( _n( '%s site', '%s sites', $c_sites, 'wp-global-admin' ), number_format_i18n( $c_sites ) );

		/* translators: 1: text indicating the number of sites in the setup, 2: text indicating the number of networks in the setup, 3: text indicating the number of users in the setup */
		$sentence = sprintf( __( 'You have %1$s, %2$s and %3$s.', 'wp-global-admin' ), $site_text, $network_text, $user_text );

		if ( $actions ) {
			echo '<ul class="subsubsub">';
			foreach ( $actions as $class => $action ) {
				$actions[ $class ] = "\t<li class='$class'>$action";
			}
			echo implode( " |</li>\n", $actions ) . "</li>\n";
			echo '</ul>';
		}

		?>
		<br class="clear" />

		<p class="youhave"><?php echo $sentence; ?></p>

		<form action="<?php echo network_admin_url( 'users.php' ); ?>" method="get">
			<p>
				<label class="screen-reader-text" for="search-users"><?php _e( 'Search Users', 'wp-global-admin' ); ?></label>
				<input type="search" name="s" value="" size="30" autocomplete="off" id="search-users"/>
				<?php submit_button( __( 'Search Users', 'wp-global-admin' ), '', false, false, array( 'id' => 'submit_users' ) ); ?>
			</p>
		</form>

		<?php if ( ! empty( $networks_url ) ) : ?>
			<form action="<?php echo esc_url( $networks_url ); ?>" method="get">
				<p>
					<label class="screen-reader-text" for="search-networks"><?php _e( 'Search Networks', 'wp-global-admin' ); ?></label>
					<input type="search" name="s" value="" size="30" autocomplete="off" id="search-networks"/>
					<?php submit_button( __( 'Search Networks', 'wp-global-admin' ), '', false, false, array( 'id' => 'submit_networks' ) ); ?>
				</p>
			</form>
		<?php endif; ?>
		<?php

		/**
		 * Fires at the end of the 'Right Now' widget in the Global Admin dashboard.
		 *
		 * @since 1.0.0
		 */
		do_action( 'global_rightnow_end' );
	}

endif;
