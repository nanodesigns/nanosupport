<?php
/**
 * Email Functions
 *
 * All the responsible functions for emailing for NanoSupport.
 *
 * @author      nanodesigns
 * @category    Email
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Force HTML mail.
 * @return string HTML content type.
 * ------------------------------------------------------------------------------
 */
function nanosupport_mail_content_type() {
    return "text/html";
}


/**
 * NanoSupport Emailer
 *
 * @link http://stackoverflow.com/a/14502884/1743124
 *
 * @since  1.0.0
 * 
 * @param  string $to_email       To email.
 * @param  string $subject        Email subject.
 * @param  string $email_subhead  Email sub header for email content.
 * @param  string $message        HTML/Plain email body.
 * @param  string $reply_to_email Specified reply to email (optional).
 * @return boolean|array          If sent, true, else error message.
 * ------------------------------------------------------------------------------
 */
function ns_email( $to_email, $subject, $email_subhead, $message, $reply_to_email = '' ) {
    
    if( empty($to_email) || empty($subject) || empty($email_subhead) || empty($message) )
        return;

    ob_start();
        ns_get_template_part( 'content', 'email' );
    $email_content  = ob_get_clean();
    $email_content  = str_replace( "%%NS_MAIL_SUBHEAD%%",  $email_subhead, $email_content );
    $email_content  = str_replace( "%%NS_MAIL_CONTENT%%",  $message,       $email_content );

    $sender         = get_bloginfo( 'name' );
    $from_email     = ns_ondomain_email(); //noreply@yourdomain.dom
    $reply_to_email = ! empty($reply_to_email) ? $reply_to_email : $from_email;

    $headers        = "From: ". $sender ." <". $from_email .">\r\n";
    $headers        .= "Reply-To: ". $reply_to_email ."\r\n";
    $headers        .= "MIME-Version: 1.0\r\n";
    $headers        .= "Content-Type: text/html; charset=UTF-8";

    add_filter( 'wp_mail_content_type', 'nanosupport_mail_content_type' );

        //send the email
        $ns_email = wp_mail( $to_email, $subject, $email_content, $headers );

    //to stop conflict
    remove_filter( 'wp_mail_content_type', 'nanosupport_mail_content_type' );

    //return true || mail object
    return ! is_wp_error($ns_email) ? true : $ns_email;
}


/**
 * Handle Notification Email.
 *
 * @since   1.0.0
 *
 * @param   integer $ticket_id  Ticket Integer.
 * @return  boolean|string      True if sent, else error message.
 * ------------------------------------------------------------------------------
 */
function nanosupport_handle_notification_email( $ticket_id = null ) {

    //If the ticket is not submitted, return
    if( ! isset( $_POST['ns_submit'] ) )
        return;

    if( null === $ticket_id )
        return;

    /**
     * Generate Dynamic values
     */
    $ticket_edit_link = get_edit_post_link( $ticket_id );
    $ticket_view_link = get_permalink( $ticket_id );

    //Get ticket information
    $ticket_control = get_post_meta( $ticket_id, 'ns_control', true );
    switch ($ticket_control['priority']) {
        case 'low':
            $priority = __( 'Low', 'nanosupport' );
            break;

        case 'medium':
            $priority = __( 'Medium', 'nanosupport' );
            break;

        case 'high':
            $priority = __( 'High', 'nanosupport' );
            break;

        case 'critical':
            $priority = __( 'Critical', 'nanosupport' );
            break;
        
        default:
            $priority = '';
            break;
    }
    

    $subject = sprintf ( __( 'New Ticket Submitted &mdash; %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

    $email_subhead = __( 'New Ticket Submitted', 'nanosupport' );

    $message = '';
    $message = '<p style="margin: 0 0 16px;">'. __( 'A support ticket is submitted. Please find the links below:', 'nanosupport' ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;"><a style="font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Roboto, Arial, sans-serif;font-size: 100%;line-height: 2;color: #ffffff;border-radius: 25px;display: inline-block;cursor: pointer;font-weight: bold;text-decoration: none;background: #1c5daa;margin: 0;padding: 0;border-color: #1c5daa;border-style: solid;border-width: 1px 20px;" href="'. esc_url($ticket_view_link) .'">'. __( 'Link to the Ticket', 'nanosupport' ) .'</a></p>';
    $message .= '<p style="margin: 0 0 16px;"><a style="font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Roboto, Arial, sans-serif;font-size: 100%;line-height: 2;color: #ffffff;border-radius: 25px;display: inline-block;cursor: pointer;font-weight: bold;text-decoration: none;background: #f6bb42;margin: 0;padding: 0;border-color: #f6bb42;border-style: solid;border-width: 1px 20px;" href="'. esc_url($ticket_edit_link) .'">'. __( 'Ticket in edit mode', 'nanosupport' ) .'</a></p>';
    $message .= '<p style="margin: 0 0 16px;">'. sprintf( __( 'Priority: <strong>%s</strong>', 'nanosupport' ), $priority ) .'</a></p>';

    $notification_email = ns_email( ns_ondomain_email('info'), $subject, $email_subhead, $message );

    return $notification_email;
}


/**
 * Send Generated Password to Email.
 *
 * @since  1.0.0
 *
 * @param   integer $ticket_id  Ticket Integer.
 * @return  boolean|string      True if sent, else error message.
 * ------------------------------------------------------------------------------
 */
function nanosupport_handle_account_opening_email( $user_id = '', $generated_password = '' ) {

    //If the ticket is not submitted, return
    if( ! isset( $_POST['ns_submit'] ) )
        return;

    //If the ticket is not submitted from registration panel, return
    if( ! isset( $_POST['ns_registration_submit'] ) )
        return;

    //Must have a password generated to email
    if( empty($user_id) )
        return;

    $user = get_user_by( 'id', $user_id );
    $username = $user ? $user->user_login : '';
    $email = $user ? $user->user_email : '';

    $subject = sprintf ( __( 'Account Created &mdash; %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

    $email_subhead = __( 'Welcome to your Account', 'nanosupport' );

    //Email Content
    $message = '';
    $message = '<p style="margin: 0 0 16px;">'. sprintf( __('To manage your support tickets an account has been created on %s.', 'nanosupport'), get_bloginfo( 'name', 'display' ) ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;">'. __('Account credentials are as following:', 'nanosupport') .'</p>';
    if( !empty($generated_password) )
        $message .= '<p style="margin: 0 0 20px 20px;">'. sprintf( __('Username: <strong>%1$s</strong><br>Password: <strong>%2$s</strong><br>Email: %3$s', 'nanosupport'), $username, $generated_password, $email ) .'</p>';
    else
        $message .= '<p style="margin: 0 0 20px 20px;">'. sprintf( __('Username: %1$s<br>Password: %2$s<br>Email: %3$s', 'nanosupport'), $username, '<em>'. __('Your Password', 'nanosupport') .'</em>', $email ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;">'. sprintf( __('You can edit your account details and reset your password from your <a href="%s" target="_blank">Profile</a>', 'nanosupport'), get_edit_user_link( absint($user_id) ) ) .'</p>';

    $password_email = ns_email( ns_ondomain_email('info'), $subject, $email_subhead, $message );

    return $password_email;
}
