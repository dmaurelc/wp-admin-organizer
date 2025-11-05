<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Admin_Organizer
 * @subpackage WP_Admin_Organizer/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap wp-admin-organizer-container">
    <div class="wp-admin-organizer-header">
        <div class="wp-admin-organizer-header-top">
            <div class="wp-admin-organizer-header-title">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            </div>

            <?php if ($is_admin && $config_mode !== 'personal') : ?>
                <!-- Admin Mode: Compact Configuration Selector -->
                <div class="wp-admin-organizer-config-compact">
                    <div class="config-mode-switcher">
                        <div class="mode-toggle">
                            <button type="button" class="mode-tab-compact <?php echo $config_mode === 'role' ? 'active' : ''; ?>" data-mode="role">
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                <?php _e('Roles', 'wp-admin-organizer'); ?>
                            </button>
                            <button type="button" class="mode-tab-compact <?php echo $config_mode === 'user' ? 'active' : ''; ?>" data-mode="user">
                                <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php _e('Users', 'wp-admin-organizer'); ?>
                            </button>
                            <?php if (count($users_with_personal_configs) > 0) : ?>
                                <span class="badge-compact"><?php echo count($users_with_personal_configs); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($config_mode === 'role') : ?>
                            <!-- Role Selector -->
                            <div class="selector-inline">
                                <select id="role-selector" name="role-selector" class="select-compact">
                                    <?php foreach ($available_roles as $role_key => $role_name) : ?>
                                        <option value="<?php echo esc_attr($role_key); ?>" <?php selected($editing_role, $role_key); ?>>
                                            <?php echo esc_html($role_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php elseif ($config_mode === 'user') : ?>
                            <!-- User Selector -->
                            <div class="selector-inline">
                                <select id="user-selector" name="user-selector" class="select-compact">
                                    <?php foreach ($all_users as $user) : ?>
                                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($editing_user_id, $user->ID); ?>>
                                            <?php
                                            echo esc_html($user->display_name);
                                            if (!empty($user->roles)) {
                                                echo ' (' . esc_html(translate_user_role(ucfirst($user->roles[0]))) . ')';
                                            }
                                            if (isset($user_has_personal_config_map[$user->ID]) && $user_has_personal_config_map[$user->ID]) {
                                                echo ' ★';
                                            }
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- User Actions (compact) -->
                                <div class="user-actions-compact">
                                    <?php if (!$has_personal_config) : ?>
                                        <button type="button" id="enable-personal-config" class="btn-action-compact btn-secondary-compact">
                                            <?php _e('Enable Personal', 'wp-admin-organizer'); ?>
                                        </button>
                                    <?php else : ?>
                                        <button type="button" id="copy-from-role" class="btn-action-compact btn-secondary-compact">
                                            <?php _e('Copy from Role', 'wp-admin-organizer'); ?>
                                        </button>
                                        <button type="button" id="reset-personal-config" class="btn-action-compact btn-danger-compact">
                                            <?php _e('Reset', 'wp-admin-organizer'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- User Info Card -->
                            <div class="user-info-card">
                                <div class="user-info-row">
                                    <strong><?php _e('User:', 'wp-admin-organizer'); ?></strong>
                                    <span class="user-name"><?php
                                        // Find and display current editing user info
                                        foreach ($all_users as $user) {
                                            if ($user->ID == $editing_user_id) {
                                                echo esc_html($user->display_name);
                                                break;
                                            }
                                        }
                                    ?></span>
                                </div>
                                <div class="user-info-row">
                                    <strong><?php _e('Role:', 'wp-admin-organizer'); ?></strong>
                                    <span class="user-role"><?php
                                        foreach ($all_users as $user) {
                                            if ($user->ID == $editing_user_id) {
                                                if (!empty($user->roles)) {
                                                    echo esc_html(translate_user_role(ucfirst($user->roles[0])));
                                                }
                                                break;
                                            }
                                        }
                                    ?></span>
                                </div>
                                <div class="user-info-row">
                                    <strong><?php _e('Config Status:', 'wp-admin-organizer'); ?></strong>
                                    <span class="config-status <?php echo $has_personal_config ? 'personal' : 'role'; ?>">
                                        <?php echo $has_personal_config ? '★ ' . __('Personal Config', 'wp-admin-organizer') : __('Using Role Config', 'wp-admin-organizer'); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else : ?>
                <!-- Personal Mode: Compact Status -->
                <div class="wp-admin-organizer-personal-config-compact">
                    <div class="status-bar">
                        <?php if ($has_personal_config) : ?>
                            <div class="status-indicator active">
                                <span class="status-dot"></span>
                                <span><?php _e('Personal Config Active', 'wp-admin-organizer'); ?></span>
                                <button type="button" id="copy-from-role-personal" class="btn-action-compact btn-secondary-compact">
                                    <?php _e('Reset to Role', 'wp-admin-organizer'); ?>
                                </button>
                                <button type="button" id="disable-personal-config" class="btn-action-compact btn-danger-compact">
                                    <?php _e('Disable', 'wp-admin-organizer'); ?>
                                </button>
                            </div>
                        <?php else : ?>
                            <div class="status-indicator inactive">
                                <span class="status-dot"></span>
                                <span>
                                    <?php
                                    $user = wp_get_current_user();
                                    $role_name = !empty($user->roles) ? translate_user_role(ucfirst($user->roles[0])) : 'User';
                                    printf(__('Using %s role', 'wp-admin-organizer'), '<strong>' . esc_html($role_name) . '</strong>');
                                    ?>
                                </span>
                                <button type="button" id="enable-personal-config-self" class="btn-action-compact btn-primary-compact">
                                    <?php _e('Enable Personal', 'wp-admin-organizer'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="wp-admin-organizer-content">
        <div class="wp-admin-organizer-menu-items">
            <div class="wp-admin-organizer-menu-header">
                <h2><?php _e('Menu Items', 'wp-admin-organizer'); ?></h2>
                <div class="wp-admin-organizer-search-container">
                    <input type="text" id="menu-search" placeholder="<?php _e('Search menu items...', 'wp-admin-organizer'); ?>" class="widefat">
                    <span class="dashicons dashicons-search"></span>
                </div>
            </div>
            
            <div class="wp-admin-organizer-menu-list">
            <?php
            // Prepare an array to hold all items (menu items and separators) in their correct positions
            $all_items = array();
            
            // Display current menu items
            if (!empty($menu)) {
                // First, add saved menu items to the all_items array
                if (!empty($saved_menu_order)) {
                    foreach ($saved_menu_order as $item_id) {
                        foreach ($menu as $index => $item) {
                            if (isset($item[2]) && $item[2] === $item_id) {
                                // Skip separators
                                if (strpos($item[4], 'wp-menu-separator') !== false) {
                                    continue;
                                }
                                
                                // Add the menu item to the all_items array
                                $all_items[] = array(
                                    'type' => 'menu_item',
                                    'id' => $item[2],
                                    'title' => wp_strip_all_tags($item[0])
                                );
                                
                                break;
                            }
                        }
                    }
                }
                
                // Then, add any remaining menu items to the all_items array
                foreach ($menu as $index => $item) {
                    // Skip separators
                    if (strpos($item[4], 'wp-menu-separator') !== false) {
                        continue;
                    }
                    
                    // Skip items that are already added
                    if (!empty($saved_menu_order) && in_array($item[2], $saved_menu_order)) {
                        continue;
                    }
                    
                    // Add the menu item to the all_items array
                    $all_items[] = array(
                        'type' => 'menu_item',
                        'id' => $item[2],
                        'title' => wp_strip_all_tags($item[0])
                    );
                }
            }
            
            // Add saved separators to the all_items array at their correct positions
            if (!empty($saved_separators)) {
                foreach ($saved_separators as $index => $separator) {
                    $position = isset($separator['position']) ? intval($separator['position']) : 0;
                    $type = isset($separator['type']) ? $separator['type'] : 'simple';
                    $text = isset($separator['text']) ? $separator['text'] : '';
                    
                    // Create a separator item and insert it at the correct position
                    $separator_item = array(
                        'type' => 'separator',
                        'separator_type' => $type,
                        'text' => $text,
                        'position' => $position
                    );
                    
                    // Insert the separator at the specified position, or append it if position is beyond array bounds
                    if ($position < count($all_items)) {
                        // Insert at position
                        array_splice($all_items, $position, 0, array($separator_item));
                    } else {
                        // Append to the end
                        $all_items[] = $separator_item;
                    }
                }
            }
            
            // Now display all items in their correct order
            $position = 0;
            foreach ($all_items as $item) {
                if ($item['type'] === 'menu_item') {
                    // Display menu item with position number integrated in the title
                    $position++;
                    $is_hidden = in_array($item['id'], $hidden_items);
                    $is_favorite = in_array($item['id'], $favorite_items);
                    $custom_name = isset($renamed_items[$item['id']]) ? $renamed_items[$item['id']] : '';
                    $display_title = !empty($custom_name) ? $custom_name : $item['title'];

                    echo '<div class="wp-admin-organizer-menu-item' . ($is_hidden ? ' hidden' : '') . ($is_favorite ? ' favorite' : '') . '" data-menu-id="' . esc_attr($item['id']) . '"' . (!empty($custom_name) ? ' data-custom-name="' . esc_attr($custom_name) . '"' : '') . '>';

                    // Menu item header with title and action buttons
                    echo '<div class="wp-admin-organizer-menu-item-header">';
                    echo '<div class="wp-admin-organizer-drag-handle"></div>';
                    echo '<div class="wp-admin-organizer-menu-item-title">#' . esc_html($position) . ' - ' . esc_html($display_title) . '</div>';

                    // Action buttons container - aligned to the right
                    echo '<div class="wp-admin-organizer-menu-item-actions">';

                    // Check if this menu item has submenus - add toggle icon
                    $has_submenus = isset($submenu[$item['id']]) && !empty($submenu[$item['id']]);
                    if ($has_submenus) {
                        echo '<a href="#" class="toggle-submenu" title="' . __('Toggle Submenus', 'wp-admin-organizer') . '"><span class="dashicons dashicons-arrow-down-alt2"></span></a>';
                    }

                    echo '<a href="#" class="toggle-favorite" title="' . ($is_favorite ? __('Remove from Favorites', 'wp-admin-organizer') : __('Add to Favorites', 'wp-admin-organizer')) . '"><span class="dashicons dashicons-star-' . ($is_favorite ? 'filled' : 'empty') . '"></span></a>';
                    echo '<a href="#" class="toggle-visibility" title="' . ($is_hidden ? __('Show', 'wp-admin-organizer') : __('Hide', 'wp-admin-organizer')) . '"><span class="dashicons dashicons-' . ($is_hidden ? 'hidden' : 'visibility') . '"></span></a>';

                    echo '</div>'; // .wp-admin-organizer-menu-item-actions
                    echo '</div>'; // .wp-admin-organizer-menu-item-header

                    echo '<input type="hidden" class="position" value="' . esc_attr($position) . '">';

                    // Check if this menu item has submenus
                    if ($has_submenus) {
                        echo '<div class="wp-admin-organizer-submenu-list" data-parent-id="' . esc_attr($item['id']) . '">';

                        // Get saved submenu order for this parent
                        $parent_submenu_order = isset($saved_submenu_order[$item['id']]) ? $saved_submenu_order[$item['id']] : array();

                        // Prepare submenu items
                        $submenu_items_to_display = array();

                        if (!empty($parent_submenu_order)) {
                            // Display in saved order
                            foreach ($parent_submenu_order as $sub_slug) {
                                foreach ($submenu[$item['id']] as $sub_item) {
                                    if (isset($sub_item[2]) && $sub_item[2] === $sub_slug) {
                                        $submenu_items_to_display[] = $sub_item;
                                        break;
                                    }
                                }
                            }
                            // Add remaining items not in saved order
                            foreach ($submenu[$item['id']] as $sub_item) {
                                if (!in_array($sub_item[2], $parent_submenu_order)) {
                                    $submenu_items_to_display[] = $sub_item;
                                }
                            }
                        } else {
                            // Use default order
                            $submenu_items_to_display = $submenu[$item['id']];
                        }

                        // Display submenu items
                        foreach ($submenu_items_to_display as $sub_item) {
                            echo '<div class="wp-admin-organizer-submenu-item" data-submenu-id="' . esc_attr($sub_item[2]) . '">';
                            echo '<div class="wp-admin-organizer-submenu-drag-handle"></div>';
                            echo '<div class="wp-admin-organizer-submenu-item-title">' . wp_strip_all_tags($sub_item[0]) . '</div>';
                            echo '</div>';
                        }

                        echo '</div>';
                    }

                    echo '</div>';
                } else if ($item['type'] === 'separator') {
                    // Display separator with position number integrated in the title
                    $position++;
                    $type = $item['separator_type'];
                    $text = $item['text'];
                    $sep_position = $item['position'];

                    echo '<div class="wp-admin-organizer-separator-item' . ($type === 'text' ? ' text-separator' : '') . '">';
                    echo '<div class="wp-admin-organizer-separator-item-title">#' . esc_html($position) . ' - ' .
                         esc_html($type === 'text' ? 'Text Separator: ' . $text : 'Simple Separator') . '</div>';
                    echo '<a href="#" class="remove-separator">' . __('Remove', 'wp-admin-organizer') . '</a>';
                    echo '<span class="separator-text" style="display:none;">' . esc_html($text) . '</span>';
                    echo '<input type="hidden" class="position" value="' . esc_attr($position) . '">';
                    echo '<input type="hidden" name="position" value="' . esc_attr($sep_position) . '">';
                    echo '</div>';
                }
            }
            ?>
            </div>
            
            <div class="wp-admin-organizer-buttons">
                <button id="reset-menu-order" class="button button-secondary"><?php _e('Reset to Default', 'wp-admin-organizer'); ?></button>
            </div>
        </div>
        
        <div class="wp-admin-organizer-sidebar">
            <div class="wp-admin-organizer-settings-container">
                <div class="wp-admin-organizer-settings-section">
                    <h3><?php _e('Save Changes', 'wp-admin-organizer'); ?></h3>
                    <button id="save-menu-order" class="button button-primary"><?php _e('Save Changes', 'wp-admin-organizer'); ?></button>
                    <button id="reset-menu-order" class="button button-secondary"><?php _e('Reset to Default', 'wp-admin-organizer'); ?></button>
                    <p><?php _e('Click to save your menu organization and separator settings.', 'wp-admin-organizer'); ?></p>
                </div>
                
                <div class="wp-admin-organizer-settings-section">
                    <h3><?php _e('Add Separator', 'wp-admin-organizer'); ?></h3>
                    
                    <div class="wp-admin-organizer-add-separator-form">
                        <div class="form-field">
                            <label for="separator-type"><?php _e('Separator Type', 'wp-admin-organizer'); ?></label>
                            <select id="separator-type" name="separator-type" class="widefat">
                                <option value="simple"><?php _e('Simple Separator', 'wp-admin-organizer'); ?></option>
                                <option value="text"><?php _e('Text Separator', 'wp-admin-organizer'); ?></option>
                            </select>
                        </div>
                        
                        <div class="form-field text-field">
                            <label for="separator-text"><?php _e('Separator Text', 'wp-admin-organizer'); ?></label>
                            <input type="text" id="separator-text" name="separator-text" class="widefat" placeholder="<?php _e('Enter text for separator', 'wp-admin-organizer'); ?>">
                            <p class="description"><?php _e('Text will appear above the separator line', 'wp-admin-organizer'); ?></p>
                        </div>
                        
                        <div class="form-field">
                            <button id="add-separator" class="button button-primary"><?php _e('Add Separator', 'wp-admin-organizer'); ?></button>
                        </div>
                    </div>
                </div>
                
                <div class="wp-admin-organizer-settings-section">
                    <h3><?php _e('Admin Logo', 'wp-admin-organizer'); ?></h3>

                    <div class="wp-admin-organizer-logo-form">
                        <div class="form-field">
                            <label for="admin-logo"><?php _e('Upload Logo', 'wp-admin-organizer'); ?></label>
                            <div class="logo-preview-container">
                                <?php
                                $logo_url = get_option('wp_admin_organizer_logo', '');
                                if (!empty($logo_url)) :
                                ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="Admin Logo" class="logo-preview">
                                <?php endif; ?>
                            </div>
                            <input type="hidden" id="admin-logo" name="admin-logo" value="<?php echo esc_attr(get_option('wp_admin_organizer_logo', '')); ?>">
                            <button id="upload-logo-button" class="button"><?php _e('Select Image', 'wp-admin-organizer'); ?></button>
                            <?php if (!empty($logo_url)) : ?>
                            <button id="remove-logo-button" class="button"><?php _e('Remove', 'wp-admin-organizer'); ?></button>
                            <?php endif; ?>
                            <p class="description"><?php _e('Upload a logo to display at the top of your admin menu. Recommended a ligth logo version, size recommended: 160px width max.', 'wp-admin-organizer'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wp-admin-organizer-settings-section">
                    <h3><?php _e('Import / Export', 'wp-admin-organizer'); ?></h3>

                    <div class="wp-admin-organizer-import-export">
                        <div class="form-field">
                            <button id="export-config" class="button button-secondary"><?php _e('Export Configuration', 'wp-admin-organizer'); ?></button>
                            <p class="description"><?php _e('Download your current menu configuration as a JSON file.', 'wp-admin-organizer'); ?></p>
                        </div>

                        <div class="form-field">
                            <button id="import-config" class="button button-secondary"><?php _e('Import Configuration', 'wp-admin-organizer'); ?></button>
                            <input type="file" id="import-config-file" accept=".json" style="display:none;">
                            <p class="description"><?php _e('Import a previously exported configuration file.', 'wp-admin-organizer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="wp-admin-organizer-dialog" title="<?php _e('Message', 'wp-admin-organizer'); ?>"></div>
    
    <!-- Success Modal -->
    <div class="wp-admin-organizer-success-modal">
        <div class="wp-admin-organizer-success-modal-content">
            <div class="wp-admin-organizer-success-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </div>
            <h3><?php _e('Changes Saved Successfully!', 'wp-admin-organizer'); ?></h3>
            <p><?php _e('Your menu organization has been updated.', 'wp-admin-organizer'); ?></p>
            <button id="success-modal-close"><?php _e('OK', 'wp-admin-organizer'); ?></button>
        </div>
    </div>
</div>