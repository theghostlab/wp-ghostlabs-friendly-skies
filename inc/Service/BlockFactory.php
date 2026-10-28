<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockFactory
{
    /** Every block this plugin registers is namespaced under this prefix. */
    public const BLOCK_NAMESPACE = 'ghostlabs/friendly-skies-';

    private string $blocksDir;

    /**
     * @var array|string[]
     */
    private array $dirBlackList = ['..', '.', '.DS_Store', '_shared'];

    public function __construct()
    {
        $this->blocksDir = GHOSTLABS_FRIENDLY_SKIES_PLUGIN_PATH . 'build/blocks';
    }

    /**
     * @return array<callable>
     */
    public function setRegisterBlocks(): array
    {
        if( !is_dir($this->blocksDir) ) return [];

        $scanResult = scandir($this->blocksDir);
        if ($scanResult === false) return [];

        /** @var array<string> $filteredBlocks */
        $filteredBlocks = array_diff($scanResult, $this->dirBlackList);
        $blocks = array_values($filteredBlocks);

        if( empty($blocks) ) return [];

        $new_functions = [];

        $new_functions[] = function () {
            $this->registerGlobalStyle();
        };

        foreach ($blocks as $block)
        {
            $new_functions[] = function () use ($block) {
                $blockType = $this->blocksDir.'/'.$block;
                register_block_type( $blockType );
            };
        }

        return $new_functions;
    }

    /**
     * The editor script handles WordPress generated for this plugin's block types.
     *
     * Needed because `wp_localize_script()` on an unregistered handle is a silent no-op,
     * and blocks registered from `block.json` get one handle each, derived from the block
     * name — there is no single plugin-wide editor handle to attach data to. Attaching to
     * a guessed handle is how `ghostlabsFriendlySkies` came to be localized into nothing.
     *
     * Handles are derived with core's `generate_block_asset_handle()` rather than by
     * string-building, so the naming stays WordPress's business.
     *
     * Call after `init`, once block types are registered — `enqueue_block_editor_assets`
     * is safely after.
     *
     * @return list<string>
     */
    public function editorScriptHandles(): array
    {
        $handles = [];

        foreach (\WP_Block_Type_Registry::get_instance()->get_all_registered() as $blockType) {
            // The block type's own name, not the array key. PHP casts a numeric-looking key
            // to an int, so the key is `int|string` — and a block named "123" would arrive
            // here as an integer.
            $name = $blockType->name;

            // The empty check is not decoration: a block type with no name cannot be ours,
            // and generate_block_asset_handle() would return a meaningless handle for it.
            if ($name === '' || !str_starts_with($name, self::BLOCK_NAMESPACE)) {
                continue;
            }

            $handle = generate_block_asset_handle($name, 'editorScript');

            if ($handle !== '') {
                $handles[] = $handle;
            }
        }

        return $handles;
    }

    private function registerGlobalStyle(): void
    {
        $globalCss = $this->blocksDir . '/_shared/global.css';

        if ( ! file_exists( $globalCss ) ) {
            return;
        }

        wp_register_style(
            'ghostlabs-fs-global',
            GHOSTLABS_FRIENDLY_SKIES_PLUGIN_URL . 'build/blocks/_shared/global.css',
            [],
            (string) filemtime( $globalCss )
        );
    }
}
