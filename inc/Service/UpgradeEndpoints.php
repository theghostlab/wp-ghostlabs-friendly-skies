<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Enum\UpsellSource;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Every place this edition sends somebody, and nothing it talks to.
 *
 * All of them are links. This edition sends no host a request of any kind: the in-place upgrade
 * that used to verify a key and download Pro was refused by WordPress.org on 2026-10-08
 * (Guideline 8), and `NoInDashboardInstallTest` holds the ban. The old client is kept at the
 * tag `reference/in-place-upgrade-free-2026-10-08`.
 *
 * 🔴 **Every URL is built here, finished, and the editor only reads it.** No URL literal in
 * JavaScript and no fallback there: a hard-coded link renders, resolves and silently drops the
 * attribution. `AssetManager` inlines the editor's map from this class.
 *
 * ## ⚠️ Two origins on two axes
 *
 * - **Buy, docs and support** are public pages on the marketing site and WordPress.org. The
 *   release rewrite does not touch them.
 * - **Account** is the licence backend, where a buyer downloads Pro. Its base is the dev
 *   literal `https://enter.theghostlab.io`, rewritten by `build.sh --release-host` and refused in a
 *   release by `assert_release_transforms()`. Keep it one uninterrupted literal: a composed
 *   base is the helper the textual rewrite would miss, and a finished production URL is worse,
 *   because the rewrite never matches it and the assertion has nothing to find.
 *
 * @package GhostLabsFriendlySkies
 */
final class UpgradeEndpoints
{
    /** The backend's origin. See the class docblock before touching it. */
    private const BACKEND_BASE = 'https://enter.theghostlab.io';

    /** The customer account, which lists each purchase with its download. */
    private const ACCOUNT_PATH = '/account';

    /** The marketing site. Production, in every build: there is no rewrite axis for it. */
    private const MARKETING_BASE = 'https://theghostlab.io';

    /** The product page: the point of sale. One route for both editions (register § Listing, 2026-10-19). */
    private const PRODUCT_PATH = '/friendly-skies';

    /** A placeholder until the owner builds the page (2026-10-18). */
    private const DOCS_PATH = '/docs/friendly-skies';

    /**
     * A free plugin's support goes to its WordPress.org forum, which is staffed by volunteers
     * and does not cover commercial plugins. The slug is the one requested (register Open #2);
     * the forum exists once the listing does.
     */
    private const SUPPORT_URL = 'https://wordpress.org/support/plugin/ghostlabs-friendly-skies/';

    /**
     * The buy link for one surface: the product page, and which surface asked.
     *
     * 🔴 **`from=` and nothing else. No `upgrade_return`, no `domain`.** With them the licence
     * manager mints a claim at checkout and its receipt page redirects the buyer back to
     * wp-admin, away from the one page showing their key and the Download link. Nothing in this
     * edition reads a claim any more. Silent: every link still renders and resolves.
     */
    public static function upgradeUrl(UpsellSource $source): string
    {
        return self::MARKETING_BASE . self::PRODUCT_PATH . '?from=' . rawurlencode($source->value);
    }

    public static function docsUrl(): string
    {
        return self::MARKETING_BASE . self::DOCS_PATH;
    }

    public static function supportUrl(): string
    {
        return self::SUPPORT_URL;
    }

    /** Where a buyer downloads Friendly Skies Pro. */
    public static function accountUrl(): string
    {
        return self::BACKEND_BASE . self::ACCOUNT_PATH;
    }
}
