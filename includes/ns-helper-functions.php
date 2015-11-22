<?php
/**
 * Helper Functions
 *
 * Stored all the helper functions that are ocassionally used
 * for specific purposes only.
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create Pages
 * 
 * Create necessary pages for the plugin.
 * 
 * @param  string $title   Title of the page.
 * @param  string $slug    Hyphenated slug of the page.
 * @param  string $content Anything of a wide range of alphanumeric contents.
 * @return integer         ID of the page that is created or already exists.
 * -----------------------------------------------------------------------
 */
function ns_create_necessary_page( $title, $slug, $content ) {

    global $current_user;

    //set a default so that we can check nothing happend
    $page_id = -1;

    $ns_check_page = get_page_by_path( $slug ); //default post type 'page'

    if( null === $ns_check_page ) {

        //set the page_id as the page created
        $page_id = wp_insert_post( array(
                                        'post_title'        => sanitize_text_field( $title ),
                                        'post_name'         => sanitize_text_field( $slug ),
                                        'post_content'      => htmlentities( $content ),
                                        'post_status'       => 'publish',
                                        'post_type'         => 'page',
                                        'post_author'       => absint( $current_user->ID ),
                                        'comment_status'    => 'closed',
                                        'ping_status'       => 'closed'
                                    ) );

        return $page_id;

    } else {

        return $ns_check_page->ID;

        //arbitrarily taken -2 so that we can understand the given page already exists
        //$page_id = -2

    }

}


/**
 * Get last response information
 *
 * Get the last response informatin including response ID, user ID,
 * and response date.
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
 * @param  integer $comment_ID.
 * @return integer The comment_ID if it exists.
 * -----------------------------------------------------------------------
 */
function ns_response_exists( $comment_ID ) {
    
    global $wpdb;

    $comment_ID = absint( $comment_ID );
 
    return $wpdb->get_var( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments
            WHERE comment_ID = %s", $comment_ID ) );
}


/**
 * Pagination
 * 
 * Paginate_links enabled with Bootstrap Pagination.
 *
 * @author  Erik Larsson
 * @link http://www.ordinarycoder.com/paginate_links-class-ul-li-bootstrap/
 * 
 * @param  object $query the query where the pagination is called.
 * -----------------------------------------------------------------------
 */
function ns_bootstrap_pagination( $query ) {

	global $wp_query;
	$query = $query ? $query : $wp_query;

	echo '<nav class="ns-pagination">';
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
				echo '<ul class="pagination pagination-sm">';
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