<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Enum;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * How long Bluesky content is cached before it is fetched again.
 *
 * The only thing a free install has to configure. There is no account, no credentials, no
 * cron and no destination — but every block makes an HTTP request the first time it renders,
 * and how long that result is reused is a real trade between freshness and load that differs
 * per site.
 *
 * Backed by seconds so the value is the TTL, with no lookup table between the stored option
 * and what `Cache` is handed.
 *
 * @package GhostLabsFriendlySkies
 */
enum CacheDuration: int
{
    case FiveMinutes    = 300;
    case FifteenMinutes = 900;
    case ThirtyMinutes  = 1800;
    case OneHour        = 3600;
    case SixHours       = 21600;
    case TwelveHours    = 43200;
    case OneDay         = 86400;

    public function label(): string
    {
        return match ($this) {
            self::FiveMinutes    => __('5 minutes', 'ghostlabs-friendly-skies'),
            self::FifteenMinutes => __('15 minutes', 'ghostlabs-friendly-skies'),
            self::ThirtyMinutes  => __('30 minutes', 'ghostlabs-friendly-skies'),
            self::OneHour        => __('1 hour', 'ghostlabs-friendly-skies'),
            self::SixHours       => __('6 hours', 'ghostlabs-friendly-skies'),
            self::TwelveHours    => __('12 hours', 'ghostlabs-friendly-skies'),
            self::OneDay         => __('24 hours', 'ghostlabs-friendly-skies'),
        };
    }

    /**
     * Coerce a stored option into a case, or null if it is not one.
     *
     * Null is meaningful and is not an error: it means "no preference saved", and the plugin
     * then uses `Cache`'s own per-type defaults. A site that never opens this screen must
     * behave exactly as it did before the screen existed.
     */
    public static function tryFromOption(mixed $stored): ?self
    {
        if (is_int($stored)) {
            return self::tryFrom($stored);
        }

        // Options survive a round trip through the database, and a numeric string is what
        // comes back from a form post.
        if (is_string($stored) && ctype_digit($stored)) {
            return self::tryFrom((int) $stored);
        }

        return null;
    }

    /** @return list<self> */
    public static function options(): array
    {
        return self::cases();
    }
}
