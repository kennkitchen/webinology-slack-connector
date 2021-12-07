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
    <img src="<?= plugins_url('webinology-slack-connector') ?>/admin/img/webnSlackConnLogo.png">
    <div class="wrap">
        <h2>Alert Settings</h2>
        <form method="post" action="options.php" class="form-style-5">
            <?php settings_fields('webn_slack_options_group');
            do_settings_sections( 'webn_slack_options_group' ); ?>
            <?php $webn_slack_options = get_option('webn_slack_options'); ?>
            <fieldset class="settings-boxes"><legend>Your Slack Webhook</legend><br />
                <label for="webn_slack_options[webn_slack_alert_on_published]">Enter your Slack webhook:</label>

                <input name="webn_slack_options[webn_slack_inbound_webhook]" type="text" <?php echo ($webn_slack_options['webn_slack_inbound_webhook'] == '') ? ('placeholder="https://hooks.slack.com/services/xxxxxxxxx/xxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxx"') : ('value="' . $webn_slack_options['webn_slack_inbound_webhook'] .'"'); ?>>
                <br />

            </fieldset>
            <fieldset class="settings-boxes"><legend>Post Settings</legend><br />
                <!-- Alert when post is published-->
                <label for="webn_slack_options[webn_slack_alert_on_published]">Alert when post published?:</label>

                <input name="webn_slack_options[webn_slack_alert_on_published]"
                       value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_published'], true) ?> type="radio">Yes<br>
                <input name="webn_slack_options[webn_slack_alert_on_published]"
                       value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_published'], true) ?> type="radio">No<br>
                <br />

                <!--Alert when post is unpublished-->
                <label for="webn_slack_options[webn_slack_alert_on_unpublish]">Alert when post unpublished?:</label>

                <input name="webn_slack_options[webn_slack_alert_on_unpublish]"
                       value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_unpublish'], true) ?> type="radio">Yes<br>
                <input name="webn_slack_options[webn_slack_alert_on_unpublish]"
                       value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_unpublish'], true) ?> type="radio">No<br>
                <br />

                <!--Alert when a previously published post is updated-->
                <label for="webn_slack_options[webn_slack_alert_on_post_update]">Alert when a published post is updated?:</label>

                <input name="webn_slack_options[webn_slack_alert_on_post_update]"
                       value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_post_update'], true) ?> type="radio">Yes<br>
                <input name="webn_slack_options[webn_slack_alert_on_post_update]"
                       value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_post_update'], true) ?> type="radio">No<br>
                <br />

            </fieldset>
            <fieldset class="settings-boxes"><legend>Comment Settings</legend><br />
                <!-- Alert when comment is added-->
                <label for="webn_slack_options[webn_slack_alert_on_new_comment]">Alert when a comment is added?:</label>

                <input name="webn_slack_options[webn_slack_alert_on_new_comment]"
                       value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_new_comment'], true) ?> type="radio">Yes<br>
                <input name="webn_slack_options[webn_slack_alert_on_new_comment]"
                       value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_new_comment'], true) ?> type="radio">No<br>
                <br />

            </fieldset>
            <p class="submit">
                <input type="submit" class="default-button" value="Save Changes" />
            </p>
        </form>
    </div>
</div>