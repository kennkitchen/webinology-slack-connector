<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'webinology-slack-connector',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
