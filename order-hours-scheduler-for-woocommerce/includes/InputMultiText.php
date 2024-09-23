<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class InputMultiText extends \Zhours\Aspect\Input {

	const TYPE_MULTI_TEXT = 'MultiText';

	public function htmlMultiText( $post, $parent ) {
		$values = $this->getValue( $parent, null, $post );
		$name  = $this->nameInput( $post, $parent );
		?>
        <div class="zh-multi-text">
            <?php
            foreach ( $this->attaches as $field ) {
                $value = isset( $values[$field['name']] ) ? $values[$field['name']] : '';
                $placeholder = isset( $field['placeholder'] ) ? 'placeholder="' . $field['placeholder'] . '"' : '';
                ?>
                <label>
                    <input type="text" name="<?= $name; ?>[<?= $field['name']; ?>]" value="<?= $value; ?>" <?= $placeholder; ?>>
                    <?php if ( isset( $field['description'] ) ) { ?>
                        <span><?= $field['description']; ?></span>
                    <?php } ?>
                </label>
            <?php } ?>
        </div>
        <?php
	}
}
