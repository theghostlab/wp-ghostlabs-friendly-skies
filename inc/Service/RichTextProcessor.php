<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class RichTextProcessor
{
    /**
     * @param list<array<string, mixed>> $facets
     * @param bool $newTabHint The blocks' hidden "(opens in a new tab)". ⚠️ Off for stored content
     *                         (the importer): the class that hides it ships only with the blocks'
     *                         stylesheet, so in a post, a feed or an excerpt it would read as text.
     */
    public static function renderFacets(string $text, array $facets, bool $newTabHint = true): string
    {
        if (empty($facets)) {
            return esc_html($text);
        }

        $sorted = true;
        for ($i = 1, $len = count($facets); $i < $len; $i++) {
            if (($facets[$i]['index']['byteStart'] ?? 0) < ($facets[$i - 1]['index']['byteStart'] ?? 0)) {
                $sorted = false;
                break;
            }
        }

        if (!$sorted) {
            usort($facets, fn(array $a, array $b): int =>
                ($a['index']['byteStart'] ?? 0) <=> ($b['index']['byteStart'] ?? 0)
            );
        }

        $result = '';
        $lastByte = 0;

        foreach ($facets as $facet) {
            $start = (int) ($facet['index']['byteStart'] ?? 0);
            $end = (int) ($facet['index']['byteEnd'] ?? 0);
            $features = $facet['features'] ?? [];

            if ($start < $lastByte || $start >= $end) {
                continue;
            }

            $result .= esc_html(mb_strcut($text, $lastByte, $start - $lastByte, 'UTF-8'));

            $segment = mb_strcut($text, $start, $end - $start, 'UTF-8');

            $href = self::facetHref($features);

            if (!empty($href)) {
                // Hidden text, never an aria-label: a label would replace the link's own words.
                $hint = $newTabHint
                    ? sprintf('<span class="ghostlabs-fs-visually-hidden"> %s</span>', esc_html__('(opens in a new tab)', 'ghostlabs-friendly-skies'))
                    : '';
                $result .= sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s%s</a>',
                    esc_url($href),
                    esc_html($segment),
                    $hint,
                );
            } else {
                $result .= esc_html($segment);
            }

            $lastByte = $end;
        }

        $result .= esc_html(mb_strcut($text, $lastByte, null, 'UTF-8'));

        return $result;
    }

    /** @param list<array<string, mixed>> $features */
    public static function facetHref(array $features): string
    {
        foreach ($features as $feature) {
            $type = $feature['$type'] ?? '';

            switch ($type) {
                case 'app.bsky.richtext.facet#mention':
                    $did = $feature['did'] ?? '';
                    return !empty($did) ? 'https://bsky.app/profile/' . $did : '';

                case 'app.bsky.richtext.facet#link':
                    return $feature['uri'] ?? '';

                case 'app.bsky.richtext.facet#tag':
                    $tag = $feature['tag'] ?? '';
                    return !empty($tag) ? 'https://bsky.app/hashtag/' . urlencode($tag) : '';
            }
        }

        return '';
    }
}
