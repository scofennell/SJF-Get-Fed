<?php

/**
 * The admin settings screen.
 * 
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

function sjf_gf_results_init() {
	new SJF_GF_Results();
}
add_action( 'init', 'sjf_gf_results_init' );

class SJF_GF_Results extends SJF_GF_Admin {

	/**
	 * Adds actions for our class methods.
	 */
	function __construct() {    
		
		// Add our menu item.
		add_action( 'admin_menu', array( $this, 'admin_menu_tab' ) );

		#add_action( 'my_new_event', array( $this, 'do_this_in_a_second' ), 10, 1 );

	}

	/**
	 * Add a menu item for our plugin.
	 */
	function admin_menu_tab() {
		
		add_submenu_page(
			get_parent_class(),
			__CLASS__,
			esc_attr__( 'Browse Recent Imports', 'sjf-gf' ),
			'manage_options',
			__CLASS__,
			array( $this, 'the_page' )
		);

	}

	/**
	 * A page for used for help / faq  / clearing transients, etc.
	 */
	function the_page() {
	
		// Check capability.
		$this -> accost();

		$this -> enqueue();

		$title = __CLASS__;

		$content = $this -> get_results();

		$out = $this -> wrap( $title, $content );

		echo $out;

	}

	function get_results() { 

		$header  = 'results header';
		
		$content = '';

		$previous_crons = get_option( SJF_GF . '-previous_crons' );

		if( is_array( $previous_crons ) ) {

			foreach( $previous_crons as $timestamp => $cron ) {

				$time = date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $timestamp );

				$content .= "<h3>$time ($timestamp)</h3>";

				if( ! is_array( $cron ) ) { continue; }

				foreach( $cron as $source_id => $posts ) {

					$content .= "<h4>$source_id</h4>";

					foreach( $posts as $post ) {

						$content .= $post[0];

					}

				}

			}

		}

		$out = $this -> get_section( $header, $content );

		return $out;

	}

}