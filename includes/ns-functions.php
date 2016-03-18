<?php
/**
 * All the Functions
 *
 * All the responsible functions for NanoSupport.
 *
 * @author      nanodesigns
 * @category    Core
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hide all the Responses in Comments page (Admin Panel)
 *
 * @author gmazzap <https://twitter.com/gmazzap>
 * @link   http://wordpress.stackexchange.com/a/186281/22728
 *
 * @since  1.0.0
 *
 * hooked: pre_get_comments (10)
 *
 * @param  \WP_Comment_Query $query All the comments from comments table.
 * @return \WP_Comment_Query        Filtering 'nanosupport_response' hiding them.
 * -----------------------------------------------------------------------
 */
function ns_exclude_responses_in_comments(\WP_Comment_Query $query) {
    /* only allow 'nanosupport_response' when is required explicitly */
    if ( $query->query_vars['type'] !== 'nanosupport_response' ) {
        $query->query_vars['type__not_in'] = array_merge((array) $query->query_vars['type__not_in'], array('nanosupport_response'));
    }
}

add_action( 'pre_get_comments', 'ns_exclude_responses_in_comments' );


/**
 * Process Ticket Submission
 *
 * Process Ticket Submission including Login/Registration.
 *
 * @since   1.0.0
 *
 * @return  void
 * -----------------------------------------------------------------------
 */
function ns_registration_login_ticket_submission_redir() {
    if( !isset( $_POST['ns_submit'] ) )
        return;

    //form validation here
    global $ns_errors, $current_user;

    $ns_errors = array();
    $data       = array();

    //Ticket Subject
    if( empty( $_POST['ns_ticket_subject'] ) ) {
        $ns_errors[]    = __( "Ticket subject can't be empty", "nanosupport" );
    } else {
        $ticket_subject = $_POST['ns_ticket_subject'];        
    }

    //Ticket Details
    if( empty( $_POST['ns_ticket_details'] ) ){
        $ns_errors[] = __( "Ticket details can't be empty", "nanosupport" );
    } else if( ! empty( $_POST['ns_ticket_details'] ) && strlen( $_POST['ns_ticket_details'] ) < 30 ) {
        $ns_errors[]    = __( 'Ticket details must be at least 30 characters long', 'nanosupport' );
    } else {
        $ticket_details = $_POST['ns_ticket_details'];
    }


    //Ticket Priority
    if( empty( $_POST['ns_ticket_priority'] ) ){
        $ns_errors[]        = __( 'Ticket priority must be set', 'nanosupport' );
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

        //set to 'support' department (whatever the user role is...)
        wp_set_object_terms( $ticket_post_id, 'support', 'nanosupport_departments' );

        //prepare the meta information array
        $meta_control = array(
                'status'    => esc_html( 'open' ),
                'priority'  => wp_strip_all_tags( $ticket_priority ),
                'agent'     => '',
            );

        //insert the meta information into postmeta
        add_post_meta( $ticket_post_id, 'ns_control', $meta_control );

        /**
         * Notify the site admin by email, under the hood.
         * @since  1.0.0
         * ...
         */
        nanosupport_handle_notification_email( $ticket_post_id );

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

add_action( 'template_redirect', 'ns_registration_login_ticket_submission_redir' );


/**
 * Navigation on Knowledgebase
 *
 * Disply a NanoSupport navigation on the Knowledgebase.
 *
 * @since  1.0.0
 *
 * hooked: nanosupport_before_knowledgebase (10)
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_knowledgebase_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    ob_start(); ?>

    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-7 text-muted">
                <?php _e( "Find your desired question in the knowledgebase. If you can't find your question, submit a new support ticket.", 'nanosupport' ); ?>
            </div>
            <div class="col-sm-5 text-right">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="btn btn-sm btn-primary">
                    <span class="ns-icon-tag"></span> <?php echo $all_tickets_label; ?>
                </a>
                <a class="btn btn-sm btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                    <span class="ns-icon-tag"></span> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                </a>
            </div>
        </div>
    </div>

    <?php
    echo ob_get_clean();
}

add_action( 'nanosupport_before_knowledgebase', 'ns_knowledgebase_navigation', 10 );


/**
 * Navigation on New Ticket
 *
 * Disply a NanoSupport navigation on the New Ticket page.
 *
 * @since  1.0.0
 *
 * hooked: nanosupport_before_new_ticket (10)
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_new_ticket_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings        = get_option( 'nanosupport_settings' );
    $ns_knowledgebase_settings  = get_option('nanosupport_knowledgebase_settings');

    ob_start(); ?>

    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-5">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="btn btn-sm btn-primary">
                    <span class="ns-icon-tag"></span> <?php esc_html_e( $all_tickets_label ); ?>
                </a>
                <a class="btn btn-sm btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
                </a>
            </div>
            <div class="col-sm-7 text-muted">
                <small><?php _e( 'Consult the Knowledgebase for your query. If they are <em>not</em> close to you, then submit a new ticket here.', 'nanosupport' ); ?></small>
            </div>
        </div>
    </div>
    
    <?php
    echo ob_get_clean();
}

add_action( 'nanosupport_before_new_ticket', 'ns_new_ticket_navigation', 10 );


/**
 * Navigation on Support Desk
 *
 * Disply a NanoSupport navigation on the Support Desk page.
 *
 * @since  1.0.0
 *
 * hooked: nanosupport_before_support_desk (10)
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_support_desk_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings        = get_option( 'nanosupport_settings' );
    $ns_knowledgebase_settings  = get_option('nanosupport_knowledgebase_settings');

    ob_start(); ?>

    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-7 text-muted">
                <?php _e( 'Tickets are visible to the admins, designated support assistant and/or to the ticket owner only.', 'nanosupport' ); ?>
            </div>
            <div class="col-sm-5 text-right">
                <a class="btn btn-sm btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
                </a>
                <a class="btn btn-sm btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                    <span class="ns-icon-tag"></span> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php
    echo ob_get_clean();
}

add_action( 'nanosupport_before_support_desk', 'ns_support_desk_navigation', 10 );
