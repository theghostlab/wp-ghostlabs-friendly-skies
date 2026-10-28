<?php
/**
 * Plugin Name:       GhostLabs Friendly Skies
 * Plugin URI:        https://theghostlab.io/friendly-skies
 * Description:       Display Bluesky posts, profiles, feeds and threads on your WordPress site. Five Gutenberg blocks, no Bluesky account required.
 * Version:           1.0.0
 * Requires at least: 7.1
 * Requires PHP:      8.1
 * Author:            GhostLabs
 * Author URI:        https://theghostlab.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ghostlabs-friendly-skies
 *
 * @package GhostLabsFriendlySkies
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Identify this as the free edition. Nothing branches on it (WordPress.org Guideline 5).
// ⚠️ Guarded, because both editions define these names. Since 2026-10-08 a buyer switches
// editions by uploading the other one and activating it while this one is still active
// (WordPress.org refused the in-place upgrade), so both headers load in one request until this
// edition is switched off, and forever under WP-CLI, which never fires admin_init.
// Unguarded, the second header warned once per constant (an error from PHP 9). Whichever header
// loads first wins, and the other one's require_once below then resolves to the same
// bootstrap.php through PLUGIN_PATH, so only one edition boots. PluginHeaderTest pins the guard.
//
// Keep the version's define() call in exactly its current shape: the command center's version
// stamp rewrites it.
defined( 'GHOSTLABS_FRIENDLY_SKIES_EDITION' ) || define( 'GHOSTLABS_FRIENDLY_SKIES_EDITION', 'free' );
defined( 'GHOSTLABS_FRIENDLY_SKIES_VERSION' ) || define( 'GHOSTLABS_FRIENDLY_SKIES_VERSION', '1.0.0' );
defined( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_PATH' ) || define( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
defined( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_URL' ) || define( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG' ) || define( 'GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG', plugin_basename( __FILE__ ) );

// Boot the plugin. The bootstrap lives in shared/ and is identical for both editions.
require_once GHOSTLABS_FRIENDLY_SKIES_PLUGIN_PATH . 'bootstrap.php';
