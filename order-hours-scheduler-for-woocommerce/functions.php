<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

use Zhours\Aspect\InstanceStorage, Zhours\Aspect\Page, Zhours\Aspect\TabPage, Zhours\Aspect\Box;
use Zhours\Frontend\Shop;

/**
 * Retrieves the current store status.
 * @return bool The store status
 */
function get_current_status() {
	if ( ! plugin_enabled() ) {
		return true;
	}
	list( $rewrite, $status ) = get_force_override_status();

	if ( $rewrite ) { // return force status if enabled
		return (bool) $status;
	}
	$periods = get_day_periods();
	if ( ! $periods ) {
		return false;
	}

	$holidays_calendar = get_holidays();

	$holidays = explode( ', ', $holidays_calendar );
	if ( is_holiday( $holidays ) ) {
		return false;
	}

	$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

	$current_index = \date_i18n( 'N' ) - 1;
	$current_index = $days[ $current_index ];

	$today = isset( $periods[ $current_index ] ) ? $periods[ $current_index ] : null;

	$time = \date_i18n( 'H:i' );

	if ( ! $today || ! isset( $today['periods'] ) ) {
		return false;
	}

	$matches = array_filter( $today['periods'], function ( $element ) use ( $time ) {
		return $time >= $element['start'] && $time <= $element['end'];
	} );

	return count( $matches ) !== 0;
}

function is_plugin_settings_page() {
	$page = Page::get( 'order hours' );

	return isset( $_GET['page'] ) && $_GET['page'] === Page::getName( $page );
}

function get_status_on_special_date( $date ) {
	$time = $date[1];
	$date = $date[0];
	$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];

	$day_of_week = date( 'N', strtotime( $date ) );
	$periods     = get_day_periods();
	$day         = $days[ $day_of_week - 1 ];
	if ( isset( $periods[ $day ] ) ) {
		$matches = array_filter( $periods[ $day ]['periods'], function ( $element ) use ( $time ) {
			return $time >= $element['start'] && $time <= $element['end'];
		} );

		return count( $matches ) !== 0;

	}

	return false;
}

function is_holiday( $dates ) {
	if ( $dates ) {
		$date_format = get_date_format();

		$today = date( $date_format, current_time( 'timestamp' ) );
		foreach ( $dates as $date ) {
			if ( $today === $date ) {
				return true;
			}
		}
	}

	return false;
}

function get_date_format() {
	$standardFormat = 'd/m/Y';
	$wpFormat       = get_option( 'date_format' );
	if ( preg_match( '/^(?=.*\bd\b)(?=.*\bm\b)(?=.*\by\b).*$/i', $wpFormat ) ) {
		return $wpFormat;
	} else {
		return $standardFormat;
	}
}

function plugin_enabled(): bool {
	$setting = Settings::getValue( 'schedule', 'status', 'order hours status' );
	return is_array( $setting ) ? '1' === $setting[0] : false;
}

function get_alertbutton() {
	list ( $text, $size, $color, $bg_color ) = InstanceStorage::getGlobalStorage()->asCurrentStorage( function () {
		return Page::get( 'order hours' )->scope( function () {
			return TabPage::get( 'actions' )->scope( function ( TabPage $alertbutton ) {
				$options  = Box::get( 'options' );
				$text     = Input::get( 'text' );
				$size     = Input::get( 'font size' );
				$color    = Input::get( 'color' );
				$bg_color = Input::get( 'background color' );
				$values   = [ $text, $size, $color, $bg_color ];

				return array_map( function ( Input $value ) use ( $options, $alertbutton ) {
					return $value->getValue( $options, null, $alertbutton );
				}, $values );
			} );
		} );
	} );

	$color    = ( $color ) ? $color : 'black';
	$bg_color = ( $bg_color ) ? $bg_color : 'transparent';
	if ( ! $text || get_current_status() ) {
		return;
	}
	?>
    <style>
		.zhours_alertbutton {
			color: <?= $color; ?>;
			background-color: <?= $bg_color; ?>;
			padding: <?= $size; ?>px;
			font-size: <?= $size; ?>px;
		}
    </style>
    <div class="zhours_alertbutton">
		<?= $text; ?>
    </div>
	<?php
}

