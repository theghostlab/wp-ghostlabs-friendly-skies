<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Render;

use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Pro tab's two buy links share one shape, so it is written once.
 *
 * Every link lands on the product page, not checkout, so every one reads *See Friendly Skies
 * Pro*: one label for one URL (Undercard's rule).
 *
 * @package GhostLabsFriendlySkies
 */
final class BuyLink
{
    /**
     * @param string $url   Built by `Service\UpgradeEndpoints::upgradeUrl()`, carrying its
     *                      surface. The caller renders nothing when it has no URL.
     * @param string $class The visual treatment: the hero's pill or the table's button.
     */
    public static function render(string $url, string $class): string
    {
        // ⚠️ The new-tab warning is part of the accessible name (WCAG 3.2.5), hidden visually
        // only because the design carries an arrow instead. Removing the span silently
        // reintroduces the failure. The arrow is decoration, so it is hidden from the
        // accessibility tree.
        return sprintf(
            '<a class="%s" href="%s" target="_blank" rel="noopener noreferrer">%s'
            . '<span class="screen-reader-text"> %s</span><span aria-hidden="true"> &rarr;</span></a>',
            esc_attr($class),
            esc_url($url),
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('See %s', 'ghostlabs-friendly-skies'),
                TierMatrix::paidEditionName()
            )),
            esc_html__('(opens in a new tab)', 'ghostlabs-friendly-skies')
        );
    }
}
