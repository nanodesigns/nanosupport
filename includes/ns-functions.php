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
        $ns_errors[]    = __( 'Ticket subject can&#8217;t be empty', 'nanosupport' );
    else
        $ticket_subject = $_POST['ns_ticket_subject'];

    //Ticket Details
    if( empty( $_POST['ns_ticket_details'] ) )
        $ns_errors[] = __( 'Ticket details can&#8217;t be empty', 'nanosupport' );
    else if( ! empty( $_POST['ns_ticket_details'] ) && strlen( $_POST['ns_ticket_details'] ) < 30 )
        $ns_errors[]    = __( 'Write down a little detail. At least 30 characters or longer', 'nanosupport' );
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
    if( ! empty( $ns_errors ) ){
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
        wp_set_object_terms( $ticket_post_id, 'support', 'nanosupport_department' );

        //insert the meta information into postmeta
        add_post_meta( $ticket_post_id, '_ns_ticket_status',   esc_html( 'open' ) );
        add_post_meta( $ticket_post_id, '_ns_ticket_priority', wp_strip_all_tags( $ticket_priority ) );
        add_post_meta( $ticket_post_id, '_ns_ticket_agent',    '' ); //no ticket agent assigned

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
 * Preview email template
 * 
 * Display a preview of the email template according to
 * the NanoSupport Email Template admin settings.
 *
 * @since  1.0.0
 * -----------------------------------------------------------------------
 * 
 */
function ns_preview_email_template() {
    if ( isset( $_GET['nanosupport_email_preview'] ) ) {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'email-preview' ) ) {
            die( 'Failed security check' );
        }

        ob_start();
            //get the designated email template
            ns_get_template_part( 'content', 'email' );
        $email_content = ob_get_clean();

        ob_start();
            //get the email content for the preview
            include 'admin/ns-email-template-preview.php';
        $message = ob_get_clean();

        $email_subhead = __( 'Email Template Preview', 'nanosupport' );

        $email_content  = str_replace( "%%NS_MAIL_SUBHEAD%%", $email_subhead, $email_content );
        $email_content  = str_replace( "%%NS_MAIL_CONTENT%%", $message, $email_content );

        echo $email_content;

        exit;
    }
}

add_action( 'admin_init', 'ns_preview_email_template' );


/**
 * Navigation on Knowledgebase
 *
 * Disply a NanoSupport navigation on the Knowledgebase.
 *
 * @since  1.0.0
 *
 * hooked: nanosupport_before_knowledgebase (10)
 * -----------------------------------------------------------------------
 */
function ns_knowledgebase_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : true;

    //If not enabled, don't display the notice and navigation
    if( ! $enable_notice )
        return;

    ob_start(); ?>

    <div class="ns-well ns-well-sm">
        <div class="ns-row">
            <div class="ns-col-md-7 ns-col-sm-6 ns-well-left ns-text-muted">
                <?php _e( 'Find your desired question in the knowledgebase. If you can&#8217;t find your question, submit a new support ticket.', 'nanosupport' ); ?>
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
 * -----------------------------------------------------------------------
 */
function ns_new_ticket_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings        = get_option( 'nanosupport_settings' );
    $ns_knowledgebase_settings  = get_option('nanosupport_knowledgebase_settings');

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : true;

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
 * -----------------------------------------------------------------------
 */
function ns_support_desk_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings        = get_option( 'nanosupport_settings' );
    $ns_knowledgebase_settings  = get_option('nanosupport_knowledgebase_settings');

    $enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : true;

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


