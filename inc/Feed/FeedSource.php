<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Somewhere the Feed block can read its posts from.
 *
 * `Service\FeedRenderer` dispatches on the block's `sourceType` attribute to the source of that
 * name. A build has the sources it was composed with and no others (`SharedFeed`).
 *
 * @package GhostLabsFriendlySkies
 */
interface FeedSource
{
    /** The value the block stores in its `sourceType` attribute. */
    public function name(): string;

    /**
     * What this source shows, read from the block's attributes — a handle, a URL. An empty string
     * means there is nothing to show yet, and the block renders nothing.
     *
     * @param array<string, mixed> $attributes
     */
    public function subject(array $attributes): string;

    /**
     * @return array{posts: list<array<string, mixed>>, cursor: string|null}|\WP_Error
     */
    public function fetch(string $subject, int $limit): array|\WP_Error;
}
