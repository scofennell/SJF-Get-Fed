<?php

/**
 * A class for cron functions.
 *
 * @package WordPress
 * @subpackage sjf-gf
 * @since SJF Get Fed 0.1
 */

class SJF_GF_Cron {

	public function __constuct() {
		
		register_activation_hook(__FILE__, array( $this, 'my_activation' ) );
		add_action('my_hourly_event', array( $this, 'do_this_hourly' ) );

		register_deactivation_hook(__FILE__, array( $this, 'my_deactivation' ) );

	}

	function my_activation() {
		wp_schedule_event(time(), 'hourly', 'my_hourly_event');
	}

	function do_this_hourly() {
		// do something every hour
	}

	function my_deactivation() {
		wp_clear_scheduled_hook('my_hourly_event');
	}

}