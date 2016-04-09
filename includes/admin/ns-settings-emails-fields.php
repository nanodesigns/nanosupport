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

// Callback: Email-specific Settings page
function ns_email_settings_section_callback() {
    echo '<p class="screen-reader-text">'. __( 'All the settings specific to Email for the Support ticketing.', 'nanosupport' ) .'</p>';
}

// Email Settings : Field 1 : Notification Email
function ns_notification_email_field() {
    $options = get_option('nanosupport_email_settings');

    $admin_email = get_option( 'admin_email' );
    $email_value = $options['notification_email'] ? $options['notification_email'] : $admin_email;

    echo '<input type="email" class="ns-field-item ns-textbox" name="nanosupport_email_settings[notification_email]" value="'. sanitize_email($email_value) .'">';

    echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Write down the email to get notification of new ticket submission. Default is, admin email.', 'nanosupport' ) .'"></span>';
}

/**
 * Validate Email Settings
 * @param  array $input  Array of all the settings fields' value.
 * @return array         Validated settings fields.
 */
function ns_email_settings_validate( $input ) {
    $options = get_option('nanosupport_email_settings');

    //Notification email
    $notification_email = $input['notification_email'] ? $input['notification_email'] : '';

    $options['notification_email'] = sanitize_email($notification_email);

    return $options;
}
