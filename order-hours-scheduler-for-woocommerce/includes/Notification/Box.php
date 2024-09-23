<?php

namespace Zhours\Notification;

defined( 'ABSPATH' ) || exit;

use Zhours\Addons;

class Box extends Base {

	public function __construct() {
		parent::__construct( 'options' );
	}

	protected function init() {
		if ( ! apply_filters( 'zh_notification_type_status', true ) ) {
			return;
		}

		$message          = self::get_setting( 'message' );
		$override_message = apply_filters( 'zhd_get_override_alert_bar_message', null );
		if ( $override_message !== null ) {
			if ( isset( $override_message['checkbox'] ) && $override_message['checkbox'] ) {
				return;
			}
			$message = $override_message['edit'];
		}

		$color         = self::get_setting( 'color' ) ? self::get_setting( 'color' ) : 'white';
		$bg_color      = self::get_setting( 'background color' ) ? self::get_setting( 'background color' ) : '#e21212';
		$hide          = ! empty( self::get_setting( 'hide alert bar' ) ) ? self::get_setting( 'hide alert bar' ) : array();
		$font_size     = self::get_setting( 'font size' );
		$hide_duration = apply_filters( 'zh_box_hide_duration', empty( self::get_setting( 'hide duration' )['sec'] ) ? 10 : (int) self::get_setting( 'hide duration' )['sec'] );

		$this->get_notification( $hide_duration, $hide, $message, $font_size, $color, $bg_color );
	}

	private function get_notification(
		int $hide_duration,
		array $hide,
		string $message,
		string $font_size,
		string $color,
		string $bg_color
	) {
		?>
        <div class="zh-notification zh-box <?= $this->has_quick_links() ? 'zh-quick-links__parent' : ''; ?>" style="display: none;" data-zh-hide-duration="<?= $hide_duration; ?>">
            <div class="zh-box__window" style="<?= apply_filters( 'zh_box_window_styles', 'color:' . $color . ';background-color:' . $bg_color . ';font-size:' . $font_size . 'px;' ); ?>" tabindex="-1">
				<?php if ( $hide && isset( $hide['checkbox'] ) ) { ?>
                    <button type="button" class="zh-notification__close zh-box__close">
						<?php if ( isset( $hide['edit'] ) && $hide['edit'] ) { ?>
                            <span class="zh-box__close-title"><?= $hide['edit']; ?></span>
						<?php } ?>
                        <span class="zh-box__close-icon zh-icon zh-icon_close"></span>
                    </button>
				<?php } ?>
                <div class="zh-box__body">
                    <div class="zh-box__message">
						<?php if ( ! Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE ) ) { ?>
                            <div class="zh-box__icon">
                                <img src="<?= ZH_ROOT_URL . 'assets/bundles/images/closed-time.png'; ?>" alt="<?= esc_attr__( 'Closed Time', 'order-hours-scheduler-for-woocommerce' ); ?>">
                            </div>
						<?php } ?>
						<?php do_action( 'zh_box_icon' ); ?>
						<?= $message; ?>
                    </div>
                </div>
				<?php $this->get_quick_links( $font_size, $color, $bg_color ); ?>
            </div>
        </div>
		<?php
	}

	private function get_quick_links(
		string $font_size,
		string $color,
		string $bg_color
	) {
		if ( $this->has_quick_links() ) {
			$btn_labels = $this->get_quick_links_btn_labels();
			?>
            <div class="zh-box__links zh-quick-links" style="color: <?= $color; ?>; background-color: <?= $bg_color; ?>; font-size: <?= $font_size ?>px;" tabindex="-1">
                <div class="zh-box__links-body"><?php $this->get_quick_links_list( $color ); ?></div>
                <button class="zh-box__links-toggle zh-quick-links__toggle" style="color: <?= $color; ?>; border-color: <?= $color; ?>;" type="button">
                    <span class="zh-icon zh-icon_close"></span>
					<?php if ( isset( $btn_labels['close'] ) ) { ?><span><?= esc_html( $btn_labels['close'] ); ?></span><?php } ?>
                </button>
            </div>
            <button class="zh-box__links-toggle zh-quick-links__toggle" style="<?= apply_filters( 'zh_box_links_toggle_styles', 'color:' . $color . ';' . 'border-color:' . $color . ';' ); ?>" type="button">
                <span class="zh-icon zh-icon_caret-circle-down"></span>
				<?php if ( isset( $btn_labels['open'] ) ) { ?><span><?= esc_html( $btn_labels['open'] ); ?></span><?php } ?>
            </button>
            <div class="zh-box__links-toggle-spacer"></div>
			<?php
		}
	}
}
