<?php
declare(strict_types=1);
/**
 * Fired during plugin deactivation
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        delete_option('webn_slack_options');

        $timestamp = wp_next_scheduled( 'webn_slack_cron_hook' );
        wp_unschedule_event( $timestamp, 'webn_slack_cron_hook' );
	}

}
