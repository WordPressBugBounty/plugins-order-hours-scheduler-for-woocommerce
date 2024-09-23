<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

use Zhours\Aspect\Page;
use Zhours\Aspect\TabPage;
use Zhours\Model\ServiceBox;

class Input extends \Zhours\Aspect\Input
{
    const TYPE_DAYS_PERIODS = 'DaysPeriod';
    const TYPE_HOLIDAYS_SCHEDULE = 'HolidaysSchedule';
    const TYPE_DELIVERY_CHECKBOX = 'DeliveryCheckbox';
    const TYPE_CHECKBOX_EDIT_ONE_ROW = 'CheckBoxEditOneRow';
    const TYPE_CARD_PLUGIN = 'CardPlugin';
    const TYPE_SWITCHER = 'Switcher';
    const TYPE_STATUS = 'Status';
    const TYPE_RADIO_PLUS  = 'RadioPlus';
    const TYPE_DISPLAY_ON = 'DisplayOn';
    const TYPE_RADIO_DEPENDENT = 'RadioDependent';

    public function htmlDaysPeriod($post, $parent)
    {
        $base_name = $this->nameInput($post, $parent);
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $value = $this->getValue($parent, null, $post);
        $value = (array)maybe_unserialize($value);

        foreach ($days as $day) {
            if (!isset($value[$day]) || !isset($value[$day]['periods'])) {
                $value[$day]['periods'] = [];
            }
        }
        ?>
        <div class="aspect_days_periods">
            <div class="aspect_days_tabs">
                <?php foreach ($days as $day) { ?>
                    <a href="#" data-day="<?= esc_attr($day); ?>"><?php _e(ucwords($day)); ?></a>
                <?php } ?>
            </div>

            <?php foreach ($days as $day) {
                $day_value = $value[$day];
                $day_period = $day_value['periods'];
                $input_name = $base_name . '[' . esc_attr($day) . ']';
                if (!isset($day_value['all_day'])) {
                    $day_value['all_day'] = '0';
                }
                $is_all_day = $day_value['all_day'] === '1';
                ?>
                <div class="aspect_day_period" data-day="<?= esc_attr($day); ?>" data-base=<?= $base_name; ?>>
                    <table>
                        <thead>
                        <tr>
                            <th><?php _e('Opening', 'order-hours-scheduler-for-woocommerce'); ?></th>
                            <th><?php _e('Closing', 'order-hours-scheduler-for-woocommerce'); ?></th>
                            <td>
                                <input class="aspect_all_day_value" type="hidden" name="<?= $input_name ?>[all_day]" value="<?= $day_value['all_day'] ?>"/>
                                <button class="aspect_all_day button <?= $is_all_day ? 'active' : '' ?>">
                                <?php _e('All Day', 'order-hours-scheduler-for-woocommerce'); ?>
                                </button>
                             </td>
                            <td>
                                <button class="aspect_day_add button <?= $is_all_day ? 'hidden' : '' ?>">+</button>
                            </td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (count($day_period) === 0) {
                            $day_period = [['start' => null, 'end' => null]];
                        }
                        foreach ($day_period as $id => $period) {
                            $name = $input_name . '[periods][' . $id . ']';
                            ?>
                            <tr class="aspect_period" data-id="<?= $id; ?>">
                                <td><input type="time" name="<?= $name; ?>[start]"
                                           class="aspect_day_start"
                                           value="<?= $period['start'] ?>"></td>
                                <td><input type="time" name="<?= $name; ?>[end]"
                                           class="aspect_day_end"
                                           value="<?= $period['end'] ?>"></td>
                               <td></td>
                                <td>
                                    <button class="aspect_day_delete button <?= $is_all_day ? 'hidden' : '' ?>">&times;</button>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    public function htmlHolidaysSchedule($post, $parent)
    {
        $base_name = $this->nameInput($post, $parent);
        $value = $this->getValue($parent, null, $post);
        $value = (array)maybe_unserialize($value);

        if (!isset($value[0])) {
            $value[0] = [];
        }
        $date_format = get_option('date_format');
        $input_name = $base_name;
        ?>

        <div class="aspect_holidays_calendar">
        <div class="aspect_holidays_tab">
            <table>
                <td class="relative-column">
                    <textarea readonly id="date_picker_values" cols="30" rows="4"><?= $value[0]; ?></textarea>
                    <input name="<?= $input_name ?>" value="<?= $value[0] ?>" type="text" id="date_picker" readonly="readonly" >
                </td>
                <td>
                    <p><?php _e('Click on Text Box to Open Calendar and Select Your Holidays', 'order-hours-scheduler-for-woocommerce'); ?></p>
                </td>
            </table>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                function getDateFormat(){
                    const standardFormat = 'dd/mm/yy';
                    let wpFormat = '<?= $date_format ?>';
                    wpFormat = wpFormat.replace('Y', 'y').replace('M', 'm').replace('D', 'd');

                    wpFormat = replaceFormat(wpFormat, 'm');
                    wpFormat = replaceFormat(wpFormat, 'd');
                    wpFormat = replaceFormat(wpFormat, 'y');

                    if (wpFormat.includes('y') && wpFormat.includes('m') && wpFormat.includes('d')){
                        return wpFormat;
                    } else return standardFormat;
                }

                function replaceFormat(format, match) {
                    const regExp = new RegExp(match, 'ig');
                    if ((format.match(regExp) || []).length === 1) {
                        return format.replace(match, `${match}${match}`);
                    }
                    return format;
                }

                $('#date_picker_values').on('click', function(event) {
                  const datePicker = $('#date_picker');
                  const textareaHeight = $(this).height();
                  $('#date_picker').height(textareaHeight);
                  datePicker.select();
                });

                $('#date_picker').multiDatesPicker({
                    dateFormat: getDateFormat(),
                    showButtonPanel: true,
                     onSelect: function() {
                        const textStr = $('#date_picker').val();
                        $('#date_picker_values').val(textStr);
                    }
                });
            });
        </script>
        <?php
    }

    public function htmlCheckboxEditOneRow($post, $parent) {
        $base_name = $this->nameInput($post, $parent);
        $value = $this->getValue($parent, null, $post);
        $classes = $this->getClass();

        if (!isset($value['checkbox'])) {
            $value['checkbox'] = false;
        }
        ?>
                <label class="<?= $classes ?>"><input type="checkbox" <?php self::isChecked($value['checkbox']); ?>
                          name="<?= $base_name ?>[checkbox]"
                          value="1">&nbsp;<?= $this->getLabelText() ?></label>

               <input class="large-text code zh-text-input <?= $classes ?>" type="text"
               name="<?= $base_name ?>[edit]"
               id="<?= $base_name ?>"
               value="<?= isset($value['edit']) ? $value['edit'] : '' ?>"/>
        <?php
        if (!empty($this->getDescription())) {
            ?>
            <p class="right-description-highlight description"> <?= $this->getDescription() ?></p>
            <?php
        }
        if ( isset( $this->args['show_note'] ) && $this->args['show_note'] ) {
            ?>
            <div class="zh-note"><?php echo $this->args['note']; ?></div>
            <?php
        }
    }

    public function htmlCardPlugin($post, $parent) {
        $plugins = get_add_on_plugins();
        $services = self::getServices();
        ?>
        <div class="zh-plugins-area">
        	<h2 class="zh-section-header">
        		<i class="fal fa-plus-circle zh-section-header-icon"></i>
        		<?php _e('Add More Premium Features', 'order-hours-scheduler-for-woocommerce'); ?>
        	</h2>
        	<div class="zh-services-wrapper">
						 <?php
						foreach ($plugins as $key => $plugin) {
								$is_active = Addons::is_active_add_on($plugin->getNamespace());
						?>
								<div class="zh-card-box-plugin" id="<?= $key ?>">
										<div class="zh-card-box-header">
												<?= $plugin->getLabelText() ?>
										</div>
										<div class="zh-card-box-description">
												<?= $plugin->getDescription() ?>
										</div>
										<div class="zh-card-box-footer">
												<div class="zh-card-box-left-footer">
												<?php
														if (!$is_active) {
																?>
																<span class="zh-dot zh-dot-enable"></span> <span><a href="<?= admin_url('plugins.php') ?>">
																	<?php _e('Enable', 'order-hours-scheduler-for-woocommerce'); ?>
																</a></span>
																<?php
														} else {
																?>
																<span class="zh-dot zh-dot-active"></span><span><?php _e('Active', 'order-hours-scheduler-for-woocommerce'); ?></span>
																<?php
														}
												 ?>
												</div>
												<?php
														if (!$is_active && $plugin->linkToBuy) {
																?>
																<div class="zh-card-box-center-footer">
																		<a class="zh-buy-button" href="<?= $plugin->linkToBuy ?>">
																			<?php _e('Buy', 'order-hours-scheduler-for-woocommerce'); ?>
																		</a>
																</div>
																<?php
														}
												?>
												<div class="zh-card-box-right-footer">
														<a href="<?= $plugin->getLink() ?>">
														<?php _e('More info', 'order-hours-scheduler-for-woocommerce'); ?>
														</a>
												</div>
										</div>

								</div>
								<?php } ?>
			
					</div>
        </div>
       
        <style>
        html th{
            width: 0 !important;
        }
        </style>	
		
        <?php
    }

    public function htmlSwitcher($post, $parent) {
        $value = $this->getValue($parent, null, $post);

        $is_required_addon_active = isset( $this->args['is_required_addon_active'] ) ? $this->args['is_required_addon_active'] : true;
        $disabled = isset($this->args['disabled']) ? $this->args['disabled'] : false;
        $disabled = !$is_required_addon_active && !$disabled ? !$is_required_addon_active : $disabled;

        $label_disabled = isset( $this->args['label']['disabled'] ) ? $this->args['label']['disabled'] : __( 'Disabled', 'order-hours-scheduler-for-woocommerce' );
        $label_enable = isset( $this->args['label']['disabled'] ) ? $this->args['label']['disabled'] : __( 'Enabled', 'order-hours-scheduler-for-woocommerce' );

        foreach ($this->attaches as $option) {
            if (is_array($option)) {
                $value = $disabled && isset($this->args['onDisabledValue']) ? $this->args['onDisabledValue'] : ( isset( $value[0] ) ? $value[0] : $value );
                ?>
                <label class="<?= $this->getClass() ?> zh-switcher">
                    <input type="checkbox" <?php self::isChecked($value); ?> name="<?= $this->nameInput($post, $parent) ?>[]" value="<?= esc_attr($option[0]) ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                    <span class="zh-switcher-control"></span>
                    <span class="zh-switcher-label">
                        <span><?= $label_disabled ?></span>
                        <span><?= $label_enable ?></span>
                    </span>
                    <?php
                    // TODO refactor this
                    $upgrade_text = isset( $this->args['upgrade_text'] ) ? $this->args['upgrade_text'] : '';
                    if ( ! $is_required_addon_active ) {
                        $settings = Page::get('order hours');
                        $addons_tab = $settings->scope( function () {
                            return TabPage::get( 'add-ons' );
                        } );
                        ?>
                        <span class="zh-upgrade-text"><span class="zh-upgrade-icon"></span><?php echo sprintf( $upgrade_text, $settings->getUrl( $addons_tab ) ); ?></span>
                        <?php
                    }
                    ?>
                </label>
                <?php
                if ($this->getDescription()) { ?>
                    <p style="padding-left: 24px;"><i><?= $this->getDescription() ?></i></p>
                <?php } ?>

            <?php
            } else {
                $option = $disabled && isset($this->args['onDisabledValue']) ? $this->args['onDisabledValue'] : $option;
                ?>
                <label class="<?= $this->getClass() ?> zh-switcher">
                    <input type="checkbox" <?php self::checked($value, esc_attr($option)); ?> name="<?= $this->nameInput($post, $parent) ?>[]" value="<?= esc_attr($option) ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                    <span class="zh-switcher-control"></span>
                    <span class="zh-switcher-label">
                        <span><?= $label_disabled ?></span>
                        <span><?= $label_enable ?></span>
                    </span>
                    <?php
                    // TODO refactor this
                    $upgrade_text = $this->args['upgrade_text'];
                    if ( ! $is_required_addon_active ) {
                        $settings = Page::get('order hours');
                        $addons_tab = $settings->scope( function () {
                            return TabPage::get( 'add-ons' );
                        } );
                        ?>
                        <span class="zh-upgrade-text"><span class="zh-upgrade-icon"></span><?php echo sprintf( $upgrade_text, $settings->getUrl( $addons_tab ) ); ?></span>
                        <?php
                    }
                    ?>
                </label>
                <?php if ($this->getDescription()) { ?>
                    <p style="padding-left: 24px;"><i><?= $this->getDescription() ?></i></p>
                    <?php
                }
            }
        }
    }

    public function htmlStatus() {
        call_user_func(function () {
            if (get_current_status()) {
                $color = 'green';
                $current_status = __('OPEN', 'order-hours-scheduler-for-woocommerce');
            } else {
                $color = 'red';
                $current_status = __('CLOSED', 'order-hours-scheduler-for-woocommerce');
            }
            $time = \date_i18n('H:i');
            ?>
            <span class="zh-status">
                <span class="zh-status-control" style='background-color: <?php echo $color ?>; padding: 10px; display: inline-block; color: white; font-style: normal; line-height: 1;'><?php echo __('Current time:', 'order-hours-scheduler-for-woocommerce') . " $time . " . __('Status:', 'order-hours-scheduler-for-woocommerce') . ' '. $current_status; ?></span>
                <span class="zh-status-note"><?php echo __('Note: Store Time Based Upon <a href="/wp-admin/options-general.php">General Settings</a> > Timezone Settings, Local Time') ?></span>
            </span>
            <?php
        });
    }

    public function htmlRadioPlus($post, $parent)
    {
        $value = $this->getValue($parent, 'attr', $post);
        $is_plus_addon_active = Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE );
        $i = 0;
        foreach ($this->attaches as $option) {
            $checked = $is_plus_addon_active ? checked($value, esc_attr($option[0]), false) : ( $i === 0 ? 'checked' : '' );
            $disabled = ! $is_plus_addon_active && $i > 0;
            $item_description = isset($option[2]) ? '<span>' .  esc_html($option[2]) . '</span>' : '';
            ?>
            <label class="zh-radio-plus <?= $disabled ? 'zh-radio-disabled' : '' ?> <?= $this->getClass(); ?>"><input type="radio" <?= $checked ?>
                          name="<?= $this->nameInput($post, $parent) ?>"
                          value="<?= esc_attr($option[0]) ?>" <?= $disabled ? 'disabled' : '' ?>>&nbsp;<?= esc_html($option[1]) ?> <?= $item_description ?></label>
            <?php
            $i++;
        }
        $upgrade_text = isset( $this->args['upgrade_text'] ) ? $this->args['upgrade_text'] : '';
        if ( ! $is_plus_addon_active && ! empty( $upgrade_text ) ) {
            $settings = Page::get('order hours');
            $addons_tab = $settings->scope( function () {
                return TabPage::get( 'add-ons' );
            } );
            ?>
            <span class="zh-upgrade-text"><span class="zh-upgrade-icon"></span><?php echo sprintf( $upgrade_text, $settings->getUrl( $addons_tab ) ); ?></span>
            <?php
        }
    }

