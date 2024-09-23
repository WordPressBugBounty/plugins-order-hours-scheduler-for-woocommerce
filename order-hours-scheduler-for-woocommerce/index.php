<?php
/**
 * Plugin Name: Store Order Hours Manager for WooCommerce
 * Plugin URI: https://www.bizswoop.com/store-order-hours/
 * Description: Create Custom Open & Close Store Schedules for Automatically Enabling & Disabling Customer Checkout Functionality for WooCommerce
 * Version: 4.3.20
 * Text Domain: order-hours-scheduler-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 2.4.0
 * WC tested up to: 8.5.2
 * Author: BizSwoop a CPF Concepts, LLC Brand
 * Author URI: http://www.bizswoop.com
 */

namespace Zhours;

defined( 'ABSPATH' ) || exit;

$plugin_data = get_file_data( __FILE__, array( 'version' => 'Version' ) );

const ACTIVE = true;
const PLUGIN_ROOT = __DIR__;
const PLUGIN_ROOT_FILE = __FILE__;
const ASPECT_PREFIX = 'zh';
define( 'ZH_VERSION', $plugin_data['version'] );
define( 'ZH_ROOT_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register(function ($name) {
	$name = explode('\\', $name);
	if($name[0] === __NAMESPACE__) {
		$name[0] = null;
	}
	$name = array_filter($name);

	$path = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $name) . '.php';
	if (file_exists($path)) {
		require_once $path;
	}
});

new Setup();

