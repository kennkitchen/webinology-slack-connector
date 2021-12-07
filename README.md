# Webinology Slack Connector
![Product Status](https://img.shields.io/badge/Status%3A-Beta-yellow) ![Product Version](https://img.shields.io/badge/Version%3A-1.3.0-informational)

This plugin sends alerts to Slack based on selected WordPress events.

## Change Log
* 1.3.0 - Added notification for new comments.
* 1.2.0 - Significant refactoring.
* 1.1.0 - Added logging and new feature for alerting on post updates.
* 1.0.2 - Options were not initialized on activation, which caused errors.
* 1.0.1 - Fixed issue where updates were being sent for false events.
* 1.0.0 - Initial commit.

## Currently implemented
* Settings page
  * Field to contain Slack webhook
  * Fields for post/page publish/unplublish
  * Field for post update
* Everything works

## TODO
The do_action webhooks can be skipped completely (i.e. don't register) if the options
to use them aren't selected.

Some good places to add alert options:
* Plugins
  * Plugin activated
  * Plugin deactivated
  * Plugin needs update
* Users
  * New registration
  * Password reset
* Posts/Pages
  * Add post type granularity
  * Add content alerts

I'm sure there are others!

## Use in training
This could be a good plugin to use for training purposes.
* Because of all of the possible hooks that can be used, it gives the trainee quite the round-trip tour.
* It covers settings pages and options.
