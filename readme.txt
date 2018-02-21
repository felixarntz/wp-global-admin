=== WP Global Admin ===

Plugin Name:       WP Global Admin
Plugin URI:        https://github.com/felixarntz/wp-global-admin
Author:            Felix Arntz
Author URI:        https://leaves-and-love.net
Contributors:      flixos90
Requires at least: 4.9
Tested up to:      4.9
Stable tag:        1.0.0-beta.1
Version:           1.0.0-beta.1
License:           GNU General Public License v2 (or later)
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Tags:              global admin, network, multisite, multinetwork

Introduces a global admin panel in WordPress.

== Description ==

= Features =

* introduces an entirely new administration panel, the global administration panel
* introduces global administrators and adjust capabilities so that certain setup-wide actions can no longer be performed by network administrators
* shows all users in the global administration panel, and only the ones in the current network in the network administration panel (if the WP Network Roles plugin is activated)
* includes a global settings page in the new administration panel
* includes custom dashboard widgets for the global administration panel
* introduces a global user count, network count and site count, and fixes the incorrect network user count to actually only count users of the respective network (if the WP Network Roles is activated)
* supports the WP Multi Network plugin, the WP Network Roles plugin and the WP User Signups plugin

== Installation ==

1. Upload the entire `wp-global-admin` folder to the `/wp-content/plugins/` directory or download it through the WordPress backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Where should I submit my support request? =

I preferably take support requests as [issues on GitHub](https://github.com/felixarntz/wp-global-admin/issues), so I would appreciate if you created an issue for your request there. However, if you don't have an account there and do not want to sign up, you can of course use the [wordpress.org support forums](https://wordpress.org/support/plugin/wp-global-admin) as well.

= How can I contribute to the plugin? =

If you're a developer and you have some ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request in the [GitHub repository for the plugin](https://github.com/felixarntz/wp-global-admin).

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/wp-global-admin) to get started.

== Changelog ==

= 1.0.0 =
* First stable version
