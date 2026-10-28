<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Traits\RenderHelper;

if ( ! defined( 'ABSPATH' ) ) exit;

class EmbedRenderer
{
    use RenderHelper;

    public function __construct(private BlueskyService $blueskyService) {}

    protected function cssPrefix(): string
    {
        return 'ghostlabs-fs-embed';
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes): string
    {
        $url = $attributes['url'] ?? '';

        if (empty($url)) {
            return '';
        }

        $postData = $this->blueskyService->getPostFromUrl($url);

        if (is_wp_error($postData)) {
            return $this->renderError($url);
        }

        /** @var array<string, mixed> $author */
        $author = $postData['author'] ?? [];
        /** @var array<string, mixed> $record */
        $record = $postData['record'] ?? [];
        /** @var array<string, mixed> $embed */
        $embed = $postData['embed'] ?? [];

        $theme = $attributes['theme'] ?? 'system';
        $displayMode = $attributes['displayMode'] ?? 'card';
        $showEngagement = $attributes['showEngagement'] ?? true;
        $showTimestamp = $attributes['showTimestamp'] ?? true;
        $clickBehavior = $attributes['clickBehavior'] ?? 'new-tab';

        $modeClass = $displayMode === 'theme-integrated'
            ? 'ghostlabs-fs-embed--theme-integrated'
            : 'ghostlabs-fs-embed--card';

        $content = $this->renderAuthorHeader($author);
        $content .= $this->renderPostText($record);

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

        if ($clickBehavior === 'new-tab') {
            $content = sprintf(
                '<a class="ghostlabs-fs-embed__link" href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                esc_url($url),
                $content,
                $this->newTabHint(),
            );
        }

        return sprintf(
            '<div class="ghostlabs-fs-embed %s" data-ghostlabs-fs-theme="%s">%s</div>',
            esc_attr($modeClass),
            esc_attr($theme),
            $content,
        );
    }

    private function renderError(string $url): string
    {
        $link = '';
        if (!empty($url)) {
            $link = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                esc_url($url),
                esc_html__('View on Bluesky', 'ghostlabs-friendly-skies'),
                $this->newTabHint(),
            );
        }

        return sprintf(
            '<div class="ghostlabs-fs-embed ghostlabs-fs-embed--error"><p>%s</p>%s</div>',
            esc_html__("Couldn't load this Bluesky post.", 'ghostlabs-friendly-skies'),
            $link,
        );
    }

}
