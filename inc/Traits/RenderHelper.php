<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Traits;

use GHOSTLABS\FRIENDLY_SKIES\Service\RichTextProcessor;

if ( ! defined( 'ABSPATH' ) ) exit;

trait RenderHelper
{
    abstract protected function cssPrefix(): string;

    /**
     * A count abbreviated the way Bluesky shows it, in the site's locale: the decimal mark
     * comes from `number_format_i18n()` and the K / M suffix is a translatable string, so a
     * locale can write "1,2 k" or "1,5 Mio." (accessibility audit 2026-10-23, 🟡 12).
     */
    protected function formatCount(int $count): string
    {
        if ($count >= 1000000) {
            return sprintf(
                /* translators: %s: a count in millions, already formatted for the locale, e.g. "1.5". */
                \__('%sM', 'ghostlabs-friendly-skies'),
                number_format_i18n($count / 1000000, 1),
            );
        }

        if ($count >= 1000) {
            return sprintf(
                /* translators: %s: a count in thousands, already formatted for the locale, e.g. "1.2". */
                \__('%sK', 'ghostlabs-friendly-skies'),
                number_format_i18n($count / 1000, 1),
            );
        }

        return (string) $count;
    }

    /**
     * Visually hidden text that ends a link opening a new tab.
     *
     * ⚠️ **Never an `aria-label` on the link instead.** An `aria-label` *is* the link's name,
     * and on a link that wraps a post it replaced the whole post for screen-reader users
     * (accessibility audit 2026-10-23, ✅ 1 and 🟡 14). Appended text extends the name at the
     * end and replaces nothing. The class is block-scoped (`_shared/global.scss`) because a
     * theme is not guaranteed to define `.screen-reader-text`.
     */
    protected function newTabHint(): string
    {
        return sprintf(
            '<span class="ghostlabs-fs-visually-hidden"> %s</span>',
            esc_html__('(opens in a new tab)', 'ghostlabs-friendly-skies'),
        );
    }

    /**
     * @param array<string, mixed> $author
     */
    protected function renderAuthorHeader(array $author): string
    {
        $p = $this->cssPrefix();
        $avatar = $author['avatar'] ?? '';
        $displayName = $author['displayName'] ?? '';
        $handle = $author['handle'] ?? '';

        // Decorative: the display name and handle are right beside it, so an alt naming the
        // author would be read twice, and open every post link's name (audit 🟡 11).
        $avatarHtml = '';
        if (!empty($avatar)) {
            $avatarHtml = sprintf(
                '<img class="%s__avatar" src="%s" alt="" loading="lazy" />',
                esc_attr($p),
                esc_url($avatar),
            );
        }

        return sprintf(
            '<div class="%1$s__header">%2$s<div class="%1$s__author"><span class="%1$s__display-name">%3$s</span><span class="%1$s__handle">@%4$s</span></div></div>',
            esc_attr($p),
            $avatarHtml,
            esc_html($displayName),
            esc_html($handle),
        );
    }

    /**
     * @param array<string, mixed> $record
     */
    protected function renderPostText(array $record, bool $truncate = false, int $truncateLength = 280): string
    {
        $p = $this->cssPrefix();
        $text = $record['text'] ?? '';

        if (empty($text)) {
            return '';
        }

        /** @var list<array<string, mixed>> $facets */
        $facets = $record['facets'] ?? [];

        $fullHtml = !empty($facets)
            ? RichTextProcessor::renderFacets($text, $facets)
            : esc_html($text);

        if ($truncate && mb_strlen($text, 'UTF-8') > $truncateLength) {
            $truncatedText = mb_substr($text, 0, $truncateLength, 'UTF-8') . '…';
            $truncatedHtml = esc_html($truncatedText);

            return sprintf(
                '<div class="%1$s__content"><span class="%1$s__content-truncated">%2$s</span><span class="%1$s__content-full" hidden>%3$s</span><button class="%1$s__show-more" aria-expanded="false" data-label-more="%4$s" data-label-less="%5$s">%6$s</button></div>',
                esc_attr($p),
                $truncatedHtml,
                $fullHtml,
                esc_attr(\__('Show more', 'ghostlabs-friendly-skies')),
                esc_attr(\__('Show less', 'ghostlabs-friendly-skies')),
                esc_html(\__('Show more', 'ghostlabs-friendly-skies')),
            );
        }

        return sprintf('<div class="%s__content">%s</div>', esc_attr($p), $fullHtml);
    }

