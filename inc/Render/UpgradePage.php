<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Render;

use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Pro tab: the Upsell Page every GhostLabs free tier shares.
 *
 * Owner, 2026-10-17 (register § The free build): the tab looks like Penpot → WordPress >
 * Plugins → **Upsell Page** (F3), signed off on 2026-10-18 as Penpot → Friendly Skies →
 * *Admin — Upsell Surfaces*, board 1. Its shape is the argument: what stays free, the quiet way
 * in for somebody who already bought, the hero that says what Pro is for, the table that sets
 * what you have beside what you would buy — and the ask again, after the case is made.
 *
 * A composer only. Each section is its own renderer, so the order below is the one thing this
 * class decides. Ported from Undercard's.
 *
 * @package GhostLabsFriendlySkies
 */
final class UpgradePage
{
    /**
     * @param string $tableUrl The foot-of-table ask, from its own `from` bucket
     *                         (`UpsellSource::SettingsTable`). Empty renders no link.
     */
    public function __construct(
        private readonly UpgradePanel $panel,
        private readonly HeroBand $hero,
        private readonly ComparisonTable $table,
        private readonly string $tableUrl,
    ) {
    }

    public function render(): void
    {
        echo '<div class="ghlbsfs-upgrade">';

        $this->intro();
        $this->panel->render();
        $this->hero->render();

        echo '<section class="ghlbsfs-compare">';
        $this->table->render();
        $this->foot();
        echo '</section>';

        echo '</div>';
    }

    /**
     * What stays free, and what Pro adds. Claims-checked 2026-10-17.
     */
    private function intro(): void
    {
        echo '<section class="ghlbsfs-upgrade__card">';

        printf('<h2 class="ghlbsfs-upgrade__card-title">%s</h2>', esc_html__('Friendly Skies', 'ghostlabs-friendly-skies'));

        printf(
            '<p>%s</p>',
            esc_html__('All five blocks — Bluesky Post, Profile Card, Follow Button, Feed and Thread — are in this plugin, and they stay free.', 'ghostlabs-friendly-skies')
        );

        printf(
            '<p>%s</p>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s adds four more Feed layouts, three more Feed sources and Import from Bluesky — what it includes is below.', 'ghostlabs-friendly-skies'),
                TierMatrix::paidEditionName()
            ))
        );

        echo '</section>';
    }

    /**
     * The ask, repeated at the foot of the table.
     *
     * The table is tall enough to push the hero's link off-screen, so the offer comes again —
     * the same offer, a different reading of the screen, and therefore its own bucket.
     *
     * 🟡 "the product page" is register Open #7, and the price Open #1: true once the page
     * exists. That blocks the release, not this screen.
     */
    private function foot(): void
    {
        echo '<div class="ghlbsfs-compare__foot"><div class="ghlbsfs-compare__note">';

        printf('<p class="ghlbsfs-compare__note-title">%s</p>', esc_html__('Ready when you are', 'ghostlabs-friendly-skies'));
        printf('<p>%s</p>', esc_html__('Pricing and the full feature tour live on the product page.', 'ghostlabs-friendly-skies'));

        echo '</div>';

        if ($this->tableUrl !== '') {
            echo wp_kses_post(BuyLink::render($this->tableUrl, 'ghlbsfs-button ghlbsfs-button--primary'));
        }

        echo '</div>';
    }
}
