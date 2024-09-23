<?php

namespace Zhours\Notification;

defined( 'ABSPATH' ) || exit;

use Zhours\Addons;
use Zhours\Settings;

abstract class Base {

	protected static $setting_tab = 'notification';
	protected static $setting_box = '';

	public function __construct( string $setting_box ) {
		static::$setting_box = $setting_box;
		$this->init();
	}

	public static function get_setting( string $setting, string $box = '' )
        /* : bool|array|int|string */ { // multiple type declaration since PHP 8.0
		return Settings::getValue( static::$setting_tab, empty( $box ) ? static::$setting_box : $box, $setting );
	}

	protected function init() {}

	protected function has_quick_links(): bool {
		$quick_links_status = static::get_setting( 'quick links status', 'quick links' )[0] ?? false;

		return (bool) $quick_links_status && ! empty( static::get_setting( 'quick links list', 'quick links' ) );
	}

	protected function get_quick_links_btn_labels(): array {
		$btn_labels = static::get_setting( 'quick links labels', 'quick links' );

		return $btn_labels !== false ? $btn_labels : ( empty( $btn_labels ) ? array(
			'open'  => __( 'Quick Links', 'order-hours-scheduler-for-woocommerce' ),
			'close' => __( 'Close', 'order-hours-scheduler-for-woocommerce' ),
		) : array() );
	}

	protected function get_quick_links_list( string $link_color ) {
		$links = self::get_setting( 'quick links list', 'quick links' );
		?>
        <ul>
			<?php
			foreach ( $links as $link ) {
				$url   = isset( $link['url'] ) ? $link['url'] : '#';
				$label = isset( $link['label'] ) ? $link['label'] : $link['url'];
				?>
                <li><a href="<?= esc_attr( $url ); ?>" style="color: <?= $link_color; ?>;"><?= esc_html( $label ); ?></a></li>
				<?php
			}
			?>
        </ul>
		<?php
	}
}
