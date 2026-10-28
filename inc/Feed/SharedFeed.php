<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

use GHOSTLABS\FRIENDLY_SKIES\Service\BlueskyService;
use GHOSTLABS\FRIENDLY_SKIES\Service\FeedPostRenderer;
use GHOSTLABS\FRIENDLY_SKIES\Service\FeedRenderer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Feed block every build has: the List and Grid layouts, over one account's own posts.
 *
 * A build renders what it was composed with and nothing else, so the names below are what the
 * block offers in that build — never a list of what it would refuse. A tier that has more
 * composes more, from its own tree.
 *
 * ⚠️ **The one declaration.** The settings screen's comparison table reads these names rather
 * than restating them.
 *
 * @package GhostLabsFriendlySkies
 */
final class SharedFeed
{
    /** In the order the editor lists them. The first is what an unknown layout renders as. */
    public const LAYOUTS = [ListLayout::NAME, GridLayout::NAME];

    /** In the order the editor lists them. */
    public const SOURCES = [AccountSource::NAME];

    /** @return list<FeedLayout> */
    public static function layouts(FeedPostRenderer $postRenderer): array
    {
        return [new ListLayout($postRenderer), new GridLayout($postRenderer)];
    }

    /** @return list<FeedSource> */
    public static function sources(BlueskyService $blueskyService): array
    {
        return [new AccountSource($blueskyService)];
    }

    public static function renderer(BlueskyService $blueskyService, FeedPostRenderer $postRenderer): FeedRenderer
    {
        return new FeedRenderer($blueskyService, self::layouts($postRenderer), self::sources($blueskyService));
    }
}
