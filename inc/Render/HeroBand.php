<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Render;

use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Pro tab's brand hero — Cycle Block's `HeroBand`, server-rendered, by way of Undercard.
 *
 * The one piece of the screen that is not wp-admin furniture. It is inset from the content
 * column rather than full-bleed, so it reads as a panel *on* the settings screen instead of a
 * takeover *of* it — which is the whole reason a marketing band is acceptable in somebody's
 * admin at all.
 *
 * ⚠️ The gradient, the line texture and the sweep ship inside the plugin
 * (`assets/images/upsell/`, drawn by `settings.css`): Guideline 7 prohibits offloading assets to
 * a remote service.
 *
 * @package GhostLabsFriendlySkies
 */
final class HeroBand
{
    /**
     * @param string $url Built by `Service\UpgradeEndpoints::upgradeUrl()`, so it carries this
     *                    surface's source. ⚠️ No fallback: if it is empty, no link renders.
     */
    public function __construct(private readonly string $url)
    {
    }

    public function render(): void
    {
        echo '<section class="ghlbsfs-hero">';

        // Decorative, and marked as such: backgrounds behind aria-hidden nodes rather than
        // images a screen reader has to skip past.
        echo '<div class="ghlbsfs-hero__texture" aria-hidden="true"></div>';
        echo '<div class="ghlbsfs-hero__sweep" aria-hidden="true"></div>';

        echo '<div class="ghlbsfs-hero__copy">';

        printf('<p class="ghlbsfs-hero__eyebrow">%s</p>', esc_html(TierMatrix::paidEditionName()));

        printf(
            '<h2 class="ghlbsfs-hero__headline">%s</h2>',
            // Owner, 2026-10-18 (register § The free build). The hero only: the lead line for
            // copy is still Open #4.
            esc_html__('Your Bluesky posts, imported into WordPress.', 'ghostlabs-friendly-skies')
        );

        printf(
            '<p class="ghlbsfs-hero__sub">%s</p>',
            // "Bluesky list", not "List": the free List layout is in the table below
            // (owner, 2026-10-18).
            esc_html__('Every block and feed you already have — plus the Masonry, Carousel, Highlight and Ticker layouts, the Search, Custom Feed and Bluesky list sources, and Import from Bluesky on a schedule.', 'ghostlabs-friendly-skies')
        );

        if ($this->url !== '') {
            echo wp_kses_post(BuyLink::render($this->url, 'ghlbsfs-hero__pill'));
        }

        echo '</div></section>';
    }
}
