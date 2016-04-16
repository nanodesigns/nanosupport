<?php
/**
 * Helper Functions
 *
 * Stored all the helper functions that are ocassionally used
 * for specific purposes only.
 *
 * @author      nanodesigns
 * @category    Helpers
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get last response information
 *
 * Get the last response informatin including response ID, user ID,
 * and response date.
 *
 * @since  1.0.0
 * 
 * @param  integer 	$ticket_id 	NanoSupport post ID.
 * @return array 				comment_ID, user_id, comment_date
 * -----------------------------------------------------------------------
 */
function ns_get_last_response( $ticket_id = null ) {

    $post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;
    
    global $wpdb;
    $query = "SELECT comment_ID, user_id, comment_date
                FROM $wpdb->comments
                WHERE comment_ID = ( SELECT MAX(comment_ID)
                                        FROM $wpdb->comments
                                        WHERE comment_type = 'nanosupport_response'
                                            AND comment_post_ID = $post_id
                                            AND comment_approved = 1
                                    )";
    $max_comment_array = $wpdb->get_results( $query, ARRAY_A );

    if( $max_comment_array ) {
        $last_response = $max_comment_array[0];
    } else {
        $last_response = array(
                            'comment_ID'    => '',
                            'user_id'       => '',
                            'comment_date'  => ''
						);
    }

    return $last_response;
}


/**
 * Response exists or not
 * 
 * Check whether the Response is already exists or not.
 *
 * @since  1.0.0
 * 
 * @param  integer $comment_ID  Comment ID.
 * @return integer              The comment_ID if it exists.
 * -----------------------------------------------------------------------
 */
