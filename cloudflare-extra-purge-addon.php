<?php
/**
 * Plugin Name: Cloudflare Extra Purge Addon
 * Plugin URI: https://github.com/beapi/cloudflare-extra-purge-addon
 * Description: Automatically purges the entire Cloudflare cache when content is published or updated. Works as an addon to the official Cloudflare plugin.
 * Version: 1.0.0
 * Author: Be API
 * Author URI: https://beapi.fr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cloudflare-extra-purge-addon
 * Requires at least: 5.0
 * Requires PHP: 7.4
 *
 * @package Cloudflare_Extra_Purge_Addon
 */

namespace Cloudflare_Extra_Purge_Addon;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle post status transitions.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Old post status.
 * @param WP_Post $post       Post object.
 * @return void
 */
function on_post_status_change( $new_status, $old_status, $post ) {
	// Only purge when transitioning to publish status.
	if ( 'publish' !== $new_status ) {
		return;
	}

	// Skip revisions and autosaves.
	if ( \wp_is_post_revision( $post ) || \wp_is_post_autosave( $post ) ) {
		return;
	}

	// Purge cache when post is published (new or updated).
	purge_all_cache( $post->ID );
}

/**
 * Handle post save.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 * @return void
 */
function on_post_save( $post_id, $post, $update ) {
	// Skip revisions and autosaves.
	if ( \wp_is_post_revision( $post_id ) || \wp_is_post_autosave( $post_id ) ) {
		return;
	}

	// Only purge if post is published and being updated.
	if ( $update && 'publish' === $post->post_status ) {
		purge_all_cache( $post_id );
	}
}

/**
 * Purge entire Cloudflare cache.
 *
 * @param int $post_id Post ID (optional, for logging purposes).
 * @return bool True on success, false on failure or if Cloudflare plugin is not available.
 */
function purge_all_cache( $post_id = 0 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	// Check if Cloudflare plugin is active.
	if ( ! is_cloudflare_plugin_active() ) {
		return false;
	}

	// Only proceed if Cloudflare plugin's API is available.
	if ( ! class_exists( '\CF\WordPress\Hooks' ) ) {
		return false;
	}

	// Use the plugin's Hooks class directly.
	try {
		$cloudflare_hooks = new \CF\WordPress\Hooks();
		if ( method_exists( $cloudflare_hooks, 'purgeCacheEverything' ) ) {
			// Call the method (it doesn't return a value, but logs internally).
			$cloudflare_hooks->purgeCacheEverything();
			log_purge( 'success', 'Purged via Cloudflare plugin Hooks class' );
			return true;
		}
	} catch ( \Exception $e ) {
		log_purge( 'error', 'Exception when using Cloudflare plugin API: ' . $e->getMessage() );
		return false;
	}

	return false;
}

/**
 * Check if Cloudflare plugin is active.
 *
 * @return bool True if active, false otherwise.
 */
function is_cloudflare_plugin_active() {
	// Check if plugin is active using WordPress function.
	if ( ! \function_exists( 'is_plugin_active' ) ) {
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return \is_plugin_active( 'cloudflare/cloudflare.php' );
}

/**
 * Log purge operation.
 *
 * @param string $status  Status (success, error).
 * @param string $message Log message.
 * @return void
 */
function log_purge( $status, $message ) {
	if ( \defined( 'WP_DEBUG' ) && \WP_DEBUG ) {
		\error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			\sprintf(
				'[Cloudflare Extra Purge] %s: %s',
				\strtoupper( $status ),
				$message
			)
		);
	}
}

/**
 * Initialize the plugin.
 *
 * @return void
 */
function init() {
	// Hook into post status transitions (publish, update, scheduled publish).
	\add_action( 'transition_post_status', __NAMESPACE__ . '\\on_post_status_change', 10, 3 );

	// Hook into post save for immediate updates.
	\add_action( 'save_post', __NAMESPACE__ . '\\on_post_save', 10, 3 );

	// Hook into scheduled post publication.
	\add_action( 'publish_future_post', __NAMESPACE__ . '\\purge_all_cache', 10, 1 );
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
