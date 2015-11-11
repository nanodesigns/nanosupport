<?php
/**
 * @link              http://nanodesignsbd.com/
 * @since             1.0.0
 * @package           Nano Support Ticket
 *
 * @wordpress-plugin
 * Plugin Name:       Nano Support Ticket
 * Plugin URI:        http://nst.nanodesignsbd.com/
 * Description:       Create a fully featured Support Center within your WordPress environment without any third party software
 * Version:           1.0.0
 * Author:            nanodesigns
 * Author URI:        http://nanodesignsbd.com/
 * Requires at least: 4.0
 * Tested up to:      4.3.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       'nano-support-ticket'
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
 * -----------------------------------------------------------------------
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
 * Translation-ready
 * 
 * Make the plugin translation-ready.
 * -----------------------------------------------------------------------
 */
function nst_load_textdomain() {
    load_plugin_textdomain(
    	'nano-support-ticket',
    	FALSE,
    	dirname( plugin_basename( __FILE__ ) ) .'/i18n/languages/'
    );
}
add_action( 'init', 'nst_load_textdomain', 1 );


/**
 * Require additional files
 * 
 * @package Nano Support Ticket
 * -----------------------------------------------------------------------
 */
require_once 'includes/nst-core-functions.php';
require_once 'includes/nst-setup.php';
require_once 'includes/nst-settings.php';
require_once 'includes/nst-cpt-nanosupport.php';
require_once 'includes/nst-cpt-documentation.php';
require_once 'includes/nst-metaboxes-control.php';
require_once 'includes/nst-metaboxes-responses.php';
require_once 'includes/nst-responses.php';

require_once 'includes/shortcodes/nst-support-desk.php';
require_once 'includes/shortcodes/nst-submit-ticket.php';

require_once 'includes/nst-helper-functions.php';

require_once '__TEST.php';