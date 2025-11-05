# Changelog
All notable changes to WP Admin Organizer will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.5.1] - 2025-11-05
### Added
- **Complete Frontend UI for User-Based Configurations** - Fully implemented user configuration interface
- Mode tabs styling with active/inactive states for switching between admin role editing, admin user editing, and personal user configuration
- User/role selector container with dropdown styling
- Status badges showing personal configuration status (active/inactive)
- Personal config status section with visual indicators

### Changed
- Enhanced JavaScript with complete mode switching functionality
- User selector now triggers configuration mode changes
- Three new AJAX handlers for user configuration management:
  - `enablePersonalConfiguration()` - Allow users to create personal configs
  - `copyRoleToUser()` - Admins can copy role defaults to specific users
  - `resetPersonalConfiguration()` - Users/admins can disable personal configs
- Updated `saveMenuOrder()` to support `config_mode` and `user_id` parameters
- Added button-danger CSS variant for reset/destructive actions
- Configuration now fully supports three-mode operation with UI

## [1.5.0] - 2025-11-05
### Added
- **User-Based Configuration System** 👤 - Individual users can now have personalized menu configurations
- User configuration system with three-mode operation: admin role editing, admin user editing, and personal user configuration
- `get_user_config()` method to retrieve user-specific menu configurations with role fallback
- `save_user_config()` method to persist user settings separately from role settings
- `has_personal_config()` method to check if user has personalized settings
- User configuration UI with mode tabs and user/role selectors
- Status badges showing configuration mode and source (personal, role, or default)
- AJAX handlers for enabling personal configs, copying role settings to users, and resetting user configurations
- Database storage via `wp_admin_organizer_user_configs` option
- Permission model distinguishing admin capabilities from standard user access
- Users can now maintain separate menu organization settings while admins manage role defaults

### Technical
- New configuration hierarchy: personal user settings > role-based defaults > system defaults
- Enhanced permission checking for user vs admin configuration access
- New UI components for three-mode configuration interface
- Database structure using `wp_admin_organizer_user_configs` option

## [1.4.3] - 2025-11-05
### Fixed
- **Action Button Overflow**: Fixed action buttons (accordion toggle, favorite star, visibility eye) overflowing outside menu item header
- Properly contained all buttons within header boundaries with optimized spacing
- Added box-sizing: border-box for consistent width calculations
- Buttons now stay within header padding with proper sizing constraints
- Hover background color now applies uniformly across entire menu item including action buttons
- Reduced button padding and removed gaps between buttons for optimal layout
- Menu item titles now use text-overflow: ellipsis for proper handling of long names

## [1.4.2] - 2025-11-05
### Fixed
- **Duplicate Drag Handle Icon**: Removed duplicate ::before pseudo-element from menu items
- Menu items now correctly display only one drag handle icon
- Separated menu item and separator item styles to prevent conflicts

## [1.4.1] - 2025-11-05
### Fixed
- **Accordion Layout**: Submenu accordion now properly displays below menu item instead of inline
- **Icon Alignment**: Role/favorites/visibility icons now properly aligned to the right with correct spacing
- **Menu Item Layout**: Restructured to two-line layout for better visual hierarchy
  - First row: drag handle + menu title + action buttons (right-aligned)
  - Second row: expandable submenu accordion (full-width)
- **Spacing Issues**: Removed extra whitespace on the right side of menu items
- **Visual Hierarchy**: Better organization with clear separation between menu header and submenu content

### Changed
- Menu item structure now uses `.wp-admin-organizer-menu-item-header` container
- Action buttons now grouped in `.wp-admin-organizer-menu-item-actions` container
- Improved hover states for better visual feedback

## [1.4.0] - 2025-11-05
### Added
- **Role-Based Configuration Profiles** 🎯 - Configure different menu layouts for each WordPress user role
- Role selector in configuration page to switch between Administrator, Editor, Author, etc.
- Each role maintains independent configurations (menu order, hidden items, favorites, etc.)
- **Search/Filter Menu Items** 🔍 - Real-time search box to filter menu items in configuration
- Search by menu title or menu ID for quick access in sites with many plugins
- Visual feedback with instant filtering as you type

### Changed
- Configuration system refactored to support role-based profiles
- Export/Import now handles multiple role configurations
- Backward compatibility maintained - old exports automatically migrate to new format
- Enhanced UI with role selector and search field in header

### Technical
- Added `wp_admin_organizer_role_configs` option storing all role configurations
- New helper methods: `get_role_config()`, `save_role_config()`, `get_all_roles()`
- `reorganize_admin_menu()` now uses current user's role configuration
- `save_menu_order()` updated to save per role with POST['role'] parameter
- Export format includes `role_configs` array with all role configurations
- Import detects format version and migrates old exports to new structure
- JavaScript handlers for role selector change and real-time search filtering
- CSS styles for role selector box and search container