function ns_response_exists( $comment_ID ) {
    global $wpdb;
    $comment_ID = absint( $comment_ID );
 
    return $wpdb->get_var( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments
            WHERE comment_ID = %s", $comment_ID ) );
}


/**
 * NanoSupport Pagination
 * 
 * Paginate_links enabled with Pagination CSS.
 *
 * @since   1.0.0
 *
 * @author  Erik Larsson
 * @link    http://www.ordinarycoder.com/paginate_links-class-ul-li-bootstrap/
 * 
 * @param   object $query the query where the pagination is called.
 * -----------------------------------------------------------------------
 */
function ns_pagination( $query ) {

	global $wp_query;
	$query = $query ? $query : $wp_query;

	echo '<nav class="nanosupport-pagination">';
		$big = 999999999; // need an unlikely integer
		$total = $query->max_num_pages;
		if( $total > 1 ) {
			$pages = paginate_links( array(
					'base'		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'	=> '?paged=%#%',
					'show_all'	=> false,
					'prev_next'	=> true,
					'prev_text'	=> '&laquo;',
					'next_text'	=> '&raquo;',
					'current'	=> max( 1, get_query_var('paged') ),
					'total'		=> $total,
					'type'		=> 'array'
				) );

			if( $pages ) {
				$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
				echo '<ul class="ns-pagination ns-pagination-sm">';
				foreach ( $pages as $page ) {
					echo '<li>'. $page .'</li>';
				}
				echo '</ul>';
			}
		}
	echo '</nav>';
}


/**
 * Get the ticket departments
 *
 * @since  1.0.0
 * 
 * @param  integer $post_id Ticket Post ID.
 * @return string           Comma-separated names of the departments.
 * -----------------------------------------------------------------------
 */
function ns_get_ticket_departments( $post_id = null ) {
    
    $post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

    $_departments = get_the_terms( $post_id, 'nanosupport_departments' );

    $department_count = count($_departments);
    $departments = '';

    if ( $_departments && ! is_wp_error( $_departments ) ) :
        $counter = 1;
        foreach ( $_departments as $department ) {
           $departments .= $department->name;
           if( $department_count != $counter )
                $departments .= ', ';

           $counter++;
        }
    endif;

    return $departments;
}


/**
 * Create a Support Seeker user account
 *
 * Create a Support Seeker user account on ticket submission
 * for non-logged in users only.
 *
 * @since  1.0.0
 * 
 * @param  string $email    Email provided by the user.
 * @param  string $username Username | null
 * @param  string $password Password | null
 * @return integer|mixed    Registered user ID | Error
 * -----------------------------------------------------------------------
 */
function ns_create_support_seeker( $email, $username = '', $password = '', $antispam = '' ) {

    /**
     * Make the email address ready
     */
    if ( empty($email) || ! is_email($email) ) {
        return new WP_Error( 'reg-error-email-invalid', __( 'Please provide a valid email address', 'nanosupport' ) );
    }

    if ( email_exists($email) ) {
        return new WP_Error( 'reg-error-email-exists', __( 'An account is already registered with your email address. Please login', 'nanosupport' ) );
    }

    /**
     * Make the username ready
     */
    $options = get_option( 'nanosupport_settings' );
    if( $options['account_creation']['generate_username'] !== 1 || ! empty($username) ) {

        //Get the username
        $username = sanitize_user( $username );

        if( empty($username) || ! validate_username($username) ) {
            return new WP_Error( 'reg-error-username-invalid', __( 'Please enter a valid username for creating an account', 'nanosupport' ) );
        }

        if( username_exists($username) ) {
            return new WP_Error( 'reg-error-username-exists', __( 'An account is already registered with that username. Please choose another', 'nanosupport' ) );
        }

    } else {

        //Generate the username from email
        $username = sanitize_user( current( explode( '@', $email ) ), true );

        //Ensure username is unique
        $append         = 1;
        $temp_username  = $username;

        while( username_exists($username) ) {
            $username = $temp_username . $append;
            $append++;
        }

    }

    /**
     * Make the password ready
     */
    if( $options['account_creation']['generate_password'] === 1 && empty($password) ) {

        //Generate the password automatically
        $password = wp_generate_password();
        $password_generated = true;

    } elseif( empty($password) ) {

        return new WP_Error( 'reg-error-password-missing', __( 'Please enter a password for your account', 'nanosupport' ) );

        $password_generated = false;

    } else {

        if ( strlen($password) < 5 ) {
            return new WP_Error( 'reg-error-password-short', __( 'Password length must be greater than 5 characters', 'nanosupport' ) );
        }

        $password_generated = false;

    }

    //Anti-spam HoneyPot Trap Validation
    if ( ! empty( $antispam ) ) {
        return new WP_Error( 'reg-error-spam-detected', __( 'Anti-spam field was filled in. Spam account cannot pass in', 'nanosupport' ) );
    }

    //WP Validation
    $validation_errors = new WP_Error();

    if( $validation_errors->get_error_code() )
        return $validation_errors;

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * nanosupport_new_support_seeker_data
     * 
     * @since  1.0.0
     *
     * @param array  $text New user data to be enterred for creating account.
     * -----------------------------------------------------------------------
     */
    $new_support_seeker_data = apply_filters( 'nanosupport_new_support_seeker_data', array(
        'user_login' => $username,
        'user_email' => $email,
        'user_pass'  => $password,
        'role'       => 'support_seeker'
    ) );

    $user_id = wp_insert_user( $new_support_seeker_data );

    if( is_wp_error($user_id) ) {
        return new WP_Error( 'reg-error', __( 'Couldn&#8217;t register you', 'nanosupport' ) );
    }

    if( $password_generated )
        $account_opening_email = nanosupport_handle_account_opening_email( $user_id, $password );
    else
        $account_opening_email = nanosupport_handle_account_opening_email( $user_id );

    return $user_id;

}


/**
 * Making onDomain Email from Host URL.
 * 
 * @author  Sisir Kanti Adhikari
 * @link    https://github.com/nanodesigns/download-via-email/
 *
 * @since   1.0.0
 *
 * @param   string $username Email username. Default: 'noreply'.
 * @return  string noreply@yourdomain.dom
 * ------------------------------------------------------------------------------
 */
function ns_ondomain_email( $username = 'noreply' ){
    $info   = parse_url( home_url() );
    $host   = $info['host'];
    $domain = preg_replace( '/^www./', '', $host );

    return $username .'@'. $domain;
}

/**
 * Sanitize variables using sanitize_text_field().
 *
 * @since  1.0.0
 * 
 * @param   string|array $var
 * @return  string|array
 * ------------------------------------------------------------------------------
 */
function ns_sanitize_text( $var ) {
    return is_array($var) ? array_map( 'ns_sanitize_text', $var ) : sanitize_text_field($var);
}

/**
 * Show a proper name of the user.
 * 
 * @since 1.0.0
 * 
 * @return string Made up name or the display name.
 * --------------------------------------------------------------------------
 */
function ns_user_nice_name( $user_id = false ) {
    if( $user_id ) {
        $current_user = get_user_by( 'id', (int) $user_id );
    } else {
        global $current_user;       
    }
    
    if( $current_user->user_firstname && $current_user->user_lastname ) {
        $user_nice_name = $current_user->user_firstname .' '. $current_user->user_lastname;
    } else {
        $user_nice_name = $current_user->display_name;
    }

    return wp_strip_all_tags( $user_nice_name );
}
