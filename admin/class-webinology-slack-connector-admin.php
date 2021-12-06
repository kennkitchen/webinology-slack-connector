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
     * @var \Monolog\Logger $logger
     */
    private $logger;

    private $communicator;


    private $plugin_options = array(
            'webn_slack_inbound_webhook' => '',
            'webn_slack_alert_on_published' => 'no',
            'webn_slack_alert_on_unpublish' => 'no',
            'webn_slack_alert_on_post_update' => 'no'
        );

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version, $logger ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;

        $this->communicator = new Webinology_Slack_Connector_Comm($this->plugin_name, $this->version, $this->logger);

        $webn_slack_options = get_option('webn_slack_options');

        if (!$webn_slack_options) {
            // if no options found, load them with default values
            update_option('webn_slack_options', $this->plugin_options);
            $this->logger->debug('Options not found so loading them from defaults.');
        } else {
            // options were found; make sure they're all there.
            foreach ($this->plugin_options as $option_label => $option_value) {
                if (!array_key_exists($option_label, $webn_slack_options)) {
                    // if an option key doesn't exist at all, set it up.
                    $this->logger->debug('Single option not found:', ['Option' => $option_label]);
                    $webn_slack_options[$option_label] = $option_value;
                    update_option('webn_slack_options', $webn_slack_options);
                } else {
                    // load class options
                    $this->plugin_options[$option_label] = $webn_slack_options[$option_label];
                }

            }
        }
//        $this->logger->debug('Class option for webook:', ['Option' => $this->plugin_options['webn_slack_inbound_webhook']]);

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

    /**
     * Admin initializaton section.
     *
     * @since    1.1.0
     * @return void
     */
    public function webn_slack_initialization() {

    }

    /**
     * Post status transition hook.
     *
     * @since 1.0.0
     * @param $new_status
     * @param $old_status
     * @param $post
     */
    public function webn_slack_post_transitions($new_status, $old_status, $post) {
        $this->logger->debug('Post transition hook fired.');
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
            return;
        }

        // TODO this should really be checking against an option where user has said which post types to alert on.
        if (!in_array($post->post_type, ['post', 'page'])) {
            return;
        }

        if ( ! empty( $_REQUEST['meta-box-loader'] ) ) { // phpcs:ignore
            return;
        }

//        $webn_slack_options = get_option('webn_slack_options');
//
//        if ($webn_slack_options['webn_slack_inbound_webhook'] == '') {
//            // use regex for better validation
//            echo 'Ya gotta have a valid webhook!'; die;
//        } else {
//            $url = $webn_slack_options['webn_slack_inbound_webhook'];
//        }

        if (($this->plugin_options['webn_slack_alert_on_published'] == 'yes') || ($this->plugin_options['webn_slack_alert_on_unpublish'] == 'yes'))  {
            $author = get_user_by('ID', $post->post_author);
            $site_name = get_bloginfo('name');
            $post_permalink = get_post_permalink($post->ID, true);

            if (($new_status != $old_status) && ($new_status == 'publish') && ($this->plugin_options['webn_slack_alert_on_published'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has just published "' . $post->post_title . '" on ' . $site_name . '. Check it out at: ' . $post_permalink;

                $result = $this->communicator->generic_curl($update_text, $this->plugin_options['webn_slack_inbound_webhook']);
            }

            if (($old_status == 'publish') && ($new_status != 'publish') && ($this->plugin_options['webn_slack_alert_on_unpublish'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has unpublished "' . $post->post_title . '" on ' . $site_name . '.';

                $result = $this->communicator->generic_curl($update_text, $this->plugin_options['webn_slack_inbound_webhook']);
            }
        }

    }

    /**
     * Post update hook.
     *
     * @since 1.1.0
     * @param int $post_ID
     * @param WP_Post $post_after
     * @param WP_Post $post_before
     * @return void
     */
    public function webn_slack_post_updates(int $post_ID, WP_Post $post_after, WP_Post $post_before) {
        $this->logger->debug('Post transition hook fired.');
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
            return;
        }

        // TODO this should really be checking against an option where user has said which post types to alert on.
        if (!in_array($post_after->post_type, ['post', 'page'])) {
            return;
        }

        if ( ! empty( $_REQUEST['meta-box-loader'] ) ) { // phpcs:ignore
            return;
        }

        if ($this->plugin_options['webn_slack_alert_on_post_update'] == 'yes') {
            $author = wp_get_current_user()->user_login;//   get_user_by('ID', $post_after->post_author);
            $site_name = get_bloginfo('name');
            $post_permalink = get_post_permalink($post_after->ID, true);

            $update_text = 'User ' . $author . ' has updated "' . $post_after->post_title . '" on ' . $site_name . '.';

            $result = $this->communicator->generic_curl($update_text, $this->plugin_options['webn_slack_inbound_webhook']);
        }

    }



    /**
     * Main Menu Page
     *
     * @since 1.0.0
     */
    public function webn_slack_main_menu_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/webinology-slack-connector-admin-display.php';
    }

    /**
     * Setting Page
     *
     * @since 1.0.0
     */
    public function webn_slack_settings_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/webinology-slack-connector-admin-settings1.php';
    }

    /**
     *
     * @since 1.0.0
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
     * @since 1.0.0
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
     * @since 1.0.0
     * @param $options
     * @return mixed
     */
    public function webn_slack_sanitize_options($options) {
        $options['webn_slack_alert_on_published'] = (!empty($options['webn_slack_alert_on_published'])) ? sanitize_text_field($options['webn_slack_alert_on_published']) : '';
        $options['webn_slack_alert_on_unpublish'] = (!empty($options['webn_slack_alert_on_unpublish'])) ? sanitize_text_field($options['webn_slack_alert_on_unpublish']) : '';
        $options['webn_slack_alert_on_post_update'] = (!empty($options['webn_slack_alert_on_post_update'])) ? sanitize_text_field($options['webn_slack_alert_on_post_update']) : '';
        $options['webn_slack_inbound_webhook'] = (!empty($options['webn_slack_inbound_webhook'])) ? sanitize_text_field($options['webn_slack_inbound_webhook']) : '';

        return $options;
    }

    /**
     * Register Admin Menus
     *
     * @since 1.0.0
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

    /**
     * @since 1.0.0
     * @param $key
     * @param $default
     * @return mixed|string
     */
    private function get_request_parameter( $key, $default = '' ) {
        // If not request set
        if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
            return $default;
        }

        // Set so process it
        return strip_tags( (string) wp_unslash( $_REQUEST[ $key ] ) );
    }


    /**
     * @return bool
     */
    public function is_webhook_valid() {
        if (strlen($this->plugin_options['webn_slack_inbound_webhook']) > 70) {
            return true;
        } else {
            return false;
        }
    }

}