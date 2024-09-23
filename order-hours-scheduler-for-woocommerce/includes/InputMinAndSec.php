<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class InputMinAndSec extends \Zhours\Aspect\Input {

	const TYPE_MINUTES_AND_SECONDS = 'MinutesAndSeconds';

	public function htmlMinutesAndSeconds( $post, $parent ) {
		$base_name = $this->nameInput( $post, $parent );
		$value     = $this->getValue( $parent, null, $post );

		$is_required_addon_active = isset( $this->args['is_required_addon_active'] ) ? $this->args['is_required_addon_active'] : true;
		$is_sec_as_select         = isset( $this->args['sec_as_select'] ) ? $this->args['sec_as_select'] : false;

		$placeholder = isset( $this->args['placeholder'] ) ? $this->args['placeholder'] : array( 0, 0 );
		$value_min   = $is_required_addon_active ? $value['min'] : $placeholder[0];
		$value_sec   = $is_required_addon_active ? $value['sec'] : $placeholder[1];

		if ( ! is_array( $value ) ) {
			$value = [];
		}
		?>
        <div class="zh-min-and-sec zh-row">
            <div class="zh-col">
                <span class="<?php echo ! $is_required_addon_active ? 'zh-disabled-feature' : ''; ?>">
                    <input class="code" type="number"
                           name="<?= $base_name ?>[min]"
                           value="<?= $value_min ?>"
                           placeholder="<?= $placeholder[0] ?>"/>
                    <span><?php echo esc_html__( 'minutes', 'order-hours-scheduler-widget-woocommerce' ) ?></span>
                </span>
                <span class="<?php echo ! $is_required_addon_active && ! $is_sec_as_select ? 'zh-disabled-feature' : ''; ?>">
                    <?php
                    if ( $is_sec_as_select ) {
                        $sec_select_default = isset( $this->args['sec_select_default'] ) ? $this->args['sec_select_default'] : 10;
                        ?>
                        <select class="code" name="<?= $base_name ?>[sec]" value="<?= $value_sec ?>">
                            <?php
                            foreach ( $this->args['sec_select_options'] as $option ) {
                                $selected = isset( $value['sec'] ) ? selected( $value['sec'], $option, false ) : ( $sec_select_default === $option ? 'selected' : '' );
                                ?>
                                <option <?php echo $selected ?> value="<?= $option ?>"><?= $option ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <input class="code" type="number"
                               name="<?= $base_name ?>[sec]"
                               value="<?= $value_sec ?>"
                               placeholder="<?= $placeholder[1] ?>"/>
                    <?php } ?>
                    <span><?php echo esc_html__( 'seconds', 'order-hours-scheduler-widget-woocommerce' ) ?></span>
                </span>
            </div>
			<?php if ( ! $is_required_addon_active ) { ?>
                <div class="zh-col">
                    <span class="zh-upgrade-icon"></span>
                </div>
			<?php } ?>
        </div>
		<?php if ( ! empty( $this->getDescription() ) ) { ?>
            <p class="right-description-highlight description <?php echo ! $is_required_addon_active ? 'zh-disabled-feature' : ''; ?>"> <?= $this->getDescription() ?></p>
		<?php } ?>
		<?php
	}

	public function processingData( $elem_id, $parent ) {
		$data     = null;
		$key_name = $this->nameInput( null, $parent );
		$data     = stripslashes_deep( $_POST[ $key_name ] );
		$data     = call_user_func_array( array( $this, 'saveBefore' ), array( $data, $key_name, $elem_id ) );
		if ( is_string( $data ) ) {
			$data = sanitize_text_field( $data );
		}
		$data = call_user_func_array( array( $this, 'saveAfter' ), array( $data, $key_name, $elem_id ) );

		return array( $data, $key_name );
	}
}
