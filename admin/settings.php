<?php

/**
 * The admin settings screen.
 * 
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

function sjf_gf_settings_init() {
	new SJF_GF_Settings();
}
add_action( 'init', 'sjf_gf_settings_init' );

class SJF_GF_Settings {

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
			SJF_GF_Meta::get_plugin_title(),
			SJF_GF_Meta::get_plugin_title(),
			SJF_GF_Meta::get_capability(),
			SJF_GF,
			array( $this, 'the_admin_page' ),
			SJF_GF_Meta::get_dashicon_class()
		);

	}

	/**
	 * A page for used for help / faq  / clearing transients, etc.
	 */
	function the_admin_page() {
	
		// Check capability.
		if( ! current_user_can( SJF_GF_Meta::get_capability() ) ) { return false; }

		// Grab our plugin JS & CSS files.
		wp_enqueue_script( SJF_GF );
		wp_enqueue_style( SJF_GF );

		$out = '';

		$title = SJF_GF_Meta::get_plugin_title();

		$out .= $this -> get_imports();

		$out = "
			<div class='wrap'>
				<h2>$title</h2>
				$out
			</div>
		";

		echo $out;

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