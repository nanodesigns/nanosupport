<?php
/**
 * All the Functions
 *
 * All the responsible functions for NanoSupport.
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hide all the Responses in Comments page (Admin Panel)
 *
 * @link http://wordpress.stackexchange.com/a/56657/22728
 * 
 * @param  array $comments All the comments from comments table.
 * @return array           Filtering 'nanosupport_response' hiding them.
 * -----------------------------------------------------------------------
 */
function ns_filter_comments_for_responses( $comments ) {
    global $pagenow;
    if( 'edit-comments.php' === $pagenow  ) {
        foreach( $comments as $i => $comment ) {
            $the_post = get_post( $comment->comment_post_ID );
            if( 'nanosupport_response' === $comment->comment_type || 'nanosupport' === $the_post->post_type )
                unset( $comments[$i] );
        }
    }
    return $comments;
}
add_filter( 'the_comments', 'ns_filter_comments_for_responses' );


/**
 * Process Ticket Submission
 *
 * Process Ticket Submission including Login/Registration.
 * -----------------------------------------------------------------------
 */
function ns_registration_login_redirection(){
    if( !isset( $_POST['ns_submit'] ) )
        return;

    //form validation here
    global $ns_errors, $current_user;

    $ns_errors = array();
    $data       = array();

    //Ticket Subject
    if( empty( $_POST['ns_ticket_subject'] ) ) {
        $ns_errors[] = __( "Ticket subject can't be empty", "nanosupport" );
    } else {
        $ticket_subject = $_POST['ns_ticket_subject'];        
    }

    //Ticket Details
    if( empty( $_POST['ns_ticket_details'] ) ){
        $ns_errors[] = __( "Ticket details can't be empty", "nanosupport" );
    } else if( ! empty( $_POST['ns_ticket_details'] ) && strlen( $_POST['ns_ticket_details'] ) < 30 ) {
        $ns_errors[] = __( 'Ticket details must be at least 30 characters long', 'nanosupport' );
    } else {
        $ticket_details = $_POST['ns_ticket_details'];
    }


    //Ticket Priority
    if( empty( $_POST['ns_ticket_priority'] ) ){
        $ns_errors[] = __( 'Ticket priority must be set', 'nanosupport' );
    } else {
        $ticket_priority    = $_POST['ns_ticket_priority'];
    }

    //DEFAULT
    $user_id        = ''; //setting it to blank to prevent annonymous ticket
    $post_status    = 'pending';

    /**
     * Front end Login Form
     * 
     * @author Agbonghama Collins
     * @link http://designmodo.com/wordpress-custom-login/
     */
    if ( isset( $_POST['ns_login_submit'] ) ) {
        
        $user = nanosupport_login_auth( $_POST['login_name'], $_POST['login_password'] );

        if( $user ) {
            if( $user->roles[0] === 'administrator' || $user->roles[0] === 'editor' ) {
                $post_status = 'private';
            } else {
                $post_status = 'pending';
            }
            $user_id = $user->ID; //setting the $user_id with logged in user id
        }
    } //login ends
    else if ( isset( $_POST['ns_registration_submit'] ) ) {
        //REGISTRATION STARTS HERE
        $ns_reg_username   = $_POST['reg_name'];
        $ns_reg_email      = $_POST['reg_email'];
        $ns_reg_password   = $_POST['reg_password'];

        $userdata = array(
            'user_login'    => esc_attr( $ns_reg_username ),
            'user_email'    => esc_attr( $ns_reg_email ),
            'user_pass'     => esc_attr( $ns_reg_password ),
        );

        $reg_validate = nanosupport_reg_validate( $ns_reg_username, $ns_reg_email, $ns_reg_password );

        if( is_wp_error($reg_validate) ) {
            $ns_errors[]   = $reg_validate->get_error_message();
        } else {
            $registered_user_id = wp_insert_user( $userdata );

            if ( ! is_wp_error( $registered_user_id ) ) {
                // set the WP login cookie
                $secure_cookie = is_ssl() ? true : false;
                wp_set_auth_cookie( $registered_user_id, true, $secure_cookie );

                $user_id = $registered_user_id;

                $user = get_user_by( 'id', $user_id );
                if( 'administrator' === $user->roles[0] || 'editor' === $user->roles[0] ) {
                    $post_status = 'private';
                } else {
                    $post_status = 'pending';
                }

            } else {
                $ns_errors[]   = $registered_user_id->get_error_message();
            }
        }
    } //registration ends

    /**
     * Save Ticket Information.
     * 
     * Finally save the ticket information into the database
     * using user credentials from above.
     */
    //set prerequisite according to different situation
    if( is_user_logged_in() ) {

        $user_id        = $current_user->ID;

        if( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
            $post_status    = 'private';
        } else {
            $post_status    = 'pending';            
        }

    } else {
        $user_id        = $current_user->ID;
    }

    if( ! empty( $user_id ) && empty( $ns_errors ) ){
        
        $ticket_post_id = wp_insert_post( array(
                            'post_status'       => esc_html( $post_status ),
                            'post_type'         => esc_html( 'nanosupport' ),
                            'post_author'       => absint( $user_id ),

                            'post_title'        => sanitize_text_field( $ticket_subject ),
                            'post_content'      => htmlentities( $ticket_details, ENT_QUOTES ),
                            'post_date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) )
                        ) );

        //set to "support" department (whatever the user role is...)
        wp_set_object_terms( $ticket_post_id, 'support', 'nanosupport_departments' );

        //prepare the meta information array
        $meta_control = array(
                'status'    => esc_html( 'open' ),
                'priority'  => wp_strip_all_tags( $ticket_priority ),
                'agent'     => '',
            );

        //insert the meta information into postmeta
        add_post_meta( $ticket_post_id, 'ns_control', $meta_control );
    } else {
        $ns_errors[]   = __( 'Sorry, your user identity is not acceptable! Your ticket is not submitted.', 'nanosupport' );
    }

    //------------------ERROR: There are errors - don't go further
    if( !empty( $ns_errors ) ){
        return;
    }

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    $args = add_query_arg( 'success', 1, get_permalink( $ns_general_settings['support_desk'] ) );
    wp_redirect( esc_url( $args ) );
    exit();
}
add_action( 'template_redirect', 'ns_registration_login_redirection' );