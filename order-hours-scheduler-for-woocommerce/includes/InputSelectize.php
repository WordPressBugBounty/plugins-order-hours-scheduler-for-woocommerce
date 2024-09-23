<?php

namespace Zhours;

defined( 'ABSPATH' ) || exit;

class InputSelectize extends \Zhours\Aspect\Input {

	const TYPE_SELECTIZE = 'Selectize';

	public function htmlSelectize( $post, $parent ) {
		$value = $this->getValue( $parent, null, $post );
		$name  = $this->nameInput( $post, $parent );

		$this->args['multiply'] = true;
		?>
        <select class="zh-selectize" name="<?= $name ?>[]" multiple id="<?= $name ?>">
			<?php
			foreach ( $this->attaches as $option ) {
				$key = array_search( $option, $this->attaches );
				?>
                <option <?php $this->selected( $value, $key ); ?>
                        value="<?= esc_attr( $key ) ?>"><?= esc_html( $option ) ?></option>
				<?php
			}
			?>
        </select>
		<?php
	}
}
