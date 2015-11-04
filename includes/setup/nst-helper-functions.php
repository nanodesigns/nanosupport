<?php
/**
 * Helper Functions.
 *
 * Stored all the helper functions that are ocassionally used
 * for specific purposes only.
 *
 * @package nano_support_ticket
 * =======================================================================
 */

/**
 * Create Pages.
 * 
 * Create necessary pages for the plugin.
 * 
 * @param  string $title   Title of the page.
 * @param  string $slug    Hyphenated slug of the page.
 * @param  string $content Anything of a wide range of alphanumeric contents.
 * @return integer         ID of the page that is created or already exists.
 * -----------------------------------------------------------------------
 */
function nst_create_necessary_page( $title, $slug, $content ) {

    global $current_user;

    //set a default so that we can check nothing happend
    $page_id = -1;

    $nst_check_page = get_page_by_path( $slug ); //default post type 'page'

    if( null === $nst_check_page ) {

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

        return $nst_check_page->ID;

        //arbitrarily taken -2 so that we can understand the given page already exists
        //$page_id = -2

    }

}


/**
 * Get last response information.
 *
 * Get the last response informatin including response ID, user ID,
 * and response date.
 * 
 * @param  integer 	$ticket_id 	Nanosupport post ID.
 * @return array 				comment_ID, user_id, comment_date
 * -----------------------------------------------------------------------
 */
function nst_get_last_response( $ticket_id ) {
    
    global $wpdb;
    $query = "SELECT comment_ID, user_id, comment_date
                FROM $wpdb->comments
                WHERE comment_ID = ( SELECT MAX(comment_ID)
                                        FROM $wpdb->comments
                                        WHERE comment_type = 'nanosupport_response'
                                            AND comment_post_ID = $ticket_id
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
 * Response exists or not.
 * 
 * Check whether the Response is already exists or not.
 * 
 * @param  integer $comment_ID.
 * @return integer The comment_ID if it exists.
 * -----------------------------------------------------------------------
 */
function nst_response_exists( $comment_ID ) {
    
    global $wpdb;

    $comment_ID = absint( $comment_ID );
 
    return $wpdb->get_var( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments
            WHERE comment_ID = %s", $comment_ID ) );
}


/**
 * Pagination.
 * 
 * Paginate_links enabled with Bootstrap Pagination.
 *
 * @author  Erik Larsson
 * @link http://www.ordinarycoder.com/paginate_links-class-ul-li-bootstrap/
 * 
 * @param  object $query the query where the pagination is called.
 * -----------------------------------------------------------------------
 */
function nst_bootstrap_pagination( $query ) {

	global $wp_query;
	$query = $query ? $query : $wp_query;

	echo '<nav class="nst-pagination">';
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
 * Elapsed time.
 *
 * Calculates how much time elapsed from a time mentioned.
 *
 * @author  arnorhs
 * @link 	http://stackoverflow.com/a/2916189/1743124
 * 
 * @param  string $time Date & Time string.
 * @return string       Elapsed time.
 * -----------------------------------------------------------------------
 */
function nst_time_elapsed( $time ) {
	$time = strtotime( $time );

    $time = time() - $time; // to get the time since that moment

    $tokens = array (
        31536000 => __( 'year','nanodesigns-nst' ),
        2592000 => __( 'month','nanodesigns-nst' ),
        604800 => __( 'week','nanodesigns-nst' ),
        86400 => __( 'day','nanodesigns-nst' ),
        3600 => __( 'hour','nanodesigns-nst' ),
        60 => __( 'minute','nanodesigns-nst' ),
        1 => __( 'second','nanodesigns-nst' )
    );

    foreach ( $tokens as $unit => $text ) {
        if ( $time < $unit )
        	continue;
        
        $number_of_units = floor( $time / $unit );

        return $number_of_units .' '. $text . ( ( $number_of_units > 1 ) ? 's' : '' );
    }
}


/**
 * Get pending ticket counts.
 * 
 * @return integer Total number of pending tickets.
 * -----------------------------------------------------------------------
 */
function nst_pending_tickets_count() {
	$get_pending_posts = new WP_Query( array(
								'post_type'			=> 'nanosupport',
								'post_status'		=> 'pending',
								'posts_per_page'	=> -1
							) );
	$pending_ticket_count = $get_pending_posts->found_posts;
	wp_reset_postdata();

	return (int) $pending_ticket_count;
}