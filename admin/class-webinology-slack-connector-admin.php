<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/admin
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webinology_Slack_Connector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webinology_Slack_Connector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webinology-slack-connector-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Webinology_Slack_Connector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Webinology_Slack_Connector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/webinology-slack-connector-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function webn_slack_initialization() {

    }

    /**
     * @param $new_status
     * @param $old_status
     * @param $post
     */
    public function webn_slack_post_transitions($new_status, $old_status, $post) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
            return;
        }

        $webn_slack_options = get_option('webn_slack_options');

        if ($webn_slack_options['webn_slack_inbound_webhook'] == '') {
            // use regex for better validation
            echo 'Ya gotta have a valid webhook!'; die;
        } else {
            $url = $webn_slack_options['webn_slack_inbound_webhook'];
        }

        if (($webn_slack_options['webn_slack_alert_on_published'] == 'yes') || ($webn_slack_options['webn_slack_alert_on_unpublish'] == 'yes'))  {
            $author = get_user_by('ID', $post->post_author);
            $site_name = get_bloginfo('name');
            $post_permalink = get_post_permalink($post->ID, true);

            if (($new_status != $old_status) && ($new_status == 'publish') && ($webn_slack_options['webn_slack_alert_on_published'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has just published "' . $post->post_title . '" on ' . $site_name . '. Check it out at: ' . $post_permalink;

                $result = $this->webn_slack_curler($update_text, $url);
            }

            if (($old_status == 'publish') && ($new_status != 'publish') && ($webn_slack_options['webn_slack_alert_on_unpublish'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has unpublished "' . $post->post_title . '" on ' . $site_name . '.';

                $result = $this->webn_slack_curler($update_text, $url);
            }
        }

	}

    /**
     * @param $update_text
     * @param $url
     * @return bool|string
     */
	private function webn_slack_curler($update_text, $url) {

        $data = array(
            'text' => $update_text,
        );

        $post_data = json_encode($data);

        $crl = curl_init($url);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);

        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $result = curl_exec($crl);

//        if ($result === false) {
//            $result_noti = 0; die();
//        } else {
//            $result_noti = 1; die();
//        }

        curl_close($crl);

        return $result;
    }

    /**
     * Main Menu Page
     */
    public function webn_slack_main_menu_page() {
        ?>
        <div class="wrap eam-panel">
            <p><img src="<?= plugins_url('webinology-slack-connector') ?>/admin/partials/webnSlackConnLogo.png"></p>
            <h2>Thank you for choosing Webinology Slack Connector!</h2>
            <p>(instructions go here; needs to include info on how to configure Slack)</p>
        </div>
        <?php
    }

    /**
     * Setting Page
     */
    public function webn_slack_settings_page() {
        ?>
        <div class="wrap eam-panel">
            <img src="<?= plugins_url('webinology-slack-connector') ?>/admin/partials/webnSlackConnLogo.png">
            <div class="wrap">
                <form method="post" action="options.php" class="form-style-5">
                    <?php settings_fields('webn_slack_options_group');
                    do_settings_sections( 'webn_slack_options_group' ); ?>
                    <?php $webn_slack_options = get_option('webn_slack_options'); ?>
                    <fieldset class="settings-boxes"><legend>Your Slack Webhook</legend><br />
                        <label for="webn_slack_options[webn_slack_alert_on_published]">Enter your Slack webhook:</label>

                        <input name="webn_slack_options[webn_slack_inbound_webhook]" type="text" <?php echo ($webn_slack_options['webn_slack_inbound_webhook'] == '') ? ('placeholder="https://hooks.slack.com/services/xxxxxxxxx/xxxxxxxxxxx/xxxxxxxxxxxxxxxxxxxxxxxx"') : ('value="' . $webn_slack_options['webn_slack_inbound_webhook'] .'"'); ?>>
                        <br />

                    </fieldset>
                    <fieldset class="settings-boxes"><legend>Alert Settings</legend><br />
                        <label for="webn_slack_options[webn_slack_alert_on_published]">Alert when post published?:</label>

                        <input name="webn_slack_options[webn_slack_alert_on_published]"
                               value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_published'], true) ?> type="radio">Yes<br>
                        <input name="webn_slack_options[webn_slack_alert_on_published]"
                               value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_published'], true) ?> type="radio">No<br>
                        <br />
                        <label for="webn_slack_options[webn_slack_alert_on_unpublish]">Alert when post unpublished?:</label>

                        <input name="webn_slack_options[webn_slack_alert_on_unpublish]"
                               value="yes" <?php checked('yes', $webn_slack_options['webn_slack_alert_on_unpublish'], true) ?> type="radio">Yes<br>
                        <input name="webn_slack_options[webn_slack_alert_on_unpublish]"
                               value="no" <?php checked('no', $webn_slack_options['webn_slack_alert_on_unpublish'], true) ?> type="radio">No<br>
                        <br />

                    </fieldset>
                    <p class="submit">
                        <input type="submit" class="default-button" value="Save Changes" />
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     *
     */
    public function webn_slack_submenu1_page() {
        $page_name = $this->get_request_parameter('page');
        ?>
        <div class="wrap eam-panel">
            <p><img src="<?= plugins_url('webinology-slack-connector') ?>/admin/partials/webnSlackConnLogo.png"></p>
            <div id="mount"></div>
            <?= $page_name ?>
        </div>
        <?php
    }

    /**
     *
     */
    public function webn_slack_submenu2_page() {
        $page_name = $this->get_request_parameter('page');
        ?>
        <div class="wrap eam-panel">
            <p><img src="<?= plugins_url('webinology-slack-connector') ?>/admin/partials/webnSlackConnLogo.png"></p>
            <div id="mount"></div>
            <?= $page_name ?>
        </div>
        <?php
    }

    /**
     * Sanitize Options
     *
     * @param $options
     * @return mixed
     */
    public function webn_slack_sanitize_options($options) {
        $options['webn_slack_alert_on_published'] = (!empty($options['webn_slack_alert_on_published'])) ? sanitize_text_field($options['webn_slack_alert_on_published']) : '';
        $options['webn_slack_alert_on_unpublish'] = (!empty($options['webn_slack_alert_on_unpublish'])) ? sanitize_text_field($options['webn_slack_alert_on_unpublish']) : '';
        $options['webn_slack_inbound_webhook'] = (!empty($options['webn_slack_inbound_webhook'])) ? sanitize_text_field($options['webn_slack_inbound_webhook']) : '';

        return $options;
    }

    /**
     * Register Admin Menus
     */
    function webn_slack_admin_menus() {
        add_menu_page('Webinology Slack Connector', 'Slack Connector', 'administrator', 'webn_slack_main_menu',
            [$this, 'webn_slack_main_menu_page'], 'dashicons-controls-volumeon');

        add_submenu_page('webn_slack_main_menu', 'Slack Connector Settings',
            'Alert Settings', 'administrator', 'webn_slack_settings', [$this, 'webn_slack_settings_page']);

//        add_submenu_page('webn_slack_main_menu', 'Slack Connector Submenu 1',
//            'Roles and Capabilities', 'administrator', 'webn_slack_submenu1', [$this, 'webn_slack_submenu1_page']);
//
//        add_submenu_page('webn_slack_main_menu', 'Slack Connector Submenu 2',
//            'User Options', 'administrator', 'webn_slack_submenu2', [$this, 'webn_slack_submenu2_page']);

        register_setting('webn_slack_options_group', 'webn_slack_options',
            [$this, 'webn_slack_sanitize_options']);
    }

    private function get_request_parameter( $key, $default = '' ) {
        // If not request set
        if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
            return $default;
        }

        // Set so process it
        return strip_tags( (string) wp_unslash( $_REQUEST[ $key ] ) );
    }
}
