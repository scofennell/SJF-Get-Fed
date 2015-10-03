<?php

/**
 * A class for calling a url in order to import content functions.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Call {

	public $url = '';

	public function __construct( $url ) {

		$this -> url = $url;

	}

	public function get() {

		return fetch_feed( $this -> url );

		#return wp_remote_get( $this -> url );

	}

}