### Migration
- Existing configurations automatically migrated to 'administrator' role on first load
- Old export files can be imported and will be assigned to administrator role
- No data loss during upgrade - all existing configurations preserved

## [1.3.1] - 2025-11-05
### Fixed
- **CRITICAL**: Hidden items now correctly disappear from WordPress admin sidebar
- Previous bug where hidden items remained visible in admin menu has been fixed
- Hidden items are now properly removed from the menu array

### Changed
- **Submenu UX Improvement**: Converted submenus to accordion-style display
- Submenus are now hidden by default and expand/collapse with a toggle button
- Added smooth transitions and animations for submenu expansion
- Improved visual hierarchy with arrow indicator that rotates on expand
- Prevents submenu lists from cluttering the configuration interface
- Better organization for menus with many submenu items

### Technical
- Refactored `reorganize_admin_menu()` to properly unset hidden items before skipping them
- Added accordion CSS with max-height transitions and opacity animations
- Implemented `.submenu-expanded` class for managing accordion state
- Added `.toggle-submenu` button with dashicon arrow indicator
- JavaScript handler for accordion toggle with stopPropagation to prevent conflicts
- Fixed logic where `continue` was called before `unset()`, preventing item removal

## [1.3.0] - 2025-11-05
### Added
- **Reorganize Submenus**: Drag and drop support for submenu items within each menu
- Submenus now display under their parent menu items in configuration
- Submenu order persists and applies to admin menu
- **Custom Icons (Backend)**: Infrastructure for custom menu icons using Dashicons
- Icons can be set via data and are applied to menu items
- All new features included in export/import

### Technical
- Added `wp_admin_organizer_submenu_order` option for storing submenu arrangements
- Added `wp_admin_organizer_custom_icons` option for storing custom icons
- New method `reorganize_submenus()` to apply submenu ordering
- Submenu drag & drop with jQuery UI Sortable
- Complete CSS styles for submenu lists and items
- Enhanced export/import to include submenus and icons

## [1.2.0] - 2025-11-05
### Added
- **Favorites/Quick Access functionality**: Mark menu items as favorites with star icon
- Favorites section at the top of admin menu for quick access
- Star toggle button for each menu item in configuration
- Visual indicators for favorite items (gold star, special styling)
- Favorites included in export/import functionality
- Dedicated "FAVORITES" section in admin menu with separator

### Fixed
- Hidden items now remain visible in configuration page for reactivation
- Inline editing can now be cancelled with ESC key
- Prevented multiple inline editors from opening simultaneously
- Improved inline editor UX with better keyboard handling (Enter to save, ESC to cancel)

### Changed
- Enhanced menu reorganization to support favorites at the top
- Improved plugin description to reflect new features

### Technical
- Added `wp_admin_organizer_favorite_items` option for storing favorite items
- Enhanced `reorganize_admin_menu()` to display favorites section
- Updated AJAX save to include favorite items
- Added favorite toggle handler in JavaScript
- New CSS styles for favorite items and toggles

## [1.1.0] - 2025-11-05
### Added
- Hide/Show menu items functionality with visibility toggle button
- Rename menu items with inline editing (click to edit)
- Export configuration to JSON file
- Import configuration from JSON file
- Visual indicators for hidden items (opacity and strike-through)
- Eye icons (dashicons) for visibility controls
- Support for custom menu names that persist across sessions

### Changed
- Enhanced menu item display with visibility controls
- Improved save functionality to include hidden and renamed items
- Updated UI with additional controls in sidebar
- Plugin description updated to reflect new features

### Technical
- Added `wp_admin_organizer_hidden_items` option for storing hidden menu items
- Added `wp_admin_organizer_renamed_items` option for storing custom menu names
- New AJAX endpoints: `export_configuration` and `import_configuration`
- Enhanced CSS with styles for visibility controls and hidden items
- JavaScript improvements for handling visibility toggles and inline editing

## [1.0.1] - 2023-12-20
### Added
- Internationalization (i18n) support
- Spanish (es_ES) translation
- English (en_US) translation
- Language files (.pot, .po) in languages directory
- Automatic text domain loading

### Changed
- Updated version number to 1.0.1
- Improved text strings to support translations

## [1.0.0] - 2023-12-19
### Added
- Initial release
- Admin menu organization with drag and drop
- Separator management
- Custom logo support
- Menu order saving
- Admin interface customization