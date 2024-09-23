<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

use Zhours\Aspect\Box;
use Zhours\Aspect\InstanceStorage;
use Zhours\Aspect\Page;
use Zhours\Aspect\TabPage;

class Settings
{
		public static function getTab( $page, $tab_name ) {
				return InstanceStorage::getGlobalStorage()->asCurrentStorage(function () use ($tab_name, $page) {
						return $page->scope(function () use ($tab_name) {
								return TabPage::get($tab_name);
						});
				});
		}

		public static function getValue($tab, $box_name, $input_name) {
				return InstanceStorage::getGLobalStorage()->asCurrentStorage(function () use ($input_name, $box_name, $tab) {
						return Page::get('order hours')->scope(function () use ($input_name, $box_name, $tab) {
								return TabPage::get($tab)->scope(function (TabPage $widget) use ($input_name, $box_name, $tab) {
										$box = Box::get($box_name);
										$input = Input::get($input_name);
										return $input->getValue($box, null, $widget);
								});
						});
				});
		}
}
