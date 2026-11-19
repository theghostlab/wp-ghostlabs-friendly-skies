<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The block's own wrapper, for the blocks whose renderer builds all of its markup itself.
 *
 * A dynamic block's alignment and Additional CSS class reach the page only through
 * `get_block_wrapper_attributes()`. Profile Card and Thread declared `supports.align` and echoed
 * their renderer's markup bare, so what the editor saved was dropped on the front end.
 *
 * @package GhostLabsFriendlySkies
 */
final class BlockWrapper
{
    /** A block with nothing to show stays empty, with no wrapper left behind. */
    public static function wrap(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return sprintf('<div %s>%s</div>', get_block_wrapper_attributes(), $html);
    }
}
