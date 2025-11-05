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
     * Get configuration for a specific role or current user's role.
     *
     * @since    1.4.0
     * @param    string    $role    Optional. Role to get config for. Defaults to current user's role.
     * @param    string    $key     Optional. Specific config key to get. If empty, returns all config.
     * @return   mixed              Configuration value(s)
     */
    private function get_role_config($role = null, $key = null) {
        // If no role specified, use current user's primary role
        if ($role === null) {
            $user = wp_get_current_user();
            $roles = $user->roles;
            $role = !empty($roles) ? $roles[0] : 'administrator';
        }

        // Get all role configurations
        $role_configs = get_option('wp_admin_organizer_role_configs', array());

        // If no config exists for this role, try to migrate from old global options
        if (!isset($role_configs[$role])) {
            $role_configs[$role] = array(
                'menu_order' => get_option('wp_admin_organizer_menu_order', array()),
                'separators' => get_option('wp_admin_organizer_separators', array()),
                'hidden_items' => get_option('wp_admin_organizer_hidden_items', array()),
                'renamed_items' => get_option('wp_admin_organizer_renamed_items', array()),
                'favorite_items' => get_option('wp_admin_organizer_favorite_items', array()),
                'submenu_order' => get_option('wp_admin_organizer_submenu_order', array()),
                'custom_icons' => get_option('wp_admin_organizer_custom_icons', array()),
            );
        }

        $config = isset($role_configs[$role]) ? $role_configs[$role] : array();

        // If specific key requested, return that
        if ($key !== null) {
            return isset($config[$key]) ? $config[$key] : array();
        }

        return $config;
    }

    /**
     * Save configuration for a specific role.
     *
     * @since    1.4.0
     * @param    string    $role      Role to save config for
     * @param    string    $key       Config key to save
     * @param    mixed     $value     Value to save
     * @return   bool                 True on success, false on failure
     */
    private function save_role_config($role, $key, $value) {
        // Get all role configurations
        $role_configs = get_option('wp_admin_organizer_role_configs', array());

        // Initialize role config if it doesn't exist
        if (!isset($role_configs[$role])) {
            $role_configs[$role] = array();
        }

        // Set the value
        $role_configs[$role][$key] = $value;

        // Save back to database
        return update_option('wp_admin_organizer_role_configs', $role_configs);
    }

    /**
     * Get all available WordPress roles.
     *
     * @since    1.4.0
     * @return   array    Array of role names
     */
    private function get_all_roles() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        return $wp_roles->get_names();
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

        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_hidden_items',
            array('sanitize_callback' => array($this, 'sanitize_menu_order'))
        );

        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_renamed_items',
            array('sanitize_callback' => array($this, 'sanitize_renamed_items'))
        );

        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_favorite_items',
            array('sanitize_callback' => array($this, 'sanitize_menu_order'))
        );

        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_submenu_order',
            array('sanitize_callback' => array($this, 'sanitize_submenu_order'))
        );

        register_setting(
            'wp_admin_organizer_settings',
            'wp_admin_organizer_custom_icons',
            array('sanitize_callback' => array($this, 'sanitize_renamed_items'))
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
     * Sanitize the renamed items option.
     *
     * @since    1.1.0
     * @param    array    $input    The renamed items array.
     * @return   array    The sanitized renamed items array.
     */
    public function sanitize_renamed_items($input) {
        if (!is_array($input)) {
            return array();
        }

        $sanitized_input = array();

        foreach ($input as $key => $value) {
            $sanitized_input[sanitize_text_field($key)] = sanitize_text_field($value);
        }

        return $sanitized_input;
    }

    /**
     * Sanitize the submenu order option.
     *
     * @since    1.3.0
     * @param    array    $input    The submenu order array.
     * @return   array    The sanitized submenu order array.
     */
    public function sanitize_submenu_order($input) {
        if (!is_array($input)) {
            return array();
        }

        $sanitized_input = array();

        foreach ($input as $parent_slug => $submenu_items) {
            $sanitized_parent = sanitize_text_field($parent_slug);

            if (is_array($submenu_items)) {
                $sanitized_input[$sanitized_parent] = array_map('sanitize_text_field', $submenu_items);
            }
        }

        return $sanitized_input;
    }

    /**
     * Render the admin page for the plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_admin_page() {
        // Get the current admin menu and submenus
        global $menu, $submenu;

        // Get all available roles
        $available_roles = $this->get_all_roles();

        // Get the role we're currently editing (from URL or default to administrator)
        $editing_role = isset($_GET['role']) && !empty($_GET['role']) ? sanitize_text_field($_GET['role']) : 'administrator';

        // Make sure the role exists
        if (!array_key_exists($editing_role, $available_roles)) {
            $editing_role = 'administrator';
        }

        // Get configuration for the selected role
        $role_config = $this->get_role_config($editing_role);

        // Extract configuration values
        $saved_menu_order = isset($role_config['menu_order']) ? $role_config['menu_order'] : array();
        $saved_separators = isset($role_config['separators']) ? $role_config['separators'] : array();
        $hidden_items = isset($role_config['hidden_items']) ? $role_config['hidden_items'] : array();
        $renamed_items = isset($role_config['renamed_items']) ? $role_config['renamed_items'] : array();
        $favorite_items = isset($role_config['favorite_items']) ? $role_config['favorite_items'] : array();
        $saved_submenu_order = isset($role_config['submenu_order']) ? $role_config['submenu_order'] : array();
        $custom_icons = isset($role_config['custom_icons']) ? $role_config['custom_icons'] : array();

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

        // Get the role we're saving for (from POST or default to administrator)
        $role = isset($_POST['role']) && !empty($_POST['role']) ? sanitize_text_field($_POST['role']) : 'administrator';

        // Verify role exists
        $all_roles = $this->get_all_roles();
        if (!array_key_exists($role, $all_roles)) {
            wp_send_json_error('Invalid role');
        }

        // Get the menu order from the POST data
        $menu_order = isset($_POST['menu_order']) ? $_POST['menu_order'] : array();

        // Sanitize the menu order
        $menu_order = $this->sanitize_menu_order($menu_order);

        // Save the menu order for this role
        $this->save_role_config($role, 'menu_order', $menu_order);

        // Get the separators from the POST data
        $separators = isset($_POST['separators']) ? $_POST['separators'] : array();

        // Sanitize the separators
        $separators = $this->sanitize_separators($separators);

        // Save the separators for this role
        $this->save_role_config($role, 'separators', $separators);

        // Get hidden items from POST data
        $hidden_items = isset($_POST['hidden_items']) ? $_POST['hidden_items'] : array();

        // Sanitize hidden items
        $hidden_items = $this->sanitize_menu_order($hidden_items);

        // Save hidden items for this role
        $this->save_role_config($role, 'hidden_items', $hidden_items);

        // Get renamed items from POST data
        $renamed_items = isset($_POST['renamed_items']) ? $_POST['renamed_items'] : array();

        // Sanitize renamed items
        $renamed_items = $this->sanitize_renamed_items($renamed_items);

        // Save renamed items for this role
        $this->save_role_config($role, 'renamed_items', $renamed_items);

        // Get favorite items from POST data
        $favorite_items = isset($_POST['favorite_items']) ? $_POST['favorite_items'] : array();

        // Sanitize favorite items
        $favorite_items = $this->sanitize_menu_order($favorite_items);

        // Save favorite items for this role
        $this->save_role_config($role, 'favorite_items', $favorite_items);

        // Get submenu order from POST data
        $submenu_order = isset($_POST['submenu_order']) ? $_POST['submenu_order'] : array();

        // Sanitize submenu order
        $submenu_order = $this->sanitize_submenu_order($submenu_order);

        // Save submenu order for this role
        $this->save_role_config($role, 'submenu_order', $submenu_order);

        // Get custom icons from POST data
        $custom_icons = isset($_POST['custom_icons']) ? $_POST['custom_icons'] : array();

        // Sanitize custom icons
        $custom_icons = $this->sanitize_renamed_items($custom_icons);

        // Save custom icons for this role
        $this->save_role_config($role, 'custom_icons', $custom_icons);

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
     * AJAX handler for exporting configuration.
     *
     * @since    1.1.0
     */
    public function export_configuration() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_admin_organizer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Get all role configurations
        $role_configs = get_option('wp_admin_organizer_role_configs', array());

        // Build configuration export with role-based configs
        $configuration = array(
            'role_configs' => $role_configs,
            'logo' => get_option('wp_admin_organizer_logo', ''),
            'version' => WP_ADMIN_ORGANIZER_VERSION,
            'exported_at' => current_time('mysql')
        );

        wp_send_json_success($configuration);
    }

    /**
     * AJAX handler for importing configuration.
     *
     * @since    1.1.0
     */
    public function import_configuration() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_admin_organizer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // Get the configuration data from POST
        $configuration = isset($_POST['configuration']) ? $_POST['configuration'] : '';

        // Decode the JSON
        $config_data = json_decode($configuration, true);

        if (!$config_data || !is_array($config_data)) {
            wp_send_json_error('Invalid configuration data');
        }

        // Import logo (global setting)
        if (isset($config_data['logo'])) {
            update_option('wp_admin_organizer_logo', esc_url_raw($config_data['logo']));
        }

        // Check if this is new format (with role_configs) or old format
        if (isset($config_data['role_configs']) && is_array($config_data['role_configs'])) {
            // New format: Import role configurations directly
            $role_configs = $config_data['role_configs'];

            // Sanitize each role configuration
            foreach ($role_configs as $role => $config) {
                if (isset($config['menu_order'])) {
                    $role_configs[$role]['menu_order'] = $this->sanitize_menu_order($config['menu_order']);
                }
                if (isset($config['separators'])) {
                    $role_configs[$role]['separators'] = $this->sanitize_separators($config['separators']);
                }
                if (isset($config['hidden_items'])) {
                    $role_configs[$role]['hidden_items'] = $this->sanitize_menu_order($config['hidden_items']);
                }
                if (isset($config['renamed_items'])) {
                    $role_configs[$role]['renamed_items'] = $this->sanitize_renamed_items($config['renamed_items']);
                }
                if (isset($config['favorite_items'])) {
                    $role_configs[$role]['favorite_items'] = $this->sanitize_menu_order($config['favorite_items']);
                }
                if (isset($config['submenu_order'])) {
                    $role_configs[$role]['submenu_order'] = $this->sanitize_submenu_order($config['submenu_order']);
                }
                if (isset($config['custom_icons'])) {
                    $role_configs[$role]['custom_icons'] = $this->sanitize_renamed_items($config['custom_icons']);
                }
            }

            update_option('wp_admin_organizer_role_configs', $role_configs);
        } else {
            // Old format: Migrate to new format by assigning to administrator role
            $role_configs = get_option('wp_admin_organizer_role_configs', array());

            $role_configs['administrator'] = array(
                'menu_order' => isset($config_data['menu_order']) ? $this->sanitize_menu_order($config_data['menu_order']) : array(),
                'separators' => isset($config_data['separators']) ? $this->sanitize_separators($config_data['separators']) : array(),
                'hidden_items' => isset($config_data['hidden_items']) ? $this->sanitize_menu_order($config_data['hidden_items']) : array(),
                'renamed_items' => isset($config_data['renamed_items']) ? $this->sanitize_renamed_items($config_data['renamed_items']) : array(),
                'favorite_items' => isset($config_data['favorite_items']) ? $this->sanitize_menu_order($config_data['favorite_items']) : array(),
                'submenu_order' => isset($config_data['submenu_order']) ? $this->sanitize_submenu_order($config_data['submenu_order']) : array(),
                'custom_icons' => isset($config_data['custom_icons']) ? $this->sanitize_renamed_items($config_data['custom_icons']) : array(),
            );

            update_option('wp_admin_organizer_role_configs', $role_configs);
        }

        wp_send_json_success('Configuration imported successfully');
    }

    /**
     * Reorganize the admin menu based on saved settings.
     *
     * @since    1.0.0
     */
    public function reorganize_admin_menu() {
        global $menu;

        // Don't apply hiding when we're on the plugin's settings page
        // so users can still see and manage hidden items
        $is_plugin_page = isset($_GET['page']) && $_GET['page'] === 'wp-admin-organizer';

        // Add the logo at the top of the menu if one is set
        $this->add_admin_logo();

        // Get configuration for current user's role
        $role_config = $this->get_role_config();

        // Extract configuration values
        $saved_menu_order = isset($role_config['menu_order']) ? $role_config['menu_order'] : array();
        $hidden_items = isset($role_config['hidden_items']) ? $role_config['hidden_items'] : array();
        $renamed_items = isset($role_config['renamed_items']) ? $role_config['renamed_items'] : array();
        $favorite_items = isset($role_config['favorite_items']) ? $role_config['favorite_items'] : array();
        $custom_icons = isset($role_config['custom_icons']) ? $role_config['custom_icons'] : array();

        // If we have a saved menu order, reorganize the menu
        if (!empty($saved_menu_order)) {
            $new_menu = array();
            $position = 10;

            // Add favorite items at the top if any exist
            if (!empty($favorite_items)) {
                // Add favorites separator
                $new_menu[$position] = array(
                    __('Favorites', 'wp-admin-organizer'),
                    'read',
                    'favorites-separator-header',
                    '',
                    'wp-menu-separator wp-admin-organizer-favorites-separator'
                );
                $position++;

                // Add each favorite item
                foreach ($favorite_items as $fav_item_id) {
                    // Skip if item is hidden and we're not on plugin page
                    if (!$is_plugin_page && in_array($fav_item_id, $hidden_items)) {
                        continue;
                    }

                    // Find the favorite item in the menu
                    foreach ($menu as $index => $item) {
                        if (isset($item[2]) && $item[2] === $fav_item_id) {
                            // Clone the item for favorites (don't remove from original yet)
                            $fav_item = $item;

                            // Apply renamed title if exists
                            if (isset($renamed_items[$fav_item_id])) {
                                $fav_item[0] = $renamed_items[$fav_item_id];
                            }

                            // Add star icon before the title
                            $fav_item[0] = '<span class="dashicons dashicons-star-filled" style="font-size: 17px; margin-right: 5px; color: #f1c40f;"></span>' . $fav_item[0];

                            // Add custom class to identify favorites
                            if (isset($fav_item[4])) {
                                $fav_item[4] .= ' wp-admin-organizer-favorite-item';
                            } else {
                                $fav_item[4] = 'wp-admin-organizer-favorite-item';
                            }

                            $new_menu[$position] = $fav_item;
                            $position++;
                            break;
                        }
                    }
                }

                // Add separator after favorites
                $new_menu[$position] = array(
                    '',
                    'read',
                    'favorites-separator-bottom',
                    '',
                    'wp-menu-separator'
                );
                $position += 10; // Larger gap after favorites section
            }

            // Loop through the saved menu order
            foreach ($saved_menu_order as $item_id) {
                // Find the menu item in the current menu first
                $found_item = null;
                $found_index = null;
                foreach ($menu as $index => $item) {
                    if (isset($item[2]) && $item[2] === $item_id) {
                        $found_item = $item;
                        $found_index = $index;
                        break;
                    }
                }

                // If item not found, skip
                if ($found_item === null) {
                    continue;
                }

                // Remove the item from the original menu regardless of whether it's hidden
                unset($menu[$found_index]);

                // Skip hidden items ONLY if we're not on the plugin settings page
                if (!$is_plugin_page && in_array($item_id, $hidden_items)) {
                    continue;
                }

                // Apply renamed title if exists
                if (isset($renamed_items[$item_id])) {
                    $found_item[0] = $renamed_items[$item_id];
                }

                // Apply custom icon if exists
                if (isset($custom_icons[$item_id]) && !empty($custom_icons[$item_id])) {
                    $found_item[6] = $custom_icons[$item_id];
                }

                // Add the item to the new menu at the next position
                $new_menu[$position] = $found_item;
                // Increment the position
                $position += 10;
            }

            // Add any remaining items to the end of the new menu
            foreach ($menu as $index => $item) {
                if (isset($item[2]) && !in_array($item[2], $saved_menu_order)) {
                    // Skip hidden items ONLY if we're not on the plugin settings page
                    if (!$is_plugin_page && in_array($item[2], $hidden_items)) {
                        continue;
                    }

                    // Apply renamed title if exists
                    if (isset($renamed_items[$item[2]])) {
                        $item[0] = $renamed_items[$item[2]];
                    }

                    $new_menu[$position] = $item;
                    $position += 10;
                }
            }

            // Replace the global menu with our new menu
            $menu = $new_menu;
        } else {
            // Even if no menu order, still apply hiding and renaming
            foreach ($menu as $index => $item) {
                if (isset($item[2])) {
                    // Hide items ONLY if we're not on the plugin settings page
                    if (!$is_plugin_page && in_array($item[2], $hidden_items)) {
                        unset($menu[$index]);
                        continue;
                    }

                    // Rename items
                    if (isset($renamed_items[$item[2]])) {
                        $menu[$index][0] = $renamed_items[$item[2]];
                    }
                }
            }
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

        // Get saved separators for current user's role
        $separators = $this->get_role_config(null, 'separators');
        
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

    /**
     * Reorganize submenus based on saved settings.
     *
     * @since    1.3.0
     */
    public function reorganize_submenus() {
        global $submenu;

        // Get saved submenu order for current user's role
        $saved_submenu_order = $this->get_role_config(null, 'submenu_order');

        if (empty($saved_submenu_order) || !is_array($submenu)) {
            return;
        }

        // Loop through each parent menu that has custom submenu order
        foreach ($saved_submenu_order as $parent_slug => $submenu_slugs) {
            // Check if this parent menu has submenus
            if (!isset($submenu[$parent_slug]) || !is_array($submenu[$parent_slug])) {
                continue;
            }

            $current_submenu = $submenu[$parent_slug];
            $new_submenu = array();
            $position = 0;

            // Add submenus in the saved order
            foreach ($submenu_slugs as $submenu_slug) {
                // Find the submenu item
                foreach ($current_submenu as $index => $sub_item) {
                    if (isset($sub_item[2]) && $sub_item[2] === $submenu_slug) {
                        $new_submenu[$position] = $sub_item;
                        unset($current_submenu[$index]);
                        $position++;
                        break;
                    }
                }
            }

            // Add any remaining submenus that weren't in the saved order
            foreach ($current_submenu as $sub_item) {
                $new_submenu[$position] = $sub_item;
                $position++;
            }

            // Replace the submenu with the reordered version
            if (!empty($new_submenu)) {
                $submenu[$parent_slug] = $new_submenu;
            }
        }
    }
}