if( ! function_exists( 'get_nanosupport_response_form' ) ) :
    /**
     * The New Response form
     *
     * The form for adding new response to the ticket. The form can be
     * overriden by calling another function with the same name.
     *
     * @since  1.0.0
     * -----------------------------------------------------------------------
     */
    function get_nanosupport_response_form() {

        if( ! is_singular( 'nanosupport' ) )
            return;

        global $current_user, $post, $response_error;

        //Display error message[s], if any
        if( is_wp_error($response_error) ) {
            foreach( $response_error->get_error_messages() as $error ){
                echo '<div class="ns-alert ns-alert-danger" role="alert">';
                    printf( '<strong>Error:</strong> %s', $error );
                echo '</div>';
            }
        }

        //Display success message, if any
        if( isset($_GET['ns_success']) && $_GET['ns_success'] == 1 ) {
            echo '<div class="ns-alert ns-alert-success" role="alert">';
                echo __( 'Your response is successfully submitted to this ticket.', 'nanosupport' );
            echo '</div>';
        }

        // Get ticket information
        $ticket_meta = ns_get_ticket_meta( $post->ID );

        // For solved tickets, display a way to reOpen the ticket
        if( 'solved' === $ticket_meta['status']['value'] && ! ( isset( $_GET['reopen'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'reopen-ticket' ) ) ) {
            $reopen_url = add_query_arg( 'reopen', '', get_the_permalink() );
            echo '<div class="ns-alert ns-alert-success" role="alert">';
                printf( __( 'This ticket is already solved. <a class="ns-btn ns-btn-sm ns-btn-warning" href="%s#write-message"><span class="ns-icon-repeat"></span> Reopen Ticket</a>', 'nanosupport' ), wp_nonce_url( $reopen_url, 'reopen-ticket' ) );
            echo '</div>';

            // and don't display the form
            return;
        }

        //Clean up request URI from temporary args for alert[s].
        $_SERVER['REQUEST_URI'] = remove_query_arg( 'ns_success', $_SERVER['REQUEST_URI'] );

        if( current_user_can( 'administrator' ) || current_user_can('editor') || $post->post_author == $current_user->ID ) : ?>

            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

                <div class="ns-cards ns-feedback">
                    <div class="ns-row">
                        <div class="ns-col-sm-9">
                            <div class="response-head">
                                <h3 class="ticket-head" id="new-response">
                                    <?php printf( __('Responding as: %s','nanosupport'), $current_user->display_name ); ?>
                                </h3>
                            </div> <!-- /.response-head -->
                        </div>
                        <div class="ns-col-sm-3 response-dates ns-small">
                            <?php echo date( 'd M Y h:iA', current_time('timestamp') ); ?>
                        </div>
                    </div> <!-- /.ns-row -->
                    <div class="ns-feedback-form">

                        <div class="ns-form-group">
                            <textarea name="ns_response_msg" id="write-message" class="ns-form-control" placeholder="<?php _e('Write down your response (at least 30 characters)', 'nanosupport'); ?>" rows="6" aria-label="<?php esc_attr_e('Write down the response to the ticket', 'nanosupport'); ?>"><?php echo isset($_POST['ns_response_msg']) ? stripslashes_deep( $_POST['ns_response_msg'] ) : ''; ?></textarea>
                        </div> <!-- /.ns-form-group -->

                        <?php
                        /**
                         * -----------------------------------------------------------------------
                         * HOOK : ACTION HOOK
                         * nanosupport_after_response_form
                         * 
                         * To Hook anything after the New Response Form.
                         *
                         * @since  1.0.0
                         * -----------------------------------------------------------------------
                         */
                        do_action( 'nanosupport_after_response_form' );
                        ?>

                        <?php wp_nonce_field( 'nanosupport-response-nonce' ); ?>

                        <button type="submit" name="submit_response" class="ns-btn ns-btn-primary">
                            <?php _e( 'Submit', 'nanosupport' ); ?>
                        </button>

                    </div>
                </div> <!-- /.ns-feedback-form -->

            </form>

        <?php
        else :

            echo '<div class="ns-alert ns-alert-info" role="alert">';
                if( 'solved' === $ticket_meta['status']['value'] ) {
                    _e( '<strong>Resolved!</strong> New Responses to this ticket is already closed. Only ticket author can reopen a closed ticket.', 'nanosupport' );
                } else {
                    _e( '<strong>Sorry!</strong> Tickets are open for responses only to the Ticket Author.', 'nanosupport' );
                }
            echo '</div>';

        endif;

    }

endif;


/**
 * Warn user on Opening Ticket.
 * 
 * Display a warning to the user on reOpening a solved ticket,
 * on Opening a pending ticket; Display the warning after
 * new response form.
 *
 * @since  1.0.0
 * 
 * hooked: nanosupport_after_response_form (10)
 * -----------------------------------------------------------------------
 */
function ns_notify_user_on_opening_ticket() {
    global $post;
    $ticket_meta = ns_get_ticket_meta( $post->ID );

    if( 'pending' === $ticket_meta['status']['value'] ) {
        echo '<div class="ns-alert ns-alert-normal" role="alert">';
            _e( '<strong>Just to inform:</strong> This ticket is still <em>pending</em>. With this response it&#8217;ll be opened.', 'nanosupport' );
        echo '</div>';
    }

    if( 'solved' === $ticket_meta['status']['value'] && isset( $_GET['reopen'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'reopen-ticket' ) ) {
        echo '<div class="ns-alert ns-alert-warning" role="alert">';
            printf( __( '<strong>Just to inform:</strong> you are about to ReOpen the ticket. <small><a href="%s">Cancel ReOpening</a></small>', 'nanosupport' ), get_the_permalink($post) );
        echo '</div>';
    }
}

add_action( 'nanosupport_after_response_form', 'ns_notify_user_on_opening_ticket' );


/**
 * Process Response Submission
 *
 * Process Response Submission and redirect with success.
 *
 * @since   1.0.0
 * -----------------------------------------------------------------------
 */
function ns_response_submit_redir() {
    if( ! is_user_logged_in() )
        return;

    if( isset( $_POST['submit_response'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'nanosupport-response-nonce' ) ) :

        global $current_user, $post, $response_error;

        // new error object
        $response_error = new WP_Error();

        $response_msg = $_POST['ns_response_msg'];

        if( empty($response_msg) ) {
            $response_error->add( 'response_empty', __( 'Response field can&#8217;t be empty.', 'nanosupport' ) );
        }
        if( strlen($response_msg) < 30 ) {
            $response_error->add( 'response_short', __( 'Your message is too short. Write down at least 30 characters.', 'nanosupport' ) );
        }

        if( is_wp_error($response_error) && ! empty($response_error->errors) )
            return;
        

        //Insert new response as a comment and get the comment ID
        $commentdata = array(
            'comment_post_ID'       => absint( $post->ID )   ,
            'comment_author'        => wp_strip_all_tags( $current_user->display_name ), 
            'comment_author_email'  => sanitize_email( $current_user->user_email ),
            'comment_author_url'    => esc_url( $current_user->user_url ),
            'comment_content'       => htmlentities( $response_msg ),
            'comment_type'          => 'nanosupport_response',
            'comment_parent'        => 0,
            'user_id'               => absint( $current_user->ID ),
        );

        $comment_id = wp_new_comment( $commentdata );

        //If error, return with the error message
        if( is_wp_error($comment_id) )
            return $comment_id->get_error_message();

        // Get ticket meta information
        $ticket_meta = ns_get_ticket_meta( $post->ID );
        $ticket_status = isset($ticket_meta['status']['value']) ? $ticket_meta['status']['value'] : 'open';

        /**
         * ReOpen a solved ticket,
         * or Open a pending ticket
         * ...
         */
        if( in_array( $ticket_status, array('solved', 'pending') ) ) {

            update_post_meta( $post->ID, '_ns_ticket_status',   wp_strip_all_tags( 'open' ) );

        }

        //Privately Publish a 'pending' ticket
        if( 'pending' === $ticket_status ) {
            wp_update_post( array(
                    'ID'            => $post->ID,
                    'post_status'   => 'private'
                ) );
        }

        //Redirect to the same page with success message
        $permalink = 'pending' === $ticket_status ? ns_get_pending_permalink( $post->ID ) : get_the_permalink( $post->ID );
        $args = add_query_arg(
                    'ns_success',
                    1,
                    $permalink
                );
        wp_redirect( esc_url( $args ) );
        exit();

    endif;

}

add_action( 'template_redirect', 'ns_response_submit_redir' );


/**
 * Make NanoSupport responses approved by default.
 *
 * Using wp_new_comment() won't make comments (responses) approved by default.
 * Therefore we need to manually intervene and make them approved before saving.
 *
 * @author gmazzap
 * @link   http://wordpress.stackexchange.com/a/186281/22728
 *
 * @since  1.0.0
 * 
 * @param  boolean $approved True | False.
 * @param  array $data       Information about the comment.
 * @return boolean           True.
 * -----------------------------------------------------------------------
 */
function ns_make_responses_approved( $approved, $data ) {
    return isset($data['type']) && $data['type'] === 'nanosupport_response' ? 1 : $approved;
}

add_filter( 'pre_comment_approved', 'ns_make_responses_approved', 20, 2 );


/**
 * Delete response in Admin panel.
 *
 * Delete individual response in admin panel edit post.
 *
 * @since  1.0.0
 * -----------------------------------------------------------------------
 */
function ns_del_admin_response() {
    if( ! is_admin() )
        return;

    if( ! ( isset($_GET['_wpnonce']) && wp_verify_nonce( $_GET['_wpnonce'], 'delete-ticket-response' ) ) )
        return;

    // Move to trash
    wp_delete_comment( $_GET['del_response'] );
}

add_action( 'admin_init', 'ns_del_admin_response' );


/**
 * Delete Response in admin panel.
 * 
 * AJAX powered deletion of response.
 *
 * @since  1.0.0
 * -----------------------------------------------------------------------
 */
function ns_del_ajax_response() {
    if( isset( $_POST['id'] ) ) {
        $comment_id = $_POST['id'];
        // Move to trash
        wp_delete_comment( $comment_id );
        echo $comment_id;
        die;
    } else {
        echo false;
        die;
    }
}

add_action( 'wp_ajax_delete_response', 'ns_del_ajax_response' );
