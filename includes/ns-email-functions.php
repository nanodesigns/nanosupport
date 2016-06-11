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


//Execute new ticket notification
add_action( 'transition_post_status', 'nanosupport_new_ticket_notification_email', 10, 3 );

//First, disable comment notification for 'nanosupport' Responses
add_filter( 'comment_notification_recipients', 'ns_disable_wp_comment_notification', PHP_INT_MAX, 2 );
//Then, execute response notification
add_action( 'wp_insert_comment', 'nanosupport_email_on_ticket_response', PHP_INT_MAX, 2 );



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
 * @link    http://stackoverflow.com/a/14502884/1743124
 *
 * @since   1.0.0
 * 
 * @param   string $to_email       To email.
 * @param   string $subject        Email subject.
 * @param   string $email_subhead  Email sub header for email content.
 * @param   string $message        HTML/Plain email body.
 * @param   string $reply_to_email Specified reply to email (optional).
 * @return  boolean                If sent, true, else false.
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

    //return true || false
    return $ns_email;
}

/**
 * Handle Notification Email.
 *
 * @since   1.0.0
 *
 * @param   integer $ticket_id  Ticket Integer.
 * @return  boolean             True if sent, else false.
 * ------------------------------------------------------------------------------
 */
function nanosupport_new_ticket_notification_email( $new_status, $old_status, $post ) {

    if( 'nanosupport' === $post->post_type && 'new' === $old_status && 'pending' === $new_status ) :

        $ticket_id = $post->ID;

        /**
         * Generate Dynamic values
         */
        $ticket_view_link   = get_permalink( $ticket_id );
        

        $subject = sprintf ( __( 'New Ticket Submitted — %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

        $email_subhead = __( 'New Ticket Submitted', 'nanosupport' );

        $message = '';
        $message = '<p style="margin: 0 0 16px;">'. __( 'A support ticket is submitted and is <strong>Pending</strong>. Please find the link below:', 'nanosupport' ) .'</p>';
        $message .= '<p style="margin: 0 0 16px;"><a style="font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Roboto, Arial, sans-serif;font-size: 100%;line-height: 2;color: #ffffff;border-radius: 25px;display: inline-block;cursor: pointer;font-weight: bold;text-decoration: none;background: #1c5daa;margin: 0;padding: 0;border-color: #1c5daa;border-style: solid;border-width: 1px 20px;" href="'. esc_url($ticket_view_link) .'">'. __( 'Link to the Ticket', 'nanosupport' ) .'</a></p>';

        $nanosupport_email_settings = get_option('nanosupport_email_settings');
        $to_email = $nanosupport_email_settings['notification_email'];

        $notification_email = ns_email(
            $to_email,
            $subject,
            $email_subhead,
            $message
        );

    endif;

}


/**
 * Send Generated Password to Email.
 *
 * @since  1.0.0
 *
 * @param   integer $ticket_id  Ticket Integer.
 * @return  boolean             True if sent, else false.
 * ------------------------------------------------------------------------------
 */
function nanosupport_handle_account_opening_email( $user_id = '', $generated_password = '' ) {

    //If the ticket is not submitted, return
    if( ! isset( $_POST['ns_submit'] ) )
        return;

    //If the ticket is not submitted from registration panel, return
    if( ! isset( $_POST['ns_registration_submit'] ) )
        return;

    //Must have a user ID to send email to
    if( empty($user_id) )
        return;

    $user       = get_user_by( 'id', $user_id );
    $username   = $user ? $user->user_login : '';
    $email      = $user ? $user->user_email : '';

    $subject = sprintf ( __( 'Account Created — %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

    $email_subhead = __( 'Welcome to your Account', 'nanosupport' );

    //Email Content
    $message = '';
    $message = '<p style="margin: 0 0 16px;">'. sprintf( __( 'To manage your support tickets an account has been created on %s.', 'nanosupport' ), get_bloginfo( 'name', 'display' ) ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;">'. __( 'Account credentials are as following:', 'nanosupport' ) .'</p>';
    if( !empty($generated_password) )
        $message .= '<p style="margin: 0 0 20px 20px;">'. sprintf( __( 'Username: <strong>%1$s</strong><br>Password: <strong>%2$s</strong><br>Email: %3$s', 'nanosupport' ), $username, $generated_password, $email ) .'</p>';
    else
        $message .= '<p style="margin: 0 0 20px 20px;">'. sprintf( __( 'Username: %1$s<br>Password: %2$s<br>Email: %3$s', 'nanosupport' ), $username, '<em>'. __( 'Your Password', 'nanosupport' ) .'</em>', $email ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;">'. __( 'You can edit your account details and reset your password from your Profile', 'nanosupport' ) .'</p>';
    $message .= '<p style="margin: 0 0 16px;">'. sprintf( __( 'Log in here: <a style="color: #1c5daa; text-decoration: none;" href="%1$s" target="_blank" title="Account Login URL">%1$s</a>', 'nanosupport' ), esc_url( wp_login_url() ) ) .'</p>';

    //send the email
    $password_email = ns_email( $email, $subject, $email_subhead, $message );

    return $password_email;
}


/**
 * Send email when ticket is responded
 *
 * Send an email notification to the ticket author if the ticket is
 * reponded by someone other than them. Send email whether the
 * ticket is responded regardless from front-end or back end.
 *
 * Send an email to the ticket agent notifying ticket modification.
 *
 * @since  1.0.0
 * 
 * @param  integer $comment_ID    The comment ID.
 * @param  object $comment_object The comment post object.
 * ------------------------------------------------------------------------------
 */
function nanosupport_email_on_ticket_response( $comment_ID, $comment_object ) {
    $comment = get_comment( $comment_ID );
    $post_id = $comment->comment_post_ID;
    
    if( 'nanosupport' !== get_post_type($post_id) )
        return;

    if( 'pending' === get_post_status($post_id) )
        return;

    $author_id      = get_post_field( 'post_author', $post_id );
    $author_email   = get_the_author_meta( 'user_email', $author_id );
    $last_response  = ns_get_last_response( $post_id );

    //Don't send email on self-response
    if( ( $last_response['user_id'] != $author_id ) && isset($author_email) && is_email($author_email) ) :
        $subject = sprintf ( esc_html__( 'Your ticket is replied — %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

        $email_subhead = __( 'Support Ticket Replied', 'nanosupport' );

        //Email Content
        $message = '';
        $message = '<p style="margin: 0 0 16px;">'. sprintf( __( 'Your support ticket &rsquo;<strong>%1$s</strong>&rsquo; on %2$s is replied by <em>%3$s</em>.', 'nanosupport' ), get_the_title($post_id), get_bloginfo( 'name', 'display' ), ns_user_nice_name($last_response['user_id']) ) .'</p>';
        $message .= '<p style="margin: 0 0 16px;"><a style="font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Roboto, Arial, sans-serif;font-size: 100%;line-height: 2;color: #ffffff;border-radius: 25px;display: inline-block;cursor: pointer;font-weight: bold;text-decoration: none;background: #1c5daa;margin: 0;padding: 0;border-color: #1c5daa;border-style: solid;border-width: 1px 20px;" href="'. esc_url(get_permalink($post_id)) .'">'. __( 'View Ticket', 'nanosupport' ) .'</a></p>';

        //Send the email
        ns_email( $author_email, $subject, $email_subhead, $message );

    endif;


    /**
     * Email the agent, if any.
     * ...
     */
    $ticket_meta    = ns_get_ticket_meta( $post_id );
    $ticket_agent   = isset($ticket_meta['agent']['ID']) ? $ticket_meta['agent']['ID'] : '';
    if( $ticket_agent && $last_response['user_id'] == $ticket_agent ) {
        $agent_user     = get_user_by( 'id', $ticket_agent );
        $agent_email    = $agent_user ? $agent_user->user_email : '';
    }

    if( isset($agent_email) && is_email($agent_email) ) :

        $subject = sprintf ( __( 'An assigned ticket is replied — %s', 'nanosupport' ), get_bloginfo( 'name', 'display' ) );

        $email_subhead = __( 'Support Ticket Replied', 'nanosupport' );

        //Email Content
        $message = '';
        $message = '<p style="margin: 0 0 16px;">'. sprintf( __( 'An assigned support ticket &rsquo;<strong>%1$s</strong>&rsquo; on %2$s is replied by <em>%3$s</em>.', 'nanosupport' ), get_the_title($post_id), get_bloginfo( 'name', 'display' ), ns_user_nice_name($last_response['user_id']) ) .'</p>';
        $message .= '<p style="margin: 0 0 16px;"><a style="font-family: \'Helvetica Neue\', \'Helvetica\', Helvetica, Roboto, Arial, sans-serif;font-size: 100%;line-height: 2;color: #ffffff;border-radius: 25px;display: inline-block;cursor: pointer;font-weight: bold;text-decoration: none;background: #1c5daa;margin: 0;padding: 0;border-color: #1c5daa;border-style: solid;border-width: 1px 20px;" href="'. esc_url(get_permalink($post_id)) .'">'. __( 'View Ticket', 'nanosupport' ) .'</a></p>';

        //Send the email
        ns_email( $agent_email, $subject, $email_subhead, $message );

    endif;
}


/**
 * Disable default comment notification email
 *
 * Disable comment notification email to inform user with
 * custom notification email specific to 'nanosupport_response'
 *
 * @since  1.0.0
 * 
 * @param  array $emails       Array of emails.
 * @param  integer $comment_ID Comment ID.
 * @return array               Filled or empty array based on condition.
 * ------------------------------------------------------------------------------
 */
function ns_disable_wp_comment_notification( $emails, $comment_ID ) {
    $comment = get_comment( $comment_ID );
    $post_id = $comment->comment_post_ID;

    if( 'nanosupport' === get_post_type($post_id) ) {
        $emails = array('');
    }

    return $emails;
}
