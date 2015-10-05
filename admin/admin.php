<?php

/**
 * The admin settings screen.
 * 
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

function sjf_gf_admin_init() {
	new SJF_GF_Admin();
}
add_action( 'init', 'sjf_gf_admin_init' );

class SJF_GF_Admin {

	/**
	 * Adds actions for our class methods.
	 */
	function __construct() {    
		
		// Add our menu item.
		add_action( 'admin_menu', array( $this, 'admin_menu_tab' ) );
	}

	/**
	 * Add a menu item for our plugin.
	 */
	function admin_menu_tab() {


		// Add a primary menu item.
		add_menu_page(
			'',//SJF_GF_Meta::get_plugin_title(),
			SJF_GF_Meta::get_plugin_title(),
			SJF_GF_Meta::get_capability(),
			__CLASS__,
			array( $this, 'the_page' ),
			SJF_GF_Meta::get_dashicon_class()
		);

		// Replace the duplicate submenu item with an empty submenu item.
		add_submenu_page(
		    __CLASS__,
		    '',
		    '',
		    SJF_GF_Meta::get_capability(),
		    __CLASS__
		);

	}

	/**
	 * A page for used for help / faq  / clearing transients, etc.
	 */
	function the_page() {
	
		// Check capability.
		$this -> accost();

		// Grab our plugin JS & CSS files.
		wp_enqueue_script( SJF_GF );
		wp_enqueue_style( SJF_GF );

		$title = SJF_GF_Meta::get_plugin_title();

		$content = $this -> get_welcome();

		$out = $this -> wrap( $title, $content );

		echo $out;

	}

	function accost() {
		if( ! current_user_can( SJF_GF_Meta::get_capability() ) ) {
			wp_die( esc_html__( 'Not Permitted', 'sjf-gf' ) );
		}
	}

	function enqueue() {
		// Grab our plugin JS & CSS files.
		wp_enqueue_script( SJF_GF );
		wp_enqueue_style( SJF_GF );
	}

	function wrap( $title, $content ) {
		
		$out = "
			<div class='wrap'>
				<h2>$title</h2>
				$content
			</div>
		";

		return $out;

	}

	/**
	 * Given a header and a body, output a section in the manner expected by our admin page.
	 * 
	 * @param  string $header The section title.
	 * @param  string $content The section content.
	 * @return string A block of HTML formatted in the manner expected by our admin page.
	 */
	function get_section( $header, $content = '' ) {

		$out = '';

		$class = SJF_GF_Formatting::get_css_class( __CLASS__, __FUNCTION__ );

		$header = wp_kses_post( $header );
		$header = "<h3 class='$class-header'>$header</h3>";
		
		if( ! empty( $content ) ) { 
			$content = "<div class='$class-content'>$content</div>";
		}

		return "
			<div class='$class'>
				$header
				$content
			</div>
		";

	}

	function get_welcome() { 

		$header  = 'welcome';
	
		$content = 'hi';

		$out = $this -> get_section( $header, $content );

		return $out;

	}

}