<?php
/**
 * Settings Page
 *
 * Showing a settings page for the Plugin setup.
 *
 * @author  	nanodesigns
 * @category 	core
 * @package 	Nano Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_settings_page() {
    add_menu_page(
        __('Nano Support', 'nano-support' ),			//$page_title
        __('Nano Support', 'nano-support' ),			//$menu_title
        'manage_options',								//$capability
        'ns-settings',									//$menu_slug
        'nanosupport_settings_page_callback',			//callback function
        'dashicons-shield',								//icon
        28												//position
    );
}
add_action( 'admin_menu', 'ns_settings_page' );