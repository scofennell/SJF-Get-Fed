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

		#add_action( 'my_new_event', array( $this, 'do_this_in_a_second' ), 10, 1 );

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

		add_submenu_page(
			'settings.php',
			SJF_GF_Meta::get_plugin_title(),
			SJF_GF_Meta::get_plugin_title(),
			'manage_options',
			SJF_GF,
			array( $this, 'the_admin_page' )
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

	function get_imports() { 

		$results = array();

		$header  = 'imports header';
		
		$import_all_text = esc_html__( 'Import from all sources', 'sjf-gf' );

		$import_all_href = SJF_GF_Meta::get_admin_url( array( 'source_ids' => 'all' ) );

		$content = "<a href='$import_all_href'>$import_all_text</a>";

		if( isset( $_GET['source_id'] ) ) {
			$source_ids[]=$_GET['source_id'];
		} elseif( isset( $_GET['source_ids'] ) ) {

			if( $_GET['source_ids'] == 'all' ) {
				
				$source_ids = $this -> get_source_ids();

			} 

		}

		if( isset( $source_ids ) ) {

			#wp_schedule_single_event( time() + 1, 'my_new_event', array( $source_ids  ) );
			
			foreach( $source_ids as $source_id ) {
			
				$import = new SJF_GF_Import( $source_id );

				$results[]= $import -> get();
		
			}

		}

		$results_str = '';

		foreach( $results as $source_result ) {

			foreach( $source_result as $post_result ) {
					
				$results_str .= $post_result;

			}

		}

		$previous_cron = get_option( SJF_GF . '-latest_cron' );
		if( ! empty( $previous_cron  ) ) {

			$content .= "<div><h3>previous results</h3>$previous_cron</div>";

		}

		update_option( SJF_GF . '-latest_cron', $results_str );

		$content .= "<div><h3>results</h3>$results_str</div>";

		$out = $this -> get_section( $header, $content );

		return $out;

	}

	/*function do_this_in_a_second( $source_ids ) {

		foreach( $source_ids as $source_id ) {
			
			$import = new SJF_GF_Import( $source_id );

			$import -> get();
	
		}

	}*/

	function get_source_ids() {

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'source',
			'post_status' => 'publish',

		);

		$the_query = new WP_Query( $args );

		if ( ! $the_query -> have_posts() ) { return FALSE; }
		
		while ( $the_query -> have_posts() ) {
			
			$the_query -> the_post();

			$out[]= get_the_ID();

		}

		return $out;

	}

}