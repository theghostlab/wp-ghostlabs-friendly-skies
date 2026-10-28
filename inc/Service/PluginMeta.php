<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Config\PluginConfig;
use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;
use GHOSTLABS\FRIENDLY_SKIES\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The free edition's row on the Plugins screen.
 *
 * Register § The free build (owner, 2026-10-17): **the row follows Undercard's**
 * (`undercard-dev/free-only/inc/Service/UpsellLinks.php`), signed off as Penpot → Friendly
 * Skies → *Admin — Upsell Surfaces*, board 5. The action links carry Settings and one upgrade
 * link; the row meta carries Docs and Support.
 *
 * **Why this surface at all.** `plugin_action_links` is the only in-admin upsell the
 * WordPress.org review team names in writing — "a link on their entry on the plugin list page" —
 * and it interrupts nothing: nobody who is not already reading a row about this plugin sees it.
 * That is deliberately all this class does: no admin notice, no activation banner, no dashboard
 * widget and no review prompt ship in the free edition.
 *
 * ⚠️ **One ask, emphasised once.** The upgrade link is the only weighted thing in the row, and
 * the row meta carries no second "Upgrade".
 *
 * @package GhostLabsFriendlySkies
 */
final class PluginMeta
{
	/**
	 * No constructor dependency: every link this edition adds is derived from constants, and a
	 * promoted property written but never read is what PHPStan level 8 rejects.
	 */

	/**
	 * Prepend the Settings and upgrade links to this plugin's row.
	 *
	 * The signature is the four-argument `plugin_action_links_{$plugin_file}` filter's.
	 *
	 * @param array<int|string, mixed> $links_array
	 * @param string                   $plugin_file_name
	 * @param array<string, mixed>     $plugin_data
	 * @param string                   $context
	 * @return array<int|string, mixed>
	 */
	public function actionSettings(array $links_array, string $plugin_file_name, array $plugin_data, string $context): array
	{
		$settings = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url(admin_url('options-general.php?page=' . PluginConfig::$settingsMenuSlug)),
			esc_attr__('Friendly Skies settings', 'ghostlabs-friendly-skies'),
			esc_html__('Settings', 'ghostlabs-friendly-skies')
		);

		// Settings first: it is where someone who just activated the plugin is going.
		array_unshift($links_array, $this->upgradeLink());
		array_unshift($links_array, $settings);

		return $links_array;
	}

	/**
	 * Docs and Support — the right-hand column of the row.
	 *
	 * 🟢 **Why it earns anything at all:** core builds `Version`, `By`, then *either* `View
	 * details` *or* `Visit plugin site` — an `elseif`. On a .org install the slug is set, so core
	 * shows *View details* and suppresses the `Plugin URI` link. There is no route to the
	 * plugin's own pages in that row by default.
	 *
	 * 🟡 Both are placeholders (owner, 2026-10-18): the docs page is built later on the
	 * marketing site, and the forum exists once the WordPress.org listing does.
	 *
	 * Reached only for this plugin's row: `PluginRowMeta` compares the plugin file before it
	 * applies the filter this answers, which is why no basename is checked here.
	 *
	 * @param array<int|string, string> $meta
	 * @return array<int|string, string>
	 */
	public function rowMeta(array $meta): array
	{
		$meta[] = PluginRowMeta::link(
			UpgradeEndpoints::docsUrl(),
			__('Docs', 'ghostlabs-friendly-skies'),
			__('Friendly Skies docs', 'ghostlabs-friendly-skies')
		);

		$meta[] = PluginRowMeta::link(
			UpgradeEndpoints::supportUrl(),
			__('Support', 'ghostlabs-friendly-skies'),
			__('Friendly Skies support forum', 'ghostlabs-friendly-skies')
		);

		return $meta;
	}

	/**
	 * ⚠️ **No colour of its own, and that is the whole of the styling decision.** The link
	 * keeps core's admin link colour, so it follows the user's admin colour scheme, keeps core's
	 * hover and focus treatment, and cannot drift from the links either side of it. Weight is
	 * the one thing added, because weight needs no hover state to work. GhostLabs purple is in
	 * the register's § Rejected (owner, 2026-10-17), as it is in Undercard's.
	 *
	 * The URL is built by PHP and carries this surface's source: a bare marketing URL here
	 * would file the sale in a bucket nobody reads.
	 */
	private function upgradeLink(): string
	{
		// `target="_blank"` keeps plugins.php open behind the product page, which makes
		// WCAG 3.2.5 apply. The visible label stays short, so the warning rides in
		// `.screen-reader-text` — a core admin class already on this screen. ⚠️ No
		// `aria-label`: it would replace the accessible name, warning included.
		return sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer" style="font-weight:700">%s'
				. '<span class="screen-reader-text"> %s</span></a>',
			esc_url(UpgradeEndpoints::upgradeUrl(UpsellSource::PluginsRow)),
			esc_html(sprintf(
				/* translators: %s: the paid edition's name. */
				__('Upgrade to %s', 'ghostlabs-friendly-skies'),
				TierMatrix::paidEditionName()
			)),
			esc_html__('(opens in a new tab)', 'ghostlabs-friendly-skies')
		);
	}
}
