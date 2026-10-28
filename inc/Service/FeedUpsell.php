<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Feed block's Upsell panel: a closed panel in the block's inspector naming the layouts and
 * sources the paid edition adds (owner, 2026-10-27; register § The free build).
 *
 * It points out, and offers nothing. The free Feed block's selects carry List, Grid and one
 * account's posts only, and nothing the panel names is in this build (WordPress.org Guideline 5,
 * `NoLockedFeatureTest`). Serendipity's closed Layout Studio panel is the same surface.
 *
 * The copy and the URL are finished here and handed to `assets/js/feed-upsell.js`, which only
 * reads them. Two reasons: every URL this edition links to is built in `UpgradeEndpoints`, and the
 * panel's sentences are upsell copy that belongs beside the rest of it in PHP, so the free
 * build's JavaScript names none of the paid layouts.
 *
 * The script is plain, unbuilt JavaScript on purpose: the free tier has no bundler of its own, and
 * a reviewer reads exactly what ships.
 *
 * @package GhostLabsFriendlySkies
 */
final class FeedUpsell
{
    public const HANDLE = 'ghostlabs-fs-feed-upsell';

    private const SCRIPT = 'assets/js/feed-upsell.js';

    private const DEPENDENCIES = ['wp-components', 'wp-compose', 'wp-element', 'wp-hooks'];

    public function enqueue(): void
    {
        wp_enqueue_script(
            self::HANDLE,
            GHOSTLABS_FRIENDLY_SKIES_PLUGIN_URL . self::SCRIPT,
            self::DEPENDENCIES,
            GHOSTLABS_FRIENDLY_SKIES_VERSION,
            true
        );

        // wp_add_inline_script(), never a script tag printed by hand (WordPress.org, "Use wp_enqueue
        // commands", Serendipity's review of 2026-10-26).
        wp_add_inline_script(
            self::HANDLE,
            'window.ghlbsfsFeedUpsell = ' . wp_json_encode($this->copy()) . ';',
            'before'
        );
    }

    /**
     * Claims-checked: the four layouts and three sources are the paid edition's whole set.
     * "Bluesky list", not "List", so it can't be read as the free List layout (owner, 2026-10-18).
     *
     * @return array{title: string, body: string, keeps: string, link: string, url: string}
     */
    private function copy(): array
    {
        return [
            'title' => __('More layouts and sources', 'ghostlabs-friendly-skies'),
            'body'  => __('Friendly Skies Pro adds four layouts (Masonry, Carousel, Highlight and Ticker) and three sources: Search, Custom Feed and Bluesky list.', 'ghostlabs-friendly-skies'),
            'keeps' => __('Every Feed block keeps working when you switch.', 'ghostlabs-friendly-skies'),
            'link'  => __('Upgrade to Friendly Skies Pro', 'ghostlabs-friendly-skies'),
            'url'   => UpgradeEndpoints::upgradeUrl(UpsellSource::EditorFeed),
        ];
    }
}
