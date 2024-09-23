<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

use \Zhours\Aspect\Addon, \Zhours\Aspect\Page, \Zhours\Aspect\TabPage, \Zhours\Aspect\Box;

require_once __DIR__ . '/../functions.php';

if ( ! function_exists( 'get_editable_roles' ) ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
}

$setting = new Page( 'order hours' );

do_action( 'get_setting_page', $setting );

add_action( 'init', function () {
	$roles = [ 'shop_manager', 'administrator' ];
	array_walk( $roles, function ( $role_name ) {
		$role = get_role( $role_name );
		$role->add_cap( 'zhours_manage_options', true );
	} );
} );

$setting
	->setArgument( 'capability', 'zhours_manage_options' )
	->setArgument( 'parent_slug', 'woocommerce' );

$setting->scope( function ( Page $setting ) {
	if ( $setting->isRequested() ) {
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_style( 'zhours-style', plugins_url( 'assets/bundles/styles/setting.css', PLUGIN_ROOT_FILE ), [], ZH_VERSION );
			wp_enqueue_script( 'zhours-script', plugins_url( 'assets/bundles/scripts/setting.js', PLUGIN_ROOT_FILE ), [ 'jquery', 'wp-i18n' ], ZH_VERSION );
		} );
	}

	$schedule = new TabPage( 'schedule' );
	$schedule
		->setArgument( 'capability', 'zhours_manage_options' )
		->setLabel( 'singular_name', __( 'Schedule', 'order-hours-scheduler-for-woocommerce' ) );

	$notification = new TabPage( 'notification' );
	$notification
		->setArgument( 'capability', 'zhours_manage_options' )
		->setLabel( 'singular_name', __( 'Notification', 'order-hours-scheduler-for-woocommerce' ) );


	$alertbutton = new TabPage( 'actions' );
	$alertbutton
		->setArgument( 'capability', 'zhours_manage_options' )
		->setLabel( 'singular_name', __( 'Actions', 'order-hours-scheduler-for-woocommerce' ) );

	$general_settings = new TabPage( 'general settings' );
	$general_settings
		->setArgument( 'capability', 'zhours_manage_options' )
		->setLabel( 'singular_name', __( 'Settings', 'order-hours-scheduler-for-woocommerce' ) );

	$add_on = new TabPage( 'add-ons' );
	$add_on
		->setArgument( 'capability', 'zhours_manage_options' )
		->setLabel( 'singular_name', __( 'Premium Add-ons', 'order-hours-scheduler-for-woocommerce' ) );

	$setting->attach( $schedule, $notification, $alertbutton, $general_settings, $add_on );

	$schedule->scope( function ( TabPage $schedule ) {
		$status = new Box( 'status' );
		$status->attachTo( $schedule );

		$enable = new Input( 'order hours status' );

		$force_status = new Box( 'force status' );
		$force_status
			->setLabel( 'singular_name',
				__( 'Force Override Store Schedule', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $schedule )
			->scope( function ( $box ) {
				$rewrite = new Input( 'force rewrite status' );
				$rewrite
					->setLabel( 'singular_name',
						__( 'Turn-on Force Override', 'order-hours-scheduler-for-woocommerce' ) )
					->setArgument( 'default', false )
					->attachTo( $box )
					->attach( [ true, '' ] )
					->setType( Input::TYPE_CHECKBOX );

				$status = new Input( 'force status' );
				$status
					->setLabel( 'singular_name', __( 'Ordering Status', 'order-hours-scheduler-for-woocommerce' ) )
					->setArgument( 'default', '' )
					->attachTo( $box )
					->setType( Input::TYPE_SWITCHER )
					->attach( [ true, '' ] );

			} );

		$days_schedule = new Box( 'days schedule' );
		$days_schedule->attachTo( $schedule );

		$period = new Input( 'period' );
		$period
			->attachTo( $days_schedule )
			->setType( Input::TYPE_DAYS_PERIODS );

		$holidays_schedule = new Box( 'holidays schedule' );
		$holidays_schedule->attachTo( $schedule );

		$holidays_calendar = new Input( 'holidays calendar' );
		$holidays_calendar
			->attachTo( $holidays_schedule )
			->setType( Input::TYPE_HOLIDAYS_SCHEDULE );

		$cache = new Box( 'cache management' );
		$cache->attachTo( $schedule );

		$notifications = new Input( 'notifications' );
		$notifications
			->setArgument( 'default', 'ajax' )
			->setLabel( 'singular_name', __( 'Notifications', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_RADIO )
			->setArgument( 'on_new_line', true )
			->attachTo( $cache )
			->attachFew( array(
				array( 'ajax', __( 'AJAX', 'order-hours-scheduler-for-woocommerce' ), __( '(Recommended for use with sites using cache plugins or services)', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'html', __( 'HTML', 'order-hours-scheduler-for-woocommerce' ), __( '(Recommended if AJAX creates Theme conflicts)', 'order-hours-scheduler-for-woocommerce' ) ),
			) );

		$enable_cache_clearing = new Input( 'enable cache clearing' );
		$enable_cache_clearing
			->setArgument( 'default', false )
			->setLabel( 'singular_name', __( 'Enable cache clearing', 'order-hours-scheduler-for-woocommerce' ) )
			->setLabelText( __( 'Website cache will be cleared for each scheduled store open and close event',
				'order-hours-scheduler-for-woocommerce' ) )
			->setDescription( __( 'Important: Clearing the website cache may impact website loading speed and performance. Only locally stored cache is cleared, server side cache services are not cleared.',
				'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $cache )
			->attach( [ true, '' ] )
			->setType( Input::TYPE_CHECKBOX );

		$enable
			->setLabel( 'singular_name', __( 'Store Hours Manager', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'default', true )
			->attachTo( $status )
			->setType( Input::TYPE_SWITCHER )
			->attach( [ true, '' ] );

		$status_label = new Input( 'status label' );
		$status_label
			->setLabel( 'singular_name', __( 'Store Status', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'default', '' )
			->attach( [ true, '' ] )
			->attachTo( $status )
			->setType( Input::TYPE_STATUS );
	} );

	$notification->scope( function ( TabPage $alertbar ) {
		$type_box = new Box( 'type' );
		$type_box
			->attachTo( $alertbar )
			->setLabel( 'singular_name',
				__( 'Options for Sitewide Notice', 'order-hours-scheduler-for-woocommerce' ) );

		$notification_type_status = new Input( 'notification type status' );
		$notification_type_status
			->setLabel( 'singular_name', __( 'Notification Type Status', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_SWITCHER )
			->setArgument( 'default', '' )
			->attach( [ true, '' ] )
			->setArgument( 'onDisabledValue', 'true' )
			->setArgument( 'is_required_addon_active', Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) )
			->setArgument( 'upgrade_text', __( 'Upgrade to <a href="%s">Store Hours Manager Plus add-on</a> for advanced features', 'order-hours-scheduler-for-woocommerce' ) );

		$type = new Input( 'notification type' );
		$type
			->setArgument( 'default', 'box' )
			->setClass( 'zh-notification-type' )
			->setLabel( 'singular_name', __( 'Select Notification Type', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_RADIO_PLUS )
			->attachFew( array(
				array( 'box', __( 'Box', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'bar', __( 'Bar', 'order-hours-scheduler-for-woocommerce' ) ),
			) );

		$trigger_type = new Input( 'trigger type' );
		$trigger_type
			->setArgument( 'default', 'site' )
			->setLabel( 'singular_name', __( 'Trigger Notification Time by', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_RADIO )
			->attachFew( array(
				array( 'site', __( 'Site visit', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'page', __( 'Page visit', 'order-hours-scheduler-for-woocommerce' ) )
			) );

		$trigger_time = new InputMinAndSec( 'trigger time' );
		$trigger_time
			->setType( InputMinAndSec::TYPE_MINUTES_AND_SECONDS )
			->setLabel( 'singular_name', __( 'Trigger Notification Time', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'default', array(
				'min' => 0,
				'sec' => 0
			) )
			->setDescription( __( 'Note: Set 0 to minutes and seconds for the notification to show immediately on load.', 'order-hours-scheduler-for-woocommerce' ) );

		$display_on = new Input( 'display on' );
		$display_on
			->setArgument( 'default', array(
				'all'         => 'yes',
				'page_all'    => true,
				'post_all'    => true,
				'product_all' => true,
			) )
			->setLabel( 'singular_name', __( 'Display on', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_DISPLAY_ON );

		$type_box->attach( $notification_type_status, $type, $trigger_type, $trigger_time, $display_on );

		$box_quick_links = new Box( 'quick links' );
		$box_quick_links
			->attachTo( $alertbar )
			->setLabel( 'singular_name', '' );

		$quick_links_status = new Input( 'quick links status' );
		$quick_links_status
			->setLabel( 'singular_name', __( 'Notification Quick Links', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'default', '' )
			->attachTo( $box_quick_links )
			->setType( Input::TYPE_SWITCHER )
			->attach( [ true, '' ] );

		$quick_links_labels = new InputMultiText( 'quick links labels' );
		$quick_links_labels
			->setLabel( 'singular_name', __( 'Quick Links Labels', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $box_quick_links )
			->setType( InputMultiText::TYPE_MULTI_TEXT )
			->attachFew( array(
				array(
					'name' => 'open',
					'placeholder' => __( 'Open button label', 'order-hours-scheduler-for-woocommerce' ),
					'description' => __( 'Custom text for quick links button, leave blank for icon only', 'order-hours-scheduler-for-woocommerce' )
				),
				array(
					'name' => 'close',
					'placeholder' => __( 'Close button label', 'order-hours-scheduler-for-woocommerce' ),
					'description' => __( 'Custom text for quick links close button, leave blank for icon only', 'order-hours-scheduler-for-woocommerce' )
				),
			) );

		$quick_links_list = new InputLinksList( 'quick links list' );
		$quick_links_list
			->setLabel( 'singular_name', __( 'Quick Links', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $box_quick_links )
			->setType( InputLinksList::TYPE_LINKS_LIST )
			->setArgument( 'add_button_label', __( 'Add Quick Link', 'order-hours-scheduler-for-woocommerce' ) );

		$box_options_box = new Box( 'options' );
		$box_options_box
			->attachTo( $alertbar )
			->setLabel( 'singular_name', '' );

		$hide_box = new Input( 'hide alert bar' ); // saved old name for backward compatibility
		$hide_box
			->setLabel( 'singular_name', __( 'Hide Alert Box', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_CHECKBOX_EDIT_ONE_ROW )
			->setArgument( 'default', false )
			->attach( [ true, '' ] )
			->setLabelText( __( 'Allow Customer to Hide Alert Box', 'order-hours-scheduler-for-woocommerce' ) )
			->setClass( 'one-row-input' )
			->setDescription( __( 'Custom text for hide button, leave blank for icon only', 'order-hours-scheduler-for-woocommerce' ) );

		$hide_duration = new InputMinAndSec( 'hide duration' );
		$hide_duration
			->setType( InputMinAndSec::TYPE_MINUTES_AND_SECONDS )
			->setLabel( 'singular_name', __( 'Dismiss Duration', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'is_required_addon_active', Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) )
			->setArgument( 'placeholder', array( 0, 10 ) )
			->setArgument( 'sec_as_select', ! Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) )
			->setArgument( 'sec_select_options', array( 1, 5, 10, 15, 30, 45, 60, 90, 120 ) )
			->setArgument( 'sec_select_default', 30 )
			->setDescription( __( 'Note: Set 0 to minutes and seconds for the alert to not show again after being dismissed until the page reloads.', 'order-hours-scheduler-for-woocommerce' ) );

		$message = new Input( 'message' );
		$message->setLabel( 'singular_name', __( 'Alert Box Message', 'order-hours-scheduler-for-woocommerce' ) );

		$size = new Input( 'font size' );
		$size
			->setArgument( 'default', 16 )
			->setArgument( 'min', 1 )
			->setType( Input::TYPE_NUMBER )
			->setLabel( 'singular_name', __( 'Alert Box Font Size', 'order-hours-scheduler-for-woocommerce' ) );

		$color = new Input( 'color' );
		$color
			->setType( Input::TYPE_COLOR )
			->setLabel( 'singular_name', __( 'Alert Box Color', 'order-hours-scheduler-for-woocommerce' ) );

		$bg_color = new Input( 'background color' );
		$bg_color
			->setType( Input::TYPE_COLOR )
			->setLabel( 'singular_name', __( 'Alert Box Background Color', 'order-hours-scheduler-for-woocommerce' ) );

		$icon = new Input( 'icon' );
		$icon
			->setType( Input::TYPE_MEDIA )
			->setLabel( 'singular_name', __( 'Alert Box Icon/Logo', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'placeholder', ZH_ROOT_URL . 'assets/bundles/images/closed-time.png' )
			->setArgument( 'is_required_addon_active', Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) );

		$icon_background = new Input( 'icon background' );
		$icon_background
			->setArgument( 'default', 'circle' )
			->setLabel( 'singular_name', __( 'Icon/Logo Background Canvas', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_RADIO )
			->setArgument( 'field_class', ! Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) ? 'zh-disabled-feature' : '' )
			->attachFew( array(
				array( 'circle', __( 'Circle', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'square', __( 'Square', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'transparent', __( 'Transparent', 'order-hours-scheduler-for-woocommerce' ) )
			) );

		$icon_position = new Input( 'icon position' );
		$icon_position
			->setType( Input::TYPE_RADIO_DEPENDENT )
			->setLabel( 'singular_name', __( 'Alert Box Icon/Logo Position', 'order-hours-scheduler-for-woocommerce' ) )
			->setArgument( 'default', [
				'radio' => 'middle',
				'text'  => '80px',
			] )
			->setArgument( 'field_class', ! Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) ? 'zh-disabled-feature' : '' )
			->setArgument( 'text_filed', true )
			->attachFew( array(
				array(
					'options' => array(
						array( 'top', __( 'Top', 'order-hours-scheduler-for-woocommerce' ) ),
						array( 'middle', __( 'Middle', 'order-hours-scheduler-for-woocommerce' ), 'default' ),
						array( 'bottom', __( 'Bottom', 'order-hours-scheduler-for-woocommerce' ) ),
					),
				)
			) );

		$box_options_box->attach( $hide_box, $hide_duration, $message, $size, $color, $bg_color, $icon, $icon_background, $icon_position );
	} );

	$alertbutton->scope( function ( TabPage $alertbutton ) {

		$type_box = new Box( 'closed behavior' );
		$type_box
			->attachTo( $alertbutton )
			->setLabel( 'singular_name',
				__( 'Store Closed Behavior', 'order-hours-scheduler-for-woocommerce' ) );

		$type = new Input( 'closed behavior type' );
		$type
			->setArgument( 'default', 'disabled' )
			->setClass( 'zh-closed-behavior-type' )
			->setArgument( 'upgrade_text',
				__( 'Upgrade to <a href="%s">Store Hours Manager Plus</a> add-on for advanced features', 'order-hours-scheduler-for-woocommerce' ) )
			->setLabel( 'singular_name', __( 'Select Behavior', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_RADIO_PLUS )
			->attachFew( array(
				array( 'disabled', __( 'Checkout Disabled', 'order-hours-scheduler-for-woocommerce' ) ),
				array( 'allowed', __( 'Checkout Allowed', 'order-hours-scheduler-for-woocommerce' ), __( 'Only show notification', 'order-hours-scheduler-for-woocommerce' ) ),
			) );

		$type_box->attach( $type );

		$cart_functionality = new Box( 'cart functionality' );
		$cart_functionality
			->attachTo( $alertbutton )
			->setLabel( 'singular_name', __( 'Add to Cart Functionality', 'order-hours-scheduler-for-woocommerce' ) );

		$hide = new Input( 'hide' );
		$hide
			->setArgument( 'default', false )
			->setLabel( 'singular_name',
				__( 'Hide Add to Cart button if Closed', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $cart_functionality )
			->attach( [ true, '' ] )
			->setType( Input::TYPE_CHECKBOX )
			->setClass( 'reverse' );

		$gateway_functionality = new Box( 'gateway functionality' );
		$gateway_functionality
			->attachTo( $alertbutton )
			->setLabel( 'singular_name',
				__( 'Payment Gateway Functionality', 'order-hours-scheduler-for-woocommerce' ) );

		$remove_gateways = new Input( 'remove gateways' );
		$remove_gateways
			->setArgument( 'default', false )
			->setLabel( 'singular_name', __( 'Remove payment gateway options if closed to prevent checkout', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $gateway_functionality )
			->attach( [ true, '' ] )
			->setType( Input::TYPE_CHECKBOX )
			->setClass( 'reverse' );

		$options = new Box( 'options' );
		$options
			->attachTo( $alertbutton )
			->setLabel( 'singular_name',
				__( 'Alert Button Options for Checkout', 'order-hours-scheduler-for-woocommerce' ) );

		$text = new Input( 'text' );
		$text->setLabel( 'singular_name', __( 'Alert Button Text', 'order-hours-scheduler-for-woocommerce' ) );

		$size = new Input( 'font size' );
		$size
			->setArgument( 'default', 16 )
			->setArgument( 'min', 1 )
			->setType( Input::TYPE_NUMBER )
			->setLabel( 'singular_name', __( 'Alert Button Font Size', 'order-hours-scheduler-for-woocommerce' ) );

		$color = new Input( 'color' );
		$color
			->setType( Input::TYPE_COLOR )
			->setLabel( 'singular_name', __( 'Alert Button Color', 'order-hours-scheduler-for-woocommerce' ) );

		$bg_color = new Input( 'background color' );
		$bg_color
			->setType( Input::TYPE_COLOR )
			->setLabel( 'singular_name',
				__( 'Alert Button Background Color', 'order-hours-scheduler-for-woocommerce' ) );

		$options->attach( $text, $size, $color, $bg_color );
	} );

	$general_settings->scope( function ( TabPage $general_settings ) {
		$roles = array_map( function ( $role ) {
			return $role['name'];
		}, get_editable_roles() );

		$notification_box = new Box( 'notification' );
		$notification_box
			->attachTo( $general_settings )
			->setLabel( 'singular_name',
				__( 'Notification Settings', 'order-hours-scheduler-for-woocommerce' ) );

		$notification_management_mode = new Input( 'notification management mode' );
		$notification_management_mode
			->setLabel( 'singular_name', __( 'Management Mode', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_SWITCHER )
			->setArgument( 'default', '' )
			->attach( [ true, '' ] );

		$notification_management_roles = new InputSelectize( 'notification management roles' );
		$notification_management_roles
			->setLabel( 'singular_name', __( 'By Role', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( InputSelectize::TYPE_SELECTIZE )
			->setArgument( 'default', [ 'administrator' ] )
			->attachFew( $roles )
			->setArgument( 'description', __( 'Closed Status Notification Will Be Disabled For Selected Roles', 'order-hours-scheduler-for-woocommerce' ) );

		$notification_box->attach( $notification_management_mode, $notification_management_roles );

		$actions_box = new Box( 'actions' );
		$actions_box
			->attachTo( $general_settings )
			->setLabel( 'singular_name',
				__( 'Actions Settings', 'order-hours-scheduler-for-woocommerce' ) );

		$actions_management_mode = new Input( 'actions management mode' );
		$actions_management_mode
			->setLabel( 'singular_name', __( 'Management Mode', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( Input::TYPE_SWITCHER )
			->setArgument( 'default', '' )
			->attach( [ true, '' ] );

		$actions_management_roles = new InputSelectize( 'actions management roles' );
		$actions_management_roles
			->setLabel( 'singular_name', __( 'By Role', 'order-hours-scheduler-for-woocommerce' ) )
			->setType( InputSelectize::TYPE_SELECTIZE )
			->setArgument( 'default', [ 'administrator' ] )
			->attachFew( $roles )
			->setArgument( 'description', __( 'Checkout Status Will Be Allowed To Process Orders For Selected Roles', 'order-hours-scheduler-for-woocommerce' ) );

		$actions_box->attach( $actions_management_mode, $actions_management_roles );
	} );

	$add_on->scope( function ( TabPage $add ) {
		$plugins = new Box( 'plugins' );
		$plugins
			->setLabel( 'singular_name', __( 'Plugins', 'order-hours-scheduler-for-woocommerce' ) )
			->attachTo( $add );

		$hours_widget = new Addon( 'hours_widget' );
		$hours_widget
			->setLabelText( __( 'Plus', 'order-hours-scheduler-for-woocommerce' ) )
			->setDescription( __( 'Manage store notification type, add a widget to show store schedule, show store status countdown and more powerful features', 'order-hours-scheduler-for-woocommerce' ) )
			->setNamespace( Addons::ORDER_HOURS_WIDGET_NAMESPACE )
			->setLinkToBuy( 'https://www.bizswoop.com/checkout/?add-to-cart=16061' )
			->setLink( 'https://www.bizswoop.com/store-order-hours' );

		$delivery_plugin = new Addon( 'delivery_plugin' );
		$delivery_plugin
			->setLabelText( __( 'Pick-up, Delivery or Ship', 'order-hours-scheduler-for-woocommerce' ) )
			->setDescription( __( 'Easily Add Pick-up, Take-out, Curbside or Local Delivery and Ship functionality to the checkout workflow with time and date selection', 'order-hours-scheduler-for-woocommerce' ) )
			->setNamespace( Addons::ORDER_DELIVERY_NAMESPACE )
			->setLinkToBuy( 'https://www.bizswoop.com/checkout/?add-to-cart=1879' )
			->setLink( 'https://www.bizswoop.com/pickup-delivery-scheduler/' );

		$plugins_list = new Input( '' );
		$plugins_list
			->setType( Input::TYPE_CARD_PLUGIN )
			->attach( $hours_widget )
			->attach( $delivery_plugin )
			->attachTo( $plugins );
	} );
} );
