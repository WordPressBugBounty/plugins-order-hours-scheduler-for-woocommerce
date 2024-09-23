<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class Translation
{
		public function __construct()
		{
				load_plugin_textdomain(
					'order-hours-scheduler-for-woocommerce',
					false,
					dirname(plugin_basename(PLUGIN_ROOT_FILE)) . '/languages/'
				);
		}
}
