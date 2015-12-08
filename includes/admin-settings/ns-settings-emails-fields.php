<?php
/**
 * Settings: Email Settings
 *
 * Showing Email settings callback, fields, and validation.
 *
 * @author  	nanodesigns
 * @category 	Settings API
 * @package 	NanoSupport
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_email_settings_section_callback() {
    //echo "Email section";
}

function ns_email_field() {
    $options = get_option('nanosupport_email_settings');
    echo "<input name='nanosupport_email_settings[email_check]' id='email' type='checkbox' value='1' ".checked( 1, $options['email_check'], false ) . " /> <label for='email'>". __( 'Load jQuery from plugin', 'nanosupport' ) ."</label>";
}