<?php
/**
 * Friendly Skies — the packages this edition ships
 *
 * Required by bootstrap.php, before the Composer autoloader. Each tier ships its own copy of this
 * file, so which packages load is decided by what the build carries — the bootstrap never asks.
 *
 * The free edition ships one: wp-kit, GPL-2.0-or-later, which OptionsService needs.
 *
 * @package GhostLabsFriendlySkies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/ghostlabs/wp-kit/wp-kit.php';

// The activation request, on which the version election cannot run (see bootstrap.php).
if ( did_action( 'plugins_loaded' ) && ! interface_exists( 'GhostLabs\WPKit\Contracts\PluginIdentityInterface' ) ) {
	require_once __DIR__ . '/vendor/ghostlabs/wp-kit/autoload.php';
}
