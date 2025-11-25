# Cloudflare Extra Purge Addon

A simple WordPress plugin that automatically purges the entire Cloudflare cache when content is published or updated. Works as an addon to the official [Cloudflare WordPress plugin](https://wordpress.org/plugins/cloudflare/).

## Description

This plugin extends the functionality of the official Cloudflare plugin by automatically purging the **entire** Cloudflare cache (not just specific URLs) whenever:

- A post or page is published (new or scheduled)
- A post or page is updated
- Any content transitions to the "publish" status

## Features

- ✅ Automatically purges entire Cloudflare cache on content publish/update
- ✅ Works with scheduled posts
- ✅ Compatible with the official Cloudflare plugin
- ✅ Uses Cloudflare plugin's API directly (no fallbacks)
- ✅ Debug logging support
- ✅ Follows WordPress coding standards
- ✅ Simple namespace-based architecture

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- [Cloudflare WordPress plugin](https://wordpress.org/plugins/cloudflare/) installed and configured

## Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins
   git clone https://github.com/beapi/cloudflare-extra-purge-addon.git
   ```

2. Make sure the official [Cloudflare plugin](https://wordpress.org/plugins/cloudflare/) is installed and configured with your API credentials.

3. Activate the plugin through the 'Plugins' menu in WordPress.

## How It Works

The plugin hooks into WordPress post lifecycle events:

- `transition_post_status` - Detects when posts transition to "publish" status
- `save_post` - Detects when published posts are updated
- `publish_future_post` - Handles scheduled post publications

When any of these events occur, the plugin:

1. Checks if the Cloudflare plugin is active and its API is available
2. Uses the Cloudflare plugin's `Hooks` class to call `purgeCacheEverything()`
3. Purges the entire cache using `purge_everything: true`

**Note:** If the Cloudflare plugin is not active or its API is not available, this plugin does nothing (no fallbacks).

## Configuration

No configuration needed! The plugin automatically uses the Cloudflare plugin's API. Make sure the official Cloudflare plugin is installed, activated, and properly configured.

## Debugging

To enable debug logging, add this to your `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

Purge operations will be logged to `/wp-content/debug.log` with the prefix `[Cloudflare Extra Purge]`.

## Development

### Code Standards

This plugin follows:
- WordPress Coding Standards (WPCS)
- WordPress VIP coding requirements
- PSR-12 for PHP formatting
- Uses PHP namespaces for code organization

### Testing

Test the plugin by:
1. Publishing a new post
2. Updating an existing published post
3. Scheduling a post for future publication
4. Checking Cloudflare dashboard or debug logs to verify cache purge

## License

GPL v2 or later

## Support

For issues, feature requests, or contributions, please open an issue on GitHub.

## Changelog

### 1.0.0
- Initial release
- Automatic cache purge on post publish/update
- Support for scheduled posts
- Integration with official Cloudflare plugin
- Namespace-based architecture
- No fallbacks - uses Cloudflare plugin API exclusively
