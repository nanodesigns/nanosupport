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
 * Hide all the Responses in Comments queries (Admin Panel)
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
    if( is_admin() ) :
        $screen = get_current_screen();
        if( !('edit' === $screen->parent_base && 'nanosupport' === $screen->post_type) ) :
            /* only allow 'nanosupport_response' when is required explicitly */
            if ( $query->query_vars['type'] !== 'nanosupport_response' ) :
                $query->query_vars['type__not_in'] = array_merge((array) $query->query_vars['type__not_in'], array('nanosupport_response'));
            endif;
        endif;
    endif;
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
    if( ! isset( $_POST['ns_submit'] ) )
        return;

    //form validation here
    global $ns_errors;

    $ns_errors  = array();

    //Ticket Subject
    if( empty( $_POST['ns_ticket_subject'] ) )
        $ns_errors[]    = __( "Ticket subject can't be empty", "nanosupport" );
    else
        $ticket_subject = $_POST['ns_ticket_subject'];

    //Ticket Details
    if( empty( $_POST['ns_ticket_details'] ) )
        $ns_errors[] = __( "Ticket details can't be empty", "nanosupport" );
    else if( ! empty( $_POST['ns_ticket_details'] ) && strlen( $_POST['ns_ticket_details'] ) < 30 )
        $ns_errors[]    = __( 'Ticket details must be at least 30 characters long', 'nanosupport' );
    else
        $ticket_details = $_POST['ns_ticket_details'];


    //Ticket Priority
    if( empty( $_POST['ns_ticket_priority'] ) )
        $ns_errors[]        = __( 'Ticket priority must be set', 'nanosupport' );
    else
        $ticket_priority    = $_POST['ns_ticket_priority'];


    /**
     * Process the Submission
     * ...
     */
    if( is_user_logged_in() ) {

        /**
         * AUTHENTICATED USER
         * Ticket Submission with Registered and Logged in user
         * ...
         */
        global $current_user;
        $user_id        = $current_user->ID;

        if( current_user_can( 'administrator' ) || current_user_can( 'editor' ) )
            $post_status    = 'private';
        else
            $post_status    = 'pending';

        //logged in submission ends
    }
    elseif( isset($_POST['ns_login_submit']) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'nanosupport-login' ) ) {

        /**
         * LOGIN
         * Ticket Submission with Login
         * ...
         */

        $creds = array();

        $username = trim( $_POST['login_name'] );
        $password = $_POST['login_password'];

        if( empty( $username ) ) {
            $ns_errors[] = __( 'Username cannot be empty', 'nanosupport' );
        }

        if( empty( $password ) ) {
            $ns_errors[] = __( 'Password must be filled', 'nanosupport' );
        }

        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * nanosupport_username_from_email
         * 
         * @since  1.0.0
         *
         * @param boolean  Yes/No.
         * -----------------------------------------------------------------------
         */
        $get_username_from_email = apply_filters( 'nanosupport_username_from_email', true );

        if( is_email($username) && $get_username_from_email ) {
            $user = get_user_by( 'email', $username );

            if( isset( $user->user_login ) ) {
                $creds['user_login'] = $user->user_login;
            } else {
                $ns_errors[] = __( 'There is no user found with this email address', 'nanosupport' );
            }
        } else {
            $creds['user_login'] = $username;
        }

        $creds['user_password'] = $password;
        $creds['remember']      = isset( $_POST['rememberme'] );
        $secure_cookie          = is_ssl() ? true : false;

        if( !empty( $username ) && !empty($password) ) {
            //Log the user in
            $user = wp_signon(
                        apply_filters( 'nanosupport_login_credentials', $creds ),
                        $secure_cookie
                    );
            
            if( is_wp_error( $user ) ) {
                $err_message = $user->get_error_message();
                $err_message = str_replace( '<strong>ERROR</strong>:', '', $err_message );
                $ns_errors[] = $err_message;
            }

            if( ! is_wp_error($user) ) {
                if( in_array($user->roles[0], array('administrator','editor')) )
                    $post_status = 'private';
                else
                    $post_status = 'pending';
                
                //setting the $user_id with logged in user's id
                $user_id = $user->ID;
            }
        }



        //login submission ends
    }
    elseif ( isset($_POST['ns_registration_submit']) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'nanosupport-registration' ) ) {

        /**
         * REGISTRATION
         * Ticket Submission with Login
         * ...
         */
        $options    = get_option( 'nanosupport_settings' );

        //Set values according to settings
        $username   = ( $options['account_creation']['generate_username'] !== 1 ) ? $_POST['reg_name'] : '';
        $password   = ( $options['account_creation']['generate_password'] !== 1 ) ? $_POST['reg_password'] : '';
        $email      = $_POST['reg_email'];
        $spam       = $_POST['repeat_email']; //spam field (hidden from human eye)

        $new_support_seeker_id = ns_create_support_seeker(
            sanitize_email( $email ),
            ns_sanitize_text( $username ),
            $password,
            $spam
        );

        if( is_wp_error( $new_support_seeker_id ) ) {
            $ns_errors[] = $new_support_seeker_id->get_error_message();
        } else {
            //set the WP login cookie
            $secure_cookie = is_ssl() ? true : false;
            wp_set_auth_cookie( $new_support_seeker_id, true, $secure_cookie );

            $user_id = $new_support_seeker_id;

            $user = get_user_by( 'id', $user_id );
            if( 'administrator' === $user->roles[0] || 'editor' === $user->roles[0] ) {
                $post_status = 'private';
            } else {
                $post_status = 'pending';
            }
        }

        //registration submission ends
    } else {
        
        /**
         * DEFAULT
         * If all the way failed
         * ...
         */
        $user_id        = ''; //setting it to blank to prevent annonymous ticket
        $post_status    = 'pending';

    }

    //------------------ERROR: There are errors - don't go further
    if( !empty( $ns_errors ) ){
        return;
    }


    /**
     * Save Ticket Information.
     * 
     * Finally save the ticket information into the database
     * using user credentials from above.
     */
    if( ! empty( $user_id ) && empty( $ns_errors ) ){
        
        $ticket_post_id = wp_insert_post( array(
                            'post_status'       => $post_status,
                            'post_type'         => 'nanosupport',
                            'post_author'       => absint( $user_id ),

                            'post_title'        => wp_strip_all_tags( $ticket_subject ),
                            'post_content'      => $ticket_details,
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

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    //Redirect to the same page with success message
    $args = add_query_arg(
                'ns_success',
                1,
                get_permalink( $ns_general_settings['submit_page'] )
            );
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

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : false;

    //If not enabled, don't display the notice and navigation
    if( ! $enable_notice )
        return;

    ob_start(); ?>

    <div class="ns-well ns-well-sm">
        <div class="ns-row">
            <div class="ns-col-md-7 ns-col-sm-6 ns-well-left ns-text-muted">
                <?php _e( "Find your desired question in the knowledgebase. If you can't find your question, submit a new support ticket.", 'nanosupport' ); ?>
            </div>
            <div class="ns-col-md-5 ns-col-sm-6 ns-well-right ns-text-right">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="ns-btn ns-btn-sm ns-btn-primary">
                    <span class="ns-icon-tag"></span> <?php echo $all_tickets_label; ?>
                </a>
                <a class="ns-btn ns-btn-sm ns-btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                    <span class="ns-icon-tag"></span> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                </a>
            </div>
        </div>
    </div> <!-- /.ns-well.ns-well-sm -->

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

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : false;

    //If not enabled, don't display the notice and navigation
    if( ! $enable_notice )
        return;

    ob_start(); ?>

    <div class="ns-well ns-well-sm">
        <div class="ns-row">
            <div class="ns-col-md-5 ns-col-sm-6 ns-well-left">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="ns-btn ns-btn-sm ns-btn-primary">
                    <span class="ns-icon-tag"></span> <?php esc_html_e( $all_tickets_label ); ?>
                </a>
                <a class="ns-btn ns-btn-sm ns-btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
                </a>
            </div>
            <div class="ns-col-md-7 ns-col-sm-6 ns-well-right ns-text-muted">
                <span class="ns-small"><?php _e( 'Consult the Knowledgebase for your query. If they are <em>not</em> close to you, then submit a new ticket here.', 'nanosupport' ); ?></span>
            </div>
        </div>
    </div> <!-- /.ns-well.ns-well-sm -->
    
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

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : false;

    //If not enabled, don't display the notice and navigation
    if( ! $enable_notice )
        return;

    ob_start(); ?>

    <div class="ns-well ns-well-sm">
        <div class="ns-row">
            <div class="ns-col-md-7 ns-col-sm-6 ns-well-left ns-text-muted">
                <?php _e( 'Tickets are visible to the admins, designated support assistant and/or to the ticket owner only.', 'nanosupport' ); ?>
            </div>
            <div class="ns-col-md-5 ns-col-sm-6 ns-well-right ns-text-right">
                <a class="ns-btn ns-btn-sm ns-btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
                </a>
                <a class="ns-btn ns-btn-sm ns-btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                    <span class="ns-icon-tag"></span> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                </a>
            </div>
        </div>
    </div> <!-- /.ns-well.ns-well-sm -->
    
    <?php
    echo ob_get_clean();
}

add_action( 'nanosupport_before_support_desk', 'ns_support_desk_navigation', 10 );
