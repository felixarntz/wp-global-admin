[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/wp-global-admin.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-admin/)
[![WordPress](https://img.shields.io/wordpress/v/wp-global-admin.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-admin/)
[![Build Status](https://api.travis-ci.org/felixarntz/wp-global-admin.png?branch=master)](https://travis-ci.org/felixarntz/wp-global-admin)
[![Code Climate](https://codeclimate.com/github/felixarntz/wp-global-admin/badges/gpa.svg)](https://codeclimate.com/github/felixarntz/wp-global-admin)
[![Test Coverage](https://codeclimate.com/github/felixarntz/wp-global-admin/badges/coverage.svg)](https://codeclimate.com/github/felixarntz/wp-global-admin/coverage)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/wp-global-admin/version)](https://packagist.org/packages/felixarntz/wp-global-admin)
[![License](https://poser.pugx.org/felixarntz/wp-global-admin/license)](https://packagist.org/packages/felixarntz/wp-global-admin)

# WP Global Admin

Introduces a global admin panel in WordPress. Works best with WP Multi Network.

While WordPress brings along the possibility to have multiple networks, there's no UI to manage them. The WP Multi Network plugin does a great job in exposing a UI to the user, however its default setup needs to be adjusted in most cases since it exposes the network management UI to all super admins.

Therefore this plugin is built on top of [WP Multi Network](https://github.com/stuttter/wp-multi-network). It does not have the plugin as a dependency, but won't make a lot of sense unless. The plugin integrates deeply into WordPress Core to offer a new kind of backend, the Global Administration panel, which follows similar concepts like the Network Administration and User Administration panels.
Unfortunately some quite hacky stuff is required to be able to generate this additional backend from a plugin, but that's the only way to make it work unless it's part of WordPress Core.

The plugin also supports [WP User Signups](https://github.com/stuttter/wp-user-signups) properly.

**This is a very early proof-of-concept, a rather experimental approach to investigate possibilities for an actual Global Administration panel in the future. Please do not use it on a production site.**

Feel free to install it on your development environment - I'd suggest to add its own dedicated one since, especially in these very early stages, the plugin might mess up your environment. Contributions, ideas, feedback all welcome! Be aware that some concepts used in this plugin might be completely thrown overboard at some point. No backwards compatibility here at the moment. There is a [Google document for discussion](https://docs.google.com/document/d/1v3jZzOyQ4ksxnOVw3Yqmh5OzjxMIPpMleS7AdlD2eiI/edit?usp=sharing) and an overview of what exactly this plugin can do / is planned to do.

## Concept

For an introduction about some of the concepts of the plugin, please read the [wiki](https://github.com/felixarntz/wp-global-admin/wiki).

## Compatibility

Some parts of the plugin are very hacky to have WordPress behave the required way. Custom administration panels are not supported, therefore this is a necessary evil. Some minor issues can still appear as they can't be addressed at this point (for example functions like `is_blog_admin()` will not take the global administration panel into account). [This Trac ticket aims to solve it.](https://core.trac.wordpress.org/ticket/37526)

## Installation and Setup

You can download the plugin from GitHub. Just clone the master branch or download it as ZIP file. Note that the plugin requires WordPress 4.8 or higher. When using the plugin with WP Multi Network, please use the very latest version, at least [at this commit](https://github.com/stuttter/wp-multi-network/commit/4b131231813905addc6e6d5a139f7e598e92d989).

Note that the plugin will initially hide the Networks UI of WP Multi Network. That is because this UI should only be available in the global administration panel. To enable it, define a constant `WP_ALLOW_MULTINETWORK` in your `wp-config.php` and set it to true. This will enable a new "Global Setup" menu item in the network admin. Follow the instructions to set up the global admin panel.
