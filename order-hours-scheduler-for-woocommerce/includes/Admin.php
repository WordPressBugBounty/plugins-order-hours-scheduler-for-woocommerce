<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

use Zhours\Aspect\Page;

class Admin
{
	public function __construct()
	{
			new Admin\Layout();
	}

		public static function getUrl( $tab_name ) {
			$page = Page::get('order hours');
			$tab = Settings::getTab($page, $tab_name);

			return $page->getUrl($tab);
		}
}
