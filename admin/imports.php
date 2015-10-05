<?php

/**
 * The admin imports screen.
 * 
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

function sjf_gf_imports_init() {
	new SJF_GF_Imports();
}
add_action( 'init', 'sjf_gf_imports_init' );

class SJF_GF_Imports extends SJF_GF_Admin  {

	public $source_ids = '';

	/**
	 * Adds actions for our class methods.
	 */
	function __construct() {    
		
		// Add our menu item.
		add_action( 'admin_menu', array( $this, 'admin_menu_tab' ) );

		if( isset( $_GET['source_id'] ) ) {
			$this -> source_ids = array( absint( $_GET['source_id'] ) );
		}

		if( isset( $_GET['source_ids'] ) ) {

			$source_ids = sanitize_text_field( $_GET['source_ids'] );

			if( $source_ids == 'all' ) {

				$sources = new SJF_GF_Sources;
				$source_ids = $sources -> get_ids();

			} else {

				$source_ids = explode( ',', $source_ids );
	
			}

			$this -> source_ids = $source_ids;

		}

	}

	function get_source_ids() {
		return $this -> source_ids;
	}

	/**
	 * Add a menu item for our plugin.
	 */
	function admin_menu_tab() {

		add_submenu_page(
			get_parent_class(),
			__CLASS__,
			esc_attr__( 'Import Posts', 'sjf-gf' ),
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

		// Grab our plugin JS & CSS files.
		$this -> enqueue();

		$title = __CLASS__;

		$content = $this -> get_imports();

		$out = $this -> wrap( __CLASS__, $content );

		echo $out;

	}

	function get_import_all_link() {

		$import_all_text = esc_html__( 'Import from all sources', 'sjf-gf' );

		$import_all_href = SJF_GF_Meta::get_admin_url( array( 'page' => __CLASS__, 'source_ids' => 'all' ) );

		return "<a href='$import_all_href'>$import_all_text</a>";

	}

	function get_imports() { 

		$header  = 'imports header';
		
		$content = $this -> get_import_all_link();

		$content .= $this -> run_imports();

		$out = $this -> get_section( $header, $content );

		return $out;

	}

	function run_imports() {

		$source_ids = $this -> get_source_ids();

		if( ! is_array( $source_ids ) ) { return FALSE; }

		$results = array();
	
		foreach( $source_ids as $source_id ) {
			
			$import = new SJF_GF_Import( $source_id );

			$results[ $source_id ]= $import -> get();
		
		}

		$result = new SJF_GF_Result( $results );
		$result -> update();
		$result_str = $result -> get_report();

		return "<div><h3>results</h3>$result_str</div>";

	}

}