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
 * Custom Registration Form
 *
 * A customized front-end registration form
 * especially for Nanodesigns Support Ticket front-end registration.
 *
 * @since   1.0.0
 *
 * @author  Agbonghama Collins
 * @link    http://designmodo.com/wordpress-custom-registration/
 * ------------------------------------------------------------------------------
 */
function nanosupport_reg_validate( $ns_reg_username, $ns_reg_email, $ns_reg_password ) {

    if ( empty( $ns_reg_username ) || empty( $ns_reg_password ) || empty( $ns_reg_email ) ) {
        return new WP_Error('field', 'Required form field is missing');
    }

    if ( strlen( $ns_reg_username ) < 4 ) {
        return new WP_Error('username_length', 'Username is too short. At least 4 characters is required');
    }

    if ( strlen( $ns_reg_password ) < 5 ) {
        return new WP_Error('password', 'Password length must be greater than 5');
    }

    if ( ! is_email( $ns_reg_email ) ) {
        return new WP_Error('email_invalid', 'Email is not valid');
    }

    if ( email_exists( $ns_reg_email ) ) {
        return new WP_Error('email', 'Email already in use');
    }

    $details = array(
        'Username' => $ns_reg_username
    );

    foreach ( $details as $field => $detail ) {
        if ( ! validate_username( $detail ) ) {
            return new WP_Error('name_invalid', 'Sorry, the "'. $field .'" you entered is not valid');
        }
    }
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
