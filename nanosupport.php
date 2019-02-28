<?php
/**
 * @package           NanoSupport
 * @author            nanodesigns <info@nanodesignsbd.com>
 * @license           GPL-2.0+
 * @link              https://nanodesignsbd.com/
 *
 * @wordpress-plugin
 * Plugin Name:       NanoSupport
 * Plugin URI:        https://nanosupport.nanodesignsbd.com/
 * Description:       Create a fully featured Support Center within your WordPress environment without any third party system dependency, completely FREE. The built-in Knowledgebase is to inform public with generalized queries.
 * Version:           0.6.0
 * Author:            nanodesigns
 * Author URI:        https://nanodesignsbd.com/
 * Requires at least: 4.4.0
 * Tested up to:      5.1
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nanosupport
 * Domain Path:       /i18n/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Translation-ready
 * Make the plugin translation-ready.
 *
 * Note:
 * the first-loaded translation file overrides any
 * following ones if the same translation is present.
 *
 * Locales found in:
 *      - WP_LANG_DIR/nanosupport/nanosupport-LOCALE.mo
 *      - WP_LANG_DIR/plugins/nanosupport-LOCALE.mo
 */
function ns_load_textdomain() {

	/**
	 * -----------------------------------------------------------------------
	 * WP FILTER HOOK
	 * plugin_locale
	 *
	 * WordPress' core filter hook to filter a plugin's locale.
	 *
	 * @link   https://developer.wordpress.org/reference/hooks/plugin_locale/
	 *
	 * @param  string $locale The plugin's current locale.
	 * @param  string $domain Text domain. Unique identifier for retrieving translated strings.
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
		dirname( plugin_basename( __FILE__ ) ) .'/i18n/languages'
	);
}


if ( ! class_exists( 'NanoSupport' ) ) :

/**
 * Main NanoSupport Class
 *
 * @class NanoSupport
 * -----------------------------------------------------------------------
 */
final class NanoSupport {

	/**
	 * @var string
	 */
	public $plugin = 'NanoSupport';

	/**
	 * @var string
	 */
	public $version = '0.6.0';

	/**
	 * Minimum WordPress version.
	 * @var string
	 */
	public $wp_version = '4.4.0';

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
	 * Get the plugin base location.
	 * @return string
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		/**
		 * -----------------------------------------------------------------------
		 * HOOK : FILTER HOOK
		 * ns_template_path
		 *
		 * The template path used in theme to override.
		 *
		 * @since  1.0.0
		 * -----------------------------------------------------------------------
		 */
		return apply_filters( 'ns_template_path', 'nanosupport/' );
	}


}

endif;

/**
 * Returns the main instance of NanoSupport.
 * @return NS
 * -----------------------------------------------------------------------
 */
function NS() {
	return NanoSupport::instance();
}


/**
 * Cross Check Requirements when active
 *
 * Cross check for Current WordPress version is
 * greater than required. Cross check whether the user
 * has privilege to `activate_plugins`, so that notice
 * cannot be visible to any non-admin user.
 *
 * @link   https://10up.com/blog/2012/wordpress-plug-in-self-deactivation/
 * -----------------------------------------------------------------------
 */
function ns_cross_check_on_activation() {
	$unmet = false;

	if ( current_user_can( 'activate_plugins' ) ) :

		if ( ! ns_is_version_supported() ) {
			$unmet = true;
			add_action( 'admin_notices', 'ns_fail_version_admin_notice' );
		}

		if ( ! ns_is_dependency_loaded() ) {
			$unmet = true;
			add_action( 'admin_notices', 'ns_fail_dependency_admin_notice' );
		}

		if( $unmet ) {

			add_action( 'admin_init', 'ns_force_deactivate' );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

		}

	endif;
}

add_action( 'plugins_loaded', 'ns_cross_check_on_activation' );


