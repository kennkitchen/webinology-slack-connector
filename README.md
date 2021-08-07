# Webinology Slack Connector
![Product Status](https://img.shields.io/badge/Status%3A-Alpha-red)

This plugin sends alerts to Slack based on selected WordPress events.

## Currently implemented
* Settings page
  * Field to contain Slack webhook
  * Fields for post/page publish/unplublish
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
