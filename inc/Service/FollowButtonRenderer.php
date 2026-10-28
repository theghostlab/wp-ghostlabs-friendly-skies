<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Traits\RenderHelper;

if ( ! defined( 'ABSPATH' ) ) exit;

class FollowButtonRenderer
{
    use RenderHelper;

    protected function cssPrefix(): string
    {
        return 'ghostlabs-fs-follow';
    }

    /** @var array<string, array<string, mixed>|\WP_Error> */
    private static array $profileCache = [];

    public function __construct(private BlueskyService $blueskyService) {}

    /**
     * @param array<string, mixed> $attributes
     */
    public function render(array $attributes): string
    {
        $handle = $attributes['handle'] ?? '';

        if (empty($handle)) {
            return '';
        }

        $theme = $attributes['theme'] ?? 'system';
        $buttonStyle = $attributes['buttonStyle'] ?? 'primary';
        $showFollowerCount = $attributes['showFollowerCount'] ?? false;
        $label = $attributes['label'] ?? \__('Follow on Bluesky', 'ghostlabs-friendly-skies');
        $size = $attributes['size'] ?? 'medium';
        $showIcon = $attributes['showIcon'] ?? true;

        $followerCount = null;

        if ($showFollowerCount) {
            if (!isset(self::$profileCache[$handle])) {
                self::$profileCache[$handle] = $this->blueskyService->getProfile($handle);
            }

            $profile = self::$profileCache[$handle];

            if (!is_wp_error($profile)) {
                $followerCount = (int) ($profile['followersCount'] ?? 0);
            }
        }

        $profileUrl = 'https://bsky.app/profile/' . $handle;

        // ⚠️ No aria-label: the link is named by its own text, in reading order — the visible
        // label first, whatever the author typed, then the handle, the count and the new-tab
        // hint as text a screen reader reads and nobody sees. An aria-label built from
        // "Follow" + handle + "on Bluesky" interrupted the visible label and dropped a custom
        // one from the name entirely (accessibility audit 2026-10-23, 🟡 5; WCAG 2.5.3).
        $content = '';

        if ($showIcon) {
            $content .= $this->renderIcon();
        }

        $content .= sprintf(
            '<span class="ghostlabs-fs-follow__label">%s</span><span class="ghostlabs-fs-visually-hidden"> (@%s)</span>',
            esc_html($label),
            esc_html($handle),
        );

        if ($followerCount !== null) {
            $content .= $this->renderCount($followerCount);
        }

        return sprintf(
            '<a class="ghostlabs-fs-follow ghostlabs-fs-follow--%s ghostlabs-fs-follow--%s" href="%s" target="_blank" rel="noopener noreferrer" data-ghostlabs-fs-theme="%s">%s%s</a>',
            esc_attr($buttonStyle),
            esc_attr($size),
            esc_url($profileUrl),
            esc_attr($theme),
            $content,
            $this->newTabHint(),
        );
    }

    private function renderIcon(): string
    {
        return '<svg class="ghostlabs-fs-follow__icon" viewBox="0 0 600 530" fill="currentColor" aria-hidden="true"><path d="m135.72 44.03c66.496 49.921 138.02 151.14 164.28 205.46 26.262-54.316 97.782-155.54 164.28-205.46 47.98-36.021 125.72-63.892 125.72 24.795 0 17.712-10.155 148.79-16.111 170.07-20.703 73.984-96.144 92.854-163.25 81.433 117.3 19.964 147.14 86.092 82.697 152.22-122.39 125.59-175.91-31.511-189.63-71.766-2.514-7.3797-3.6904-10.832-3.7077-7.8964-0.0174-2.9357-1.1937 0.51669-3.7077 7.8964-13.714 40.255-67.233 197.36-189.63 71.766-64.444-66.128-34.605-132.26 82.697-152.22-67.108 11.421-142.55-7.4491-163.25-81.433-5.9562-21.282-16.111-152.36-16.111-170.07 0-88.687 77.742-60.816 125.72-24.795z"/></svg>';
    }

    private function renderCount(int $count): string
    {
        return sprintf(
            '<span class="ghostlabs-fs-follow__count">%s<span class="ghostlabs-fs-visually-hidden"> %s</span></span>',
            esc_html($this->formatCount($count)),
            esc_html__('followers', 'ghostlabs-friendly-skies'),
        );
    }

}
