<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap eam-panel">
    <p><img src="<?= plugins_url('webinology-slack-connector') ?>/admin/img/webnSlackConnLogo.png"></p>
    <h2>Thank you for choosing Webinology Slack Connector!</h2>
    <p>If you're just getting set up for the first time, there are some things you need to have:</p>
    <ul>
        <li>A Slack workspace where you have the rights to create a new app;</li>
        <li>A channel in your Slack workspace where you'd like notifications from your WordPress site to go.</li>
    </ul>
    <p>Once the above two conditions are met, you'll need to <a href="/wp-admin/admin.php?page=webn_slack_submenu1">create an app in your Slack workspace</a> to allow this plugin to work.</p>
</div>