function is_enable_cache_clearing() {
	return Settings::getValue( 'schedule', 'cache management', 'enable cache clearing' );
}

function is_hide_add_to_cart() {
	return Settings::getValue( 'actions', 'cart functionality', 'hide' );
}

function get_day_periods() {
	return Settings::getValue( 'schedule', 'days schedule', 'period' );
}

function get_holidays() {
	return Settings::getValue( 'schedule', 'holidays schedule', 'holidays calendar' );
}

function get_add_on_plugins() {
	return InstanceStorage::getGLobalStorage()->asCurrentStorage( function () {
		return Page::get( 'order hours' )->scope( function () {
			return TabPage::get( 'add-ons' )->scope( function ( TabPage $alertbutton ) {
				$box = Box::get( 'plugins' );

				return $box->attaches[0]->attaches;
			} );
		} );
	} );
}

function check_if_holiday( $date ) {
	$holidays = get_holidays();
	$holidays = explode( ', ', $holidays );
	foreach ( $holidays as $holiday ) {
		if ( $date === $holiday ) {
			return true;
		}
	}

	return false;
}

function get_date_from_day_of_the_week( $day, $is_cycled = false ) {
	$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
	$date = new \DateTime();
	if ( in_array( strtolower( $day ), $days ) ) {
		if ( $is_cycled ) {
			$date->modify( '+1 day' );
		}
		$day_of_week = strtolower( date( 'l', $date->getTimestamp() ) );
		while ( $day_of_week !== $day ) {
			$date->modify( '+1 day' );
			$day_of_week = strtolower( date( 'l', $date->getTimestamp() ) );
		}
	}

	return $date;
}

function cache_cleaner( $is_cycled = false ) {
	if ( ! is_enable_cache_clearing() ) {
		if ( wp_next_scheduled( 'zhours_cache_clear_open' ) ) {
			wp_clear_scheduled_hook( 'zhours_cache_clear_open' );
		}
		if ( wp_next_scheduled( 'zhours_cache_clear_close' ) ) {
			wp_clear_scheduled_hook( 'zhours_cache_clear_close' );
		}

		return;
	}
	if ( wp_next_scheduled( 'zhours_cache_clear_open' ) && wp_next_scheduled( 'zhours_cache_clear_close' ) ) {
		return;
	}
	$all_periods       = get_day_periods();
	$days              = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
	$current_index     = \date_i18n( 'N' ) - 1;
	$current_date_time = date_i18n( 'Y-m-d H:i:s' );
	$day_of_week       = strtolower( date( 'l', strtotime( $current_date_time ) ) );
	foreach ( $all_periods as $key => $day_periods ) {
		if ( ! isset( $day_periods['periods'] ) || ( ! $is_cycled && array_search( $key, $days ) < $current_index ) ) {
			continue;
		}
		foreach ( $day_periods['periods'] as $val => $period ) {
			$start = get_date_from_day_of_the_week( $key, $is_cycled );
			$start->setTime( explode( ':', $period['start'] )[0], explode( ':', $period['start'] )[1] );
			$start = $start->format( 'Y-m-d H:i:s' );
			$end   = get_date_from_day_of_the_week( $key, $is_cycled );
			$end->setTime( explode( ':', $period['end'] )[0], explode( ':', $period['end'] )[1] );
			$end = $end->format( 'Y-m-d H:i:s' );
			if ( $current_date_time < $start ) {
				if ( ! wp_next_scheduled( 'zhours_cache_clear_open' ) ) {
					$time_offset = date( 'Y-m-d H:i:s',
						strtotime( '-' . get_option( 'gmt_offset' ) . ' hours', strtotime( $start ) ) );
					wp_schedule_event( strtotime( $time_offset ), 'daily', 'zhours_cache_clear_open' );
				}
				if ( ! wp_next_scheduled( 'zhours_cache_clear_close' ) ) {
					$time_offset = date( 'Y-m-d H:i:s',
						strtotime( '-' . get_option( 'gmt_offset' ) . ' hours', strtotime( $end ) ) );
					wp_schedule_event( strtotime( $time_offset ), 'daily', 'zhours_cache_clear_close' );
				}
			}
			if ( $current_date_time > $start && $current_date_time < $end ) {
				if ( ! wp_next_scheduled( 'zhours_cache_clear_close' ) ) {
					$time_offset = date( 'Y-m-d H:i:s',
						strtotime( '-' . get_option( 'gmt_offset' ) . ' hours', strtotime( $end ) ) );
					wp_schedule_event( strtotime( $time_offset ), 'daily', 'zhours_cache_clear_close' );
				}
			}
			if ( wp_next_scheduled( 'zhours_cache_clear_open' ) && wp_next_scheduled( 'zhours_cache_clear_close' ) ) {
				return;
			}
			if ( $is_cycled && $day_of_week === $key ) {
				return;
			}
			if ( end( $all_periods ) === $day_periods ) {
				cache_cleaner( true );
			}
		}
	}
}

