<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Traits;

if ( ! defined( 'ABSPATH' ) ) exit;

trait Singleton
{
    private static ?self $instance = null;

    final public function __construct() {}
    final public function __clone() {}
    final public function __wakeup() {}

    final public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}