<?php

/**
 * 
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Result {

	public $result_arr = '';

	public function __construct( $result_arr ) {

		$this -> result_arr = $result_arr;

	}

	function get_result_arr() {
		return $this -> result_arr;
	}

	function update() {

		//delete_option( SJF_GF . '-previous_crons' );

		$result_arr = $this -> get_result_arr();

		$previous_crons = get_option( SJF_GF . '-previous_crons' );

		$now = time();

		if( is_array( $previous_crons ) ) {

			$previous_crons[ $now ]= $result_arr;

			ksort( $previous_crons );
			$previous_crons = array_reverse( $previous_crons, TRUE );

			$previous_crons_new = array_slice( $previous_crons, 0, 5, TRUE );

		} else {

			$previous_crons_new[ $now ] = $result_arr;
		
		}


		update_option( SJF_GF . '-previous_crons', $previous_crons_new );

	}

	function get_report() {

		$result_arr = $this -> get_result_arr();

		$out = '';

		foreach( $result_arr as $source_result ) {

			foreach( $source_result as $k => $v ) {
					
				$out .= $v[0];

			}

		}

		return $out;

	}

}