# WP Admin Organizer - Features Guide

## Version 1.1.0 - New Features

### 1. Hide/Show Menu Items

**How to use:**
- Each menu item now has an eye icon on the right side
- Click the eye icon to toggle visibility
- Hidden items will appear with reduced opacity and a strike-through effect
- Hidden items won't appear in your WordPress admin menu after saving
- This is perfect for hiding plugins you rarely use without deactivating them

**Visual indicators:**
- 👁️ Visible eye icon = Item is visible
- 🚫 Hidden eye icon = Item is hidden (appears grayed out and crossed)

### 2. Rename Menu Items

**How to use:**
- Click on any menu item title to edit it
- An input field will appear with the current name
- Type the new name you want
- Press Enter or click outside to save the change
- Click "Save Changes" to make the rename permanent

**Use cases:**
- White-label your WordPress admin
- Translate menu items to your language
- Use more descriptive names for your clients
- Example: Rename "Posts" to "Blog Articles" or "News"

### 3. Export Configuration

**How to use:**
- Scroll to the "Import / Export" section in the sidebar
- Click "Export Configuration" button
- A JSON file will be downloaded to your computer
- Filename: `wp-admin-organizer-config.json`

**What's included in the export:**
- Menu order
- Separators (simple and text)
- Custom logo URL
- Hidden items list
- Renamed items list
- Plugin version number
- Export timestamp

### 4. Import Configuration

**How to use:**
- Scroll to the "Import / Export" section in the sidebar
- Click "Import Configuration" button
- Select a previously exported JSON file
- Configuration will be applied immediately
- Page will refresh to show the new settings

**Perfect for:**
- Agencies managing multiple WordPress sites
- Setting up consistent admin interfaces across client sites
- Backing up your menu configuration
- Sharing configurations with team members

## Existing Features

### Drag and Drop Reordering
- Click and drag menu items to reorder them
- Position numbers update automatically
- Works with both menu items and separators

### Separators
- **Simple Separator**: A visual dividing line
- **Text Separator**: A dividing line with a custom label
- Click separator text to edit it inline
- Remove separators with the "Remove" link

### Custom Logo
- Upload a logo to appear at the top of your admin menu
- Recommended size: 160px width maximum
- Supports all common image formats
- Light logos work best on dark admin theme

## Tips and Best Practices

1. **Test First**: Try changes on a staging site before applying to production
2. **Export Regularly**: Keep backups of your configurations
3. **Use Text Separators**: Group related menu items together with labeled separators
4. **Hide Unused Items**: Clean up your admin menu by hiding items you rarely use
5. **Descriptive Names**: Use clear, descriptive names when renaming menu items
6. **Save Often**: Click "Save Changes" after making modifications

## Keyboard Shortcuts

- **Enter**: Confirm inline editing (rename function)
- **Escape**: Cancel inline editing (coming in future version)
- **Tab**: Navigate between form fields

## Troubleshooting

**Q: My changes aren't saving**
- Make sure to click "Save Changes" button
- Check that you have admin permissions
- Try refreshing the page and making changes again

**Q: Hidden items still appear**
- Click "Save Changes" after hiding items
- Refresh your browser to see changes
- Clear WordPress and browser cache if needed

**Q: Import isn't working**
- Ensure the JSON file is a valid WP Admin Organizer export
- Check that file hasn't been modified manually
- Try exporting from the same plugin version

**Q: Renamed items reverted to original names**
- Make sure to save changes after renaming
- Check that renamed items were included in your configuration

## Support

For bugs, feature requests, or questions:
- Open an issue on GitHub
- Contact: [Your support channel]

---

**Version**: 1.1.0
**Last Updated**: 2025-11-05
