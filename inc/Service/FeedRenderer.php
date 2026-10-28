<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Feed\FeedLayout;
use GHOSTLABS\FRIENDLY_SKIES\Feed\FeedSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Feed block's render: read a source, filter and pin, then hand the posts to a layout.
 *
 * It renders with the layouts and sources it was composed with and knows no others —
 * `Feed\SharedFeed` composes every build's. A value it has none for is malformed input, not a
 * feature to explain: an unknown layout renders as the first one, and an unknown source has
 * nothing to show.
 */
class FeedRenderer
{
    /** @var array<string, FeedLayout> */
    private array $layouts = [];

    /** @var array<string, FeedSource> */
    private array $sources = [];

    /**
     * @param list<FeedLayout> $layouts In the editor's order; the first is the fallback.
     * @param list<FeedSource> $sources In the editor's order; the first is the default.
     */
    public function __construct(
        private BlueskyService $blueskyService,
        array $layouts,
        array $sources,
    ) {
        foreach ($layouts as $layout) {
            $this->layouts[$layout->name()] = $layout;
        }

        foreach ($sources as $source) {
            $this->sources[$source->name()] = $source;
        }
    }

    /** @return list<string> */
    public function layoutNames(): array
    {
        return array_keys($this->layouts);
    }

    /** @return list<string> */
    public function sourceNames(): array
    {
        return array_keys($this->sources);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes): string
    {
        $source = $this->source($attributes['sourceType'] ?? null);

        if ($source === null) {
            return '';
        }

        $subject = $source->subject($attributes);

        if ($subject === '') {
            return '';
        }

        $maxPosts = (int) ($attributes['maxPosts'] ?? 10);
        $feedData = $source->fetch($subject, $maxPosts);

        if (is_wp_error($feedData)) {
            return $this->renderError();
        }

        $posts = $feedData['posts'];

        $includeReplies = (bool) ($attributes['includeReplies'] ?? false);
        $includeReposts = (bool) ($attributes['includeReposts'] ?? true);
        $contentFilter = $attributes['contentFilter'] ?? 'all';

        $posts = $this->filterPosts($posts, $includeReplies, $includeReposts, $contentFilter);

        $pinnedPostUri = $attributes['pinnedPostUri'] ?? '';
        if (!empty($pinnedPostUri)) {
            $resolvedPinned = $this->blueskyService->resolveToAtUri($pinnedPostUri);
            if (!is_wp_error($resolvedPinned)) {
                $posts = $this->applyPinnedPost($posts, $resolvedPinned);
            }
        }

        $posts = array_slice($posts, 0, $maxPosts);

        return $this->layout($attributes['layout'] ?? null)->render($posts, $attributes);
    }

    /** A block with no `sourceType` reads the default source; one it has no source for, none. */
    private function source(mixed $name): ?FeedSource
    {
        if ($name === null) {
            return reset($this->sources) ?: null;
        }

        return is_string($name) ? ($this->sources[$name] ?? null) : null;
    }

    private function layout(mixed $name): FeedLayout
    {
        if (is_string($name) && isset($this->layouts[$name])) {
            return $this->layouts[$name];
        }

        /** @var FeedLayout */
        return reset($this->layouts);
    }

    /**
     * @param list<array<string, mixed>> $posts
     * @return list<array<string, mixed>>
     */
    private function filterPosts(array $posts, bool $includeReplies, bool $includeReposts, string $contentFilter): array
    {
        return array_values(array_filter($posts, function (array $post) use ($includeReplies, $includeReposts, $contentFilter): bool {
            if (!$includeReplies && isset($post['reply'])) {
                return false;
            }

            if (!$includeReposts && isset($post['reason']['$type']) && $post['reason']['$type'] === 'app.bsky.feed.defs#reasonRepost') {
                return false;
            }

            if ($contentFilter !== 'all') {
                $embedType = $post['embed']['$type'] ?? '';
                return match ($contentFilter) {
                    'text' => empty($embedType),
                    'images' => str_contains($embedType, 'images'),
                    'video' => str_contains($embedType, 'video'),
                    'links' => str_contains($embedType, 'external'),
                    default => true,
                };
            }

            return true;
        }));
    }

    /**
     * @param list<array<string, mixed>> $posts
     * @return list<array<string, mixed>>
     */
    private function applyPinnedPost(array $posts, string $pinnedUri): array
    {
        $pinnedIndex = null;
        foreach ($posts as $i => $post) {
            if (($post['uri'] ?? '') === $pinnedUri) {
                $pinnedIndex = $i;
                break;
            }
        }

        if ($pinnedIndex !== null && $pinnedIndex > 0) {
            $pinned = $posts[$pinnedIndex];
            array_splice($posts, $pinnedIndex, 1);
            array_unshift($posts, $pinned);
        }

        return $posts;
    }

    private function renderError(): string
    {
        return sprintf(
            '<div class="ghostlabs-fs-feed ghostlabs-fs-feed--error"><p>%s</p></div>',
            esc_html__("Couldn't load this Bluesky feed.", 'ghostlabs-friendly-skies'),
        );
    }
}
