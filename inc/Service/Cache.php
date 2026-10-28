<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cache
{
    private const PREFIX = 'ghlbsfs_';

    private const TTL_MAP = [
        'profile' => 3600,
        'handle'  => 86400,
        'post'    => 900,
        'feed'    => 300,
        'thread'  => 900,
    ];

    private const DEFAULT_TTL = 900;

    private const CACHE_GROUP = 'ghostlabs-friendly-skies';

    /**
     * How long the last good copy of a response is kept, for `BlueskyService` to serve while
     * Bluesky can't be reached (owner, 2026-10-19; register § Product behaviour). A transient
     * with an expiry, so WordPress never autoloads it.
     */
    public const LAST_GOOD_TTL = 7 * 86400;

    /** How long requests stop after an outage, so pages don't each wait out the retries. */
    public const PAUSE_TTL = 120;

    private const LAST_GOOD = 'lastgood_';
    private const PAUSED = 'paused';

    public function get(string $key): mixed
    {
        $prefixed = self::PREFIX . $key;

        $cached = wp_cache_get($prefixed, self::CACHE_GROUP);
        if ($cached !== false) {
            return $cached;
        }

        $value = get_transient($prefixed);
        if ($value !== false) {
            wp_cache_set($prefixed, $value, self::CACHE_GROUP);
        }

        return $value;
    }

    public function set(string $key, mixed $value, string $type): bool
    {
        $prefixed = self::PREFIX . $key;
        wp_cache_set($prefixed, $value, self::CACHE_GROUP);
        set_transient(self::PREFIX . self::LAST_GOOD . $key, $value, self::LAST_GOOD_TTL);

        return set_transient($prefixed, $value, $this->getTtl($type));
    }

    /** The last value `set()` stored under this key, up to a week old, or false. */
    public function lastGood(string $key): mixed
    {
        return get_transient(self::PREFIX . self::LAST_GOOD . $key);
    }

    /** Pause every request, or with a key only that one. */
    public function pauseRequests(?string $key = null): void
    {
        set_transient(self::PREFIX . self::PAUSED . ($key !== null ? '_' . $key : ''), 1, self::PAUSE_TTL);
    }

    /** Whether this key's request is paused, by its own pause or a site-wide one. */
    public function requestsPaused(string $key): bool
    {
        return get_transient(self::PREFIX . self::PAUSED) !== false
            || get_transient(self::PREFIX . self::PAUSED . '_' . $key) !== false;
    }

    public function delete(string $key): bool
    {
        $prefixed = self::PREFIX . $key;
        wp_cache_delete($prefixed, self::CACHE_GROUP);
        delete_transient(self::PREFIX . self::LAST_GOOD . $key);

        return delete_transient($prefixed);
    }

    public function getTtl(string $type): int
    {
        $default = self::TTL_MAP[$type] ?? self::DEFAULT_TTL;

        /** @var int */
        return apply_filters("friendly_skies_cache_ttl_{$type}", $default);
    }
}
