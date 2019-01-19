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

    echo '<input type="email" class="ns-field-item ns-textbox" name="nanosupport_email_settings[notification_email]" id="ns-email-field" value="'. sanitize_email($email_value) .'" aria-describedby="ns-notification-mail">';

    echo ns_tooltip( 'ns-notification-mail', __( 'Write down the email to get notification of new ticket submission. Default is, admin email.', 'nanosupport' ), 'right' );
}

// Email Settings : Field 2 : Email Choices
function ns_email_choices_field() {
    $options = get_option('nanosupport_email_settings');

    $new_ticket_notification = isset($options['email_choices']['new_ticket']) ? $options['email_choices']['new_ticket'] : '';
    echo '<input name="nanosupport_email_settings[email_choices][new_ticket]" id="new_ticket" type="checkbox" value="1" aria-describedby="ns-new-ticket" '. checked( 1, $new_ticket_notification, false ) . '/> <label for="new_ticket">'. __( 'Yes, send an email to admin on new ticket submission', 'nanosupport' ) .'</label>';
    echo ns_tooltip( 'ns-new-ticket', wp_kses( __( 'If you check here, an email will be sent to Notification email notifying about every new ticket submission. Default: <code aria-label="true">true</code>.', 'nanosupport' ), array('code' => array('aria-label'=> array())) ), 'right' );

    echo '<br><br>';

    $response_notification = isset($options['email_choices']['response']) ? $options['email_choices']['response'] : '';
    echo '<input name="nanosupport_email_settings[email_choices][response]" id="response" type="checkbox" value="1" aria-describedby="ns-email-response" '. checked( 1, $response_notification, false ) . '/> <label for="response">'. __( 'Yes, notify the ticket author when their ticket is replied', 'nanosupport' ) .'</label>';
    echo ns_tooltip( 'ns-email-response', wp_kses( __( 'If you check here, an email will be sent to the ticket author when their ticket is replied. Default: <code aria-label="true">true</code>.', 'nanosupport' ), array('code' => array('aria-label'=> array())) ), 'right' );

    echo '<br><br>';

    $agent_response_notification = isset($options['email_choices']['agent_response']) ? $options['email_choices']['agent_response'] : '';
    echo '<input name="nanosupport_email_settings[email_choices][agent_response]" id="agent_response" type="checkbox" value="1" aria-describedby="ns-agent-response" '. checked( 1, $agent_response_notification, false ) . '/> <label for="agent_response">'. __( 'Yes, notify the assigned agent when their ticket is replied', 'nanosupport' ) .'</label>';
    echo ns_tooltip( 'ns-agent-response', wp_kses( __( 'If you check here, an email will be sent to the agent who is assigned to the ticket when their ticket is replied. Default: <code aria-label="true">true</code>.', 'nanosupport' ), array('code' => array('aria-label'=> array())) ), 'right' );
}


// Callback: Email Template-specific Settings page
function ns_email_template_section_callback() {
    $message = '<p>';
        $message .= __( 'Control the Email template settings from here', 'nanosupport' );
        $message .= '&nbsp;| <i class="dashicons dashicons-visibility" aria-hidden="true"></i> <a href="'. wp_nonce_url( admin_url( '?nanosupport_email_preview=true' ), 'email-preview' ) .'" target="_blank" rel="noopener">';
            $message .= __( 'Preview email template', 'nanosupport' );
        $message .= '</a>';
    $message .= '</p>';

    echo $message;
}

// Email Template : Field 1 : Header Background Color
function ns_email_header_bg_color_field() {
    $options = get_option('nanosupport_email_settings');

    $header_bg_color = isset($options['header_bg_color']) && $options['header_bg_color'] ? $options['header_bg_color'] : '#1c5daa';

    echo '<input type="text" class="ns-colorbox" id="ns-email-header-bg-color" name="nanosupport_email_settings[header_bg_color]" value="'. $header_bg_color .'" data-default-color="#1c5daa" aria-describedby="ns-header-bg-color">';

    /* translators: color code */
    echo ns_tooltip( 'ns-header-bg-color', sprintf( wp_kses( __( 'Choose a color for the header background of the email template. Default: NS Blue (%s)', 'nanosupport' ), array('code' => array()) ), '<code>#1c5daa</code>'), 'right' );
}

