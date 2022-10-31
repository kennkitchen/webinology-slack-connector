<?php
declare(strict_types=1);
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kenn.kitchen
 * @since             1.0.0
 * @package           Webinology_Slack_Connector
 *
 * @wordpress-plugin
 * Plugin Name:       Webinology Slack Connector
 * Plugin URI:        https://webinology.io
 * Description:       Get notifications in Slack when things change on your WordPress website.
 * Version:           1.6.3
 * Author:            Kenn Kitchen
 * Author URI:        https://kenn.kitchen
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webinology-slack-connector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Autoload Composer-included libraries
require plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
define( 'PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ));

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
const WEBINOLOGY_SLACK_CONNECTOR_VERSION = '1.6.3';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webinology-slack-connector-activator.php
 */
function activate_webinology_slack_connector() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-webinology-slack-connector-activator.php';
    Webinology_Slack_Connector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webinology-slack-connector-deactivator.php
 */
function deactivate_webinology_slack_connector() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-webinology-slack-connector-deactivator.php';
    Webinology_Slack_Connector_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_webinology_slack_connector' );
register_deactivation_hook( __FILE__, 'deactivate_webinology_slack_connector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-webinology-slack-connector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webinology_slack_connector() {
    $plugin = new Webinology_Slack_Connector();
    $plugin->run();

}
run_webinology_slack_connector();