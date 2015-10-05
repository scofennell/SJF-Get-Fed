<?php
/**
 * Plugin Name: SJF Get Fed
 * Plugin URI: http://scottfennell.org
 * Description: Grabs content from urls.
 * Version: 0.1
 * Author: Scott Fennell
 * Author URI: http://scottfennell.org
 * Text Domain: sjf-gf
 * Domain Path: /lang
 */

/* 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

// Establish a value for plugin version to bust file caches.
define( 'SJF_GF_VERSION', '0.1' );

// A constant to define the paths to our plugin folders.
define( 'SJF_GF', 'sjf_gf' );
define( 'SJF_GF_FILE', __FILE__ );
define( 'SJF_GF_PATH', trailingslashit( plugin_dir_path( SJF_GF_FILE ) ) );
define( 'SJF_GF_ADMIN_PATH', SJF_GF_PATH . 'admin/' );
define( 'SJF_GF_INC_PATH', SJF_GF_PATH . 'inc/' );

// A constant to define the urls to our plugin folders.
define( 'SJF_GF_URL', trailingslashit( plugin_dir_url( SJF_GF_FILE ) ) );
define( 'SJF_GF_ADMIN_URL', SJF_GF_URL . 'admin/' );
define( 'SJF_GF_INC_URL', SJF_GF_URL . 'inc/' );

/**
 * Require files for both wp-admin & front end.
 */

require_once( SJF_GF_INC_PATH . 'formatting.php' );

require_once( SJF_GF_INC_PATH . 'call.php' );

require_once( SJF_GF_INC_PATH . 'cron.php' );

require_once( SJF_GF_INC_PATH . 'import.php' );

require_once( SJF_GF_INC_PATH . 'result.php' );

// Information about our plugin.
require_once( SJF_GF_INC_PATH . 'meta.php' );

// Register post type.
require_once( SJF_GF_INC_PATH . 'source.php' );

require_once( SJF_GF_INC_PATH . 'sources.php' );

// Register settings page.
require_once( SJF_GF_ADMIN_PATH . 'admin.php' );
require_once( SJF_GF_ADMIN_PATH . 'imports.php' );
require_once( SJF_GF_ADMIN_PATH . 'results.php' );