add_filter( 'pre_option_zhours_current_status', function () {
	return get_current_status() ? "yes" : "no";
} );

add_filter( 'check_if_store_hours_is_opened', function ( $date ) {
	return get_status_on_special_date( $date );
} );

add_filter( 'check_if_holiday', function ( $date ) {
	return check_if_holiday( $date );
} );

add_filter( 'get_period_schedule_by_day', function ( $day ) {
	$periods = get_day_periods();

	return isset( $periods[ $day ] ) ? $periods[ $day ] : [];
} );

add_filter( 'body_class', function ( $classes ) {
	if ( ! get_current_status() ) {
		$classes[] = 'zhours-closed-store';
	}

	return $classes;
} );

function get_force_override_status() {
	return InstanceStorage::getGlobalStorage()->asCurrentStorage( function () {
		return Page::get( 'order hours' )->scope( function () {
			return TabPage::get( 'schedule' )->scope( function ( TabPage $schedule ) {
				$force_status = Box::get( 'force status' );

				return $force_status->scope( function ( $box ) use ( $schedule ) {
					$rewrite = Input::get( 'force rewrite status' );
					$rewrite = apply_filters( 'zh_force_rewrite_status', $rewrite->getValue( $box, null, $schedule ) );
					$status  = Input::get( 'force status' );
					$status  = apply_filters( 'zh_force_status', $status->getValue( $box, null, $schedule ) );

					return [ $rewrite, $status ];
				} );
			} );
		} );
	} );
}

function zhours_cache_clear( $status ) {
	delete_directory( ABSPATH . 'wp-content/cache/' );
	wp_clear_scheduled_hook( 'zhours_cache_clear_' . $status );
	cache_cleaner();
	do_action( 'zhours_on_cache_clearing' );
}

function delete_directory( $dir ) {
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach ( $files as $file ) {
		( is_dir( "$dir/$file" ) ) ? delete_directory( "$dir/$file" ) : unlink( "$dir/$file" );
	}

	return rmdir( $dir );
}

function get_single_product_class() {
	return get_current_status() ? 'zh_single_add_to_cart_button_open' : 'zh_single_add_to_cart_button_close';
}

add_action( 'zhours_cache_clear_open', function () {
	zhours_cache_clear( 'open' );
} );

add_action( 'zhours_cache_clear_close', function () {
	zhours_cache_clear( 'close' );
} );

function render_after_mini_cart() {
	if ( ! WC()->cart->is_empty() ) :
		\ob_end_clean();
		get_alertbutton();
	endif;
}

function render_widget_shopping_cart_before_buttons() {
	\ob_start();
}

function init_checkout_actions() {
	$allow_order_placing = apply_filters( 'zh_is_allowed_order_placing', false );
	if ( $allow_order_placing ) {
		return;
	}
	if ( is_checkout() ) {
		header( 'Location:' . wc_get_cart_url() );
		exit;
	}

	if ( is_hide_add_to_cart() ) {
		\remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		\remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );

		add_filter( 'woocommerce_blocks_product_grid_item_html', [ Shop::class, 'update_product_grid_item' ], 10, 3 );
	}

	\remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
	\add_action( 'woocommerce_proceed_to_checkout', '\Zhours\get_alertbutton' );

	\add_action( 'woocommerce_widget_shopping_cart_before_buttons',
		'\Zhours\render_widget_shopping_cart_before_buttons' );
	\add_action( 'woocommerce_after_mini_cart', '\Zhours\render_after_mini_cart' );
}
