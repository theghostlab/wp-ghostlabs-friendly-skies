<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Render;

use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * "Already own Friendly Skies Pro?" — the way in for somebody who has already bought.
 *
 * It sits **above** the pitch but stays quiet: one row, F3's strip, until opened. A buyer finds
 * it without scrolling, and nobody else has it shouting at them. Penpot board 2.
 *
 * 🔴 **It explains, and it does nothing itself.** Until 2026-10-08 this took a licence key and
 * installed Pro in place. WordPress.org refused that: Guideline 8 forbids installing a paid
 * version of the same plugin from anywhere but WordPress.org, and the review's "Changing Active
 * Plugins" item forbids a plugin switching plugins on or off. Now each of the three steps
 * happens on a screen this plugin doesn't own — the account to download, core's uploader to
 * install, core's Activate link to switch. Pro's own header switches this edition off.
 * `NoInDashboardInstallTest` holds the ban, and the old client is kept at the tag
 * `reference/in-place-upgrade-free-2026-10-08`. ⚠️ Do not restore it here.
 *
 * A native `<details>`: closed by default, keyboard-operable, and open without JavaScript.
 *
 * @package GhostLabsFriendlySkies
 */
final class UpgradePanel
{
    /**
     * @param string      $downloadUrl Where the buyer downloads Pro: their account.
     * @param string|null $uploadUrl   Core's plugin uploader, or null where this user cannot
     *                                 upload. `upload_plugins` is the only check needed: core
     *                                 refuses it under `DISALLOW_FILE_MODS` and limits it to
     *                                 super admins on a network, so a subsite administrator
     *                                 gets a sentence rather than a link to a screen that
     *                                 would turn them away.
     */
    public function __construct(
        private readonly string $downloadUrl,
        private readonly ?string $uploadUrl,
    ) {
    }

    public function render(): void
    {
        $paid = TierMatrix::paidEditionName();

        echo '<section class="ghlbsfs-upgrade__strip">';

        printf(
            '<details class="ghlbsfs-disclosure"><summary class="ghlbsfs-disclosure__summary"><span class="ghlbsfs-disclosure__heading">%s</span> <span class="ghlbsfs-disclosure__label">%s</span></summary>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Already own %s?', 'ghostlabs-friendly-skies'),
                $paid
            )),
            esc_html__('Have a license?', 'ghostlabs-friendly-skies')
        );

        echo '<div class="ghlbsfs-disclosure__region">';

        printf(
            '<p class="ghlbsfs-prose">%s</p>',
            // ⚠️ Not "every setting": the one setting this edition has stops applying after the
            // switch. Claims-checked 2026-10-17.
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('%s is a separate plugin. When you activate it, it switches this plugin off. Every block keeps working and every cached response is kept. Pro sets its own cache timings, so the cache duration chosen here no longer applies.', 'ghostlabs-friendly-skies'),
                $paid
            ))
        );

        echo '<ol class="ghlbsfs-list ghlbsfs-steps">';

        printf(
            '<li><a href="%s" target="_blank" rel="noopener noreferrer">%s<span class="screen-reader-text"> %s</span></a></li>',
            esc_url($this->downloadUrl),
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Download %s from your account', 'ghostlabs-friendly-skies'),
                $paid
            )),
            esc_html__('(opens in a new tab)', 'ghostlabs-friendly-skies')
        );

        if ($this->uploadUrl !== null) {
            printf(
                '<li><a href="%s">%s</a> %s</li>',
                esc_url($this->uploadUrl),
                esc_html__('Upload it to this site', 'ghostlabs-friendly-skies'),
                esc_html__('from the Plugins screen.', 'ghostlabs-friendly-skies')
            );
        } else {
            printf(
                '<li>%s</li>',
                esc_html__('Upload it to this site from the Plugins screen. Plugins can\'t be uploaded from your account on this site. Ask whoever manages plugins on this site to upload it for you.', 'ghostlabs-friendly-skies')
            );
        }

        printf(
            '<li>%s</li>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('Activate %s, then enter your license key in its settings.', 'ghostlabs-friendly-skies'),
                $paid
            ))
        );

        echo '</ol></div></details></section>';
    }
}
