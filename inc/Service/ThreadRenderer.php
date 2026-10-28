<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Traits\RenderHelper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ThreadRenderer
{
    use RenderHelper;

    protected function cssPrefix(): string
    {
        return 'ghostlabs-fs-thread-post';
    }

    private const MAX_POSTS = 50;

    public function __construct(
        private BlueskyService $blueskyService,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes): string
    {
        $url = $attributes['url'] ?? '';

        if (empty($url)) {
            return $this->renderError();
        }

        $depth = (int) ($attributes['depth'] ?? 5);
        $threadData = $this->blueskyService->getPostThreadFromUrl($url, $depth);

        if (is_wp_error($threadData)) {
            return $this->renderError();
        }

        $thread = $threadData['thread'] ?? [];

        if (empty($thread)) {
            return $this->renderError();
        }

        $flattened = $this->flattenThread($thread, 0, $depth);

        $showOnlyOpReplies = (bool) ($attributes['showOnlyOpReplies'] ?? false);
        if ($showOnlyOpReplies && !empty($flattened)) {
            $opDid = $flattened[0]['post']['author']['did'] ?? '';
            $flattened = array_values(array_filter($flattened, function (array $item) use ($opDid): bool {
                if ($item['depth'] === 0) {
                    return true;
                }
                return ($item['post']['author']['did'] ?? '') === $opDid;
            }));
        }

        if (count($flattened) > self::MAX_POSTS) {
            $flattened = array_slice($flattened, 0, self::MAX_POSTS);
        }

        $theme = $attributes['theme'] ?? 'system';
        $showEngagement = (bool) ($attributes['showEngagement'] ?? true);
        $clickBehavior = $attributes['clickBehavior'] ?? 'open';
        $collapseThreshold = (int) ($attributes['collapseThreshold'] ?? 10);

        $options = [
            'showEngagement' => $showEngagement,
            'clickBehavior' => $clickBehavior,
        ];

        $totalPosts = count($flattened);
        $needsCollapse = $totalPosts > $collapseThreshold;

        $visibleHtml = '';
        $collapsedHtml = '';

        foreach ($flattened as $i => $item) {
            $postHtml = $this->renderPost($item['post'], $item['depth'], $item['parent'], $options);
            if ($needsCollapse && $i >= $collapseThreshold) {
                $collapsedHtml .= $postHtml;
            } else {
                $visibleHtml .= $postHtml;
            }
        }

        $html = $visibleHtml;

        if ($needsCollapse) {
            $remainingCount = $totalPosts - $collapseThreshold;
            /* translators: %d: number of hidden replies. */
            $labelMore = sprintf(\__('Show %d more replies', 'ghostlabs-friendly-skies'), $remainingCount);
            $labelLess = \__('Show less', 'ghostlabs-friendly-skies');

            $html .= sprintf(
                '<button class="ghostlabs-fs-thread__toggle" data-label-more="%s" data-label-less="%s">%s</button>',
                esc_attr($labelMore),
                esc_attr($labelLess),
                esc_html($labelMore),
            );

            $html .= sprintf(
                '<div class="ghostlabs-fs-thread__collapsed" hidden>%s</div>',
                $collapsedHtml,
            );
        }

        return sprintf(
            '<div class="ghostlabs-fs-thread" data-ghostlabs-fs-theme="%s">%s</div>',
            esc_attr($theme),
            $html,
        );
    }

    /**
     * The thread in reading order, each post with its depth and the handle of the post it
     * replies to (`''` for the root). The parent is taken from the tree, before any filter,
     * so a reply still names whom it answers when the OP-only filter hides that post.
     *
     * @param array<string, mixed> $threadNode
     * @return list<array{post: array<string, mixed>, depth: int, parent: string}>
     */
    public function flattenThread(array $threadNode, int $currentDepth, int $maxDepth, string $parentHandle = ''): array
    {
        $result = [];

        $post = $threadNode['post'] ?? [];
        if (!empty($post)) {
            $result[] = ['post' => $post, 'depth' => $currentDepth, 'parent' => $parentHandle];
        }

        $handle = is_array($post) && is_string($post['author']['handle'] ?? null) ? $post['author']['handle'] : '';

        if ($currentDepth >= $maxDepth) {
            return $result;
        }

        /** @var list<array<string, mixed>> $replies */
        $replies = $threadNode['replies'] ?? [];

        foreach ($replies as $reply) {
            $type = $reply['$type'] ?? '';
            if ($type !== 'app.bsky.feed.defs#threadViewPost') {
                continue;
            }
            array_push($result, ...$this->flattenThread($reply, $currentDepth + 1, $maxDepth, $handle));
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $postData
     * @param array<string, mixed> $options
     */
    private function renderPost(array $postData, int $depth, string $parentHandle, array $options): string
    {
        /** @var array<string, mixed> $author */
        $author = $postData['author'] ?? [];
        /** @var array<string, mixed> $record */
        $record = $postData['record'] ?? [];
        /** @var array<string, mixed> $embed */
        $embed = $postData['embed'] ?? [];

        $uri = $postData['uri'] ?? '';
        $handle = $author['handle'] ?? '';
        $rkey = '';
        if (preg_match('#/([^/]+)$#', $uri, $m)) {
            $rkey = $m[1];
        }

        $clickBehavior = $options['clickBehavior'] ?? 'open';
        $showEngagement = (bool) ($options['showEngagement'] ?? true);

        $innerContent = $this->renderAuthorHeader($author);
        $innerContent .= $this->renderPostText($record);

        if ($clickBehavior === 'open') {
            $postUrl = 'https://bsky.app/profile/' . $handle . '/post/' . $rkey;
            $innerContent = sprintf(
                '<a class="ghostlabs-fs-thread-post__link" href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                esc_url($postUrl),
                $innerContent,
                $this->newTabHint(),
            );
        }

        if (!empty($embed)) {
            $innerContent .= $this->renderMedia($embed);
        }

        if ($showEngagement) {
            $innerContent .= $this->renderEngagement(
                (int) ($postData['likeCount'] ?? 0),
                (int) ($postData['replyCount'] ?? 0),
                (int) ($postData['repostCount'] ?? 0),
            );
        }

        $innerContent .= $this->renderTimestamp($postData['indexedAt'] ?? '');

        return sprintf(
            '<article class="ghostlabs-fs-thread-post" style="--depth: %d" data-post-uri="%s">%s%s</article>',
            $depth,
            esc_attr($uri),
            $this->renderReplyTo($depth, $parentHandle),
            $innerContent,
        );
    }

    /**
     * Who a reply answers, as text a screen reader reads first and nobody sees. Indentation
     * alone reached assistive technology only as a CSS custom property, so a thread read as a
     * flat run of posts (accessibility audit 2026-10-23, 🟡 9; WCAG 1.3.1). It sits before the
     * post's link, never inside it, so the link is still named by its own visible text.
     */
    private function renderReplyTo(int $depth, string $parentHandle): string
    {
        if ($depth === 0 || $parentHandle === '') {
            return '';
        }

        return sprintf(
            '<span class="ghostlabs-fs-visually-hidden">%s</span>',
            /* translators: %s: the Bluesky handle of the post being replied to, without the @. */
            esc_html(sprintf(\__('Reply to @%s', 'ghostlabs-friendly-skies'), $parentHandle)),
        );
    }

    private function renderError(): string
    {
        return sprintf(
            '<div class="ghostlabs-fs-thread ghostlabs-fs-thread--error"><p>%s</p></div>',
            esc_html__("Couldn't load this Bluesky thread.", 'ghostlabs-friendly-skies'),
        );
    }
}
