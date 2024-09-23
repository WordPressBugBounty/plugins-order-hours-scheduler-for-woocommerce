<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class Template {

		public function __construct()
		{
				add_filter( 'woocommerce_locate_template', [ $this, 'update_template_location' ], 10, 3 );
		}

		public function update_template_location($template, $template_name, $template_path) {
				global $woocommerce;
				$_template = $template;
				if ( ! $template_path )
						$template_path = $woocommerce->template_url;

				$plugin_path  = untrailingslashit( PLUGIN_ROOT )  . '/template/woocommerce/';

				$template = locate_template(
					[
						$template_path . $template_name,
						$template_name
					]
				);

				if( ! $template && file_exists( $plugin_path . $template_name ) )
						$template = $plugin_path . $template_name;

				if ( ! $template )
						$template = $_template;

				return $template;
		}
}
