<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class Setup {
	public function __construct() {
		new Translation();
	  add_action( 'before_woocommerce_init', [ $this, 'add_order_storage_support' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ], 1 );
		add_action( 'after_setup_theme', [ $this, 'themes_loaded' ], 1 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_order_storage_support() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PLUGIN_ROOT_FILE, true );
		}
	}

	public function init() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', function () { ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e( 'Order Hours Scheduler for WooCommerce require WooCommerce', 'order-hours-scheduler-for-woocommerce' ); ?></p>
                </div>
				<?php
			} );

			return;
		}

		require_once PLUGIN_ROOT . '/includes/setting.php';
		require_once PLUGIN_ROOT . '/functions.php';

		new Admin();
		new Frontend\Shop();
		new Template();
		new Ajax();

		if ( ! $this->is_ajax_notification() ) {
			add_action( 'wp_footer', [ get_called_class(), 'get_notification' ] );
		}

		add_filter( 'zh_notification_type_status', [ $this, 'apply_notification_status_by_management_mode' ], 999 );
		add_filter( 'zh_is_allowed_order_placing', [ $this, 'allow_order_placing_by_management_mode' ], 999 );

		if ( ! get_current_status() ) {
			add_action( 'wp', '\Zhours\init_checkout_actions' );
		}
		cache_cleaner();

		/** Break html5 cart caching */
		add_action( 'wp_enqueue_scripts', function () {
			wp_enqueue_script( 'wc-cart-fragments', plugin_dir_url( PLUGIN_ROOT_FILE ) . '/cart-fragments.js',
				array( 'jquery', 'jquery-cookie' ), ZH_VERSION, true );
		}, 100 );

		add_action( 'admin_enqueue_scripts', function () {
			if ( is_plugin_settings_page() ) {
				wp_enqueue_script( 'zh-multidatespicker',
					plugin_dir_url( PLUGIN_ROOT_FILE ) . '/multidatespicker/jquery-ui.multidatespicker.js',
					[ 'jquery-ui-datepicker' ] );

				wp_enqueue_style( 'zh-multidatepicker.css',
					plugin_dir_url( PLUGIN_ROOT_FILE ) . '/multidatespicker/multidatespicker.css' );
				wp_enqueue_style( 'zh-fa', plugin_dir_url( PLUGIN_ROOT_FILE ) . '/assets/fa/app.css', null,
					ZH_VERSION );
			}
		} );
	}

	public static function get_notification() {
		if ( ! \Zhours\get_current_status() ) {
			if ( ! \Zhours\Addons::is_active_add_on( \Zhours\Addons::ORDER_HOURS_WIDGET_NAMESPACE ) ) {
				new \Zhours\Notification\Box();
			}
			do_action( 'zh_init_notification' );
		}
	}

	public function themes_loaded() {
		if ( function_exists( __NAMESPACE__ . '\get_current_status' ) && ! get_current_status() && is_hide_add_to_cart() ) {
			remove_action( 'woocommerce_single_product_lightbox_summary', 'woocommerce_template_single_add_to_cart',
				30 );
		}
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'zh_frontend', plugins_url( 'assets/bundles/scripts/frontend.js', PLUGIN_ROOT_FILE ),
			[ 'jquery' ], ZH_VERSION, true );

		wp_localize_script( 'zh_frontend', 'zhFrontend', array(
			'ajaxUrl'                 => admin_url( 'admin-ajax.php' ),
			'enableNotification'      => apply_filters( 'zh_enable_notification', true ),
			'isAjaxNotification'      => $this->is_ajax_notification(), // TODO: we can remove this
			'notificationTriggerType' => Settings::getValue( 'notification', 'type', 'trigger type' ),
			'notificationTriggerTime' => $this->get_notification_trigger_time(),
			'formattedSiteUrl'        => $this->get_formatted_site_url(), // TODO: also we can remove this
		) );

		wp_enqueue_style( 'zh_frontend', plugins_url( 'assets/bundles/styles/frontend.css', PLUGIN_ROOT_FILE ), null, ZH_VERSION );
	}

	private function get_notification_trigger_time() {
		$time = Settings::getValue( 'notification', 'type', 'trigger time' );
		$min  = isset( $time['min'] ) ? (int) $time['min'] : 0;
		$sec  = isset( $time['sec'] ) ? (int) $time['sec'] : 0;

		return $min * 60 + $sec;
	}

	public function apply_notification_status_by_management_mode( $status ) {
		if ( $this->is_management_mode_enabled( 'notification' ) ) {
			$status = false;
		}

		return $status;
	}

	public function allow_order_placing_by_management_mode( $status ) {
		if ( $this->is_management_mode_enabled( 'actions' ) ) {
			$status = true;
		}

		return $status;
	}

	private function is_management_mode_enabled( $type ) {
		$is_management_mode_enabled = Settings::getValue( 'general settings', $type, "${type} management mode" );
		$roles                      = Settings::getValue( 'general settings', $type, "${type} management roles" );
		$user_role                  = wp_get_current_user()->roles[0] ?? null;

		return $is_management_mode_enabled && in_array( $user_role, $roles );
	}

	private function is_ajax_notification() {
		return Settings::getValue( 'schedule', 'cache management', 'notifications' ) === 'ajax';
	}

	private function get_formatted_site_url() {
		$url   = get_site_url();
		$host  = parse_url( $url, PHP_URL_HOST );
		$names = explode( ".", $host );

		if ( count( $names ) == 1 ) {
			return $names[0];
		}

		$names = array_reverse( $names );

		return $names[1] . '.' . $names[0];
	}
}
