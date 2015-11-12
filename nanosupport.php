<?php
/**
 * @link              http://nanodesignsbd.com/
 * @since             1.0.0
 * @package           NanoSupport
 *
 * @wordpress-plugin
 * Plugin Name:       NanoSupport
 * Plugin URI:        http://ns.nanodesignsbd.com/
 * Description:       Create a fully featured Support Center within your WordPress environment without any third party software
 * Version:           1.0.0
 * Author:            nanodesigns
 * Author URI:        http://nanodesignsbd.com/
 * Requires at least: 4.0
 * Tested up to:      4.3.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       'nanosupport'
 * Domain Path:       /i18n/languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'NS' ) ) :

/**
 * Main NS Class
 *
 * @class NS
 * -----------------------------------------------------------------------
 */
final class NS {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * @var string
	 */
	public $prefix = 'ns_';

	/**
	 * @var NS The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main NanoSupport Instance.
	 *
	 * Ensures only one instance of NanoSupport Ticket is loaded or can be loaded.
	 * 
	 * @static
	 * @see NS()
	 * @return NS - Main instance
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
		return apply_filters( 'ns_template_path', 'NS/' );
	}
}

endif;


/**
 * Translation-ready
 * 
 * Make the plugin translation-ready.
 * -----------------------------------------------------------------------
 */
function ns_load_textdomain() {
    load_plugin_textdomain(
    	'nanosupport',
    	FALSE,
    	dirname( plugin_basename( __FILE__ ) ) .'/i18n/languages/'
    );
}
add_action( 'init', 'ns_load_textdomain', 1 );


/**
 * Require additional files
 * 
 * @package NanoSupport
 * -----------------------------------------------------------------------
 */
require_once 'includes/ns-core-functions.php';
require_once 'includes/ns-setup.php';
require_once 'includes/ns-settings.php';
require_once 'includes/ns-cpt-nanosupport.php';
require_once 'includes/ns-cpt-knowledgebase.php';
require_once 'includes/ns-metaboxes-control.php';
require_once 'includes/ns-metaboxes-responses.php';
require_once 'includes/ns-responses.php';

require_once 'includes/shortcodes/ns-support-desk.php';
require_once 'includes/shortcodes/ns-submit-ticket.php';

require_once 'includes/ns-helper-functions.php';

require_once '__TEST.php';