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

		$results = array();

		$source_obj = new SJF_GF_Source( $this -> get_source_id() );

		$posts = $source_obj -> get_posts();

		foreach( $posts as $guid => $post_arr ) {

			$results[]= $this -> maybe_import_post( $guid, $post_arr );

		}

		return $results;

	}

	function maybe_import_post( $guid, $post_arr ) {

		$results = '';

		if( $this -> post_exists( $guid ) ) {

			$results .= "<p>$guid already exists</p>";

		} else {

			$results .= "<p>$guid does not alread exist</p>";

			$post_title = $post_arr['title'];
			$post_content = $post_arr['content'];

			$author_exists = $this -> author_exists( $post_arr['author'] );
			
			if( ! $author_exists ) {

				$results .= "<p>$guid author does not already exist</p>";

				$author_id = $this -> import_author( $post_arr['author'] );

				if( ! empty( $author_id ) ) {

					$results .= "<p>$guid author added as author_id $author_id</p>";

				}

			} else {

				$results .= "<p>$guid author already exists as id $author_exists</p>";

				$author_id = $author_exists;

				add_user_to_blog( get_current_blog_id(), $author_id, 'subscriber' );

			}

			$post_excerpt = $post_arr['description'];

			$args = array(
				'post_title'            => $post_title,
				'post_content'          => $post_content,
				#'post_status'          => 'draft', 
				#'post_type'            => 'post',
				'post_author'           => $author_id,
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

			if( ! empty( $new_post_id ) ) {

				$results .= "<p>$guid was inserted as post ID $new_post_id</p>";

				add_post_meta( $new_post_id,  SJF_GF . "-guid", $guid, TRUE );

			} else {

				$results .= "<p>$guid could not be inserted</p>";

			}

		}

		return $results;

	}

	function author_exists( $author_obj ) {

		$display_name = $author_obj -> name;

		$args = array(
			'search'        => $display_name,
			'search_fields' => array( 'display_name' ),
		);
		$wp_user_query = new WP_User_Query($args);

		// Get the results
		$authors = $wp_user_query -> get_results();

		$author_count = count( $authors );

		if( empty( $author_count ) ) { return FALSE; }

		$author = $authors[0];
		return $author -> ID;

	}

	function import_author( $author_obj ) {

		$display_name = $author_obj -> name;

		$userdata = array(
			'user_login' => $display_name,
			'user_pass'  => NULL  // When creating an user, `user_pass` is expected.
		);

		return wp_insert_user( $userdata ) ;

	}

	function post_exists( $guid ) {

		// 'trash' is not included in 'any', so we have to make our own list.
		$status_array = array(
			'publish',
			'pending',
			'draft',
			'auto-draft',
			'future',
			'private',
			'inherit',
			'trash',
		);

		$args = array(
			'meta_key'   => SJF_GF . "-guid",
			'meta_value' => $guid,
			'post_status' => $status_array,
		);

		$query = new WP_Query( $args );

		if( $query -> have_posts() ) { return TRUE; }

		return FALSE;

	}

}