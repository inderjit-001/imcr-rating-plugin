<?php

/**
 * Plugin Name: WP Multi-Criteria Reviews
 * Description: Multi-criteria review system. Users can review selected post types.
 * Version: 1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Inderjit Singh
 * Author URI:        https://github.com/inderjit-001
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ijs-mcr
 */

if ( !defined('ABSPATH') ) {
    exit;
}

if ( !defined('IMCR_PATH') ) {
    define('IMCR_PATH', plugin_dir_path(__FILE__));
}

if ( !defined('IMCR_URL') ) {
    define('IMCR_URL', plugin_dir_url(__FILE__));
}

if ( !defined('IMCR_PLUGIN_VERSION') ) {
    define('IMCR_PLUGIN_VERSION', '1.0.0');
}

require_once IMCR_PATH . 'includes/class-imcr.php';

// Include DB files
require_once IMCR_PATH . 'includes/db/tables.php';
register_activation_hook( __FILE__ , 'imcr_create_ratings_table');

