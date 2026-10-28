<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Traits\RenderHelper;

if ( ! defined( 'ABSPATH' ) ) exit;

class FeedPostRenderer
{
    use RenderHelper;

    protected function cssPrefix(): string
    {
        return 'ghostlabs-fs-feed-post';
    }

    /**
     * @param array<string, mixed> $postData
     * @param array<string, mixed> $options
     */
    public function render(array $postData, array $options): string
    {
        if (empty($postData['uri'])) {
            return '';
        }

        if (!empty($options['compact'])) {
            return $this->renderCompact($postData);
        }

        /** @var array<string, mixed> $author */
        $author = $postData['author'] ?? [];
        /** @var array<string, mixed> $record */
        $record = $postData['record'] ?? [];
        /** @var array<string, mixed> $embed */
        $embed = $postData['embed'] ?? [];

        $showEngagement = (bool) ($options['showEngagement'] ?? true);
        $showTimestamp = (bool) ($options['showTimestamp'] ?? true);
        $truncate = (bool) ($options['truncate'] ?? false);
        $truncateLength = (int) ($options['truncateLength'] ?? 280);

        $handle = $author['handle'] ?? '';
        $uri = $postData['uri'];
        $rkey = '';
        if (preg_match('#/([^/]+)$#', $uri, $m)) {
            $rkey = $m[1];
        }

        $postUrl = 'https://bsky.app/profile/' . $handle . '/post/' . $rkey;

        $linkContent = $this->renderAuthorHeader($author);
        $linkContent .= $this->renderPostText($record, $truncate, $truncateLength);

        $content = sprintf(
            '<a class="ghostlabs-fs-feed-post__link" href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
            esc_url($postUrl),
            $linkContent,
            $this->newTabHint(),
        );

        if (!empty($embed)) {
            $content .= $this->renderMedia($embed);
        }

        if ($showEngagement) {
            $content .= $this->renderEngagement(
                (int) ($postData['likeCount'] ?? 0),
                (int) ($postData['replyCount'] ?? 0),
                (int) ($postData['repostCount'] ?? 0),
            );
        }

        if ($showTimestamp) {
            $content .= $this->renderTimestamp($postData['indexedAt'] ?? '');
        }

        return sprintf(
            '<article class="ghostlabs-fs-feed-post" data-post-uri="%s">%s</article>',
            esc_attr($uri),
            $content,
        );
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function renderCompact(array $postData): string
    {
        /** @var array<string, mixed> $author */
        $author = $postData['author'] ?? [];
        /** @var array<string, mixed> $record */
        $record = $postData['record'] ?? [];

        $handle = $author['handle'] ?? '';
        $displayName = $author['displayName'] ?? '';
        $avatar = $author['avatar'] ?? '';
        $text = $record['text'] ?? '';
        $uri = $postData['uri'];

        $rkey = '';
        if (preg_match('#/([^/]+)$#', $uri, $m)) {
            $rkey = $m[1];
        }

        $postUrl = 'https://bsky.app/profile/' . $handle . '/post/' . $rkey;

        if (mb_strlen($text) > 100) {
            $text = mb_substr($text, 0, 100) . '…';
        }

        // The avatar is decorative: the display name follows it inside the same link.
        return sprintf(
            '<article class="ghostlabs-fs-feed-post ghostlabs-fs-feed-post--compact" data-post-uri="%s"><a class="ghostlabs-fs-feed-post__link" href="%s" target="_blank" rel="noopener noreferrer"><img class="ghostlabs-fs-feed-post__avatar" src="%s" alt="" loading="lazy" /><span class="ghostlabs-fs-feed-post__display-name">%s</span><span class="ghostlabs-fs-feed-post__content-snippet">%s</span>%s</a></article>',
            esc_attr($uri),
            esc_url($postUrl),
            esc_url($avatar),
            esc_html($displayName),
            esc_html($text),
            $this->newTabHint(),
        );
    }

}
