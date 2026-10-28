<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Plugins row's other column — after Version and By — for this plugin's row only.
 *
 * Shared adds nothing itself: it owns no URL. Each edition answers `FILTER` with the links it
 * carries — Docs and Support in both, the convention the other GhostLabs plugins follow. Each
 * tier's `Kernel` hooks `rowMeta()` to `plugin_row_meta`; no edition hooks that filter itself.
 *
 * ⚠️ **`plugin_row_meta` is global**, unlike `plugin_action_links_{file}`. Core fires it once for
 * every plugin on the screen and passes the file as an argument, so the comparison below is the
 * only thing keeping these links off every other plugin's row. On a one-plugin dev site the
 * unguarded version looks exactly right. The guard runs before the filter, so nothing an edition
 * builds is built thirty times on a site with thirty plugins.
 */
final class PluginRowMeta
{
	public const FILTER = 'ghostlabs_friendly_skies_plugin_row_meta';

	/**
	 * @param array<int|string, string> $meta
	 * @return array<int|string, string>
	 */
	public function rowMeta(array $meta, string $pluginFile): array
	{
		if ($pluginFile !== GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG) {
			return $meta;
		}

		// Typed `mixed` on purpose: any plugin can answer a public filter, with anything, and a
		// non-array here would fatal the Plugins screen on this method's return type.
		/** @var mixed $filtered */
		$filtered = apply_filters(self::FILTER, $meta);

		return is_array($filtered) ? $filtered : $meta;
	}

	/**
	 * One row link, every part escaped.
	 *
	 * ⚠️ The accessible name says whose link it is: a Plugins screen has a "Docs" for every plugin
	 * that offers one, and a screen-reader user tabbing thirty rows otherwise hears thirty
	 * identical "Docs". It must still contain the visible label (WCAG 2.5.3) so "click Docs"
	 * works by voice. Both strings are translations, so a locale can break that pairing; the
	 * name is then dropped and the label names the link, rather than shipping a mismatch.
	 *
	 * ❌ No `target="_blank"`: core's own row links do not force a new tab.
	 */
	public static function link(string $url, string $label, string $accessibleName): string
	{
		$named = $accessibleName !== '' && mb_stripos($accessibleName, $label) !== false;

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url($url),
			$named ? sprintf(' aria-label="%s"', esc_attr($accessibleName)) : '',
			esc_html($label)
		);
	}
}
