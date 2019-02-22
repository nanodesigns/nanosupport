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
 * @link   https://wordpress.stackexchange.com/a/186281/22728
 *
 * hooked: pre_get_comments (10)
 *
 * @param  \WP_Comment_Query $query All the comments from comments table.
 * @return \WP_Comment_Query        Filtering 'nanosupport_response' hiding them.
 * -----------------------------------------------------------------------
 */
function ns_exclude_responses_in_comments(\WP_Comment_Query $query) {
	if( is_admin() ) {
		$screen = get_current_screen();
		if( !('edit' === $screen->parent_base && 'nanosupport' === $screen->post_type) ) {
			/* only allow 'nanosupport_response' and 'nanosupport_change' when is required explicitly */
			if ( ! in_array($query->query_vars['type'], array('nanosupport_response', 'nanosupport_change')) ) {
				$query->query_vars['type__not_in'] = array_merge((array) $query->query_vars['type__not_in'], array('nanosupport_response', 'nanosupport_change'));
			}
		}
	}
}

add_action( 'pre_get_comments', 'ns_exclude_responses_in_comments' );


/**
 * Process Ticket Submission
 *
 * Process Ticket Submission including Login/Registration.
 * -----------------------------------------------------------------------
 */
function ns_handle_registration_login_ticket_submission() {
	if( ! isset( $_POST['ns_submit'] ) )
		return;

    //form validation here
	global $ns_errors;

	$ns_errors  = array();

    //Ticket Subject
	if( empty( $_POST['ns_ticket_subject'] ) ) {
		$ns_errors[]    = esc_html__( 'Ticket subject can&rsquo;t be empty', 'nanosupport' );
	} else {
		$ticket_subject = $_POST['ns_ticket_subject'];
	}

    //Ticket Details
	$character_limit = ns_is_character_limit();
	if( empty( $_POST['ns_ticket_details'] ) ) {
		$ns_errors[]    = esc_html__( 'Ticket details can&rsquo;t be empty', 'nanosupport' );
	} else if( ! empty( $_POST['ns_ticket_details'] ) && $character_limit && strlen( $_POST['ns_ticket_details'] ) < $character_limit ) {
		$ns_errors[]    = sprintf( esc_html__( 'Write down a little detail. At least %s characters or longer', 'nanosupport' ), $character_limit );
	} else {
		$ticket_details = $_POST['ns_ticket_details'];
	}


    //Ticket Priority
	$priority_displayed = isset($ns_general_settings['is_priority_visible']) ? absint($ns_general_settings['is_priority_visible']) : false;

	if( $priority_displayed && empty( $_POST['ns_ticket_priority'] ) ) {
		$ns_errors[]        = esc_html__( 'Ticket priority must be set', 'nanosupport' );
	} else {
		$ticket_priority    = empty($_POST['ns_ticket_priority']) ? 'low' : $_POST['ns_ticket_priority'];
	}

    // Ticket Department
	$ticket_department      = ! empty($_POST['ns_ticket_department']) ? $_POST['ns_ticket_department'] : '';

    // Ticket Product & Receipt
	$NSECommerce = new NSECommerce();
	if( $NSECommerce->ecommerce_enabled() ) {

        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * ns_mandate_product_fields
         *
         * Hook to moderate the permission for mandating product-specifc fields,
         * or not.
         *
         * @since  1.0.0
         * -----------------------------------------------------------------------
         */
        $mandate_product_fields = apply_filters( 'ns_mandate_product_fields', true );

        if( $mandate_product_fields && empty($_POST['ns_ticket_product']) ) {
        	$ns_errors[]    = esc_html__( 'Adding Product relevent to the ticket is mandatory', 'nanosupport' );
        } else {
        	$ticket_product = ! empty($_POST['ns_ticket_product']) ? $_POST['ns_ticket_product'] : '';
        }

        if( $mandate_product_fields && empty($_POST['ns_ticket_product_receipt']) ) {
        	$ns_errors[]    = esc_html__( 'Product Receipt must be mentioned for further enquiry', 'nanosupport' );
        } else {

        	/**
        	 * -----------------------------------------------------------------------
        	 * HOOK : FILTER HOOK
        	 * ns_check_receipt_validity
        	 *
        	 * Enable/Disable receipt validity checking.
        	 *
        	 * @since  1.0.0
        	 * -----------------------------------------------------------------------
        	 */
        	if( apply_filters( 'ns_check_receipt_validity', true ) ) {
        		$_product_info = $NSECommerce->get_product_info($ticket_product, $_POST['ns_ticket_product_receipt']);
        		if( empty($_product_info->purchase_date) ) {
        			$ns_errors[] = esc_html__( 'Your Product Receipt seems not valid', 'nanosupport' );
        		}
        	} else {
        		$ticket_priority = 'low';
        	}

        	$ticket_receipt = ! empty($_POST['ns_ticket_product_receipt']) ? $_POST['ns_ticket_product_receipt'] : '';

        }
    }

    //------------------ERROR: There are errors - don't go further
    if( ! empty( $ns_errors ) ){
    	return;
    }


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

        if( ns_is_user( 'manager' ) )
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

        $username = sanitize_user( $_POST['login_name'] );
        $password = $_POST['login_password'];

        if( empty( $username ) ) {
        	$ns_errors[] = esc_html__( 'Username cannot be empty', 'nanosupport' );
        }

        if( empty( $password ) ) {
        	$ns_errors[] = esc_html__( 'Password must be filled', 'nanosupport' );
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

        $user = get_user_by( 'login', $username );

        if ( ! $user && strpos( $user_name, '@' ) && $get_username_from_email ) {
        	$user = get_user_by( 'email', $username );
        }

        if( isset( $user->user_login ) ) {
        	$creds['user_login'] = $user->user_login;
        } else {
        	$ns_errors[] = esc_html__( 'There is no user found with this email address', 'nanosupport' );
        }

        $creds['user_password'] = $password;
        $creds['remember']      = isset( $_POST['rememberme'] );
        $secure_cookie          = is_ssl() ? true : false;

        if( !empty( $username ) && !empty($password) ) {
            //Log the user in
        	$user = wp_signon(
                        /**
                         * -----------------------------------------------------------------------
                         * HOOK : FILTER HOOK
                         * nanosupport_login_credentials
                         *
                         * To intercept the login credentials array.
                         *
                         * @since  1.0.0
                         * -----------------------------------------------------------------------
                         */
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
        $username   = isset($options['account_creation']['generate_username']) && $options['account_creation']['generate_username'] != 0 ? '' : $_POST['reg_name'];
        $password   = isset($options['account_creation']['generate_password']) && $options['account_creation']['generate_password'] != 0 ? '' : $_POST['reg_password'];
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

    //User identity is not acceptable
    if( empty( $user_id ) )
    	$ns_errors[]   = esc_html__( 'Sorry, your user identity is not acceptable! Your ticket is not submitted.', 'nanosupport' );


    //------------------ERROR: There are errors - don't go further
    if( ! empty( $ns_errors ) ){
    	return;
    }

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    /**
     * Save Ticket Information.
     *
     * Finally save the ticket information into the database
     * using user credentials from above.
     */
    if( ! empty( $user_id ) && empty( $ns_errors ) ){

    	$ticket_data = array(
    		'post_status'     => $post_status,
    		'post_type'       => 'nanosupport',
    		'post_author'     => $user_id,
    		'post_title'      => $ticket_subject,
    		'post_content'    => $ticket_details,
    		'post_date'       => date( 'Y-m-d H:i:s', current_time('timestamp') ),

    		'ticket_status'   => 'open',
    		'ticket_priority' => $ticket_priority,
			'ticket_agent'    => '', //empty: no ticket agent's assigned
		);

        /**
         * Assign department from user choice, if enabled.
         */
        $display_department = isset($ns_general_settings['is_department_visible']) ? absint($ns_general_settings['is_department_visible']) : false;

        if( $display_department && ! empty($ticket_department) ) {
        	$ticket_data = array_merge($ticket_data, array('department' => $ticket_department));
        }

        if( $NSECommerce->ecommerce_enabled() ) {
        	$ticket_data = array_merge($ticket_data,
        		array(
        			'ticket_product' => $ticket_product,
        			'ticket_receipt' => $ticket_receipt
        		));
        }

        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * ns_ticket_data
         *
         * Filter Ticket post data and meta data before saving.
         *
         * @since  1.0.0
         * -----------------------------------------------------------------------
         */
        $ticket_data = apply_filters( 'ns_ticket_data', $ticket_data );

        $ticket_post_id = wp_insert_post( array(
        	'post_status'  => wp_strip_all_tags( $ticket_data['post_status'] ),
        	'post_type'    => wp_strip_all_tags( $ticket_data['post_type'] ),
        	'post_author'  => absint( $ticket_data['post_author'] ),

        	'post_title'   => wp_strip_all_tags( $ticket_data['post_title'] ),
        	'post_content' => wp_kses( $ticket_data['post_content'], ns_allowed_html() ),
        	'post_date'    => wp_strip_all_tags( $ticket_data['post_date'] )
        ) );

        //set the department if one is chosen (whatever the user role is...)
        if( $display_department && ! empty($ticket_department) ) {
        	wp_set_object_terms( $ticket_post_id, (int) $ticket_data['department'], 'nanosupport_department' );
        }

        // Insert the meta information into postmeta.
        add_post_meta( $ticket_post_id, '_ns_ticket_status',   wp_strip_all_tags( $ticket_data['ticket_status'] ) );
        add_post_meta( $ticket_post_id, '_ns_ticket_priority', wp_strip_all_tags( $ticket_data['ticket_priority'] ) );
        add_post_meta( $ticket_post_id, '_ns_ticket_agent',    wp_strip_all_tags( $ticket_data['ticket_agent'] ) );

        // Save ticket product information.
        if( $NSECommerce->ecommerce_enabled() ) {
        	add_post_meta( $ticket_post_id, '_ns_ticket_product', wp_strip_all_tags( $ticket_data['ticket_product'] ) );
        	add_post_meta( $ticket_post_id, '_ns_ticket_product_receipt', wp_strip_all_tags( $ticket_data['ticket_receipt'] ) );
        }

    }

    //Redirect to the same page with success message
    $args = add_query_arg(
    	'ns_success',
    	1,
    	get_permalink( $ns_general_settings['submit_page'] )
    );
    wp_redirect( esc_url( $args ) );
    exit();
}

add_action( 'template_redirect', 'ns_handle_registration_login_ticket_submission' );


/**
 * Preview email template
 *
 * Display a preview of the email template according to
 * the NanoSupport Email Template admin settings.
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
			ns_get_template_part( 'content-email.php' );
		$email_content = ob_get_clean();

		ob_start();
            //get the email content for the preview
			include 'admin/ns-email-template-preview.php';
		$message = ob_get_clean();

		$email_subhead = esc_html__( 'Email Template Preview', 'nanosupport' );

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
			<div class="ns-col-md-7 ns-col-sm-6 ns-well-left ns-text-muted ns-small">
				<?php if( isset($ns_general_settings['knowledgebase_notice']) ) echo esc_html($ns_general_settings['knowledgebase_notice']); ?>
			</div>
			<div class="ns-col-md-5 ns-col-sm-6 ns-well-right ns-text-right">
				<a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="ns-btn ns-btn-sm ns-btn-primary">
					<i class="ns-icon-tag" aria-hidden="true"></i> <?php echo ns_is_user('agent_and_manager') ? esc_html__( 'All the Tickets', 'nanosupport' ) : esc_html__( 'My Tickets', 'nanosupport' ); ?>
				</a>
				<a class="ns-btn ns-btn-sm ns-btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
					<i class="ns-icon-tag" aria-hidden="true"></i> <?php esc_html_e( 'Submit Ticket', 'nanosupport' ); ?>
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
 * hooked: nanosupport_before_new_ticket (10)
 * -----------------------------------------------------------------------
 */
function ns_new_ticket_navigation() {

    //Get the NanoSupport Settings from Database
	$ns_general_settings       = get_option( 'nanosupport_settings' );
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

	$enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : true;

    //If not enabled, don't display the notice and navigation
	if( ! $enable_notice )
		return;

	ob_start(); ?>

	<div class="ns-well ns-well-sm">
		<div class="ns-row">
			<div class="ns-col-md-5 ns-col-sm-6 ns-well-left">
				<a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="ns-btn ns-btn-sm ns-btn-primary">
					<i class="ns-icon-tag" aria-hidden="true"></i> <?php echo ns_is_user('agent_and_manager') ? esc_html__( 'All the Tickets', 'nanosupport' ) : esc_html__( 'My Tickets', 'nanosupport' ); ?>
				</a>
				<?php
                /**
                 * Display Knowledgebase on demand
                 * Display, if enabled in admin panel.
                 */
                if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
                	<a class="ns-btn ns-btn-sm ns-btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                		<i class="ns-icon-docs" aria-hidden="true"></i> <?php esc_html_e( 'Knowledgebase', 'nanosupport' ); ?>
                	</a>
                <?php } ?>
            </div>
            <div class="ns-col-md-7 ns-col-sm-6 ns-well-right ns-text-muted ns-small">
            	<?php if( isset($ns_general_settings['submit_ticket_notice']) ) echo esc_html($ns_general_settings['submit_ticket_notice']); ?>
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
 * hooked: nanosupport_before_support_desk (10)
 * -----------------------------------------------------------------------
 */
function ns_support_desk_navigation() {

    //Get the NanoSupport Settings from Database
	$ns_general_settings       = get_option( 'nanosupport_settings' );
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

	$enable_notice = isset($ns_general_settings['enable_notice']) ? absint($ns_general_settings['enable_notice']) : true;

    //If not enabled, don't display the notice and navigation
	if( ! $enable_notice )
		return;

	ob_start(); ?>

	<div class="ns-well ns-well-sm">
		<div class="ns-row">
			<div class="ns-col-md-7 ns-col-sm-6 ns-well-left ns-text-muted ns-small">
				<?php if( isset($ns_general_settings['support_desk_notice']) ) echo esc_html($ns_general_settings['support_desk_notice']); ?>
			</div>
			<div class="ns-col-md-5 ns-col-sm-6 ns-well-right ns-text-right">
				<?php
                /**
                 * Display Knowledgebase on demand
                 * Display, if enabled in admin panel.
                 */
                if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
                	<a class="ns-btn ns-btn-sm ns-btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( $ns_knowledgebase_settings['page'] ) ); ?>">
                		<i class="ns-icon-docs" aria-hidden="true"></i> <?php esc_html_e( 'Knowledgebase', 'nanosupport' ); ?>
                	</a>
                <?php } ?>
                <a class="ns-btn ns-btn-sm ns-btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                	<i class="ns-icon-tag" aria-hidden="true"></i> <?php esc_html_e( 'Submit Ticket', 'nanosupport' ); ?>
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
    			/* translators: error message */
    			printf( __( '<strong>Error:</strong> %s', 'nanosupport' ), $error );
    			echo '</div>';
    		}
    	}

        //Display success message, if any
    	if( isset($_GET['ns_success']) ) {
    		echo '<div class="ns-alert ns-alert-success" role="alert">';
    		echo wp_kses( __( '<strong>Success:</strong> Your response is successfully submitted to this ticket.', 'nanosupport' ), array('strong' => array()) );
    		echo '</div>';
    	} else if( isset($_GET['ns_cm_success']) ) {
    		echo '<div class="ns-alert ns-alert-success" role="alert">';
    		echo wp_kses( __( '<strong>Success:</strong> The ticket is marked as &lsquo;Solved&rsquo; with this response.', 'nanosupport' ), array('strong' => array()) );
    		echo '</div>';
    	} else if( isset($_GET['ns_closed']) ) {
    		echo '<div class="ns-alert ns-alert-success" role="alert">';
    		echo wp_kses( __( '<strong>Success:</strong> You just marked the ticket as &lsquo;Solved&rsquo;.', 'nanosupport' ), array('strong' => array()) );
    		echo '</div>';
    	}

        // Get ticket information
    	$ticket_meta = ns_get_ticket_meta( $post->ID );

        // For solved tickets, display a way to reOpen the ticket
    	if( 'solved' === $ticket_meta['status']['value'] && ! ( isset( $_GET['reopen'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'reopen-ticket' ) ) ) {
    		$reopen_url = add_query_arg( 'reopen', '', get_the_permalink() );
    		echo '<div class="ns-alert ns-alert-success" role="alert">';
    		echo esc_html__( 'This ticket is already solved.', 'nanosupport' );
    		echo '&nbsp;<a class="ns-btn ns-btn-sm ns-btn-warning" href="'. wp_nonce_url( $reopen_url, 'reopen-ticket' ) .'#write-message"><i class="ns-icon-repeat" aria-hidden="true"></i>&nbsp;';
    		esc_html_e( 'Reopen Ticket', 'nanosupport' );
    		echo '</a>';
    		echo '</div>';

            // and don't display the form
    		return;
    	}

        //Clean up request URI from temporary args for alert[s].
    	$_SERVER['REQUEST_URI'] = remove_query_arg( array('ns_success', 'ns_cm_success', 'ns_closed'), $_SERVER['REQUEST_URI'] );

    	if( ns_is_user( 'agent_and_manager' ) || $post->post_author == $current_user->ID ) : ?>

    		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

    			<div class="ns-cards ns-feedback">
    				<div class="ns-row">
    					<div class="ns-col-sm-9">
    						<div class="response-head">
    							<h3 class="ticket-head" id="new-response">
    								<?php
    								/* translators: User display name */
    								printf( esc_html__('Responding as: %s','nanosupport'), $current_user->display_name ); ?>
    							</h3>
    						</div> <!-- /.response-head -->
    					</div>
    					<div class="ns-col-sm-3 response-dates ns-small">
    						<?php echo ns_date_time( current_time('timestamp') ); ?>
    					</div>
    				</div> <!-- /.ns-row -->
    				<div class="ns-feedback-form">

    					<div class="ns-form-group">
    						<textarea name="ns_response_msg" id="write-message" class="ns-form-control" placeholder="<?php esc_attr_e('Write down your response', 'nanosupport'); ?>" rows="6" aria-label="<?php esc_attr_e('Write down the response to the ticket', 'nanosupport'); ?>"><?php echo isset($_POST['ns_response_msg']) ? $_POST['ns_response_msg'] : ''; ?></textarea>
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
                        	<?php esc_html_e( 'Submit', 'nanosupport' ); ?>
                        </button>

                        <?php if( in_array( $ticket_meta['status']['value'], array('open', 'inspection') ) ) { ?>
                        	<button type="submit" name="close_ticket" class="ns-btn ns-btn-default">
                        		<?php esc_html_e( 'Close Ticket', 'nanosupport' ); ?>
                        	</button>
                        <?php } //endif open/inspection ?>

                    </div>
                </div> <!-- /.ns-feedback-form -->

            </form>

            <?php
        else :

        	echo '<div class="ns-alert ns-alert-info" role="alert">';
        	if( 'solved' === $ticket_meta['status']['value'] ) {
        		echo wp_kses( __( '<strong>Resolved!</strong> New Responses to this ticket is already closed. Only ticket author can reopen a closed ticket.', 'nanosupport' ), array('strong' => array()) );
        	} else {
        		echo wp_kses( __( '<strong>Sorry!</strong> Tickets are open for responses only to the Ticket Author.', 'nanosupport' ), array('strong' => array()) );
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
 * hooked: nanosupport_after_response_form (10)
 * -----------------------------------------------------------------------
 */
function ns_notify_user_on_opening_ticket() {
	global $post;
	$ticket_meta = ns_get_ticket_meta( $post->ID );

	if( 'pending' === $ticket_meta['status']['value'] ) {
		echo '<div class="ns-alert ns-alert-normal" role="alert">';
		echo wp_kses( __( '<strong>Just to inform:</strong> This ticket is still <em>pending</em>. With this response it&rsquo;ll be opened.', 'nanosupport' ), array('strong' => array(), 'em' => array()) );
		echo '</div>';
	}

	if( 'solved' === $ticket_meta['status']['value'] && isset( $_GET['reopen'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'reopen-ticket' ) ) {
		echo '<div class="ns-alert ns-alert-warning" role="alert">';
		echo wp_kses( __( '<strong>Just to inform:</strong> you are about to ReOpen the ticket.', 'nanosupport' ), array('strong' => array()) );
		echo '&nbsp;<a class="ns-small" href="'. get_the_permalink($post) .'">';
		echo esc_html__( 'Cancel ReOpening', 'nanosupport' );
		echo '</a>';
		echo '</div>';
	}
}

add_action( 'nanosupport_after_response_form', 'ns_notify_user_on_opening_ticket' );


/**
 * Process Response Submission
 *
 * Process Response Submission and redirect with success.
 * -----------------------------------------------------------------------
 */
function ns_handle_response_submit() {
	if( ! is_user_logged_in() )
		return;

	if( (isset($_POST['submit_response']) || isset($_POST['close_ticket'])) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'nanosupport-response-nonce' ) ) :

		global $current_user, $post, $response_error;

        // new error object
		$response_error = new WP_Error();

		$response_msg   = $_POST['ns_response_msg'];

	        //Response is not for closing so a message is required
		if( empty($response_msg) && ! isset($_POST['close_ticket']) ) {
			$response_error->add( 'response_empty', esc_html__( 'Response field can&rsquo;t be blank.', 'nanosupport' ) );
		}

		if( is_wp_error($response_error) && ! empty($response_error->errors) )
			return;

	        // Submitting response with/without closing
		if( ! empty($response_msg) && (isset($_POST['close_ticket']) || isset($_POST['submit_response'])) ) {

	        /**
	         * Sanitize ticket response content
	         * @var string
	         */
	        $response_msg = wp_kses( $response_msg, ns_allowed_html() );

	        //Insert new response as a comment and get the comment ID
	        $commentdata = array(
	        	'comment_post_ID'       => absint( $post->ID )   ,
	        	'comment_author'        => wp_strip_all_tags( $current_user->display_name ),
	        	'comment_author_email'  => sanitize_email( $current_user->user_email ),
	        	'comment_author_url'    => esc_url( $current_user->user_url ),
	        	'comment_content'       => $response_msg,
	        	'comment_type'          => 'nanosupport_response',
	        	'comment_parent'        => 0,
	        	'user_id'               => absint( $current_user->ID ),
	        );

	        $comment_id = wp_new_comment( $commentdata );

	        //If error, return with the error message
	        if( is_wp_error($comment_id) )
	        	return $comment_id->get_error_message();

	    }

	    // Get ticket meta information
	    $ticket_meta    = ns_get_ticket_meta( $post->ID );
	    $ticket_status  = isset($ticket_meta['status']['value']) ? $ticket_meta['status']['value'] : 'open';

	    /**
	     * ReOpen a solved ticket,
	     * or Open a pending ticket.
	     * ...
	     */
	    if( in_array( $ticket_status, array('solved', 'pending') ) ) {
	    	update_post_meta( $post->ID, '_ns_ticket_status', 'open' );
	    	ns_update_post_modified_date( $post->ID );
	    }

	    /**
	     * Close a ticket,
	     * if closed chosen.
	     * ...
	     */
	    if( in_array($ticket_status, array('inspection', 'open')) && isset($_POST['close_ticket']) ) {
	    	update_post_meta( $post->ID, '_ns_ticket_status', 'solved' );
	    	ns_update_post_modified_date( $post->ID );
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

	    if( ! empty($response_msg) && isset($_POST['submit_response']) ) {
	        // Response submitted
	    	$args = add_query_arg( 'ns_success', '', $permalink );
	    } else if( ! empty($response_msg) && isset($_POST['close_ticket']) ) {
	        // Closed with Response
	    	$args = add_query_arg( 'ns_cm_success', '', $permalink );
	    } else if( empty($response_msg) && isset($_POST['close_ticket']) ) {
	        // Closed without Response
	    	$args = add_query_arg( 'ns_closed', '', $permalink );
	    }

	    wp_redirect( esc_url( $args ) );
	    exit();

    endif;

}

add_action( 'template_redirect', 'ns_handle_response_submit' );


/**
 * Make NanoSupport responses approved by default.
 *
 * Using wp_new_comment() won't make comments (responses) approved by default.
 * Therefore we need to manually intervene and make them approved before saving.
 *
 * @author gmazzap
 * @link   https://wordpress.stackexchange.com/a/186281/22728
 *
 * @param  boolean  $approved       Signifies the approval status (0|1|'spam').
 * @param  array    $commentdata    Information about the comment.
 * @return int|string               1 if 'nanosupport_response'.
 * -----------------------------------------------------------------------
 */
function ns_make_responses_approved( $approved, $commentdata ) {
	return isset($commentdata['comment_type']) && ($commentdata['comment_type'] === 'nanosupport_response') ? 1 : $approved;
}

add_filter( 'pre_comment_approved', 'ns_make_responses_approved', 20, 2 );


/**
 * Delete response in Admin panel.
 *
 * Delete individual response in admin panel edit post.
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


/**
 * Include agent's tickets.
 *
 * Hooked in Support Desk shortcode.
 *
 * @param  array $clauses       Query clauses.
 * @param  object $query_object WP Query object.
 * @return array                Modified query clauses.
 * -----------------------------------------------------------------------
 */
function ns_change_query_to_include_agents_tickets( $clauses, $query_object ) {
	if( ns_is_user('agent') ) {
		global $wpdb, $current_user;
		$clauses['where'] = " AND ";
		$clauses['where'] .= "( {$wpdb->posts}.post_author IN ({$current_user->ID})
		OR (({$wpdb->postmeta}.meta_key = '_ns_ticket_agent' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) = '{$current_user->ID}')) )";
		$clauses['where'] .= " AND {$wpdb->posts}.post_type = 'nanosupport' ";
		$clauses['where'] .= " AND ({$wpdb->posts}.post_status = 'publish'
		OR {$wpdb->posts}.post_status = 'future'
		OR {$wpdb->posts}.post_status = 'draft'
		OR {$wpdb->posts}.post_status = 'pending'
		OR {$wpdb->posts}.post_status = 'private') ";
	}
	return $clauses;
}


/**
 * Filter CPT 'nanodoc' arguments.
 *
 * @param  array $array  Arguments array.
 * @return array         Modified arguments array.
 * -----------------------------------------------------------------------
 */
function ns_filter_nanodoc_arguments( $array ) {
    //get Knowledgebase settings from db
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

    /**
     * Initiate URL rewriting on demand.
     * change, if enabled in admin panel.
     */
    if( isset($ns_knowledgebase_settings['rewrite_url']) && $ns_knowledgebase_settings['rewrite_url'] === 1 ) {
    	$array['rewrite'] = array( 'slug' => 'knowledgebase/%nanodoc_category%' );
    }

    return $array;
}

add_filter( 'ns_nanodoc_arguments', 'ns_filter_nanodoc_arguments' );
