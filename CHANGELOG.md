# Changelog
All notable changes to WP Admin Organizer will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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