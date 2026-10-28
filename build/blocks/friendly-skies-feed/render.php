<?php
/**
 * Server-side rendering for the ghostlabs/friendly-skies-feed block.
 *
 * @var array<string, mixed> $attributes The block attributes.
 * @var string               $content    The block default content.
 * @var WP_Block             $block      The block instance.
 */

use GHOSTLABS\FRIENDLY_SKIES\Kernel;

if ( ! defined( 'ABSPATH' ) ) exit;

// Each edition's Kernel composes the Feed it ships.
echo Kernel::getInstance()->getFeedRenderer()->render( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the renderer escapes every value as it builds the markup (RenderHelper), which carries SVG and inline custom properties wp_kses_post() would strip.
