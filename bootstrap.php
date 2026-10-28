<?php
/**
 * Friendly Skies — Bootstrap
 *
 * Loaded by both the free and pro plugin header files after constants are
 * defined. This file lives in shared/ so the build copies it to the plugin root.
 *
 * @package GhostLabsFriendlySkies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The version-gated packages this edition ships (newest copy of each wins across all GhostLabs
// plugins). They load before the Composer autoloader so each registry can elect the newest copy on
// the site before any GhostLabs class is touched.
//
// Each tier ships its own packages.php, so what loads is what the build carries, and nothing here
// asks which build this is. ⚠️ `__DIR__`, never a path built from the edition: on the request that
// activates one edition beside the other, both headers load and both resolve to the winner's
// bootstrap.php, so the winner's packages load with it.
//
// Activation is the one request where the version gate cannot do its job, and packages.php covers
// it. The packages register at include time and hold their election on `plugins_loaded` at priority
// 0, which is sound because plugin files are all included before that fires — on a normal request.
// `activate_plugin()` includes this file mid-request, long after `plugins_loaded` has passed, so the
// election never runs, no autoloader is installed, and the first `GhostLabs\WPKit\` symbol touched
// is a fatal. `Kernel::activate()` touches one immediately — `OptionsService implements
// MutableOptionsServiceInterface`. Registering the bundled copy directly is the right fallback
// rather than a workaround: an election only matters when there is more than one copy to choose
// between, and if another plugin's copy already won at priority 0 its classes resolve and the
// guards do nothing.
require_once __DIR__ . '/packages.php';

// Load Composer autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	add_action( 'admin_notices', function () {
		echo '<div class="error"><p>Friendly Skies plugin error: Composer autoloader not found. Please run <code>composer install</code>.</p></div>';
	} );
	return;
}

// Load text domain.
add_action( 'init', function () {
	load_plugin_textdomain(
		'ghostlabs-friendly-skies',
		false,
		dirname( GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG ) . '/languages'
	);
} );

// Boot the plugin.
use GHOSTLABS\FRIENDLY_SKIES\Kernel;

// Register against GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG, which the entry point sets to
// plugin_basename( __FILE__ ) — so it always names the file WordPress actually activates,
// in either edition and whatever the plugin directory is called.
//
// These previously named 'friendly-skies.php', the local-dev-only file that build.sh
// excludes from the output, so neither hook had ever fired in a built plugin.
// register_activation_hook() runs plugin_basename() over its argument, which is idempotent
// for a path already relative to the plugins directory, so passing the slug is safe.
register_activation_hook(
	GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG,
	[ Kernel::class, 'activate' ]
);

register_deactivation_hook(
	GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG,
	[ Kernel::class, 'deactivate' ]
);

add_action( 'plugins_loaded', function () {
	try {
		Kernel::getInstance()->init()->run();
	} catch ( \Exception $e ) {
		error_log( '[Friendly Skies] ' . $e->getMessage() );
	}
}, 10 );
