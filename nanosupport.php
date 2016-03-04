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


if ( ! class_exists( 'NS' ) ) :

/**
 * -----------------------------------------------------------------------
 * Main NanoSupport Class
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
 * -----------------------------------------------------------------------
 * Returns the main instance of NS to prevent the need to use globals.
 * @return NS
 * -----------------------------------------------------------------------
 */
function NS() {
	return NS::instance();
}


/**
 * -----------------------------------------------------------------------
 * Cross Check Requirements when active
 *
 * Cross check for Current WordPress version is
 * greater than 3.9.0. Cross check whether the user
 * has privilege to activate_plugins, so that notice
 * cannot be visible to any non-admin user.
 *
 * @link   http://10up.com/blog/2012/wordpress-plug-in-self-deactivation/
 * 
 * @since  1.0.0
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_cross_check_things_on_activation() {
	if ( version_compare( get_bloginfo( 'version' ), '3.9.0', '<=' ) ) {

		if ( current_user_can( 'activate_plugins' ) ) {

			add_action( 'admin_init',		'ns_force_deactivate' );
			add_action( 'admin_notices',	'ns_fail_dependency_admin_notice' );

			function ns_force_deactivate() {
				deactivate_plugins( plugin_basename( __FILE__ ) );
			}

			function ns_fail_dependency_admin_notice() {
				echo '<div class="updated"><p>';
					printf( __('<strong>NanoSupport</strong> requires WordPress core version <strong>3.9.0</strong> or greater. The plugin has been <strong>deactivated</strong>. Consider <a href="%s">upgrading WordPress</a>.', 'nanosupport' ), admin_url('/update-core.php') );
				echo '</p></div>';

				if ( isset( $_GET['activate'] ) )
					unset( $_GET['activate'] );
			}

		}

	}
}

add_action( 'plugins_loaded', 'ns_cross_check_things_on_activation' );


/**
 * -----------------------------------------------------------------------
 * Initiate the plugin
 * 
 * Register all the necessary things when the plugin get activated.
 * -----------------------------------------------------------------------
 */
function nanosupport_activate() {

	$support_desk_page_id = ns_create_necessary_page(
                                    'Support Desk',                 //page title
                                    'support-desk',                 //page slug
                                    '[nanosupport_desk]'            //content (shortcode)
                                );

    //create another page to show support ticket-taking form to get the support tickets
    $submit_ticket_page_id = ns_create_necessary_page(
                                    'Submit Ticket',                //page title
                                    'submit-ticket',                //page slug
                                    '[nanosupport_submit_ticket]'   //content (shortcode)
                                );

    //create another page to show the knowledgebase
    $knowledgebase_page_id = ns_create_necessary_page(
                                    'Knowledgebase',                //page title
                                    'knowledgebase',                //page slug
                                    '[nanosupport_knowledgebase]'   //content (shortcode)
                                );

    //Update Options table with basic settings
    /*$nst_basic_options = array(
            'nst_bootstrap_check'   => 1,
            'support_desk'          => $support_desk_page_id,
            'submit_ticket'         => $submit_ticket_page_id
        );

    update_option( 'nst_basic_options', $nst_basic_options );*/
    
}

register_activation_hook( __FILE__, 'nanosupport_activate' );


/**
 * -----------------------------------------------------------------------
 * Translation-ready
 * 
 * Make the plugin translation-ready.
 *
 * @since  1.0.0
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
 * -----------------------------------------------------------------------
 * Require additional files
 *
 * Making all the features decentralized for feture-specific
 * orientation or organization of resources.
 * 
 * @package NanoSupport
 * -----------------------------------------------------------------------
 */
require_once 'includes/ns-core-functions.php';
require_once 'includes/ns-setup.php';

require_once 'includes/ns-cpt-nanosupport.php';
require_once 'includes/ns-cpt-knowledgebase.php';
require_once 'includes/ns-metaboxes-responses.php';
require_once 'includes/ns-functions.php';
require_once 'includes/ns-hooked-functions.php';

require_once 'includes/ns-email-functions.php';

require_once 'includes/shortcodes/ns-support-desk.php';
require_once 'includes/shortcodes/ns-submit-ticket.php';
require_once 'includes/shortcodes/ns-knowledgebase.php';

require_once 'includes/ns-helper-functions.php';

require_once 'includes/admin-settings/ns-settings.php';