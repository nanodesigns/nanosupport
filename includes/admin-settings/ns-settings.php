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

/**
 * Settings page using WP Settings API.
 *
 * @link http://ottopress.com/2009/wordpress-settings-api-tutorial/
 * @link http://www.chipbennett.net/2011/02/17/incorporating-the-settings-api-in-wordpress-themes/?all=1
 * @link http://wordpress.stackexchange.com/a/127499/22728
 */
function ns_settings_page() {
    add_menu_page(
        __('NanoSupport', 'nanosupport' ),      //$page_title
        __('NanoSupport', 'nanosupport' ),      //$menu_title
        'manage_options',                       //$capability
        'nanosupport',                          //$menu_slug
        'nanosupport_page_callback',            //callback function
        null,                 //icon
        '28.5'                                      //position
    );

    add_submenu_page(
        'nanosupport',                          //$parent_slug
        __('Settings', 'nanosupport' ),         //$page_title
        __('Settings', 'nanosupport' ),         //$menu_title
        'manage_options',                       //$capability
        'nanosupport-settings',                 //$menu_slug
        'nanosupport_settings_page_callback'    //callback function
    );
}
add_action( 'admin_menu', 'ns_settings_page' );

//Page callback functions
require_once 'nanosupport-page-callback.php';
require_once 'nanosupport-settings-page-callback.php';