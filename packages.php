<?php
/**
 * Friendly Skies — the packages this edition ships
 *
 * Required by bootstrap.php, before the Composer autoloader. Each tier ships its own copy of this
 * file, so which packages load is decided by what the build carries — the bootstrap never asks.
 *
 * This edition ships none. 🔴 **No GhostLabs package in this build**: WordPress.org pended the 1.0.0
 * submission on 2026-10-28 for one, a package its reviewers cannot resolve.
 * `FreeBuildShipsNoGhostLabsPackageTest` holds this file empty of loads.
 *
 * @package GhostLabsFriendlySkies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
