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

    $_departments = get_the_terms( $post_id, 'nanosupport_department' );

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


/**
 * Get count on Ticket Status
 * 
 * Used in NanoSupport Dashboard for displaying the graph.
 * 
 * @since  1.0.0
 * 
 * @param  string $status Ticket status.
 * @return integer        Number of tickets with that status.
 * --------------------------------------------------------------------------
 */
function ns_ticket_status_count( $status = '' ) {
    if( empty($status) )
        return;

    global $wpdb;
    if( 'pending' === $status ) {
        return $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'nanosupport' AND post_status = %s",
                        $status
                    )
                );
    } else {
        return $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*)
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta
                            ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                        WHERE post_type = 'nanosupport'
                            AND post_status IN('private', 'publish')
                            AND meta_key = 'ns_control'
                            AND meta_value LIKE %s",
                        '%'. $wpdb->esc_like($status) .'%'
                    )
                );
    }
}


/**
 * Get count of all the tickets.
 *
 * Used basically on admin dashboard.
 *
 * @since  1.0.0
 * 
 * @param  string $post_type The support ticket post type.
 * @return integer           The total numbers of tickets.
 * --------------------------------------------------------------------------
 */
function ns_total_ticket_count( $post_type = '' ) {
    if( empty($post_type) )
        return;

    global $wpdb;
    return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status IN('pending', 'publish', 'private')", $post_type ) );
}


/**
 * Tooltip
 *
 * The conflict-free CSS tooltip.
 *
 * @since  1.0.0
 * 
 * @param  string $message  The i18 message for tooltip.
 * @param  string $position Position where to appear the tooltip (below|left|right).
 * @param  string $type     Type of the tooltip icon (question|info).
 * @return string           The formatted tooltip with its icon.
 */
function ns_tooltip( $message = '', $position = 'right', $type = '' ) {
    $icon_class = 'info' === $type ? 'ns-icon-info-circled' : 'ns-icon-help-circled';
    switch ($position) {
        case 'left':
            $position_class = 'ns-tooltip-message-left';
            break;

        case 'right':
            $position_class = 'ns-tooltip-message-right';
            break;

        case 'below':
            $position_class = 'ns-tooltip-message-below';
            break;
        
        default:
            $position_class = 'ns-tooltip-message-right';
            break;
    }

    $tooltip = '<span class="ns-tooltip '. esc_attr($icon_class) .'">';
        $tooltip .= '<span class="ns-tooltip-message '. esc_attr($position_class) .'">';
            $tooltip .= $message;
        $tooltip .= '</span>';
    $tooltip .= '</span>';

    return $tooltip;
}
