<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Feed;

if ( ! defined( 'ABSPATH' ) ) exit;

/** One post after another. */
final class ListLayout extends PostsLayout
{
    public const NAME = 'list';

    public function name(): string
    {
        return self::NAME;
    }

    public function render(array $posts, array $attributes): string
    {
        return sprintf(
            '<div class="ghostlabs-fs-feed ghostlabs-fs-feed--list" data-ghostlabs-fs-theme="%s" data-feed-layout="list">%s</div>',
            esc_attr($this->theme($attributes)),
            $this->renderPosts($posts, $this->postOptions($attributes)),
        );
    }
}
