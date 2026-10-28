<?php
/**
 * Server-side rendering for the ghostlabs/friendly-skies-thread block.
 *
 * @var array<string, mixed> $attributes The block attributes.
 * @var string               $content    The block default content.
 * @var WP_Block             $block      The block instance.
 */

use GHOSTLABS\FRIENDLY_SKIES\Kernel;
use GHOSTLABS\FRIENDLY_SKIES\Service\ThreadRenderer;

if ( ! defined( 'ABSPATH' ) ) exit;

$ghlbsfs_renderer = new ThreadRenderer( Kernel::getInstance()->getBlueskyService() );
echo $ghlbsfs_renderer->render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the renderer escapes every value as it builds the markup (RenderHelper), which carries SVG and inline custom properties wp_kses_post() would strip.
