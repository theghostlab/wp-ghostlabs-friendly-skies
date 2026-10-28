<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

use GHOSTLABS\FRIENDLY_SKIES\Service\FeedPostRenderer;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * What every layout shares: one `FeedPostRenderer` for the posts, and the display options and
 * alignment the block's attributes carry.
 *
 * @package GhostLabsFriendlySkies
 */
abstract class PostsLayout implements FeedLayout
{
    public function __construct(
        protected readonly FeedPostRenderer $postRenderer,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function postOptions(array $attributes): array
    {
        return [
            'showEngagement' => (bool) ($attributes['showEngagement'] ?? true),
            'showTimestamp' => (bool) ($attributes['showTimestamps'] ?? true),
            'truncate' => (bool) ($attributes['truncatePosts'] ?? false),
            'truncateLength' => (int) ($attributes['truncateLength'] ?? 280),
        ];
    }

    /**
     * @param list<array<string, mixed>> $posts
     * @param array<string, mixed>       $options
     */
    protected function renderPosts(array $posts, array $options): string
    {
        $html = '';
        foreach ($posts as $post) {
            $html .= $this->postRenderer->render($post, $options);
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function theme(array $attributes): string
    {
        $theme = $attributes['theme'] ?? 'system';

        return is_string($theme) ? $theme : 'system';
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function alignClass(array $attributes): string
    {
        $align = $attributes['align'] ?? '';

        return match ($align) {
            'wide', 'full' => ' align' . $align,
            default => '',
        };
    }
}
