<?php
/**
 * Plugin Name: WP Admin Organizer
 * Plugin URI: https://example.com/wp-admin-organizer
 * Description: Organize WordPress admin menu items with drag and drop functionality, add separators and text separators.
 * Version: 1.0.0
 * Author: Daniel Maurel
 * Author URI: https://dmaurel.cl
 * Text Domain: wp-admin-organizer
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_ADMIN_ORGANIZER_VERSION', '1.0.0');
define('WP_ADMIN_ORGANIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_ADMIN_ORGANIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_ADMIN_ORGANIZER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once WP_ADMIN_ORGANIZER_PLUGIN_DIR . 'includes/class-wp-admin-organizer.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_wp_admin_organizer() {
    $plugin = new WP_Admin_Organizer();
    $plugin->run();
}

// Start the plugin
run_wp_admin_organizer();