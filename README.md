[![Build Status](https://api.travis-ci.org/felixarntz/global-admin.png?branch=master)](https://travis-ci.org/felixarntz/global-admin)
[![Code Climate](https://codeclimate.com/github/felixarntz/global-admin/badges/gpa.svg)](https://codeclimate.com/github/felixarntz/global-admin)
[![Test Coverage](https://codeclimate.com/github/felixarntz/global-admin/badges/coverage.svg)](https://codeclimate.com/github/felixarntz/global-admin/coverage)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/global-admin/version)](https://packagist.org/packages/felixarntz/global-admin)
[![License](https://poser.pugx.org/felixarntz/global-admin/license)](https://packagist.org/packages/felixarntz/global-admin)

# Global Admin

Introduces a global admin in WordPress. Works best with WP Multi Network.

While WordPress brings along the possibility to have multiple networks, there's no UI to manage them. The WP Multi Network plugin does a great job in exposing a UI to the user, however its default setup needs to be adjusted in most cases since it exposes the network management UI to all super admins.

Therefore this plugin builds on top of WP Multi Network. It does not have the plugin as a dependency, but won't make a lot of sense unless. The plugin integrates deeply into WordPress Core to offer a new kind of backend, the Global Administration panel, which follows similar concepts like the Network Administration and User Administration panels.
Unfortunately some quite hacky stuff is required to be able to generate this additional backend from a plugin, but that's the only way to make it work unless it's part of WordPress Core.

**This is a very early proof-of-concept, a rather experimental approach to investigate possibilities for an actual Global Administration panel in the future. Please do not use it on a production site.**

Feel free to install it on your development environment - I'd suggest to add its own dedicated one since, especially in these very early stages, the plugin might mess up your environment. Contributions, ideas, feedback all welcome! Be aware that some concepts used in this plugin might be completely thrown overboard at some point. No backwards compatibility here at the moment!

## Concept

For an introduction about some of the concepts of the plugin, please read the [wiki](https://github.com/felixarntz/global-admin/wiki).

## Installation and Setup

You can download the plugin from GitHub. Just clone the master branch or download it as ZIP file. Note that the plugin requires WordPress 4.6-beta3 or higher.
