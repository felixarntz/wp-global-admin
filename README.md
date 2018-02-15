[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/wp-global-admin.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-admin/)
[![WordPress](https://img.shields.io/wordpress/v/wp-global-admin.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-admin/)
[![Build Status](https://api.travis-ci.org/felixarntz/wp-global-admin.png?branch=master)](https://travis-ci.org/felixarntz/wp-global-admin)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/wp-global-admin/version)](https://packagist.org/packages/felixarntz/wp-global-admin)
[![License](https://poser.pugx.org/felixarntz/wp-global-admin/license)](https://packagist.org/packages/felixarntz/wp-global-admin)

# WP Global Admin

Introduces a global admin panel in WordPress. Works best with WP Multi Network.

## What it does

* introduces an entirely new administration panel, the global administration panel
* introduces global administrators and adjust capabilities so that certain setup-wide actions can no longer be performed by network administrators
* shows all users in the global administration panel, and only the ones in the current network in the network administration panel (if the WP Network Roles plugin is activated)
* includes a global settings page in the new administration panel
* includes custom dashboard widgets for the global administration panel
* introduces a global user count, network count and site count, and fixes the incorrect network user count to actually only count users of the respective network (if the WP Network Roles is activated)
* supports the WP Multi Network plugin, the WP Network Roles plugin and the WP User Signups plugin

## How to install

The plugin can either be installed as a network-wide regular plugin or alternatively as a must-use plugin.

In order to expose the global admin, your setup must either already contain multiple networks, or you need to add a `MULTINETWORK` constant to `wp-config.php` and set it to true.

## Recommendations

* While it is a best practice to prefix plugin functions and classes, this plugin is a proof-of-concept for WordPress core, and several functions may end up there eventually. This plugin only prefixes functions and classes that are specific to the plugin, internal helper functions for itself or hooks. Non-prefixed functions and classes are wrapped in a conditional so that, if WordPress core adapts them, their core variant will be loaded instead. Therefore, do not define any of the following functions or classes:
  * `is_multinetwork()`
  * `is_global_admin()`
  * `get_global_administrator()`
  * `is_global_administrator()`
  * `global_site_url()`
  * `global_home_url()`
  * `global_admin_url()`
  * `get_global_user_count()`
  * `get_global_network_count()`
  * `get_global_site_count()`
  * `wp_schedule_update_global_counts()`
  * `wp_update_global_counts()`
  * `wp_maybe_update_global_user_counts()`
  * `wp_maybe_update_global_network_counts()`
  * `wp_maybe_update_global_site_counts()`
  * `wp_update_global_user_counts()`
  * `wp_update_global_network_counts()`
  * `wp_update_global_site_counts()`
  * `wp_is_large_setup()`
  * `wp_global_dashboard_setup()`
  * `wp_global_dashboard_right_now()`

## Usage

### Adjusting Global Capabilities

Any primitive capability that is registered as a global capability is only available to global administrators, and will no longer be allowed to network administrators. By default, the following primitive capabilities are global capabilities:

* `manage_global`
* `manage_global_users`
* `manage_global_themes`
* `manage_global_plugins`
* `manage_global_options`
* `manage_networks`
* `manage_signups`

This list can be adjusted via the `global_admin_capabilities` filter.

As an additional change, mapping for all capabilities that handle server file changes (such as updates and usage of file editors) has been adjusted so that only global administrators can perform them.

### Hooks

* Action: `granted_global_administrator`
* Action: `revoked_global_administrator`
* Action: `global_admin_menu`
* Action: `global_admin_notices`
* Action: `wp_global_dashboard_setup`
* Filter: `global_admin_capabilities`
* Filter: `global_site_url`
* Filter: `global_home_url`
* Filter: `global_admin_url`
* Filter: `enable_live_global_counts`
* Filter: `initial_global_options`
* Filter: `redirect_global_admin_request`
* Filter: `global_users_columns`
* Filter: `global_user_row_actions`
* Filter: `wp_global_dashboard_widgets`

## Compatibility

Some parts of the plugin are very hacky to have WordPress behave the required way. Custom administration panels are not supported, therefore this is a necessary evil. Some minor issues can still appear as they can't be addressed at this point (for example functions like `is_blog_admin()` will not take the global administration panel into account). [This Trac ticket aims to solve it.](https://core.trac.wordpress.org/ticket/37526)