/**
 * Add Settings link on plugin page
 *
 * Add a 'Settings' link to the Admin Plugin page after the activation
 * of the plugin. So the user can easily get to the Settings page, and
 * can setup the plugin as necessary.
 *
 * @param  array $links  Links on the plugin page per plugin.
 * @return array         Modified with our link.
 * -----------------------------------------------------------------------
 */
function ns_plugin_settings_link( $links ) {
	// '/wp-admin/edit.php?post_type=nanosupport&page=nanosupport-settings';
	$settings_link = '<a href="'. esc_url( admin_url( 'edit.php?post_type=nanosupport&page=nanosupport-settings' ) ) .'" title="'. esc_attr__( 'Set the NanoSupport settings', 'nanosupport' ) .'">'. __( 'Settings', 'nanosupport' ) .'</a>';

	array_unshift( $links, $settings_link ); //make the settings link be first item
	return $links;
}

add_filter( 'plugin_action_links_'. plugin_basename( __FILE__ ), 'ns_plugin_settings_link' );



/**
 * Variables & Constants
 *
 * Define necessary variables to not to DRY. The global variables
 * are declared global to make 'em available in plugin_activation.
 * -----------------------------------------------------------------------
 */
global $ns_submit_ticket_notice, $ns_support_desk_notice, $ns_knowledgebase_notice;

//Top Navigation messages
$ns_submit_ticket_notice = __( 'Consult the Knowledgebase for your query. If they are not close to you, then submit a new ticket here.', 'nanosupport' );
$ns_support_desk_notice = __( 'Tickets are visible to the admins, designated support assistant and/or to the ticket owner only.', 'nanosupport' );
$ns_knowledgebase_notice = __( 'Find your desired question in the knowledgebase. If you can&rsquo;t find your question, submit a new support ticket.', 'nanosupport' );


/**
 * Include all the dependencies and particles
 *
 * Is to decentralize things to make things managable.
 */
/** Install the plugin **/
include_once 'includes/ns-install.php';

/** Core Functions **/
include_once 'includes/ns-core-functions.php';
/** Functions specific to setup the environments **/
include_once 'includes/ns-set-environment.php';

/** CPT Tickets **/
include_once 'includes/ns-cpt-nanosupport.php';
/** CPT Knowledgebase **/
include_once 'includes/ns-cpt-knowledgebase.php';
/** Metaboxes **/
include_once 'includes/ns-metaboxes.php';
/** Miscellaneous functions **/
include_once 'includes/ns-functions.php';

/** E-Commerce Support */
include_once 'includes/class-ecommerce.php';

/** Handling emails **/
include_once 'includes/ns-email-functions.php';

/** Shortcode: Support Desk **/
include_once 'includes/shortcodes/ns-support-desk.php';
/** Shortcode: Submit Ticket **/
include_once 'includes/shortcodes/ns-submit-ticket.php';
/** Shortcode: Knowledgebase **/
include_once 'includes/shortcodes/ns-knowledgebase.php';

/** Dashboard **/
include_once 'includes/ns-dashboard.php';

/** Changelog **/
include_once 'includes/class-ns-ticket-changelog.php';

/** Helper functions **/
include_once 'includes/ns-utility-functions.php';

/** Settings API **/
include_once 'includes/admin/ns-settings.php';

/** System Status page **/
include_once 'includes/class-system-status.php';
include_once 'includes/admin/ns-system-status.php';

/** NanoSupport Updates */
include_once 'includes/ns-updates.php';

/**
 * Set the plugin up
 */
register_activation_hook( __FILE__, 'nanosupport_install' );
add_action( 'init', 'ns_load_textdomain', 1 );

$NSECommerce = new NSECommerce();
if( $NSECommerce->ecommerce_enabled() ) {
	add_filter( 'woocommerce_prevent_admin_access', array( 'NSECommerce', 'wc_agent_admin_access' ), 20 );
	add_filter( 'show_admin_bar', array( 'NSECommerce', 'wc_agent_show_admin_bar' ), 20 );
}
