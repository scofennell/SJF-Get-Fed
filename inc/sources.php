<?php

/**
 * A class for getting our plugin posts.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Sources {

	public function __construct() {

	}

	function get() {

		$args = array(
			'post_type' => 'source',
		);

		// The Query
		$the_query = new WP_Query( $args );

		return $the_query -> posts;

	}

	function get_urls() {

		$sources = $this -> get();

		$out = array();

		
		foreach( $sources as $source ) {

			$url = get_post_meta( $source -> ID, '_my_meta_value_key', TRUE );

			$out[]= $url;

		}

		return $out;

	}

}