<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class InputLinksList extends \Zhours\Aspect\Input {

	const TYPE_LINKS_LIST = 'LinksList';

	public function htmlLinksList( $post, $parent ) {
		$values = $this->getValue( $parent, null, $post );
		$name  = $this->nameInput( $post, $parent );

		if ( ! $values || empty( $values ) ) {
		    $values = array(
                array(
                    'label' => '',
                    'url' => '',
                ),
            );
        }
		?>
        <table class="zh-links-list" data-name="<?= $name; ?>">
            <tr>
                <th><?= esc_html__( 'Link Label', 'order-hours-scheduler-for-woocommerce' ); ?></th>
                <th><?= esc_html__( 'URL Link', 'order-hours-scheduler-for-woocommerce' ); ?></th>
                <td><button class="zh-links-list__add-button button" type="button">+ <?= $this->args['add_button_label']; ?></button></td>
            </tr>
            <?php
            $i = 0;
            foreach ( $values as $value ) {
                $label = $value['label'] ?? '';
                $url = isset( $value['url'] ) ? $value['url'] : '';
                ?>
                <tr data-item-id="<?= $i; ?>">
                    <td><input type="text" class="zh-links-list__field" name="<?= $name; ?>[<?= $i; ?>][label]" value="<?= esc_attr__( $label ); ?>"></td>
                    <td><input type="url" class="zh-links-list__field" name="<?= $name; ?>[<?= $i; ?>][url]" value="<?= esc_attr( $url ); ?>"></td>
                    <td><button class="zh-links-list__remove-button button" type="button">×</button></td>
                 </tr>
                <?php
	            $i++;
            }
            ?>
        </table>
		<?php
	}
}
