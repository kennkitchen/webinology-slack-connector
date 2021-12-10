<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://kmde.us
 * @since      1.0.0
 *
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Webinology_Slack_Connector
 * @subpackage Webinology_Slack_Connector/includes
 * @author     KMD Enterprises, LLC <members@webinology.io>
 */
class Webinology_Slack_Connector {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Webinology_Slack_Connector_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
     * The Monolog Logger
     * @var Logger $logger
     */
    protected $logger;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WEBINOLOGY_SLACK_CONNECTOR_VERSION' ) ) {
			$this->version = WEBINOLOGY_SLACK_CONNECTOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'webinology-slack-connector';

        $this->logger = new Logger('WEBN-SLACK-CONNECTOR');
        $this->logger->pushHandler(new StreamHandler(PLUGIN_ROOT_PATH . 'webn_slack_connector.log', Logger::ERROR));

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Webinology_Slack_Connector_Loader. Orchestrates the hooks of the plugin.
	 * - Webinology_Slack_Connector_i18n. Defines internationalization functionality.
	 * - Webinology_Slack_Connector_Admin. Defines all hooks for the admin area.
	 * - Webinology_Slack_Connector_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webinology-slack-connector-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webinology-slack-connector-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webinology-slack-connector-admin.php';

        /**
         *
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webinology-slack-connector-comm.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-webinology-slack-connector-public.php';

		$this->loader = new Webinology_Slack_Connector_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Webinology_Slack_Connector_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Webinology_Slack_Connector_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Webinology_Slack_Connector_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_logger() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_admin, 'webn_slack_initialization' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'webn_slack_admin_menus' );

        if ($plugin_admin->is_webhook_valid()) {
            $this->loader->add_action( 'transition_post_status', $plugin_admin, 'webn_slack_post_transitions', 10, 3 );
            $this->loader->add_action( 'post_updated', $plugin_admin, 'webn_slack_post_updates', 10, 3 );
            $this->loader->add_action( 'comment_post', $plugin_admin, 'webn_slack_new_comment', 10 ,3 );
        }

        $this->loader->add_filter('cron_schedules', $plugin_admin, 'webn_slack_cron_schedules');
        $this->loader->add_action( 'webn_slack_cron_hook', $plugin_admin,'webn_slack_cron_executor');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Webinology_Slack_Connector_Public( $this->get_plugin_name(), $this->get_version(), $this->get_logger() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Webinology_Slack_Connector_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * Retrieve the Monolog logger
     *
     * @since     1.1.0
     * @return    Logger    The instantiated Monolog object
     */
    public function get_logger() {
        return $this->logger;
    }
}
