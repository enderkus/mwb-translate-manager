<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://enderkus.com.tr
 * @since             1.0.0
 * @package           Mwb_Translate_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       MWB Translate Manager
 * Plugin URI:        https://enderkus.com.tr
 * Description:       Home assigment.
 * Version:           1.0.0
 * Author:            Ender KUS
 * Author URI:        https://enderkus.com.tr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mwb-translate-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MWB_TRANSLATE_MANAGER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mwb-translate-manager-activator.php
 */
function activate_mwb_translate_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-translate-manager-activator.php';
	Mwb_Translate_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mwb-translate-manager-deactivator.php
 */
function deactivate_mwb_translate_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-translate-manager-deactivator.php';
	Mwb_Translate_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mwb_translate_manager' );
register_deactivation_hook( __FILE__, 'deactivate_mwb_translate_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mwb-translate-manager.php';

// Include the cron functions
require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-translate-manager-cron.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mwb_translate_manager() {

	$plugin = new Mwb_Translate_Manager();
	$plugin->run();

}

run_mwb_translate_manager();
?>
