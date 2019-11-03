<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Plugin Name: Indabug
 * Plugin URI:  https://wpseed.io/
 * Description: Debug Console for WordPress.
 * Version:     1.0.0
 * Author:      WP Seed
 * Author URI:  https://wpseed.io/
 * Donate link: https://wpseed.io/
 * License:     GPLv2
 *
 * Text Domain: indabug
 * Domain Path: /languages
 *
 * @link    https://wpseed.io/
 *
 * @package Indabug
 * @version 1.0.0
 */

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPSEED_INDABUG_VERSION', '1.0.0' );

/**
 * Minimum required php version.
 */
define( 'WPSEED_INDABUG_MIN_PHP_VERSION', '5.6.20' );

/**
 * Path to the plugin dir.
 */
define( 'WPSEED_INDABUG_PATH', __DIR__ );

/**
 * Basepath of the plugin.
 */
define( 'WPSEED_INDABUG_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Url of the plugin dir.
 */
define( 'WPSEED_INDABUG_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load vendor packages.
 */
require_once WPSEED_INDABUG_PATH . '/vendor/autoload.php';

/**
 * Grab the plugin object and return it.
 * Wrapper for \Wpseed\Indabug\Indabug::get_instance().
 *
 * @since  1.0.0
 * @return \Wpseed\Indabug\Indabug  Singleton instance of plugin class.
 */
function indabug() {
	return \Wpseed\Indabug\Indabug::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( indabug(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( indabug(), '_activate' ) );
register_deactivation_hook( __FILE__, array( indabug(), '_deactivate' ) );
