<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Traits\RenderHelper;

if ( ! defined( 'ABSPATH' ) ) exit;

class ProfileRenderer
{
    use RenderHelper;

    protected function cssPrefix(): string
    {
        return 'ghostlabs-fs-profile';
    }

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

        $profile = $this->blueskyService->getProfile($handle);

        if (is_wp_error($profile)) {
            return $this->renderError($handle);
        }

        $theme = $attributes['theme'] ?? 'system';
        $cardStyle = $attributes['cardStyle'] ?? 'full';
        $showStats = $attributes['showStats'] ?? true;
        $showBio = $attributes['showBio'] ?? true;
        $ctaButton = $attributes['ctaButton'] ?? 'none';

        $content = match ($cardStyle) {
            'compact' => $this->renderCompact($profile, $showStats, $showBio, $ctaButton),
            'minimal' => $this->renderMinimal($profile),
            default => $this->renderFull($profile, $showStats, $showBio, $ctaButton),
        };

        return sprintf(
            '<div class="ghostlabs-fs-profile ghostlabs-fs-profile--%s" data-ghostlabs-fs-theme="%s">%s</div>',
            esc_attr($cardStyle),
            esc_attr($theme),
            $content,
        );
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function renderFull(array $profile, bool $showStats, bool $showBio, string $ctaButton): string
    {
        $html = $this->renderBanner($profile['banner'] ?? '');
        $html .= '<div class="ghostlabs-fs-profile__body">';
        $html .= $this->renderAvatar($profile['avatar'] ?? '', 'large');
        $html .= $this->renderIdentity($profile['displayName'] ?? '', $profile['handle'] ?? '');

        if ($showBio) {
            $html .= $this->renderBio($profile['description'] ?? '');
        }

        if ($showStats) {
            $html .= $this->renderStats(
                (int) ($profile['followersCount'] ?? 0),
                (int) ($profile['followsCount'] ?? 0),
                (int) ($profile['postsCount'] ?? 0),
            );
        }

        $html .= $this->renderCtaButton($ctaButton, $profile['handle'] ?? '');
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function renderCompact(array $profile, bool $showStats, bool $showBio, string $ctaButton): string
    {
        $html = '<div class="ghostlabs-fs-profile__body">';
        $html .= $this->renderAvatar($profile['avatar'] ?? '', 'medium');
        $html .= $this->renderIdentity($profile['displayName'] ?? '', $profile['handle'] ?? '');

        if ($showBio) {
            $html .= $this->renderBio($profile['description'] ?? '');
        }

        if ($showStats) {
            $html .= $this->renderStats(
                (int) ($profile['followersCount'] ?? 0),
                (int) ($profile['followsCount'] ?? 0),
                (int) ($profile['postsCount'] ?? 0),
            );
        }

        $html .= $this->renderCtaButton($ctaButton, $profile['handle'] ?? '');
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function renderMinimal(array $profile): string
    {
        $html = $this->renderAvatar($profile['avatar'] ?? '', 'small');
        $html .= $this->renderIdentity($profile['displayName'] ?? '', $profile['handle'] ?? '');

        return $html;
    }

    private function renderBanner(string $bannerUrl): string
    {
        if (empty($bannerUrl)) {
            return '<div class="ghostlabs-fs-profile__banner"></div>';
        }

        return sprintf(
            '<div class="ghostlabs-fs-profile__banner"><img class="ghostlabs-fs-profile__banner-img" src="%s" alt="" loading="lazy" /></div>',
            esc_url($bannerUrl),
        );
    }

    private function renderAvatar(string $avatarUrl, string $size): string
    {
        if (empty($avatarUrl)) {
            return '';
        }

        // Decorative: every card style renders the display name right after it, so an alt
        // naming the person would be read twice (accessibility audit 2026-10-23, 🟡 11).
        return sprintf(
            '<img class="ghostlabs-fs-profile__avatar ghostlabs-fs-profile__avatar--%s" src="%s" alt="" loading="lazy" />',
            esc_attr($size),
            esc_url($avatarUrl),
        );
    }

    private function renderIdentity(string $displayName, string $handle): string
    {
        return sprintf(
            '<div class="ghostlabs-fs-profile__identity"><span class="ghostlabs-fs-profile__display-name">%s</span><span class="ghostlabs-fs-profile__handle">@%s</span></div>',
            esc_html($displayName),
            esc_html($handle),
        );
    }

    private function renderBio(string $description): string
    {
        if (empty($description)) {
            return '';
        }

        return sprintf(
            '<p class="ghostlabs-fs-profile__bio">%s</p>',
            esc_html($description),
        );
    }

    private function renderStats(int $followers, int $following, int $posts): string
    {
        return sprintf(
            '<ul class="ghostlabs-fs-profile__stats"><li class="ghostlabs-fs-profile__stat"><strong>%s</strong> %s</li><li class="ghostlabs-fs-profile__stat"><strong>%s</strong> %s</li><li class="ghostlabs-fs-profile__stat"><strong>%s</strong> %s</li></ul>',
            esc_html($this->formatCount($followers)),
            esc_html__('followers', 'ghostlabs-friendly-skies'),
            esc_html($this->formatCount($following)),
            esc_html__('following', 'ghostlabs-friendly-skies'),
            esc_html($this->formatCount($posts)),
            esc_html__('posts', 'ghostlabs-friendly-skies'),
        );
    }

    private function renderCtaButton(string $ctaButton, string $handle): string
    {
        if ($ctaButton === 'none') {
            return '';
        }

        $profileUrl = 'https://bsky.app/profile/' . $handle;

        if ($ctaButton === 'follow') {
            return sprintf(
                '<a class="ghostlabs-fs-profile__cta ghostlabs-fs-profile__cta--follow" href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                esc_url($profileUrl),
                esc_html__('Follow on Bluesky', 'ghostlabs-friendly-skies'),
                $this->newTabHint(),
            );
        }

        return sprintf(
            '<a class="ghostlabs-fs-profile__cta" href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
            esc_url($profileUrl),
            esc_html__('View Profile', 'ghostlabs-friendly-skies'),
            $this->newTabHint(),
        );
    }

    private function renderError(string $handle): string
    {
        $link = '';
        if (!empty($handle)) {
            $profileUrl = 'https://bsky.app/profile/' . $handle;
            $link = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                esc_url($profileUrl),
                esc_html__('View on Bluesky', 'ghostlabs-friendly-skies'),
                $this->newTabHint(),
            );
        }

        return sprintf(
            '<div class="ghostlabs-fs-profile ghostlabs-fs-profile--error"><p>%s</p>%s</div>',
            esc_html__("Couldn't load this Bluesky profile.", 'ghostlabs-friendly-skies'),
            $link,
        );
    }

}
