<?php

/**
 * Format strings and arrays.
 *
 * @package WordPress
 * @subpackage lxb-apple-fritter
 * @since lxb-apple-fritter 0.1
 */

class SJF_GF_Formatting {

	/**
	 * Sanitize each value in a multidimensional array.
	 * 
	 * @param  mixed $mixed Any untrusted value.
	 * @return mixed The input variable, run through sanitize_text_field.
	 */
	public static function deep_sanitize_text_field( $mixed ) {

		// If it's scalar, sanitize it.
		if( is_scalar( $mixed ) ) {
			
			return sanitize_text_field( $mixed );
		
		// If it's bool, just return it.
		} elseif( is_bool( $mixed ) ) {

			return $mixed;

		// If it's null, just return it.
		} elseif( is_null( $mixed ) ) {

			return $mixed;

		// If it's an array, get recursive on it!
		} elseif( is_array( $mixed ) ) {

			$out = array();

			// For each member of the array...
			foreach( $mixed as $k => $v ) {
		
				// Pass it back through this function!
				$out[$k]= self::deep_sanitize_text_field( $v );
		
			}
			
			return $out;

		}

	}

	/**
	 * Clean empty array members out of an array.
	 * 
	 * @param  array $array An array which might have some empty members.
	 * @return array An array with no empty members.
	 */
	public static function remove_empty_array_members( $array, $index = '' ) {

		$out = array();

		if( $index = 'assoc' ) {

			foreach( $array as $k => $v ) {
			
				if( empty( $v ) ) { continue; }
				$out[$k]= $v;

			
			}

		} else {

			foreach( $array as $a ) {
			
				if( empty( $a ) ) { continue; }
				$out[]= $a;
			
			}

		}

		return $out;

	}

	/**
	 * Strip all non-alphanum chars from a string.
	 * 
	 * @param  string $string A string, unclean.
	 * @return string The string, with non-alphanum stripped.
	 */
	public static function alphanum( $string ) {
		return preg_replace( '/[^a-zA-Z0-9-]/', '', $string );
	}

	/**
	 * Strip all non-alphanum & underscore & hyphen chars from a string.
	 * 
	 * @param  string $string A string, unclean.
	 * @return string The string, with non-alphanum  & underscore & hyphen stripped.
	 */
	public static function alphanum_underscore_hyphen( $string ) {
		return preg_replace( '/[^a-zA-Z0-9-_]/', '', $string );
	}

	/**
	 * Strip all non-num & hyphen chars from a string.
	 * 
	 * @param  string $string A string, unclean.
	 * @return string The string, with non-num & hyphen.
	 */
	public static function num_hyphen( $string ) {
		return preg_replace( '/[^0-9-]/', '', $string );
	}

	/**
	 * Strip all non-num & hyphen & space & comma chars from a string.
	 * 
	 * @param  string $string A string, unclean.
	 * @return string The string, with non-num & hyphen & space & comma chars from a string stripped.
	 */
	public static function num_hyphen_space_comma( $string ) {
		return preg_replace( '/[^0-9-,\s]/', '', $string );
	}

	/**
	 * Strip all non-alphanum & underscore & hyphen & space chars from a string.
	 * 
	 * @param  string $string A string, unclean.
	 * @return string The string, with non-alphanum & underscore & hyphen & space stripped.
	 */
	public static function alphanum_underscore_hyphen_space( $string ) {
		return preg_replace( '/[^a-zA-Z0-9-\s]/', '', $string );
	}


	public static function hyphenify_class_prefix( $classes = array(), $output_as = 'array' ) {

		$out = array();

		if( is_scalar( $classes ) ) {
			$classes = explode( ' ', $classes );
		}
		if( ! is_array( $classes ) ) { return $classes; }

		$ns_upper = strtoupper( SJF_GF );
		$ns_lower = strtolower( SJF_GF );		

		foreach( $classes as $class ) {

			// Replace NAMESPACE_ with NAMESPACE-
			$this_out = str_replace( $ns_upper . '_', "$ns_upper-", $class );
			
			// Replace namespace_ with namespace-
			$this_out = str_replace( $ns_lower . '_', "$ns_lower-", $this_out );
			
			$this_out = str_replace( '-' . $ns_upper, '', $this_out );
			$this_out = str_replace( '-' . $ns_lower, '', $this_out );

			$out[]= $this_out;

		}

		if( $output_as != 'array' && is_array( $out ) ) {
			$out = implode( ' ', $out );
		}

		return $out;

	}


	/**
	 * Return a CSS class name for a given php function/method.
	 * 
	 * @param  string $class The current php class.
	 * @param  string $method The current php method.
	 * @return string A css class name.
	 */
	public static function get_css_class( $class = '', $method = '' ) {
		
		// Start our output with the name of the php class.
		$out = $class;

		// Our CSS conventions dictate a hyphen after each module, so watch out for traps like __CLASS__, where __CLASS__ is something like SJF_GF_Template_Tags.
		$out = self::hyphenify_class_prefix( $out, 'string' );

		// If both the class and method are non-empty, seperate them with an underscore.
		if( ! empty( $class ) && ! empty( $method ) ) {
			$out .= '-';
		}

		// Append the method name.
		$out .= $method;

		// Lowercase it.
		$out = strtolower( $out );

		// Sometimes we get classes with commas in them from multi-inputs.
		$out = str_replace( ',', '-', $out );

		// Sanitize it.
		$out = sanitize_html_class( $out );

		return $out;

	}

	public static function get_css_classes( $arr = array() ) {

		// Don't worry, we'll convert this to a string
		$out = array();

		// For each slug, prefix it and add it to the output.
		foreach( $arr as $class => $method ) {
			
			$out[]= self::get_css_class( $class, $method );

		}

		return $out;

	}

	/**
	 * Get the local time, formatted as per date & time in blog settings.
	 * 
	 * @return string The local time, formatted as per date & time in blog settings.
	 */
	public static function get_current_local_datetime() {
		
		// Build the date& time format.
		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$datetime_format = $date_format . ', ' . $time_format;

		// Grab the local timestamp.
		$local_time = current_time( 'timestamp' );
		
		// Format the timestamp.
		$time = date( $datetime_format, $local_time );

		return $time;

	} 

	/**
	 * Get the date & time from a timestamp, available in a variety of formats.
	 * 
	 * @todo I feel like there should be a way to localize this but I'm having trouble.
	 * 
	 * @param  int $timestamp A unix timestamp.
	 * @return string A human-readable datetime.
	 */
	public static function get_datetime( $timestamp, $format = 'blog_settings' ) {

		// Build the date & time format from the blog settings page.
		if( $format == 'blog_settings' ) {
			$date_format     = get_option( 'date_format' );
			$time_format     = get_option( 'time_format' );
			$datetime_format = $date_format . ', ' . $time_format;
		} elseif ( $format == 'html5' ) {	
			$datetime_format = 'Y-m-d h:i';
		}

		// Format the timestamp.
		$time = date( $datetime_format, $timestamp );

		return $time;

	} 

	/**
	 * Get the date from a timestamp, available in a variety of formats.
	 * 
	 * @todo I feel like there should be a way to localize this but I'm having trouble.
	 * 
	 * @param  int $timestamp A unix timestamp.
	 * @return string A human-readable date.
	 */
	public static function get_date( $timestamp, $format = 'blog_settings' ) {

		// Build the date format from the blog settings page.
		if( $format == 'blog_settings' ) {
			$date_format     = get_option( 'date_format' );	
		}

		// Format the timestamp.
		$date = date( $date_format, $timestamp );

		return $date;

	} 

}