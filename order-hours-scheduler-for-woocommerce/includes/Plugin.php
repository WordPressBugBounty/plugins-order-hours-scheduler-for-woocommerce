<?php
namespace Zhours;

defined( 'ABSPATH' ) || exit;

class Plugin
{
		public static function getUrl($path, $raw = false)
		{
				$plugin_data = get_plugin_data(PLUGIN_ROOT_FILE);
				$url = plugins_url($path, PLUGIN_ROOT_FILE);

				if ($raw) {
						return $url;
				}

				return add_query_arg('version', $plugin_data['Version'], $url);
		}
}

