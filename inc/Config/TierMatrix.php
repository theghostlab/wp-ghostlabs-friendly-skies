<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Config;

use GHOSTLABS\FRIENDLY_SKIES\Feed\SharedFeed;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What the free edition includes, beside what Pro adds — declared once.
 *
 * The free/paid split would otherwise be restated in the Pro tab's table, the editor's lock and
 * the readme, and in Cycle Block it drifted three times before anyone noticed. So:
 *
 * - **The free sets are read, never restated.** `Feed\SharedFeed` is what this build renders,
 *   and the table names exactly that.
 * - **The paid totals are stated, not counted.** This build carries none of the paid edition's
 *   code, so there is nothing here to count; the paid edition's suite pins both numbers against
 *   what it composes (`TierMatrixTotalsTest`).
 * - **Labels resolve when asked, not at declaration.** A registry that called `__()` while
 *   loading would bake in whichever locale registered first.
 * - **Facts only.** Capability names, verdicts and quantities. Prose stays on its surface.
 *
 * Free-only: the paid edition sells nothing, so it has no table to fill.
 *
 * @package GhostLabsFriendlySkies
 */
final class TierMatrix
{
    /** How many Feed layouts the paid edition has, this build's included. */
    public const PAID_LAYOUT_COUNT = 6;

    /** How many Feed sources the paid edition has, this build's included. */
    public const PAID_SOURCE_COUNT = 4;

    /**
     * The paid edition's name, as the free edition's copy says it. A product name, so it is
     * not translated.
     */
    public static function paidEditionName(): string
    {
        return 'Friendly Skies Pro';
    }

    /**
     * The label of every Feed layout this build renders, keyed by the value the block stores, in
     * the editor's order.
     *
     * @return array<string, string>
     */
    public static function layoutLabels(): array
    {
        return [
            'list' => __('List', 'ghostlabs-friendly-skies'),
            'grid' => __('Grid', 'ghostlabs-friendly-skies'),
        ];
    }

    /**
     * The label of every Feed source this build reads, keyed by the value the block stores.
     *
     * @return array<string, string>
     */
    public static function sourceLabels(): array
    {
        return [
            'user' => __('User Feed', 'ghostlabs-friendly-skies'),
        ];
    }

    /**
     * The comparison table's rows, claims-checked 2026-10-17 and signed off as Penpot →
     * Friendly Skies → *Admin — Upsell Surfaces*, board 1.
     *
     * `true` / `false` is a verdict; a string is a quantity, so a row where free has part of
     * something says which part instead of being flattened to a cross.
     *
     * @return list<array{key: string, label: string, free: bool|string, paid: bool|string}>
     */
    public static function capabilities(): array
    {
        return [
            [
                'key'   => 'blocks',
                'label' => __('The Bluesky Post, Profile Card, Follow Button and Thread blocks', 'ghostlabs-friendly-skies'),
                'free'  => true,
                'paid'  => true,
            ],
            [
                'key'   => 'feed-layouts',
                'label' => __('Feed layouts', 'ghostlabs-friendly-skies'),
                'free'  => self::named(SharedFeed::LAYOUTS, self::layoutLabels()),
                'paid'  => self::all(self::PAID_LAYOUT_COUNT),
            ],
            [
                'key'   => 'feed-sources',
                'label' => __('Feed sources', 'ghostlabs-friendly-skies'),
                'free'  => self::named(SharedFeed::SOURCES, self::sourceLabels()),
                'paid'  => self::all(self::PAID_SOURCE_COUNT),
            ],
            [
                'key'   => 'import',
                'label' => __('Import from Bluesky — your posts become WordPress posts, or posts of a custom post type Friendly Skies creates for them', 'ghostlabs-friendly-skies'),
                'free'  => false,
                'paid'  => true,
            ],
            [
                'key'   => 'import-schedule',
                'label' => __('Imports on a schedule, from every 15 minutes to once a day', 'ghostlabs-friendly-skies'),
                'free'  => false,
                'paid'  => true,
            ],
            [
                // ⚠️ Not "skip words": the table claims only what a buyer can set (claims
                // check, 2026-10-17).
                'key'   => 'import-filters',
                'label' => __('Import filters — replies, reposts, posts with media only', 'ghostlabs-friendly-skies'),
                'free'  => false,
                'paid'  => true,
            ],
            [
                'key'   => 'import-styles',
                'label' => __('Imported posts styled as a Bluesky embed, or in your theme\'s own style', 'ghostlabs-friendly-skies'),
                'free'  => false,
                'paid'  => true,
            ],
        ];
    }

    /**
     * @param list<string>          $values
     * @param array<string, string> $labels
     */
    private static function named(array $values, array $labels): string
    {
        return implode(
            /* translators: separates the names in a short list, as in "List, Grid". */
            __(', ', 'ghostlabs-friendly-skies'),
            array_map(static fn(string $v): string => $labels[$v] ?? $v, $values)
        );
    }

    private static function all(int $count): string
    {
        return sprintf(
            /* translators: %d: how many there are in all, as in "All 6". */
            __('All %d', 'ghostlabs-friendly-skies'),
            $count
        );
    }
}
