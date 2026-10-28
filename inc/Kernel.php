<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES;

use GHOSTLABS\FRIENDLY_SKIES\Config\PluginConfig;
use GHOSTLABS\FRIENDLY_SKIES\Feed\SharedFeed;
use GHOSTLABS\FRIENDLY_SKIES\Service\ApiClient;
use GHOSTLABS\FRIENDLY_SKIES\Service\BlockFactory;
use GHOSTLABS\FRIENDLY_SKIES\Service\BlueskyService;
use GHOSTLABS\FRIENDLY_SKIES\Service\Cache;
use GHOSTLABS\FRIENDLY_SKIES\Service\CachePolicy;
use GHOSTLABS\FRIENDLY_SKIES\Service\FeedPostRenderer;
use GHOSTLABS\FRIENDLY_SKIES\Service\FeedRenderer;
use GHOSTLABS\FRIENDLY_SKIES\Service\FeedUpsell;
use GHOSTLABS\FRIENDLY_SKIES\Service\Loader;
use GHOSTLABS\FRIENDLY_SKIES\Service\Logger;
use GHOSTLABS\FRIENDLY_SKIES\Service\OptionsService;
use GHOSTLABS\FRIENDLY_SKIES\Service\PluginMeta;
use GHOSTLABS\FRIENDLY_SKIES\Service\PluginRowMeta;
use GHOSTLABS\FRIENDLY_SKIES\Service\Settings;
use GHOSTLABS\FRIENDLY_SKIES\Traits\Singleton;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Composition root for the free edition.
 *
 * It wires the five blocks, the settings screen, the Plugins row and the Feed block's Upsell
 * panel, and nothing else. `defineRegisterBlocks()` is unconditional: **the five blocks are not
 * gated on anything.**
 *
 * Deliberately absent, and each absence is a shipped guarantee (see the free E2E
 * assertions): no update checker, no custom post type, no cron event, no admin notices.
 *
 * There is one settings screen, added after the cache duration gave the tier something worth
 * configuring. It is server-rendered and saved through `admin-post.php`, so the screen adds no
 * REST route.
 *
 * **No REST route is registered at all.** The two this edition used to register were the
 * in-place upgrade, which WordPress.org refused on 2026-10-08 (Guideline 8): a .org edition may
 * not install, activate or deactivate plugins. `NoInDashboardInstallTest` holds the ban, and the
 * free E2E suite asserts the tier's REST namespace does not exist.
 *
 * @package GhostLabsFriendlySkies
 */
class Kernel
{
	use Singleton;

	private PluginConfig $config;
	private Loader $loader;
	private PluginMeta $pluginMeta;
	private BlockFactory $blockFactory;
	private Logger $logger;
	private Cache $cache;
	private ApiClient $apiClient;
	private BlueskyService $blueskyService;
	private ?FeedRenderer $feedRenderer = null;
	private OptionsService $optionsService;
	private CachePolicy $cachePolicy;
	private Settings $settings;

	public function init(): Kernel
	{
		$this->config         = new PluginConfig();
		$this->optionsService = new OptionsService();
		$this->cachePolicy    = new CachePolicy($this->optionsService);
		$this->settings       = new Settings($this->config, $this->cachePolicy);
		$this->loader         = new Loader();
		$this->blockFactory   = new BlockFactory();
		$this->pluginMeta     = new PluginMeta();
		$this->logger         = new Logger();
		$this->cache          = new Cache();
		$this->apiClient      = new ApiClient($this->logger);
		$this->blueskyService = new BlueskyService($this->apiClient, $this->cache);

		$this
			->defineRegisterBlocks()
			->definePluginMeta()
			->defineSettingsPage()
			->defineFeedUpsell()
		;

		// Only registers anything once a duration has been saved, so an install that never
		// opens the screen keeps Cache's own per-type defaults.
		$this->cachePolicy->registerHooks();

		return $this;
	}

	/**
	 * Register the five block types. Unconditionally — see the class docblock.
	 */
	private function defineRegisterBlocks(): Kernel
	{
		$blocks = $this->blockFactory->setRegisterBlocks();

		if (empty($blocks)) return $this;

		foreach ($blocks as $block) {
			add_action('init', $block);
		}

		return $this;
	}

	/**
	 * The one admin surface the free edition has.
	 *
	 * Server-rendered and saved through admin-post.php, so the screen adds no REST route.
	 */
	private function defineSettingsPage(): Kernel
	{
		$this->loader->add_action('admin_menu', $this->settings, 'page');
		$this->loader->add_action('admin_enqueue_scripts', $this->settings, 'assets');
		$this->loader->add_action('admin_post_' . Settings::SAVE_ACTION, $this->settings, 'save');

		return $this;
	}

	/**
	 * The Feed block's Upsell panel.
	 */
	private function defineFeedUpsell(): Kernel
	{
		$this->loader->add_action('enqueue_block_editor_assets', new FeedUpsell(), 'enqueue');

		return $this;
	}

	private function definePluginMeta(): Kernel
	{
		$hook = sprintf('plugin_action_links_%s', GHOSTLABS_FRIENDLY_SKIES_PLUGIN_SLUG);
		$this->loader->add_filter($hook, $this->pluginMeta, 'actionSettings', 10, 4);

		// A global filter, unlike the one above: PluginRowMeta compares the plugin file before
		// it asks this edition for Docs and Support.
		$this->loader->add_filter('plugin_row_meta', new PluginRowMeta(), 'rowMeta', 10, 2);
		$this->loader->add_filter(PluginRowMeta::FILTER, $this->pluginMeta, 'rowMeta');

		return $this;
	}

	/**
	 * Every block's `render.php` calls this. The render files are shared and must stay
	 * byte-identical across editions, so this accessor is part of the contract between
	 * `shared/src/blocks/` and whichever kernel the build compiled.
	 */
	public function getBlueskyService(): BlueskyService
	{
		return $this->blueskyService;
	}

	/**
	 * The Feed block's `render.php` calls this, under the same contract. The free edition's Feed
	 * is `Feed\SharedFeed` and nothing more.
	 */
	public function getFeedRenderer(): FeedRenderer
	{
		return $this->feedRenderer ??= SharedFeed::renderer($this->blueskyService, new FeedPostRenderer());
	}

	/**
	 * No-op: the free edition has nothing to set up on activation. The hook is still
	 * registered in `shared/bootstrap.php` so both editions boot identically.
	 */
	public static function activate(): void
	{
	}

	/**
	 * No-op, for the same reason: nothing to unschedule and no sync lock to clear.
	 */
	public static function deactivate(): void
	{
	}

	public function run(): void
	{
		$this->loader->run();
	}
}
