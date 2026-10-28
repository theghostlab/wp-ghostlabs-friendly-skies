<?php

namespace GHOSTLABS\FRIENDLY_SKIES\Service;

use GHOSTLABS\FRIENDLY_SKIES\Config\PluginConfig;
use GhostLabs\WPKit\Contracts\MutableOptionsServiceInterface;

if ( ! defined( 'ABSPATH' ) ) exit;

class OptionsService implements MutableOptionsServiceInterface
{
	private static function optionKey(): string {
		return PluginConfig::$domain . '_' . PluginConfig::$slug . '_options';
	}

	public function delete(string $key): ?bool {
		$options = get_option(self::optionKey(), []);

		if ( !is_array($options) || !array_key_exists($key, $options) ) {
			return null;
		}

		unset($options[$key]);

		return update_option(self::optionKey(), $options);
	}

	public function get(string $key): mixed {
		$options = get_option(self::optionKey(), []);

		if ( !is_array($options) ) {
			return null;
		}

		return $options[$key] ?? null;
	}

	public function save(string $key, mixed $data): bool
	{
		$options = get_option(self::optionKey(), []);

		if ( !is_array($options) ) {
			$options = [];
		}

		$options[$key] = $data;

		return update_option(self::optionKey(), $options);
	}
}