    /**
     * @param array<string, mixed> $embed
     */
    protected function renderMedia(array $embed): string
    {
        $type = $embed['$type'] ?? '';

        return match ($type) {
            'app.bsky.embed.images#view' => $this->renderImages($embed['images'] ?? []),
            'app.bsky.embed.external#view' => $this->renderExternalLink($embed['external'] ?? []),
            'app.bsky.embed.video#view' => $this->renderVideo($embed),
            'app.bsky.embed.record#view' => $this->renderQuotePost($embed['record'] ?? []),
            'app.bsky.embed.recordWithMedia#view' => $this->renderRecordWithMedia($embed),
            default => '',
        };
    }

    /**
     * @param list<array<string, mixed>> $images
     */
    protected function renderImages(array $images): string
    {
        if (empty($images)) {
            return '';
        }

        $p = $this->cssPrefix();
        $count = count($images);
        $gridClass = $count > 1 ? $p . '__images--grid' : '';

        $html = '';
        foreach ($images as $image) {
            $html .= sprintf(
                '<img class="%s__image" src="%s" alt="%s" loading="lazy" />',
                esc_attr($p),
                esc_url($image['thumb'] ?? ''),
                esc_attr($image['alt'] ?? ''),
            );
        }

        return sprintf(
            '<div class="%s__images %s">%s</div>',
            esc_attr($p),
            esc_attr($gridClass),
            $html,
        );
    }

    /**
     * @param array<string, mixed> $external
     */
    protected function renderExternalLink(array $external): string
    {
        if (empty($external)) {
            return '';
        }

        $p = $this->cssPrefix();

        $thumb = '';
        if (!empty($external['thumb'])) {
            $thumb = sprintf(
                '<img class="%s__external-thumb" src="%s" alt="" loading="lazy" />',
                esc_attr($p),
                esc_url($external['thumb']),
            );
        }

        return sprintf(
            '<a class="%1$s__external" href="%2$s" target="_blank" rel="noopener noreferrer">%3$s<div class="%1$s__external-info"><span class="%1$s__external-title">%4$s</span><span class="%1$s__external-description">%5$s</span><span class="%1$s__external-uri">%6$s</span></div>%7$s</a>',
            esc_attr($p),
            esc_url($external['uri'] ?? ''),
            $thumb,
            esc_html($external['title'] ?? ''),
            esc_html($external['description'] ?? ''),
            esc_html(wp_parse_url($external['uri'] ?? '', PHP_URL_HOST) ?: ''),
            $this->newTabHint(),
        );
    }

    /**
     * @param array<string, mixed> $video
     */
    protected function renderVideo(array $video): string
    {
        $p = $this->cssPrefix();
        $thumb = $video['thumbnail'] ?? '';
        $alt = is_string($video['alt'] ?? null) ? trim($video['alt']) : '';

        // The video's text alternative: the author's own alt text where they wrote one, and
        // always the word "video", which is what the ▶ tells a sighted reader (audit 🟡 10).
        $label = $alt !== ''
            /* translators: %s: the post author's description of their video. */
            ? sprintf(\__('Video: %s', 'ghostlabs-friendly-skies'), $alt)
            : \__('Video', 'ghostlabs-friendly-skies');

        if (!empty($thumb)) {
            $thumbHtml = sprintf(
                '<img class="%s__video-thumb" src="%s" alt="%s" loading="lazy" />',
                esc_attr($p),
                esc_url($thumb),
                esc_attr($label),
            );
        } else {
            $thumbHtml = sprintf(
                '<span class="ghostlabs-fs-visually-hidden">%s</span>',
                esc_html($label),
            );
        }

        // The ▶ is decorative: the tile plays nothing, so it is hidden from assistive
        // technology and must never become a button that does nothing.
        return sprintf(
            '<div class="%1$s__video">%2$s<div class="%1$s__video-play" aria-hidden="true">&#9654;</div></div>',
            esc_attr($p),
            $thumbHtml,
        );
    }

