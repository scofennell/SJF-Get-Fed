<?php

/**
 * A class for registering our plugin post types.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

function sjf_gf_source_init() {
	new SJF_GF_Source;
}
add_action( 'plugins_loaded', 'sjf_gf_source_init' );

class SJF_GF_Source {

	public $remote = FALSE;

	public $post_id = FALSE;

	public function __construct( $source_id = FALSE ) {

		if( $source_id ) {
			$this -> post_id = $source_id;
		} elseif( isset( $_GET['post'] ) ) {
			$this -> post_id = $_GET['post'];
		} elseif( isset( $_POST['post_ID'] ) ) {
			$this -> post_id = $_POST['post_ID'];
		}

		$this -> remote = $this -> retrieve_remote();

		add_action( 'init', array( $this, 'register' ) );

		add_filter( 'post_updated_messages', array( $this, 'messages' ) );

		add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );

		add_action( 'admin_head', array( $this, 'help_tab' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	
		add_action( 'save_post', array( $this, 'save' ) );
	
		add_filter( 'the_title', array( $this, 'filter_title' ), 10, 2 );

		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );

		register_activation_hook( __FILE__, array( $this, '	function rewrite_flush' ) );

	}
   
	public function post_row_actions( $actions, WP_Post $post ) {
	    
	    if ( $post->post_type != 'source' ) { return $actions; }

	    $post_id = get_the_ID();

	    $import_text = esc_html__( 'Import', 'sjf-gf' );

	    $import_href = $this -> get_import_href();

	    $actions[ __CLASS__ ] = "<a href='$import_href'>$import_text</a>";

	    return $actions;

	}

	public function filter_title( $title, $id ) {

		if( $this -> get_current_post_type() != 'source' ) { return $title; }

		$title = $this -> get_title( $id );

		return $title;

	}

	public function get_title( $id ) {

		return $this -> get_meta( 'title', $id );

	}

	function register() {

		$labels = array(
			'name'               => _x( 'Sources', 'post type general name', 'sjf-gf' ),
			'singular_name'      => _x( 'Source', 'post type singular name', 'sjf-gf' ),
			'menu_name'          => _x( 'Sources', 'admin menu', 'sjf-gf' ),
			'name_admin_bar'     => _x( 'Source', 'add new on admin bar', 'sjf-gf' ),
			'add_new'            => _x( 'Add New', 'source', 'sjf-gf' ),
			'add_new_item'       => __( 'Add New Source', 'sjf-gf' ),
			'new_item'           => __( 'New Source', 'sjf-gf' ),
			'edit_item'          => __( 'Edit Source', 'sjf-gf' ),
			'view_item'          => __( 'View Source', 'sjf-gf' ),
			'all_items'          => __( 'All Sources', 'sjf-gf' ),
			'search_items'       => __( 'Search Sources', 'sjf-gf' ),
			'parent_item_colon'  => __( 'Parent Sources:', 'sjf-gf' ),
			'not_found'          => __( 'No sources found.', 'sjf-gf' ),
			'not_found_in_trash' => __( 'No sources found in Trash.', 'sjf-gf' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'sjf-gf' ),
			'public'             => TRUE,
			'publicly_queryable' => TRUE,
			'show_ui'            => TRUE,
			'show_in_menu'       => TRUE,
			'query_var'          => TRUE,
			'menu_icon'           => 'dashicons-phone',
			'rewrite'            => array( 'slug' => 'source' ),
			'capability_type'    => 'post',
			'has_archive'        => TRUE,
			'hierarchical'       => FALSE,
			'menu_position'      => null,
			'supports'           => array( 'editor', 'thumbnail', 'excerpt', )
		);

		register_post_type( 'source', $args );

	}

	function messages( $messages ) {
		
		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['source'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Source updated.', 'sjf-gf' ),
			2  => __( 'Custom field updated.', 'sjf-gf' ),
			3  => __( 'Custom field deleted.', 'sjf-gf' ),
			4  => __( 'Source updated.', 'sjf-gf' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Source restored to revision from %s', 'sjf-gf' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Source published.', 'sjf-gf' ),
			7  => __( 'Source saved.', 'sjf-gf' ),
			8  => __( 'Source submitted.', 'sjf-gf' ),
			9  => sprintf(
				__( 'Source scheduled for: <strong>%1$s</strong>.', 'sjf-gf' ),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i', 'sjf-gf' ), strtotime( $post -> post_date ) )
			),
			10 => __( 'Source draft updated.', 'sjf-gf' )
		);

		if ( $post_type_object -> publicly_queryable ) {
			$permalink = get_permalink( $post -> ID );

			$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View source', 'sjf-gf' ) );
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
			$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview source', 'sjf-gf' ) );
			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}

		return $messages;
	}

	function contextual_help( $contextual_help, $screen_id, $screen ) {
	  
	  if ( 'source' == $screen->id ) {
		$contextual_help =
		  '<p>' . __('Things to remember when adding or editing a source:', 'your_text_domain') . '</p>' .
		  '<ul>' .
		  '<li>' . __('Specify the correct genre such as Mystery, or Historic.', 'your_text_domain') . '</li>' .
		  '<li>' . __('Specify the correct writer of the source.  Remember that the Author module refers to you, the author of this source review.', 'your_text_domain') . '</li>' .
		  '</ul>' .
		  '<p>' . __('If you want to schedule the source review to be published in the future:', 'your_text_domain') . '</p>' .
		  '<ul>' .
		  '<li>' . __('Under the Publish module, click on the Edit link next to Publish.', 'your_text_domain') . '</li>' .
		  '<li>' . __('Change the date to the date to actual publish this article, then click on Ok.', 'your_text_domain') . '</li>' .
		  '</ul>' .
		  '<p><strong>' . __('For more information:', 'your_text_domain') . '</strong></p>' .
		  '<p>' . __('<a href="http://codex.wordpress.org/Posts_Edit_SubPanel" target="_blank">Edit Posts Documentation</a>', 'your_text_domain') . '</p>' .
		  '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>', 'your_text_domain') . '</p>' ;
	  } elseif ( 'edit-source' == $screen->id ) {
		$contextual_help =
		  '<p>' . __('This is the help screen displaying the table of sources blah blah blah.', 'your_text_domain') . '</p>' ;
	  }
	  return $contextual_help;
	}

	function help_tab() {

	  $screen = get_current_screen();

	  // Return early if we're not on the source post type.
	  if ( 'source' != $screen->post_type )
		return;

	  // Setup help tab args.
	  $args = array(
		'id'      => 'you_custom_id', //unique id for the tab
		'title'   => 'Custom Help', //unique visible title for the tab
		'content' => '<h3>Help Title</h3><p>Help content</p>',  //actual help text
	  );
	  
	  // Add the help tab.
	  $screen->add_help_tab( $args );

	}

	public function get_meta_fields() {

		$out = array();

		$url = array(
		
			'atts' => array(
				'type'       => 'url',
				'placeholer' => esc_attr__( 'url', 'sjf-gf' ),
			),
			'label' => esc_html__( 'url', 'sjf-gf' ),
			'notes' => esc_html__( 'notes', 'sjf-gf' ),
			'sanitize_cb' => 'esc_url',
		);

		$out['url'] = $url;

		$get_url = $this -> get_url();

		//if( ! empty( $get_url ) ) {

			$title = array(
				'atts' => array(
					'type'       => 'text',
					'placeholer' => esc_attr__( 'Title', 'sjf-gf' ),
					'disabled'   => 'disabled',
				),			
				'label' => esc_html__( 'Title', 'sjf-gf' ),
				'notes' => esc_html__( 'Title', 'sjf-gf' ),
				'sanitize_cb' => 'sanitize_text_field',
				'remote_key' => 'title',
			);

			$out['title'] = $title;

			$format = array(
				'atts' => array(
					'type'       => 'text',
					'placeholer' => esc_attr__( 'Content Type', 'sjf-gf' ),
					'disabled'   => 'disabled',
				),	
				'label' => esc_html__( 'Format', 'sjf-gf' ),
				'notes' => esc_html__( 'Format', 'sjf-gf' ),
				'sanitize_cb' => 'sanitize_text_field',
				'remote_key' => 'format',
			);

			$out['format'] = $format;


		//}

		return $out;

	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
            
        if( $post_type != 'source' ) { return FALSE; }
		
		add_meta_box(
			__CLASS__,
			esc_html__( 'Some Meta Box Headline', 'sjf-gf' ),
			array( $this, 'render_meta_box_content' ),
			$post_type,
			'advanced',
			'high'
		);

	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST[ __CLASS__ ] ) ) { return $post_id; }

		$nonce = $_POST[ __CLASS__ ];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, __CLASS__ ) ) { return $post_id; }

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

		
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return $post_id; }

		/* OK, its safe for us to save the data now. */
		$fields = $this -> get_meta_fields();

		foreach( $fields as $field_key => $field ) {

			$id = __CLASS__ . "-$field_key";

			//SJF_GF_Source-url

			if( isset( $_POST[ $id ] ) ) {
				$data = sanitize_text_field( $_POST[ $id ] );

			} elseif( isset( $field['remote_key'] ) ) {
				$data = $this -> get_remote_val( $field['remote_key'] );

			} else {
				continue;
			}

			$data = sanitize_text_field( $data );

			// Update the meta field.
			update_post_meta( $post_id, $id, $data );

			if( $field_key == 'url' ) {
				$this -> remote = $this -> retrieve_remote();
			}

		}

	
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		$post_id = $post -> ID;

		$out = '';

		$nonce_field = wp_nonce_field( __CLASS__, __CLASS__, TRUE, FALSE );

		$out .= $nonce_field;

		$fields = $this -> get_meta_fields();

		foreach( $fields as $field_key => $field ) {

			$value = $this -> get_meta( $field_key );
		
			$label_text = esc_html( $field['label'] );

			$atts_str = '';

			foreach( $field['atts'] as $att_k => $att_v ) {
				$atts_str .= " $att_k='$att_v' ";
			}

			$id = __CLASS__ . "-$field_key";

			$notes = esc_html( $field['notes'] );

			$import_text = esc_html__( 'Import Posts', 'sjf-gf' );

			$import_href = $this -> get_import_href();

			$out .= "
				<div>
					<label for='$field_key'>
						$label_text
					</label>
					<input $atts_str value='$value' id='$id' name='$id' class='an-input'>
					<div>
						$notes
					</div>

				</div>
			";
		}

		$get_url = $this -> get_url();

		if( ! empty( $get_url ) ) {

			$out .= "
				<div>
					<a target='_blank' class='button button-secondary' href='$import_href' class='an-import-input'>$import_text</a>
				</div>
			";

		}

		echo $out;

	}

	function get_import_href() {
		return SJF_GF_Meta::get_admin_url( array( 'page' => 'SJF_GF_Imports', 'source_id' => $this -> get_post_id() ) );
	}

	function rewrite_flush() {
		flush_rewrite_rules();
	}

	function get_url() {

		return $this -> get_meta( 'url' );
	}

	function retrieve_remote() {

		$url = $this -> get_url();

		$call = new SJF_GF_Call( $url );

		$get = $call -> get();

		return $get;

	}

	function get_remote_val( $key ) {

		$remote = $this -> get_remote();

		$is_a = get_class( $remote );

		if( $is_a == 'SimplePie' ) {
			
			$content_type = 'application/rss+xml; charset=UTF-8';

		} 

		if( $key == 'format' ) {
			return $content_type;
		
		} elseif( $key == 'title' ) {
			return $remote -> get_title();
	
		} elseif( $key == 'posts' ) {
			
			$posts_arr = array();

			$items = $remote -> get_items();
	
			foreach( $items as $item ) {

				$guid =  $item -> get_id();

				$this_post = array();

				$this_post['author'] = $item -> get_author();
				$this_post['authors'] = $item -> get_authors();
				$this_post['category'] = $item -> get_category();
				$this_post['category'] = $item -> get_categories();
				$this_post['content'] = $item -> get_content();
				$this_post['date'] = $item -> get_date();
				$this_post['link'] = $item -> get_link();
				$this_post['title'] = $item -> get_title();
				$this_post['description'] = $item -> get_description();

				$posts_arr[ $guid ]= $this_post; 

			}

			return $posts_arr;

		} 
	}

	function get_remote() {
		return $this -> remote;
	}


	function get_post_id() {

		return $this -> post_id;
	
	}

	function get_meta( $key, $post_id = FALSE ) {

		if( ! $post_id ) { $post_id = $this -> get_post_id(); }

		$key = __CLASS__ . "-$key";

		return get_post_meta( $post_id, $key, TRUE );

	}

	function get_posts() {

		$posts = $this -> get_remote_val( 'posts' );

		return $posts;

	}

	function get_current_post_type() {
	  global $post, $typenow, $current_screen;
		
	  //we have a post so we can just get the post type from that
	  if ( $post && $post->post_type )
	    return $post->post_type;
	    
	  //check the global $typenow - set in admin.php
	  elseif( $typenow )
	    return $typenow;
	    
	  //check the global $current_screen object - set in sceen.php
	  elseif( $current_screen && $current_screen->post_type )
	    return $current_screen->post_type;
	  
	  //lastly check the post_type querystring
	  elseif( isset( $_REQUEST['post_type'] ) )
	    return sanitize_key( $_REQUEST['post_type'] );
		
	  //we do not know the post type!
	  return null;
	}

}