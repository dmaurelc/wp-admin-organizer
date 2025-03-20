<?php
/**
 * The main plugin class.
 *
 * This is the main class that handles the core functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Admin_Organizer
 * @subpackage WP_Admin_Organizer/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class WP_Admin_Organizer {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Admin_Organizer_Loader    $loader    Maintains and registers all hooks for the plugin.
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
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WP_ADMIN_ORGANIZER_VERSION')) {
            $this->version = WP_ADMIN_ORGANIZER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wp-admin-organizer';

        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WP_Admin_Organizer_Loader. Orchestrates the hooks of the plugin.
     * - WP_Admin_Organizer_Admin. Defines all hooks for the admin area.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once WP_ADMIN_ORGANIZER_PLUGIN_DIR . 'includes/class-wp-admin-organizer-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once WP_ADMIN_ORGANIZER_PLUGIN_DIR . 'admin/class-wp-admin-organizer-admin.php';

        $this->loader = new WP_Admin_Organizer_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Admin_Organizer_Admin($this->get_plugin_name(), $this->get_version());
        
        // Add admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Register settings
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Enqueue styles and scripts
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Register AJAX handlers
        $this->loader->add_action('wp_ajax_save_menu_order', $plugin_admin, 'save_menu_order');
        $this->loader->add_action('wp_ajax_add_separator', $plugin_admin, 'add_separator');
        $this->loader->add_action('wp_ajax_save_logo', $plugin_admin, 'save_logo');
        
        // Apply menu reorganization
        $this->loader->add_action('admin_menu', $plugin_admin, 'reorganize_admin_menu', 999);
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
     * @return    WP_Admin_Organizer_Loader    Orchestrates the hooks of the plugin.
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
}