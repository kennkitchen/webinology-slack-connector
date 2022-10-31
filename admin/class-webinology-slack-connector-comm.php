<?php
declare(strict_types=1);
/**
 * The Comm-specific functionality of the plugin.
 *
 * @link       https://kmde.us
 * @since      1.2.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/comm
 */

/**
 * The Comm-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the Comm-specific stylesheet and JavaScript.
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/comm
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector_Comm {

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

    private $webhook;


    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version, $logger, $webhook ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->logger = $logger;
        $this->webhook = $webhook;
    }

    /**
     * Register the stylesheets for the Comm area.
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

        //wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/webinology-slack-connector-Comm.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the Comm area.
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

        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/webinology-slack-connector-Comm.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Generic cURL-to-Slack function.
     *
     * @since 1.0.0
     * @param string $messageText
     * @param string $messageUrl
     * @return bool|string
     */
    public function generic_curl(string $messageText) {
        $data = array(
            'text' => $messageText,
        );
        $post_data = json_encode($data);
        $crl = curl_init($this->webhook);

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

}