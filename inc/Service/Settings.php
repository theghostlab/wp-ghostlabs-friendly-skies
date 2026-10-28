<?php

declare(strict_types=1);

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Config\PluginConfig;
use GHOSTLABS\FRIENDLY_SKIES\Enum\CacheDuration;
use GHOSTLABS\FRIENDLY_SKIES\Enum\UpsellSource;
use GHOSTLABS\FRIENDLY_SKIES\Render\ComparisonTable;
use GHOSTLABS\FRIENDLY_SKIES\Render\HeroBand;
use GHOSTLABS\FRIENDLY_SKIES\Render\UpgradePage;
use GHOSTLABS\FRIENDLY_SKIES\Render\UpgradePanel;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The free edition's settings screen.
 *
 * A flattened copy of the shared settings UI's design, not a use of it. That package is
 * published to a private registry, and a tier distributed through WordPress.org may not carry
 * a private dependency — so the markup and class names are reproduced here and the stylesheet
 * is hand-authored beside them.
 *
 * Rendered in PHP and saved through `admin-post.php`, which is why this class needs no React,
 * no bundler and no script. Saving a setting adds no REST route.
 *
 * The Pro tab sells, and explains how a buyer switches. It does nothing itself: WordPress.org
 * refused the in-place upgrade it used to carry (2026-10-08, Guideline 8), so the buyer
 * downloads Pro from their account and uploads it through core.
 *
 * @package GhostLabsFriendlySkies
 */
final class Settings
{
    public const SAVE_ACTION = 'ghlbsfs_save_settings';
    public const NONCE       = 'ghlbsfs_settings';

    private const TAB_GENERAL = 'general';
    private const TAB_PRO     = 'pro';

    public function __construct(
        private readonly PluginConfig $config,
        private readonly CachePolicy $cachePolicy,
    ) {
    }

    public function page(): void
    {
        add_submenu_page(
            'options-general.php',
            __('Friendly Skies', 'ghostlabs-friendly-skies'),
            __('Friendly Skies', 'ghostlabs-friendly-skies'),
            'manage_options',
            $this->config->getSettingsMenuSlug(),
            [$this, 'render'],
        );
    }

    public function assets(string $hook): void
    {
        // Only on this screen. A settings stylesheet loaded across wp-admin is how a plugin
        // breaks somebody else's page.
        if ($hook !== 'settings_page_' . $this->config->getSettingsMenuSlug()) {
            return;
        }

        wp_enqueue_style(
            'ghostlabs-friendly-skies-settings',
            GHOSTLABS_FRIENDLY_SKIES_PLUGIN_URL . 'assets/css/settings.css',
            [],
            $this->config->getVersion()
        );
    }

