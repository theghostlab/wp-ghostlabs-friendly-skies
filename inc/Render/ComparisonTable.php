<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Render;

use GHOSTLABS\FRIENDLY_SKIES\Config\TierMatrix;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What the free edition includes, beside what Pro adds — Cycle Block's comparison table,
 * server-rendered, by way of Undercard.
 *
 * ⚠️ **A renderer, not a declaration.** Every label, quantity and verdict comes from
 * `Config\TierMatrix`, which reads the free sets from the renderer that enforces them. Nothing
 * here restates a capability.
 *
 * A real `<table>` with `th[scope]` on both axes, so every cell is announced with the feature
 * *and* the edition it belongs to.
 *
 * @package GhostLabsFriendlySkies
 */
final class ComparisonTable
{
    /**
     * Cycle Block's two marks, 20×20.
     *
     * ⚠️ The marks ARE the answer of their cell, so they are graphical objects that need 3:1
     * against the ground (WCAG 1.4.11) — and axe cannot see that, because its colour rule reads
     * text nodes, not SVG fills. The colour lives in `settings.css`; the E2E spec measures it.
     */
    private const CHECK = 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z';

    private const CROSS = 'M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z';

    public function render(): void
    {
        echo '<table class="ghlbsfs-compare__table">';

        printf(
            '<caption class="screen-reader-text">%s</caption>',
            esc_html(sprintf(
                /* translators: %s: the paid edition's name. */
                __('What the free edition includes, and what %s adds', 'ghostlabs-friendly-skies'),
                TierMatrix::paidEditionName()
            ))
        );

        printf(
            '<thead><tr><th scope="col" class="ghlbsfs-compare__feature"><span class="screen-reader-text">%s</span></th>'
            . '<th scope="col" class="ghlbsfs-compare__free">%s</th>'
            . '<th scope="col" class="ghlbsfs-compare__paid">%s</th></tr></thead>',
            esc_html__('Feature', 'ghostlabs-friendly-skies'),
            esc_html__('Free', 'ghostlabs-friendly-skies'),
            esc_html__('Pro', 'ghostlabs-friendly-skies')
        );

        echo '<tbody>';

        foreach (TierMatrix::capabilities() as $capability) {
            $this->row($capability);
        }

        echo '</tbody></table>';
    }

    /**
     * @param array{key: string, label: string, free: bool|string, paid: bool|string} $capability
     */
    private function row(array $capability): void
    {
        printf('<tr data-capability="%s"><th scope="row">%s</th>', esc_attr($capability['key']), esc_html($capability['label']));

        echo '<td class="ghlbsfs-compare__free">';
        $this->cell($capability['free'], true);
        echo '</td><td class="ghlbsfs-compare__paid">';
        $this->cell($capability['paid'], false);
        echo '</td></tr>';
    }

    /**
     * A verdict or a quantity. A quantity is rendered as itself, so the row where free gets
     * part of something says which part instead of being flattened to a cross.
     */
    private function cell(bool|string $value, bool $muted): void
    {
        if (is_string($value)) {
            echo esc_html($value);

            return;
        }

        $this->mark(
            $value ? self::CHECK : self::CROSS,
            $value ? $muted : true,
            $value ? __('Included', 'ghostlabs-friendly-skies') : __('Not included', 'ghostlabs-friendly-skies')
        );
    }

    private function mark(string $path, bool $muted, string $word): void
    {
        printf(
            '<svg class="ghlbsfs-compare__mark%s" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false">'
            . '<path fill-rule="evenodd" clip-rule="evenodd" d="%s"/></svg>'
            . '<span class="screen-reader-text">%s</span>',
            $muted ? ' ghlbsfs-compare__mark--muted' : '',
            esc_attr($path),
            esc_html($word)
        );
    }
}
