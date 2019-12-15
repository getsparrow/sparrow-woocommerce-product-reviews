<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://getsparrow.io
 * @since             1.0.0
 * @package           Getsparrow
 *
 * @wordpress-plugin
 * Plugin Name:       Sparrow
 * Plugin URI:        https://getsparrow.io/integrations/woocommerce
 * Description:       Boost your store sales by UGC and social proof powered product reviews.
 * Version:           1.4.2
 * Author:            Sparrow
 * Author URI:        https://getsparrow.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       getsparrow
 * Domain Path:       /languages
 */

 /* include autoload */
 require_once dirname(__FILE__) . "/vendor/autoload.php";

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'COM_GETSPARROW_VERSION', '1.4.3' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-getsparrow-activator.php
 */
function activate_getsparrow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-getsparrow-activator.php';
	Getsparrow_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-getsparrow-deactivator.php
 */
function deactivate_getsparrow() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-getsparrow-deactivator.php';
	Getsparrow_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_getsparrow' );
register_deactivation_hook( __FILE__, 'deactivate_getsparrow' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-getsparrow.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_getsparrow() {

	/* load vendors */

	require_once "vendor/autoload.php";

	$plugin = new Getsparrow();
	$plugin->run();

}
run_getsparrow();
