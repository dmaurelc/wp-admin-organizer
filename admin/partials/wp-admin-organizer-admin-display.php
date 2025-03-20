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
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p><?php _e('Drag and drop menu items to reorder them. You can also add separators between items.', 'wp-admin-organizer'); ?></p>
    </div>
    
    <div class="wp-admin-organizer-content">
        <div class="wp-admin-organizer-menu-items">
            <h2><?php _e('Menu Items', 'wp-admin-organizer'); ?></h2>
            
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
                    echo '<div class="wp-admin-organizer-menu-item" data-menu-id="' . esc_attr($item['id']) . '">';
                    echo '<div class="wp-admin-organizer-drag-handle"></div>';
                    echo '<div class="wp-admin-organizer-menu-item-title">#' . esc_html($position) . ' - ' . $item['title'] . '</div>';
                    echo '<input type="hidden" class="position" value="' . esc_attr($position) . '">';
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