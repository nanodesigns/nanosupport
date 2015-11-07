<?php
/**
 * @link              http://nanodesignsbd.com/
 * @since             1.0.0
 * @package           Nanodesigns Support Ticket
 *
 * @wordpress-plugin
 * Plugin Name:       Nanodesigns Support Ticket
 * Plugin URI:        http://nst.nanodesignsbd.com/
 * Description:       Create a COMPLETE Support Center within your WordPress site
 * Version:           1.0.0
 * Author:            nanodesigns
 * Author URI:        http://nanodesignsbd.com/
 * Requires at least: 4.0
 * Tested up to:      4.3.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nanodesigns-nst
 * Domain Path:       /i18n/languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'NST' ) ) :

/**
 * Main NST Class
 *
 * @class NST
 */
final class NST {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * @var string
	 */
	public $prefix = 'nst_';

	/**
	 * @var NST The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Nano Support Ticket Instance.
	 *
	 * Ensures only one instance of Nano Support Ticket is loaded or can be loaded.
	 * 
	 * @static
	 * @see NST()
	 * @return NST - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'nst_template_path', 'NST/' );
	}
}

endif;


/**
 * Require additional files
 * 
 * @package Nano Support Ticket
 */
require_once 'includes/nst-core-functions.php';
require_once 'includes/nst-setup.php';
require_once 'includes/nst-cpt-and-taxonomy.php';
require_once 'includes/nst-metaboxes-control.php';
require_once 'includes/nst-metaboxes-responses.php';
require_once 'includes/nst-responses.php';

require_once 'includes/nst-helper-functions.php';