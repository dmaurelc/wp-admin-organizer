<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Admin_Organizer
 * @subpackage WP_Admin_Organizer/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin area.
 *
 * @package    WP_Admin_Organizer
 * @subpackage WP_Admin_Organizer/admin
 */
class WP_Admin_Organizer_Admin {

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
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only load on our plugin page
        if (isset($_GET['page']) && $_GET['page'] === 'wp-admin-organizer') {
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
            wp_enqueue_style($this->plugin_name, WP_ADMIN_ORGANIZER_PLUGIN_URL . 'admin/css/wp-admin-organizer-admin.css', array(), $this->version, 'all');
        }
        
        // Load separator styles on all admin pages
        wp_enqueue_style($this->plugin_name . '-separators', WP_ADMIN_ORGANIZER_PLUGIN_URL . 'admin/css/wp-admin-organizer-separators.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only load on our plugin page
        if (isset($_GET['page']) && $_GET['page'] === 'wp-admin-organizer') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script($this->plugin_name, WP_ADMIN_ORGANIZER_PLUGIN_URL . 'admin/js/wp-admin-organizer-admin.js', array('jquery', 'jquery-ui-sortable', 'jquery-ui-dialog'), $this->version, false);
            
            // Add the ajax url and nonce to our script
            wp_localize_script($this->plugin_name, 'wp_admin_organizer', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_admin_organizer_nonce'),
                'strings' => array(
                    'save_success' => __('Menu order saved successfully!', 'wp-admin-organizer'),
                    'save_error' => __('Error saving menu order.', 'wp-admin-organizer'),
                    'separator_added' => __('Separator added successfully!', 'wp-admin-organizer'),
                    'separator_error' => __('Error adding separator.', 'wp-admin-organizer'),
                    'confirm_reset' => __('Are you sure you want to reset the menu to default? This cannot be undone.', 'wp-admin-organizer'),
                    'select_logo' => __('Select Logo', 'wp-admin-organizer'),
                    'use_this_logo' => __('Use this logo', 'wp-admin-organizer'),
                    'logo_save_error' => __('Error saving logo.', 'wp-admin-organizer'),
                )
            ));
            
