<?php

/**
 * A class for grabbing data about our plugin.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Meta {

	public static function get_plugin_title() {
		return esc_html__( 'SJF Get Fed', 'sjf-gf' );
	}

	public static function get_dashicon_class() {
		return 'dashicons-rss';
	}

	public static function get_capability() {
		return 'edit_posts';
	}

}