    /**
     * Which tab is being rendered, defaulting to General and rejecting anything unknown.
     */
    private function currentTab(): string
    {
        // Which tab to draw, not a form submission: nothing is changed by it, so there is no nonce.
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash((string) $_GET['tab'])) : self::TAB_GENERAL; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        return in_array($tab, [self::TAB_GENERAL, self::TAB_PRO], true) ? $tab : self::TAB_GENERAL;
    }

    /**
     * Handle the form post.
     *
     * Capability first, then nonce, then validate. An invalid duration is discarded rather
     * than stored: the option is read back through a backed enum, so a value that is not a
     * case would simply be ignored later — better to never write it.
     */
    public function save(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to change these settings.', 'ghostlabs-friendly-skies'), '', ['response' => 403]);
        }

        check_admin_referer(self::NONCE);

        $submitted = isset($_POST['cache_duration']) ? sanitize_text_field(wp_unslash((string) $_POST['cache_duration'])) : '';
        $duration  = CacheDuration::tryFromOption($submitted);

        if ($duration !== null) {
            $this->cachePolicy->save($duration);
        }

        wp_safe_redirect(
            add_query_arg(
                ['page' => $this->config->getSettingsMenuSlug(), 'updated' => $duration !== null ? '1' : '0'],
                admin_url('options-general.php')
            )
        );
        exit;
    }

    public function render(): void
    {
        $slug    = $this->config->getSettingsMenuSlug();
        $tab     = $this->currentTab();
        // The redirect flag after a save, which save() checked the nonce for. Display only.
        $saved   = isset($_GET['updated']) && $_GET['updated'] === '1'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $current = $this->cachePolicy->duration();

        // `important: '#ghostlabs'` in the design system's Tailwind config scopes every rule
        // to this id, so the stylesheet cannot leak into the rest of wp-admin.
        echo '<div id="ghostlabs" class="ghostlabs-fs-settings">';

        $this->renderHeader($slug, $tab);

        // The Pro tab is F3's 75rem column; General keeps the settings UI's narrower one.
        printf(
            '<div class="ghlbsfs-panel-container%s">',
            $tab === self::TAB_PRO ? ' ghlbsfs-panel-container--upgrade' : ''
        );

        if ($saved) {
            $this->renderNotice(__('Settings saved.', 'ghostlabs-friendly-skies'), 'success');
        }

        if ($tab === self::TAB_PRO) {
            $this->renderProPanel();
        } else {
            $this->renderGeneralPanel($current);
        }

        echo '</div></div>';
    }

    // --- rendering ----------------------------------------------------------------
    //
    // Each method below mirrors one component of the shared settings UI — HeaderContainer,
    // Navigation, PanelHeader/PanelTitle, Panel, FormField/FieldTitle/FieldContent, Notice —
    // keeping its markup and class names so the two editions look like one product.

    private function renderHeader(string $slug, string $activeTab): void
    {
        // F3's header band, on both tabs: the plugin's name and which edition this is.
        printf(
            '<div class="ghlbsfs-header"><div class="ghlbsfs-header__inner"><h1 class="ghlbsfs-header__title">%s</h1>'
            . '<span class="ghlbsfs-badge">%s</span></div></div>',
            esc_html__('Friendly Skies', 'ghostlabs-friendly-skies'),
            esc_html__('Free', 'ghostlabs-friendly-skies')
        );

        echo '<div class="ghlbsfs-nav"><div class="ghlbsfs-nav__inner">';

        foreach (
            [
                self::TAB_GENERAL => __('General', 'ghostlabs-friendly-skies'),
                self::TAB_PRO     => __('Pro', 'ghostlabs-friendly-skies'),
            ] as $key => $label
        ) {
            printf(
                '<a class="ghlbsfs-nav__item%s" href="%s"%s>%s</a>',
                $key === $activeTab ? ' is-active' : '',
                esc_url(add_query_arg(['page' => $slug, 'tab' => $key], admin_url('options-general.php'))),
                $key === $activeTab ? ' aria-current="page"' : '',
                esc_html($label)
            );
        }

        echo '</div></div>';
    }

    private function renderGeneralPanel(?CacheDuration $current): void
    {
        printf(
            '<header class="ghlbsfs-panel-header"><h2 class="ghlbsfs-panel-title">%s</h2></header>',
            esc_html__('General', 'ghostlabs-friendly-skies')
        );

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        printf('<input type="hidden" name="action" value="%s" />', esc_attr(self::SAVE_ACTION));
        wp_nonce_field(self::NONCE);

        echo '<div class="ghlbsfs-panel"><div class="ghlbsfs-field">';
        printf(
            '<div class="ghlbsfs-field__title"><label for="ghlbsfs-cache-duration">%s</label></div>',
            esc_html__('Cache duration', 'ghostlabs-friendly-skies')
        );

        echo '<div class="ghlbsfs-field__content">';
        echo '<select id="ghlbsfs-cache-duration" name="cache_duration" class="ghlbsfs-input">';

        // ⚠️ Unset, no case is `selected`, so the browser shows the first and Save would store
        // five minutes without anyone choosing it. An empty value is discarded by `save()`.
        if ($current === null) {
            printf(
                '<option value="" selected>%s</option>',
                esc_html__('Default for each block', 'ghostlabs-friendly-skies')
            );
        }

        foreach (CacheDuration::options() as $duration) {
            printf(
                '<option value="%d"%s>%s</option>',
                absint($duration->value),
                selected($current?->value, $duration->value, false),
                esc_html($duration->label())
            );
        }

        echo '</select>';
        printf(
            '<p class="ghlbsfs-field__help">%s</p>',
            esc_html__(
                'How long a response from Bluesky is reused before it is fetched again. Longer means fewer requests and a faster page; shorter means posts appear sooner.',
                'ghostlabs-friendly-skies'
            )
        );

        if ($current === null) {
            printf(
                '<p class="ghlbsfs-field__help">%s</p>',
                esc_html__('Not set — each block type uses its own default.', 'ghostlabs-friendly-skies')
            );
        }

        echo '</div></div></div>';

        printf(
            '<div class="ghlbsfs-actions"><button type="submit" class="button button-primary button-large">%s</button></div>',
            esc_html__('Save Changes', 'ghostlabs-friendly-skies')
        );

        echo '</form>';
    }

    /**
     * The Pro tab: the Upsell Page (F3), composed from `Render\*`.
     *
     * It sells, and it explains how a buyer switches. It does nothing itself — see
     * `Render\UpgradePanel` for why.
     */
    private function renderProPanel(): void
    {
        $uploader = null;

        if (current_user_can('upload_plugins')) {
            // On a network only a super admin uploads, and core redirects a subsite's
            // plugin-install.php to the network screen without its `tab`.
            $uploader = is_multisite()
                ? network_admin_url('plugin-install.php?tab=upload')
                : admin_url('plugin-install.php?tab=upload');
        }

        (new UpgradePage(
            new UpgradePanel(UpgradeEndpoints::accountUrl(), $uploader),
            new HeroBand(UpgradeEndpoints::upgradeUrl(UpsellSource::Settings)),
            new ComparisonTable(),
            UpgradeEndpoints::upgradeUrl(UpsellSource::SettingsTable),
        ))->render();
    }

    private function renderNotice(string $message, string $type): void
    {
        printf(
            '<div class="ghlbsfs-notice ghlbsfs-notice--%s" role="status">%s</div>',
            esc_attr($type),
            esc_html($message)
        );
    }
}