            // Enqueue WordPress media uploader scripts
            wp_enqueue_media();
        }
    }

    /**
     * Add the options page for the plugin.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_options_page(
            __('WP Admin Organizer', 'wp-admin-organizer'),
            __('Admin Organizer', 'wp-admin-organizer'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page')
        );
    }

    /**
     * Register settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_menu_order',
            array('sanitize_callback' => array($this, 'sanitize_menu_order'))
        );
        
        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_separators',
            array('sanitize_callback' => array($this, 'sanitize_separators'))
        );
        
        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_logo',
            array('sanitize_callback' => 'esc_url_raw')
        );
    }

    /**
     * Sanitize the menu order option.
     *
     * @since    1.0.0
     * @param    array    $input    The menu order array.
     * @return   array    The sanitized menu order array.
     */
    public function sanitize_menu_order($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized_input = array();
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $sanitized_input[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_input[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized_input;
    }

    /**
     * Sanitize the separators option.
     *
     * @since    1.0.0
     * @param    array    $input    The separators array.
     * @return   array    The sanitized separators array.
     */
    public function sanitize_separators($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized_input = array();
        
        foreach ($input as $key => $separator) {
            $sanitized_separator = array();
            
            if (isset($separator['position'])) {
                $sanitized_separator['position'] = intval($separator['position']);
            }
            
            if (isset($separator['type'])) {
                $sanitized_separator['type'] = sanitize_text_field($separator['type']);
            }
            
            if (isset($separator['text'])) {
                $sanitized_separator['text'] = sanitize_text_field($separator['text']);
            }
            
            $sanitized_input[$key] = $sanitized_separator;
        }
        
        return $sanitized_input;
    }

    /**
     * Render the admin page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        // Get the current admin menu
        global $menu;
        
        // Get saved menu order
        $saved_menu_order = get_option('wp_admin_organizer_menu_order', array());
        
        // Get saved separators
        $saved_separators = get_option('wp_admin_organizer_separators', array());
        
        include_once WP_ADMIN_ORGANIZER_PLUGIN_DIR . 'admin/partials/wp-admin-organizer-admin-display.php';
    }

    /**
     * AJAX handler for saving menu order.
     *
     * @since    1.0.0
     */
    public function save_menu_order() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_admin_organizer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get the menu order from the POST data
        $menu_order = isset($_POST['menu_order']) ? $_POST['menu_order'] : array();
        
        // Sanitize the menu order
        $menu_order = $this->sanitize_menu_order($menu_order);
        
        // Save the menu order
        update_option('wp_admin_organizer_menu_order', $menu_order);
        
        // Get the separators from the POST data
        $separators = isset($_POST['separators']) ? $_POST['separators'] : array();
        
        // Sanitize the separators
        $separators = $this->sanitize_separators($separators);
        
        // Save the separators
        update_option('wp_admin_organizer_separators', $separators);
        
        wp_send_json_success('Menu order saved successfully');
    }

    /**
     * AJAX handler for adding a separator.
     *
     * @since    1.0.0
     */
    public function add_separator() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_admin_organizer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get the separator type and position from the POST data
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'simple';
        $position = isset($_POST['position']) ? intval($_POST['position']) : 0;
        $text = isset($_POST['text']) ? sanitize_text_field($_POST['text']) : '';
        
        // Get saved separators
        $separators = get_option('wp_admin_organizer_separators', array());
        
        // Add the new separator
        $separators[] = array(
            'position' => $position,
            'type' => $type,
            'text' => $text
        );
        
        // Save the separators
        update_option('wp_admin_organizer_separators', $separators);
        
        wp_send_json_success('Separator added successfully');
    }
    
    /**
     * AJAX handler for saving the logo URL.
     *
     * @since    1.0.0
     */
    public function save_logo() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_admin_organizer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get the logo URL from the POST data
        $logo_url = isset($_POST['logo_url']) ? esc_url_raw($_POST['logo_url']) : '';
        
        // Save the logo URL
        update_option('wp_admin_organizer_logo', $logo_url);
        
        wp_send_json_success('Logo saved successfully');
    }

    /**
     * Reorganize the admin menu based on saved settings.
     *
     * @since    1.0.0
     */
    public function reorganize_admin_menu() {
        global $menu;
        
        // Add the logo at the top of the menu if one is set
        $this->add_admin_logo();
        
        // Get saved menu order
        $saved_menu_order = get_option('wp_admin_organizer_menu_order', array());
        
        // If we have a saved menu order, reorganize the menu
        if (!empty($saved_menu_order)) {
            $new_menu = array();
            $position = 10;
            
            // Loop through the saved menu order
            foreach ($saved_menu_order as $item_id) {
                // Find the menu item in the current menu
                foreach ($menu as $index => $item) {
                    if (isset($item[2]) && $item[2] === $item_id) {
                        // Add the item to the new menu at the next position
                        $new_menu[$position] = $item;
                        // Remove the item from the original menu
                        unset($menu[$index]);
                        // Increment the position
                        $position += 10;
                        break;
                    }
                }
            }
            
            // Add any remaining items to the end of the new menu
            foreach ($menu as $index => $item) {
                if (isset($item[2]) && !in_array($item[2], $saved_menu_order)) {
                    $new_menu[$position] = $item;
                    $position += 10;
                }
            }
            
            // Replace the global menu with our new menu
            $menu = $new_menu;
        }
        
        // Add separators
        $this->add_menu_separators();
    }
    
    /**
     * Add the admin logo to the top of the admin menu.
     *
     * @since    1.0.0
     */
    private function add_admin_logo() {
        $logo_url = get_option('wp_admin_organizer_logo', '');
        
        if (!empty($logo_url)) {
            // Add the logo HTML to the admin footer so it can be moved to the top of the menu via JavaScript
            add_action('admin_footer', function() use ($logo_url) {
                echo '<div class="wp-admin-organizer-admin-logo">';                
                echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr__('Admin Logo', 'wp-admin-organizer') . '">';                
                echo '</div>';
                
                // Add JavaScript to move the logo to the top of the menu
                echo '<script>
                    jQuery(document).ready(function($) {
                        // Move the logo to the top of the admin menu
                        $(".wp-admin-organizer-admin-logo").prependTo("#adminmenu");
                    });
                </script>';
            });
        }
    }

    /**
     * Add separators to the admin menu.
     *
     * @since    1.0.0
     */
    private function add_menu_separators() {
        global $menu;
        
        // Get saved separators
        $separators = get_option('wp_admin_organizer_separators', array());
        
        // If we have separators, add them to the menu
        if (!empty($separators)) {
            // Sort separators by position to ensure they're added in the correct order
            usort($separators, function($a, $b) {
                $pos_a = isset($a['position']) ? intval($a['position']) : 0;
                $pos_b = isset($b['position']) ? intval($b['position']) : 0;
                return $pos_a - $pos_b;
            });
            
            // Get all menu items that are not separators
            $menu_items = array();
            foreach ($menu as $position => $item) {
                // Skip existing separators
                if (isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) {
                    continue;
                }
                $menu_items[] = $position;
            }
            sort($menu_items);
            
            // Add each separator
            foreach ($separators as $index => $separator) {
                $position = isset($separator['position']) ? intval($separator['position']) : 0;
                $type = isset($separator['type']) ? $separator['type'] : 'simple';
                $text = isset($separator['text']) ? $separator['text'] : '';
                
                // Calculate the actual menu position
                $separator_position = 0;
                
                // If position is within the range of menu items
                if ($position < count($menu_items)) {
                    // Place separator before the menu item at this position
                    $separator_position = $menu_items[$position] - 0.1;
                } else {
                    // Place at the end
                    $separator_position = end($menu_items) + 1;
                }
                
                // Add the separator to the menu
                $this->add_separator_to_menu($menu, $separator_position, $type, $text, $index);
            }
        }
    }

    /**
     * Add a separator to the admin menu.
     *
     * @since    1.0.0
     * @param    array     $menu       The admin menu array.
     * @param    int       $position   The position to add the separator.
     * @param    string    $type       The type of separator (simple or text).
     * @param    string    $text       The text for the separator (if type is text).
     * @param    int       $index      The index of the separator for unique class.
     */
    private function add_separator_to_menu(&$menu, $position, $type = 'simple', $text = '', $index = 0) {
        // Create a unique class for the separator
        $class = 'wp-menu-separator';
        
        if ($type === 'text') {
            $class .= ' separator-with-text separator-' . $index;
            
            // Add custom CSS for this text separator
            add_action('admin_head', function() use ($index, $text) {
                echo '<style>
                .separator-' . $index . '::before {
                    content: "' . esc_attr($text) . '";
                    font-size: 8px;
                    text-transform: uppercase;
                    color: #a8aaad;
                    font-weight: 500;
                    border: 1px solid #494a4c;
                    padding: 1px 3px;
                    border-radius: 3px;
                    background-color: #1d2327;
                    position: absolute;
                    top: -7px;
                    display: inline-block;
                    transform: translate(8px, 1px);
                    line-height: 1;
                }
                .separator-' . $index . ' {
                    border-bottom: 1px solid #484a4c;
                    position: relative;
                }
                </style>';
            });
        } else {
            $class .= ' simple-separator';
        }
        
        // Add the separator to the menu
        $menu[$position] = array(
            '',
            'read',
            'separator-' . $position,
            '',
            $class
        );
    }
}