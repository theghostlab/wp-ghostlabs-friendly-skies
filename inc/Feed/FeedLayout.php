<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * One way the Feed block can lay its posts out.
 *
 * `Service\FeedRenderer` dispatches on the block's `layout` attribute to the layout of that name.
 * A build has the layouts it was composed with and no others (`SharedFeed`).
 *
 * @package GhostLabsFriendlySkies
 */
interface FeedLayout
{
    /** The value the block stores in its `layout` attribute. */
    public function name(): string;

    /**
     * @param list<array<string, mixed>> $posts      Already filtered, pinned and limited.
     * @param array<string, mixed>       $attributes The block's attributes.
     */
    public function render(array $posts, array $attributes): string;
}
