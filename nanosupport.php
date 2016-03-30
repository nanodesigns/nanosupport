<?php
/**
 * @link              http://nanodesignsbd.com/
 * @since             1.0.0
 * @package           NanoSupport
 *
 * @wordpress-plugin
 * Plugin Name:       NanoSupport
 * Plugin URI:        http://ns.nanodesignsbd.com/
 * Description:       Create a fully featured Support Center within your WordPress environment without any third party software, completely FREE
 * Version:           1.0.0
 * Author:            nanodesigns
 * Author URI:        http://nanodesignsbd.com/
 * Requires at least: 3.9.0
 * Tested up to:      4.4.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       'nanosupport'
 * Domain Path:       /i18n/languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'NanoSupport' ) ) :

/**
 * -----------------------------------------------------------------------
 * Main NanoSupport Class
 *
 * @class NanoSupport
 * -----------------------------------------------------------------------
 */
final class NanoSupport {

	/**
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * @var string
	 */
	public $php_version = '5.0.2'; //?

	/**
	 * @var string
	 */
	public $wp_version = '3.9.0';

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
	 * @return NanoSupport - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->ns_includes();
		$this->init();

		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_loaded
		 * 
		 * NanoSupport Plugin is successfully loaded.
		 *
		 * @since  1.0.0
		 * -----------------------------------------------------------------------
		 */
		//do_action( 'nanosupport_loaded' );
	}

	/**
	 * Define necessary constants
	 * @return void
	 */
	private function define_constants() {
		$this->ns_define( 'NS_PLUGIN_FILE', __FILE__ );
		$this->ns_define( 'NS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	}

	/**
	 * Define constant if not yet set.
	 *
	 * @param string 		$name
	 * @param string|bool 	$value
	 */
	private function ns_define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Require additional files
	 *
	 * Making all the features decentralized for feture-specific
	 * orientation or organization of resources.
	 */
	public function ns_includes() {
		/** Classes **/
		include_once( 'includes/class-ns-install.php' );

		/** Core Functions **/
		include_once( 'includes/ns-core-functions.php' );
		/** Functions specific to setup the environments **/
		include_once( 'includes/ns-setup.php' );

		/** CPT Tickets **/
		include_once( 'includes/ns-cpt-nanosupport.php' );
		/** CPT Knowledgebase **/
		include_once( 'includes/ns-cpt-knowledgebase.php' );
		/** Metaboxes: Responses **/
		include_once( 'includes/ns-metaboxes-responses.php' );
		/** Miscellaneous functions **/
		include_once( 'includes/ns-functions.php' );

		/** Handling emails **/
		include_once( 'includes/ns-email-functions.php' );

		/** Shortcode: Support Desk **/
		include_once( 'includes/shortcodes/ns-support-desk.php' );
		/** Shortcode: Submit Ticket **/
		include_once( 'includes/shortcodes/ns-submit-ticket.php' );
		/** Shortcode: Knowledgebase **/
		include_once( 'includes/shortcodes/ns-knowledgebase.php' );

		/** Helper functions **/
		include_once( 'includes/ns-helper-functions.php' );

		/** Settings API **/
		include_once( 'includes/admin/ns-settings.php' );
	}

	public function init() {
		register_activation_hook( __FILE__, array('NS_Install', 'install') );
		add_action( 'init', array( $this, 'ns_load_textdomain' ), 1 );
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

	/**
	 * Translation-ready
	 * Make the plugin translation-ready.
	 *
	 * @since  1.0.0
	 */
	public function ns_load_textdomain() {

		/**
		 * -----------------------------------------------------------------------
		 * WP FILTER HOOK
		 * plugin_locale
		 *
		 * WordPress' core filter hook to filter a plugin's locale.
		 *
		 * @link   https://developer.wordpress.org/reference/hooks/plugin_locale/
		 *
		 * @param string $locale The plugin's current locale.
		 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
		 * -----------------------------------------------------------------------
		 */
		$locale = apply_filters( 'plugin_locale', get_locale(), 'nanosupport' );
		
		load_textdomain(
			'nanosupport',
			WP_LANG_DIR .'/nanosupport/nanosupport-'. $locale .'.mo'
		);

	    load_plugin_textdomain(
	    	'nanosupport',
	    	false,
	    	dirname( plugin_basename( __FILE__ ) ) .'/i18n/languages/'
	    );
	}
}

endif;

/**
 * -----------------------------------------------------------------------
 * Returns the main instance of NanoSupport to prevent the need to use globals.
 * @return NS
 * -----------------------------------------------------------------------
 */
function NS() {
	return NanoSupport::instance();
}

define( 'NS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
/** Classes **/
include_once( 'includes/class-ns-install.php' );

/** Core Functions **/
include_once( 'includes/ns-core-functions.php' );
/** Functions specific to setup the environments **/
include_once( 'includes/ns-setup.php' );

/** CPT Tickets **/
include_once( 'includes/ns-cpt-nanosupport.php' );
/** CPT Knowledgebase **/
include_once( 'includes/ns-cpt-knowledgebase.php' );
/** Metaboxes: Responses **/
include_once( 'includes/ns-metaboxes-responses.php' );
/** Miscellaneous functions **/
include_once( 'includes/ns-functions.php' );

/** Handling emails **/
include_once( 'includes/ns-email-functions.php' );

/** Shortcode: Support Desk **/
include_once( 'includes/shortcodes/ns-support-desk.php' );
/** Shortcode: Submit Ticket **/
include_once( 'includes/shortcodes/ns-submit-ticket.php' );
/** Shortcode: Knowledgebase **/
include_once( 'includes/shortcodes/ns-knowledgebase.php' );

/** Helper functions **/
include_once( 'includes/ns-helper-functions.php' );

/** Settings API **/
include_once( 'includes/admin/ns-settings.php' );