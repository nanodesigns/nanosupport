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
 *
 * @since   1.0.0
 *
 * @return  void
 * -----------------------------------------------------------------------
 */
function nanosupport_activate() {

	$support_desk_page_id = ns_create_page(
                                'Support Desk',                 //page title
                                'support-desk',                 //page slug
                                '[nanosupport_desk]'            //content (shortcode)
                            );

    //create another page to show support ticket-taking form to get the support tickets
    $submit_ticket_page_id = ns_create_page(
                                'Submit Ticket',                //page title
                                'submit-ticket',                //page slug
                                '[nanosupport_submit_ticket]'   //content (shortcode)
                            );

    //create another page to show the knowledgebase
    $knowledgebase_page_id = ns_create_page(
                                'Knowledgebase',                //page title
                                'knowledgebase',                //page slug
                                '[nanosupport_knowledgebase]'   //content (shortcode)
                            );

    /**
     * Set up the default Settings
     * ...
     */
    
    // General Settings
    $ns_gen_settings = array(
            'bootstrap'   	=> 1,
            'support_desk'	=> $support_desk_page_id,
            'submit_page'	=> $submit_ticket_page_id
        );
    update_option( 'nanosupport_settings', $ns_gen_settings );

    // Knowledgebase settings
    $ns_kb_settings = array(
            'page'   		=> $knowledgebase_page_id,
            'terms'			=> array(''),
            'ppc'			=> get_option( 'posts_per_page' )
        );
    update_option( 'nanosupport_knowledgebase_settings', $ns_kb_settings );

    // Email settings
    $ns_email_settings = array(
            'notification_email'	=> get_option( 'admin_email' )
        );
    update_option( 'nanosupport_email_settings', $ns_email_settings );

    /**
     * Update db version to current
     */
    delete_option( 'nanosupport_version' );
	add_option( 'nanosupport_version', NS()->version );
    
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
 * Add Settings link on plugin page
 *
 * Add a 'Settings' link to the Admin Plugin page after the activation
 * of the plugin. So the user can easily get to the Settings page, and
 * can setup the plugin as necessary.
 *
 * @since  1.0.0
 * 
 * @param  array $links  Links on the plugin page per plugin.
 * @return array         Modified with our link.
 * -----------------------------------------------------------------------
 */
function ns_plugin_settings_link( $links ) {
  //$settings_link = '/wp-admin/edit.php?post_type=nanosupport&page=nanosupport-settings';
  $settings_link = '<a href="'. esc_url( admin_url( 'edit.php?post_type=nanosupport&page=nanosupport-settings' ) ) .'" title="'. esc_attr__( 'Set the NanoSupport settings', 'nanosupport' ) .'">'. __( 'Settings', 'nanosupport' ) .'</a>';

  array_unshift($links, $settings_link); //make the settings link be first item
  return $links;
}

add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), 'ns_plugin_settings_link' );


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
/** Core Functions **/
require_once 'includes/ns-core-functions.php';
/** Functions specific to setup the environments **/
require_once 'includes/ns-setup.php';

/** CPT Tickets **/
require_once 'includes/ns-cpt-nanosupport.php';
/** CPT Knowledgebase **/
require_once 'includes/ns-cpt-knowledgebase.php';
/** Metaboxes: Responses **/
require_once 'includes/ns-metaboxes-responses.php';
/** Miscellaneous functions **/
require_once 'includes/ns-functions.php';

/** Handling emails **/
require_once 'includes/ns-email-functions.php';

/** Shortcode: Support Desk **/
require_once 'includes/shortcodes/ns-support-desk.php';
/** Shortcode: Submit Ticket **/
require_once 'includes/shortcodes/ns-submit-ticket.php';
/** Shortcode: Knowledgebase **/
require_once 'includes/shortcodes/ns-knowledgebase.php';

/** Helper functions **/
require_once 'includes/ns-helper-functions.php';

/** Settings API **/
require_once 'includes/admin/ns-settings.php';
