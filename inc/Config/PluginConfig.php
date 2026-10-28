<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Config;

use GhostLabs\WPKit\Contracts\PluginIdentityInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Static plugin metadata for the free edition.
 *
 * It implements wp-kit's `PluginIdentityInterface`, which is GPL-2.0-or-later and safe to
 * ship, and is narrow enough to implement here directly.
 *
 * `$domain` and `$slug` are the same in both editions. `shared/inc/Service/OptionsService.php`
 * builds the wp_option key from them, so changing either would orphan the stored options
 * of anyone moving between editions.
 *
 * @package GhostLabsFriendlySkies
 */
final class PluginConfig implements PluginIdentityInterface
{
	public static string $domain = 'ghostlabs';
	public static string $slug = 'friendly-skies';

	/**
	 * The one value that differs between the editions.
	 *
	 * It feeds `restNameSpace()` and `transientKey()`, giving the free edition its own
	 * REST namespace and its own transient key so the two editions cannot collide on a
	 * site where both were installed in sequence.
	 */
	public static string $tier = 'free';

	public static string $restVersion = 'v1';

	/** The same slug in both editions, so the admin URL survives a change of edition. */
	public static string $settingsMenuSlug = 'ghostlabs-friendly-skies-settings';

	public static function domain(): string
	{
		return sprintf('%s-%s', self::$domain, self::$slug);
	}

	/**
	 * The plugin directory name, derived from the entry point WordPress loaded.
	 *
	 * Uses dirname()/basename() rather than list destructuring of an exploded path: that
	 * form raises an undefined-offset notice whenever the constant
	 * holds a bare filename, which is every context where the plugin is not installed in
	 * its own directory.
	 */
	public static function pluginSlug(): string
	{
		return basename(GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG, '.php');
	}

	public static function restNameSpace(): string
	{
		return sprintf('%s/%s/%s/%s', self::$domain, self::$slug, self::$tier, self::$restVersion);
	}

	public static function transientKey(): string
	{
		return sprintf('%s-%s-%s', self::$domain, self::$slug, self::$tier);
	}

	// --- PluginIdentityInterface -------------------------------------------------

	public function getPluginFile(): string
	{
		return GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG;
	}

	public function getUpdaterSlug(): string
	{
		return self::pluginSlug();
	}

	public function getVersion(): string
	{
		return defined('GHOSTLABS_FRIENDLY_SKIES_VERSION') ? GHOSTLABS_FRIENDLY_SKIES_VERSION : '1.0.0';
	}

	public function getRestNamespace(): string
	{
		return self::restNameSpace();
	}

	public function getDomain(): string
	{
		return self::domain();
	}

	/**
	 * The free edition does have a settings screen, and this is its slug.
	 *
	 * It was empty until the cache duration gave the tier something worth configuring — the
	 * one thing a free install can meaningfully tune, since it has no account, credentials,
	 * cron or destination. The value is the same in both editions, so a site moving between
	 * them keeps the same admin URL.
	 */
	public function getSettingsMenuSlug(): string
	{
		return self::$settingsMenuSlug;
	}
}
