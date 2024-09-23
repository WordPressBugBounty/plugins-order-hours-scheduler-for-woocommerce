<?php

namespace Zhours\Ajax;

defined( 'ABSPATH' ) || exit;

abstract class Base {

	private $action;

	public function __construct( string $action ) {
		$this->action = $action;

		add_action( "wp_ajax_${action}", array( $this, 'callback' ), 1 );
		add_action( "wp_ajax_nopriv_${action}", array( $this, 'callback' ), 1 );
	}

	abstract public function callback();
}
