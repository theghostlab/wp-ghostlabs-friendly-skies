<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Enum;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Which surface a buyer clicked from — the `from=` on every buy link.
 *
 * One enum, never literals at call sites: a typo'd `from` still produces a real URL, and the
 * sale lands in a bucket nobody reads.
 *
 * ⚠️ **A wire format.** The licence manager counts these values, and shipped plugins keep
 * sending them long after a rename. Add a case; never rename one, and never reuse a retired
 * value for a different surface.
 *
 * Retired 2026-10-26, never to be reused: `editor-feed-layout` and `editor-feed-source`, the Feed
 * block's two select refusals. They went with the layouts and sources they refused, which a
 * WordPress.org build may not carry (Guideline 5).
 *
 * `from` is a hint, never an authorisation: it must not touch pricing, entitlement or anything
 * else a buyer could change by editing a URL.
 *
 * @package GhostLabsFriendlySkies
 */
enum UpsellSource: string
{
    /** The upgrade link on this plugin's row on the Plugins screen. */
    case PluginsRow = 'plugins-row';

    /** The Pro tab's hero. */
    case Settings = 'settings';

    /** The Pro tab's ask at the foot of the comparison table: a later reading of the screen. */
    case SettingsTable = 'settings-table';

    /** The Feed block's Upsell panel (2026-10-27). */
    case EditorFeed = 'editor-feed';
}
