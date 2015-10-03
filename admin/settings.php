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

		$out .= $this -> get_docs();

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

	function get_docs() {

		$header  = 'docs header';
		
		$content = 'docs content';

		if( isset( $_GET['source_id'] ) ) {
			$source_ids[]=$_GET['source_id'];
		}

		foreach( $source_ids as $source_id ) {

			$source_obj = new SJF_GF_Source( $source_id );

			$posts = $source_obj -> get_posts();

			foreach( $posts as $guid => $post_arr ) {

				$this -> maybe_import_post( $guid, $post_arr );

			}

		}

		$out = $this -> get_section( $header, $content );

		return $out;

	}

	function maybe_import_post( $guid, $post_arr ) {

		if( $this -> post_exists( $guid ) ) { return FALSE; }

		wp_die( var_dump( $post_arr ) );

		$post_title = '';
		$post_content = '';
		$post_author = '';
		$post_content_filtered = '';
		$post_excerpt = '';

		$args = array(
			'post_title'            => $post_title,
			'post_content'          => $post_content,
			#'post_status'          => 'draft', 
			#'post_type'            => 'post',
			'post_author'           => $post_author,
			#'post_parent'          => 0,
			#'menu_order'           => 0,
			#'to_ping'              =>  '',
			#'pinged'               => '',
			#'post_password'        => '',
			#'guid'                 => '',
			'post_content_filtered' => $post_content_filtered,
			'post_excerpt'          => $post_excerpt,
			#'import_id'            => 0
		);

		$new_post_id = wp_insert_post( $args );

		add_post_meta( $new_post_id,  SJF_GF . "-guid", $guid, TRUE );

	}

	function post_exists( $guid ) {

		$args = array(
			'meta_key'   => SJF_GF . "-guid",
			'meta_value' => $guid
		);

		$query = new WP_Query( $args );
	
		if( $query -> have_posts() ) { return TRUE; }

		return FALSE;

	}

}