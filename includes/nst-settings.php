<?php
/**
 * Settings Page
 *
 * Showing a settings page for the Plugin setup.
 *
 * @author  	nanodesigns
 * @category 	core
 * @package 	Nano Support Ticket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nst_settings_page() {
    add_menu_page(
        __('Nano Support Ticket', 'nano-support-ticket' ),  //$page_title
        __('NST', 'nano-support-ticket' ),                  //$menu_title
        'manage_options',                                   //$capability
        'nst-settings',                                     //$menu_slug
        'nanosupport_settings_page_callback',				//callback function
        'dashicons-shield',                					//icon
        28                									//position
    );
}
add_action( 'admin_menu', 'nst_settings_page' );