<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Enum\CacheDuration;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Applies the saved cache duration to `Cache`'s per-type TTL filters.
 *
 * `Cache::getTtl()` already passes every default through
 * `friendly_skies_cache_ttl_{$type}`, so the setting is applied by filtering rather than by
 * changing shared code. Nothing in `shared/` knows this screen exists.
 *
 * @package GhostLabsFriendlySkies
 */
final class CachePolicy
{
    public const OPTION_KEY = 'cacheDuration';

    /**
     * Content types the setting governs.
     *
     * `handle` is deliberately excluded. It caches a handle-to-DID resolution, which is an
     * identity lookup that effectively never changes and is cached for a day; letting a site
     * drop it to five minutes would multiply requests for no fresher content. The four here
     * are the ones whose content a reader actually sees change.
     */
    private const GOVERNED_TYPES = ['profile', 'post', 'feed', 'thread'];

    public function __construct(
        private readonly OptionsService $options,
    ) {
    }

    /**
     * Register the filters, but only when a preference has been saved.
     *
     * An install that never opens the settings screen must behave exactly as it did before
     * the screen existed, so with no stored value this registers nothing at all and `Cache`'s
     * own defaults stand.
     */
    public function registerHooks(): void
    {
        $duration = $this->duration();

        if ($duration === null) {
            return;
        }

        $ttl = $duration->value;

        foreach (self::GOVERNED_TYPES as $type) {
            add_filter(
                "friendly_skies_cache_ttl_{$type}",
                static fn(): int => $ttl,
                // Late, so a site's own filter in a theme or mu-plugin still wins. This is a
                // convenience for people who will not write a filter; it should not overrule
                // people who did.
                5
            );
        }
    }

    public function duration(): ?CacheDuration
    {
        return CacheDuration::tryFromOption($this->options->get(self::OPTION_KEY));
    }

    public function save(CacheDuration $duration): bool
    {
        return $this->options->save(self::OPTION_KEY, $duration->value);
    }
}
