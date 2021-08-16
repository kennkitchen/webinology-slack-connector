<?php

/**
 * Fired during plugin activation
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $options_array = array(
            'webn_slack_inbound_webhook' => '',
            'webn_slack_alert_on_published' => 'false',
            'webn_slack_alert_on_unpublish' => 'false',
        );

        $webn_slack_options = get_option('webn_slack_options');

        if (!$webn_slack_options) {
            update_option('webn_slack_options', $options_array);
        }
	}

}
