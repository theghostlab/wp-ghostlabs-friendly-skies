<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

if ( ! defined( 'ABSPATH' ) ) exit;

/** Two to four columns of equal width. */
final class GridLayout extends PostsLayout
{
    public const NAME = 'grid';

    public function name(): string
    {
        return self::NAME;
    }

    public function render(array $posts, array $attributes): string
    {
        return sprintf(
            '<div class="ghostlabs-fs-feed ghostlabs-fs-feed--grid%s" style="--ghostlabs-fs-grid-columns: %d" data-ghostlabs-fs-theme="%s" data-feed-layout="grid">%s</div>',
            $this->alignClass($attributes),
            (int) ($attributes['gridColumns'] ?? 3),
            esc_attr($this->theme($attributes)),
            $this->renderPosts($posts, $this->postOptions($attributes)),
        );
    }
}
