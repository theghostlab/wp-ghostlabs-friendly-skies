<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class Logger
{
    private bool $enabled;

    /** @var callable(string): void */
    private $writer;

    /** @param ?bool $enabled Defaults to WP_DEBUG_LOG constant */
    public function __construct(?bool $enabled = null, ?callable $writer = null)
    {
        $this->enabled = $enabled ?? (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG);
        $this->writer = $writer ?? 'error_log';
    }

    public function log(string $message): void
    {
        if (!$this->enabled) {
            return;
        }

        ($this->writer)('[Friendly Skies] ' . $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR: ' . $message);
    }
}