// Email Template : Field 2 : Header Text Color
function ns_email_header_text_color_field() {
    $options = get_option('nanosupport_email_settings');

    $header_text_color = isset($options['header_text_color']) && $options['header_text_color'] ? $options['header_text_color'] : '#fff';

    echo '<input type="text" class="ns-colorbox" id="email-header-color" name="nanosupport_email_settings[header_text_color]" value="'. $header_text_color .'" data-default-color="#fff" aria-describedby="ns-header-text-color">';

    /* translators: color code */
    echo ns_tooltip( 'ns-header-text-color', sprintf( wp_kses( __( 'Choose a color for the header text of the email template. Default: White (%s)', 'nanosupport' ), array('code' => array()) ), '<code>#fff</code>' ), 'right' );
}

// Email Template : Field 3 : Header Image
function ns_email_header_image_field() {
    $options = get_option('nanosupport_email_settings');

    $header_image = isset($options['header_image']) && $options['header_image'] ? $options['header_image'] : '';

    echo '<input type="url" class="ns-field-item ns-textbox" name="nanosupport_email_settings[header_image]" id="ns-email-header-image-url" value="'. $header_image .'" placeholder="'. esc_attr('eg. http://path/to/image.ext') .'" aria-describedby="ns-email-header-image">';

    echo ns_tooltip( 'ns-email-header-image', __( 'Upload an image through Media &raquo; Add New, and copy &amp; paste the image URL here. Or, you can place an abosolute URL of any image. Default: No image.', 'nanosupport' ), 'right' );
}

// Email Template : Field 4 : Header Text
function ns_email_header_text_field() {
    $options = get_option('nanosupport_email_settings');

    $header_text = isset($options['header_text']) && $options['header_text'] ? $options['header_text'] : '';

    echo '<input type="text" class="ns-field-item ns-textbox" name="nanosupport_email_settings[header_text]" id="ns-email-header-text" value="'. $header_text .'" placeholder="'. sprintf( __('eg. %s'), get_bloginfo( 'name', 'display' ) ) .'" aria-describedby="ns-header-text">';

    echo ns_tooltip( 'ns-header-text', __( 'Write down the Header Text for the email template. Default: Site name.', 'nanosupport' ), 'right' );
}

// Email Template : Field 5 : Footer Text
function ns_email_footer_text_field() {
    $options = get_option('nanosupport_email_settings');

    $footer_text = isset($options['footer_text']) && $options['footer_text'] ? $options['footer_text'] : '';

    /* translators: 1. site title 2. developer company name */
    $default_footer_text = sprintf( __('eg. %1$s &mdash; Powered by %2$s', 'nanosupport'), get_bloginfo( 'name', 'display' ), NS()->plugin );

    echo '<input type="text" class="ns-field-item ns-textbox" name="nanosupport_email_settings[footer_text]" id="ns-email-footer-text" value="'. $footer_text .'" placeholder="'. $default_footer_text .'" aria-describedby="ns-footer-text">';

    echo ns_tooltip( 'ns-footer-text', __( 'Write down the Footer Text for the email template.', 'nanosupport' ), 'right' );

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

    //New Ticket email checkbox
    $new_ticket = isset($input['email_choices']['new_ticket']) && (int) $input['email_choices']['new_ticket'] === 1 ? (int) $input['email_choices']['new_ticket'] : '';
    //Response email checkbox
    $response = isset($input['email_choices']['response']) && (int) $input['email_choices']['response'] === 1 ? (int) $input['email_choices']['response'] : '';
    //Agent Response email checkbox
    $agent_response = isset($input['email_choices']['agent_response']) && (int) $input['email_choices']['agent_response'] === 1 ? (int) $input['email_choices']['agent_response'] : '';

    //Email Header Background Color
    $header_bg_color = isset($input['header_bg_color']) && $input['header_bg_color'] ? $input['header_bg_color'] : '#1c5daa';
    //Email Header Text Color
    $header_text_color = isset($input['header_text_color']) && $input['header_text_color'] ? $input['header_text_color'] : '#fff';
    //Email Header Image
    $header_image = isset($input['header_image']) && $input['header_image'] ? $input['header_image'] : '';
    //Email Header Text
    $header_text = isset($input['header_text']) && $input['header_text'] ? $input['header_text'] : '';
    //Email Footer Text
    $footer_text = isset($input['footer_text']) && $input['footer_text'] ? $input['footer_text'] : '';

    $options['notification_email']              = sanitize_email($notification_email);
    $options['email_choices']['new_ticket']     = absint( $new_ticket );
    $options['email_choices']['response']       = absint( $response );
    $options['email_choices']['agent_response'] = absint( $agent_response );

    $options['header_bg_color']     = $header_bg_color;
    $options['header_text_color']   = $header_text_color;
    $options['header_image']        = $header_image;
    $options['header_text']         = $header_text;
    $options['footer_text']         = $footer_text;

    return $options;
}
