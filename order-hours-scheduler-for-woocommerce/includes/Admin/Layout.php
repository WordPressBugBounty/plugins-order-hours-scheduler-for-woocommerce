<?php

namespace Zhours\Admin;

defined( 'ABSPATH' ) || exit;

use Zhours\Admin;
use Zhours\Aspect\Page;
use Zhours\Plugin;
use Zhours\Settings;
use function Zhours\is_plugin_settings_page;

class Layout
{
	public function __construct()
	{
		add_action('in_admin_header', [$this, 'pageHeader']);
	}

	public function pageHeader()
	{
		if (!is_plugin_settings_page()) {
			return;
		} ?>
				<div class="zh-layout-wrapper">
						<div class="zh-layout">
								<div class="zh-base">
										<a href="http://bizswoop.com/wp/orderhours" target="_blank">
												<img
													class="zh-logo"
													src="<?= Plugin::getUrl('assets/logo.png'); ?>"
													alt="Store Hours"
												>
										</a>
										<div class="zh-title">
												<a href="http://bizswoop.com/wp/orderhours" target="_blank">
														<?php _e('Store Hours', 'order-hours-scheduler-for-woocommerce') ?>
												</a>
										</div>
										<div class="zh-slogan">
												<?php _e('Store Management', 'order-hours-scheduler-for-woocommerce') ?>
										</div>
								</div>
								<div class="zh-navigation">
										<ul>
												<li>
														<a
															href="<?= Admin::getUrl('schedule'); ?>"
															class="<?= self::isActiveClass('schedule'); ?>"
														>
																<div class="zh-icon">
																		<i class="fal fa-calendar-check"></i>
																</div>
																<?php _e('Schedule', 'order-hours-scheduler-for-woocommerce') ?>
														</a>
												</li>
												<li>
														<a
															href="<?= Admin::getUrl('add-ons'); ?>"
															class="<?= self::isActiveClass('add-ons'); ?>"
														>
																<div class="zh-icon">
																		<i class="far fa-cubes"></i>
																</div>
																<?php _e('Add-ons', 'order-hours-scheduler-for-woocommerce') ?>
														</a>
												</li>
												<li>
														<a href="http://bizswoop.com/" target="_blank">
																<div class="zh-icon">
																		<img
																			src="<?= Plugin::getUrl('assets/bizswoop.png'); ?>"
																			alt="BizSwoop">
																</div>
																BizSwoop
														</a>
												</li>
										</ul>
								</div>
						</div>
				</div>
				<?php
	}

	public static function isActiveClass($tab_name)
	{
			$page = Page::get('order hours');
			$tab = Settings::getTab($page, $tab_name);
			return $page->currentTab($tab) ? 'zh-active-link' : '';
	}
}
