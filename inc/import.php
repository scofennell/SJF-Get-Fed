<?php

/**
 * A class for importing posts from a source.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Import {

	public $source_id = FALSE;

	public function __construct( $source_id ) {

		$this -> source_id = $source_id;

	}

	function get_source_id() {
		return $this -> source_id;
	}

	function get() {

		$source_obj = new SJF_GF_Source( $this -> get_source_id() );

		$posts = $source_obj -> get_posts();

		foreach( $posts as $guid => $post_arr ) {

			$this -> maybe_import_post( $guid, $post_arr );

		}

	}

	function maybe_import_post( $guid, $post_arr ) {

		if( $this -> post_exists( $guid ) ) { return FALSE; }

		$post_title = $post_arr['title'];
		$post_content = $post_arr['content'];
		$post_author = $this -> get_author_id( $post_arr['author'] );
		$post_excerpt = $post_arr['description'];

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
			'post_excerpt'          => $post_excerpt,
			#'import_id'            => 0
		);

		$new_post_id = wp_insert_post( $args );

		add_post_meta( $new_post_id,  SJF_GF . "-guid", $guid, TRUE );

		var_dump( get_post( $new_post_id ) );

	}

	function get_author_id( $author_obj ) {

		$display_name = $author_obj -> name;

		$args = array(
			'search'        => $display_name,
			'search_fields' => array( 'display_name' ),
		);
		$wp_user_query = new WP_User_Query($args);

		// Get the results
		$authors = $wp_user_query -> get_results();

		if( ! empty( $authors ) ) {
			$author = $authors[0];
			return $author -> ID;
		} else {
			$userdata = array(
   				'user_login' => $display_name,
    			'user_pass'  => NULL  // When creating an user, `user_pass` is expected.
			);
			return wp_insert_user( $userdata ) ;
		}

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