    /**
     * @param array<string, mixed> $record
     */
    protected function renderQuotePost(array $record): string
    {
        $p = $this->cssPrefix();
        /** @var array<string, mixed> $author */
        $author = $record['author'] ?? [];
        /** @var array<string, mixed> $value */
        $value = $record['value'] ?? [];

        $displayName = $author['displayName'] ?? '';
        $handle = $author['handle'] ?? '';
        $text = $value['text'] ?? '';

        return sprintf(
            '<div class="%1$s__quote"><div class="%1$s__quote-author"><span class="%1$s__display-name">%2$s</span> <span class="%1$s__handle">@%3$s</span></div><div class="%1$s__quote-text">%4$s</div></div>',
            esc_attr($p),
            esc_html($displayName),
            esc_html($handle),
            esc_html($text),
        );
    }

    /**
     * @param array<string, mixed> $embed
     */
    protected function renderRecordWithMedia(array $embed): string
    {
        $media = $embed['media'] ?? [];
        $record = $embed['record'] ?? [];

        $html = '';

        if (!empty($media)) {
            $html .= $this->renderMedia($media);
        }

        if (!empty($record)) {
            $html .= $this->renderQuotePost($record['record'] ?? $record);
        }

        return $html;
    }

    protected function renderEngagement(int $likes, int $replies, int $reposts): string
    {
        $p = $this->cssPrefix();

        return sprintf(
            '<ul class="%1$s__engagement"><li class="%1$s__stat %1$s__stat--replies"><strong>%2$s</strong> %3$s</li><li class="%1$s__stat %1$s__stat--reposts"><strong>%4$s</strong> %5$s</li><li class="%1$s__stat %1$s__stat--likes"><strong>%6$s</strong> %7$s</li></ul>',
            esc_attr($p),
            esc_html($this->formatCount($replies)),
            esc_html__('replies', 'ghostlabs-friendly-skies'),
            esc_html($this->formatCount($reposts)),
            esc_html__('reposts', 'ghostlabs-friendly-skies'),
            esc_html($this->formatCount($likes)),
            esc_html__('likes', 'ghostlabs-friendly-skies'),
        );
    }

    protected function renderTimestamp(string $indexedAt): string
    {
        if (empty($indexedAt)) {
            return '';
        }

        $timestamp = strtotime($indexedAt);

        if ($timestamp === false) {
            return '';
        }

        // The site's timezone, locale and its own date and time formats (Settings › General);
        // date() is UTC under WordPress, and a fixed English pattern forced English word
        // order, a 12-hour clock and the word "at" on every locale (audit 🟡 12).
        $formatted = (string) wp_date($this->dateTimeFormat(), $timestamp);

        return sprintf(
            '<time class="%s__timestamp" datetime="%s">%s</time>',
            esc_attr($this->cssPrefix()),
            esc_attr($indexedAt),
            esc_html($formatted),
        );
    }

    /**
     * The site's date format and time format, joined. WordPress's own defaults stand in for an
     * option that is missing or empty, so a timestamp never renders as a bare time.
     */
    private function dateTimeFormat(): string
    {
        $date = get_option('date_format');
        $time = get_option('time_format');

        $date = is_string($date) && $date !== '' ? $date : 'F j, Y';
        $time = is_string($time) && $time !== '' ? $time : 'g:i a';

        return $date . ' ' . $time;
    }
}
