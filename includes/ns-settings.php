<?php
/**
 * Settings Page
 *
 * Showing a settings page for the Plugin setup.
 *
 * @author  	nanodesigns
 * @category 	core
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_settings_page() {
    add_menu_page(
        __('NanoSupport', 'nano-support' ),     //$page_title
        __('NanoSupport', 'nano-support' ),     //$menu_title
        'manage_options',                       //$capability
        'ns-settings',                          //$menu_slug
        'nanosupport_settings_page_callback',   //callback function
        'dashicons-shield',                     //icon
        28                                      //position
    );
}
add_action( 'admin_menu', 'ns_settings_page' );

function nanosupport_settings_page_callback() {
?>
    <div class="wrap">
        <h2><?php _e( 'NanoSupport Settings', 'nano-support' ); ?></h2>
    </div>
<?php
}