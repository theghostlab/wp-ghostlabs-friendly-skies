<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

use GHOSTLABS\FRIENDLY_SKIES\Service\BlueskyService;

if ( ! defined( 'ABSPATH' ) ) exit;

/** One account's own posts, by handle or DID — the block's `user` source. */
final class AccountSource implements FeedSource
{
    public const NAME = 'user';

    public function __construct(
        private readonly BlueskyService $blueskyService,
    ) {}

    public function name(): string
    {
        return self::NAME;
    }

    public function subject(array $attributes): string
    {
        $actor = $attributes['actor'] ?? '';

        return is_string($actor) ? $actor : '';
    }

    public function fetch(string $subject, int $limit): array|\WP_Error
    {
        return $this->blueskyService->getAuthorFeed($subject, $limit, 'posts_no_replies');
    }
}
