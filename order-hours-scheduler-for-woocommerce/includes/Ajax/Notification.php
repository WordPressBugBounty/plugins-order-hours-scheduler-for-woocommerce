<?php

namespace Zhours\Ajax;

defined( 'ABSPATH' ) || exit;

class Notification extends Base {

	public function __construct() {
		parent::__construct( 'zh_get_notification' );
	}

	public function callback() {
		\Zhours\Setup::get_notification();
		die();
	}
}
