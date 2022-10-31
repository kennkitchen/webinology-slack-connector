<?php
declare(strict_types=1);
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
            'webn_slack_alert_on_post_update' => 'no',
            'webn_slack_post_types' => 'all',
            'webn_slack_alert_on_new_comment' => 'no',
            'webn_slack_alert_on_available_updates' => 'no',
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

        $this->communicator = new Webinology_Slack_Connector_Comm(
                $this->plugin_name,
                $this->version,
                $this->logger,
                $this->plugin_options['webn_slack_inbound_webhook']
        );

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

        if ( ! wp_next_scheduled( 'webn_slack_cron_hook' ) ) {
            wp_schedule_event( time(), 'six_hours', 'webn_slack_cron_hook' );
        }

    }

    /**
     * @param $schedules
     * @return mixed
     */
    public function webn_slack_cron_schedules($schedules) {
        $schedules['six_hours'] = array(
            'interval' => 21600,
            'display'  => esc_html__( 'Every Six Hours' ), );

        return $schedules;
    }

    /**
     * @return void
     */
    public function webn_slack_cron_executor() {
        if ($this->plugin_options['webn_slack_alert_on_available_updates'] == 'yes') {
            $updates = get_site_transient( 'update_plugins' );

            if ($updates->response) {
                foreach ($updates->response as $response) {
                    // check for transient
                    $have_we_notified = null;
                    $transient_name = 'webn_slack_' . $response->slug;
                    $have_we_notified = get_transient( $transient_name );
                    if (!$have_we_notified) {
                        $result = $this->webn_slack_updates_available($response->slug);
                        set_transient( $transient_name, true, 86400 );
                    }
                }
            }
        }
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

        if (!in_array($post->post_type, $this->plugin_options['webn_slack_post_types'])) {
            return;
        }

        if ( ! empty( $_REQUEST['meta-box-loader'] ) ) { // phpcs:ignore
            return;
        }

        $result = null;

        if (($this->plugin_options['webn_slack_alert_on_published'] == 'yes') || ($this->plugin_options['webn_slack_alert_on_unpublish'] == 'yes'))  {
            $author = get_user_by('ID', $post->post_author);
            $site_name = get_bloginfo('name');
            $post_permalink = get_post_permalink($post->ID, true);

            if (($new_status != $old_status) && ($new_status == 'publish') && ($this->plugin_options['webn_slack_alert_on_published'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has just published "' . $post->post_title . '" on ' . $site_name . '. Check it out at: ' . $post_permalink;

                $result = $this->communicator->generic_curl($update_text);
            }

            if (($old_status == 'publish') && ($new_status != 'publish') && ($this->plugin_options['webn_slack_alert_on_unpublish'] == 'yes')) {
                $update_text = 'User ' . $author->display_name . ' has unpublished "' . $post->post_title . '" on ' . $site_name . '.';

                $result = $this->communicator->generic_curl($update_text);
            }
        }

        return $result;

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

        if (!in_array($post_after->post_type, $this->plugin_options['webn_slack_post_types'])) {
            return;
        }

        if ( ! empty( $_REQUEST['meta-box-loader'] ) ) { // phpcs:ignore
            return;
        }

        $result = null;

        if ($this->plugin_options['webn_slack_alert_on_post_update'] == 'yes') {
            $author = wp_get_current_user()->user_login;//   get_user_by('ID', $post_after->post_author);
            $site_name = get_bloginfo('name');
            $post_permalink = get_post_permalink($post_after->ID, true);

            $update_text = 'User ' . $author . ' has updated "' . $post_after->post_title . '" on ' . $site_name . '.';

            $result = $this->communicator->generic_curl($update_text);
        }

        return $result;
    }

    /**
     * @param int $comment_ID
     * @param string $comment_approved
     * @param array $commentdata
     * @return void
     */
    public function webn_slack_new_comment(int $comment_ID, string $comment_approved, array $commentdata) {
        $this->logger->debug('New comment hook fired.');

        $result = null;

        if ($this->plugin_options['webn_slack_alert_on_new_comment'] == 'yes') {
            $commenter = $commentdata['comment_author'];
            $site_name = get_bloginfo('name');
            $post = get_post($commentdata['comment_post_ID']);

            $update_text = 'There is a new comment on "' . $post->post_title . '" from ' . $commenter . ' on ' . $site_name . '.';

            $result = $this->communicator->generic_curl($update_text);

        }

        return $result;
    }

    /**
     * @param string $slug
     * @return void
     */
    private function webn_slack_updates_available(string $slug): string {
        $this->logger->debug('Update available for ' . $slug);

        if ($this->plugin_options['webn_slack_alert_on_available_updates'] == 'yes') {
            $site_name = get_bloginfo('name');
            $update_text = 'There is an update available for "' . $slug . '" on ' . $site_name . '.';
            $result = $this->communicator->generic_curl($update_text);
        } else {
            $result = $this->communicator->generic_curl('No updates found.');
        }

        return $result;
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
        require_once plugin_dir_path( __FILE__ ) . 'partials/webinology-slack-connector-admin-settings0.php';
    }

    /**
     *
     * @since 1.0.0
     */
    public function webn_slack_submenu1_page() {
        $page_name = $this->get_request_parameter('page');
        require_once plugin_dir_path( __FILE__ ) . 'partials/webinology-slack-connector-admin-settings1.php';
    }

    /**
     *
     * @since 1.0.0
     */
    public function webn_slack_submenu2_page() {
        $page_name = $this->get_request_parameter('page');
        require_once plugin_dir_path( __FILE__ ) . 'partials/webinology-slack-connector-admin-settings2.php';
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
        add_menu_page('Webinology Slack Connector', 'Slack Connector', 'manage_options', 'webn_slack_main_menu',
            [$this, 'webn_slack_main_menu_page'], 'dashicons-controls-volumeon');

        add_submenu_page('webn_slack_main_menu', 'Slack Connector Settings',
            'Alert Settings', 'manage_options', 'webn_slack_settings', [$this, 'webn_slack_settings_page']);

        add_submenu_page('webn_slack_main_menu', 'Slack Connector Submenu 1',
            'Slack Configuration', 'manage_options', 'webn_slack_submenu1', [$this, 'webn_slack_submenu1_page']);

        add_submenu_page('webn_slack_main_menu', 'Slack Connector Submenu 2',
            'Get Help', 'manage_options', 'webn_slack_submenu2', [$this, 'webn_slack_submenu2_page']);

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
        //todo refine this with regex
        if (preg_match('|https:\/\/hooks\.slack\.com\/services\/[a-z,A-Z,0-9]{9}\/[a-z,A-Z,0-9]{11}\/[a-z,A-Z,0-9]{24}|', $this->plugin_options['webn_slack_inbound_webhook'])) {
            return true;
        } else {
            return false;
        }
    }

    private function hook_preflight_checks($type) {
        $continue = false;


    }

}