    public function htmlDisplayOn($post, $parent) {
        $value = $this->getValue($parent, null, $post);
        $name = $this->nameInput($post, $parent);
        $is_plus_addon_active = Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE );
        $this->args['multiply'] = true;
        ?>
        <div class="zh-display-on">
            <select <?php echo ! $is_plus_addon_active ? 'disabled' : ''; ?> class="zh-display-on__select" name="<?= $name ?>[all]" id="<?= $name ?>">
                <option <?php echo $is_plus_addon_active ? selected($value['all'], 'yes', false) : 'selected'; ?> value="yes"><?php echo esc_html__( 'All Site-wide', 'order-hours-scheduler-for-woocommerce' ); ?></option>
                <option <?php echo $is_plus_addon_active ? selected($value['all'], 'no', false) : ''; ?> value="no"><?php echo esc_html__( 'Select Pages, Posts & Products', 'order-hours-scheduler-for-woocommerce' ); ?></option>
            </select>
            <?php
            $this->getDisplayOnField( $value, $name, 'page', __('Pages', 'order-hours-scheduler-for-woocommerce') );
            $this->getDisplayOnField( $value, $name, 'post', __('Posts', 'order-hours-scheduler-for-woocommerce') );
            $this->getDisplayOnField( $value, $name, 'product', __('Products', 'order-hours-scheduler-for-woocommerce') );
            ?>
        </div>
        <?php
    }

    private function getDisplayOnField( $value, $name, $post_type, $label ) {
        $all_name = $post_type . '_all';
        $is_plus_addon_active = Addons::is_active_add_on( Addons::ORDER_HOURS_WIDGET_NAMESPACE );
        ?>
        <div class="zh-display-on__option <?php echo !$is_plus_addon_active ? 'zh-display-on__option_disabled' : ''; ?>" <?php echo ( isset( $value['all'] ) && $value['all'] === 'yes' ) || !$is_plus_addon_active ? 'style="display: none;"' : ''; ?>>
            <label class="zh-display-on__label" for="<?= $name ?>_<?= $post_type ?>"><?php echo esc_html($label); ?></label>
            <div class="zh-display-on__row">
                <div class="zh-display-on__col">
                    <select class="zh-display-on__selectize zh-selectize" name="<?= $name ?>[<?= $post_type ?>][]" multiple <?php echo ( isset( $value[$all_name] ) && $value[$all_name] ) || !$is_plus_addon_active ? 'disabled' : ''; ?> id="<?= $name ?>_<?= $post_type ?>">
                        <?php
                        $posts = get_posts( array(
                            'numberposts' => -1,
                            'post_type' => $post_type,
                        ) );
                        foreach ($posts as $post) {
                            ?>
                            <option <?php $this->selected( ( isset( $value[$post_type] ) ? $value[$post_type] : '' ), esc_attr($post->ID) ); ?> value="<?php echo esc_attr($post->ID); ?>"><?php echo esc_html($post->post_title); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="zh-display-on__col">
                    <?php $checked = ( isset( $value[$all_name] ) && $value[$all_name] ) || ! $is_plus_addon_active; ?>
                    <label><input class="zh-display-on__checkbox" type="checkbox" name="<?= $name ?>[<?= $all_name ?>]" <?php echo !$is_plus_addon_active ? 'disabled' : ''; ?> <?php echo $checked ? 'checked' : ''; ?>> <span><?php echo esc_html__('All', 'order-hours-scheduler-for-woocommerce') . ' ' . esc_html($label); ?></span></label>
                </div>
            </div>
        </div>
        <?php
    }

    public function htmlMedia($post, $parent) {
        $value = $this->getValue($parent, 'html', $post);
        $src_data = wp_get_attachment_image_src($value, 'full');
        $src = $src_data[0] ?? '';
        static $calling = false;
        if (!$calling) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            $calling = true;
        }
        $placeholder = isset( $this->args['placeholder'] ) ? $this->args['placeholder'] : '';
        $is_required_addon_active = isset( $this->args['is_required_addon_active'] ) ? $this->args['is_required_addon_active'] : true;
        $field_class = isset( $this->args['field_class'] ) ? $this->args['field_class'] : '';
        ?>
        <div class="zh-media-upload zh-row <?= $field_class ?> <?= ! $is_required_addon_active ? 'zh-disabled-feature' : '' ?>">
            <div class="zh-col">
                <script>
                    jQuery(document).ready(function ($) {
                        $('#<?= $this->nameInput($post, $parent) ?>_upload').click(function (e) {
                            e.preventDefault();
                            tb_show('Upload', 'media-upload.php?referer=<?= $this->nameInput($post, $parent) ?>&type=image&TB_iframe=true&post_id=0', false);
                        });
                        $('#<?= $this->nameInput($post, $parent) ?>_remove').click(function (e) {
                            e.preventDefault();
                            $('#<?= $this->nameInput($post, $parent) ?>_src, #<?= $this->nameInput($post, $parent) ?>').val('');
                            $('#<?= $this->nameInput($post, $parent) ?>_preview img').attr({'src': '<?= $placeholder ?>'});
                        });
                        window.send_to_editor = function (html) {
                            var image_url = $(html).attr('src');
                            var id_attach = $(html).attr('class').match(/\d+/g);
                            id_attach = id_attach[0];
                            var name = 'referer';
                            name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
                            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                                results = regex.exec(jQuery('#TB_iframeContent').attr('src'));
                            var id = results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
                            $('#' + id).val(id_attach);
                            $('#' + id + '_src').val(image_url);
                            $('#' + id + '_preview img').attr({'src': image_url}).show();
                            tb_remove();
                        }
                    });
                </script>
                <input type="hidden" id="<?= $this->nameInput($post, $parent) ?>" name="<?= $this->nameInput($post, $parent) ?>"
                       value="<?= $value ?>"/>
                <input class="large-text code" type="text"
                       id="<?= $this->nameInput($post, $parent) ?>_src"
                       value="<?= $src ?>"/>
                <input id="<?= $this->nameInput($post, $parent) ?>_upload" class="button" type="button"
                       value="<?php _e('Upload','order-hours-scheduler-for-woocommerce'); ?>"/>
                <input id="<?= $this->nameInput($post, $parent) ?>_remove" class="button" type="button"
                       value="<?php _e('Remove', 'order-hours-scheduler-for-woocommerce'); ?>"/>
                <div id="<?= $this->nameInput($post, $parent) ?>_preview" style="margin-top: 10px">
                    <img style="max-width:50%;" src="<?= empty( $src ) ? $placeholder : $src; ?>"/>
                </div>
            </div>
            <?php if ( ! $is_required_addon_active ) { ?>
                <div class="zh-col">
                    <div class="zh-upgrade-icon"></div>
                </div>
            <?php } ?>
        </div>
    <?php }

    public function htmlRadio($post, $parent)
    {
        $value = $this->getValue($parent, 'attr', $post);
        $field_class = isset( $this->args['field_class'] ) ? $this->args['field_class'] : '';
        $on_new_line = isset( $this->args['on_new_line'] ) && $this->args['on_new_line'];
        ?>
        <div class="<?= $field_class ?>">
            <?php
            foreach ($this->attaches as $option) {
                echo $on_new_line ? '<div class="zh-radio-on-new-line">' : '';
                if (is_array($option)) {
                    $description = isset( $option[2] ) ? ' <i>' . esc_html($option[2]) . '</i>' : '';
                    ?>
                    <label><input type="radio" <?php checked($value, esc_attr($option[0])); ?>
                                  name="<?= $this->nameInput($post, $parent) ?>"
                                  value="<?= esc_attr($option[0]) ?>">&nbsp;<?= esc_html($option[1]) ?><?= $description ?></label>
                <?php } else { ?>
                    <label><input type="radio" <?php checked($value, esc_attr($option)); ?>
                                  name="<?= $this->nameInput($post, $parent) ?>"
                                  value="<?= esc_attr($option) ?>">&nbsp;<?= ucfirst(esc_html($option)) ?></label>
                    <?php
                }
                echo $on_new_line ? '</div>' : '';
            }
            ?>
        </div>
        <?php
    }

    public function htmlRadioDependent($post, $parent) {
        $value = $this->getValue($parent, 'attr', $post);
        $field_class = isset( $this->args['field_class'] ) ? $this->args['field_class'] : '';
        ?>
        <div class="zh-radio-dependent <?= isset( $this->args['setting_dependency'] ) ? 'zh-radio-dependent_active' : '' ?> <?= $field_class ?>" <?= isset( $this->args['setting_dependency'] ) ? 'data-setting-dependency="' . $this->args['setting_dependency'] . '"' : '' ?>>
            <?php
            foreach ($this->attaches as $group) {
                $if_classes = isset( $group['if'] ) ? implode( ' ', array_map( function($item) {
                    return 'zh-radio-dependent__group_if_' . $item;
                }, $group['if'] ) ) : '';
                ?>
                <span class="zh-radio-dependent__group <?= $if_classes ?>">
                    <?php foreach ($group['options'] as $option) { ?>
                        <label><input class="<?= isset( $option[2] ) && $option[2] === 'default' ? 'zh-radio-dependent__default' : '' ?>" type="radio" <?php checked($value['radio'], esc_attr($option[0])); ?>
                                    name="<?= $this->nameInput($post, $parent) ?>[radio]"
                                    value="<?= esc_attr($option[0]) ?>">&nbsp;<?= esc_html($option[1]) ?></label>
                    <?php } ?>
                </span>
            <?php } ?>
            <?php if ( isset( $this->args['text_filed'] ) && $this->args['text_filed'] ) { ?>
                <label><input type="text" name="<?= $this->nameInput($post, $parent) ?>[text]" value="<?= esc_attr($value['text']) ?>"> size</label>
            <?php } ?>
        </div>
        <?php
    }

    protected static function getServices() {
    		return [
    			new ServiceBox(
    				__('WordPress.org', 'order-hours-scheduler-for-woocommerce'),
    				__('Free plugin apps for the open source community', 'order-hours-scheduler-for-woocommerce'),
    				'fab fa-wordpress-simple',
    				'https://wordpress.org/plugins/search/bizswoop',
    				__('Explore Free Apps', 'order-hours-scheduler-for-woocommerce')
    			),
    			new ServiceBox(
    				__('Premium Plugins', 'order-hours-scheduler-for-woocommerce'),
    				__('Smart plugin apps for your advanced business requirements', 'order-hours-scheduler-for-woocommerce'),
    				'fal fa-cubes',
    				'https://www.bizswoop.com/wp',
    				__('Explore All Apps', 'order-hours-scheduler-for-woocommerce')
    			),
    			new ServiceBox(
    				__('Powerful Platforms', 'order-hours-scheduler-for-woocommerce'),
    				__('Advanced platforms for agencies, developers and businesses', 'order-hours-scheduler-for-woocommerce'),
    				'fal fa-window',
    				'https://www.bizswoop.com/platforms',
    				__('Explore Platforms', 'order-hours-scheduler-for-woocommerce')
    			),
    			new ServiceBox(
    				__('Super Services', 'order-hours-scheduler-for-woocommerce'),
    				__('High-touch services to boost your business technology solutions', 'order-hours-scheduler-for-woocommerce'),
    				'fal fa-feather',
    				'https://www.bizswoop.com/services/',
    				__('Explore Services', 'order-hours-scheduler-for-woocommerce')
    			),
    		];
    }
}
