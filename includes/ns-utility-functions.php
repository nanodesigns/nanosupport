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
 
    return $wpdb->get_var( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_ID = %s", $comment_ID ) );
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

	$big = 999999999; // need an unlikely integer
	$total = $query->max_num_pages;
	if( $total > 1 ) {
        echo '<nav class="nanosupport-pagination">';
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
        echo '</nav>';
	}
	
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
function ns_ticket_status_count( $status = '', $user_id = '' ) {
    if( empty($status) )
        return;

    global $wpdb;
    if( 'pending' === $status ) {
        if( empty($user_id) ) {
            $count = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'nanosupport' AND post_status = %s",
                            $status
                        )
                    );
        } else {
            $count = $wpdb->query(
                        $wpdb->prepare(
                            "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID
                                FROM $wpdb->posts
                                INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                WHERE 1= 1
                                    AND post_type = 'nanosupport'
                                    AND post_status = %s
                                    AND ( $wpdb->posts.post_author IN(%d) OR (($wpdb->postmeta.meta_key = '_ns_ticket_agent' AND CAST($wpdb->postmeta.meta_value AS CHAR) = '%d')) )
                                    GROUP BY $wpdb->posts.ID",
                            $status,
                            $user_id,
                            $user_id
                        )
                    );
        }
    } else {
        if( empty($user_id) ) {
            $count = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*)
                            FROM $wpdb->posts
                            INNER JOIN $wpdb->postmeta
                                ON $wpdb->posts.ID = $wpdb->postmeta.post_id
                            WHERE post_type = 'nanosupport'
                                AND post_status IN('private', 'publish')
                                AND meta_key = '_ns_ticket_status' AND meta_value = %s",
                            $status
                        )
                    );
        } else {
            $count = $wpdb->query(
                        $wpdb->prepare(
                            "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID
                            FROM $wpdb->posts
                            INNER JOIN $wpdb->postmeta AS PM1 ON ($wpdb->posts.ID = PM1.post_id)
                            INNER JOIN $wpdb->postmeta AS PM2 ON ($wpdb->posts.ID = PM2.post_id)
                            WHERE 1=1
                                AND post_type = 'nanosupport'
                                AND post_status IN('private', 'publish')
                                AND ( PM1.meta_key = '_ns_ticket_status' AND PM1.meta_value = %s )
                                AND ( post_author IN(%d) OR ((PM2.meta_key = '_ns_ticket_agent' AND CAST(PM2.meta_value AS CHAR) = '%d')) )
                            GROUP BY $wpdb->posts.ID",
                            $status,
                            $user_id,
                            $user_id
                        )
                    );
        }
    }

    return (int) $count;
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
function ns_total_ticket_count( $post_type = '', $user_id = '' ) {
    if( empty($post_type) )
        return;

    global $wpdb;
    if( empty($user_id) ) {
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status IN('pending', 'publish', 'private')", $post_type ) );
    } else {
        $count = $wpdb->query(
                    $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS $wpdb->posts.ID
                                        FROM $wpdb->posts
                                        INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)
                                        WHERE 1=1
                                            AND post_type = %s
                                            AND post_status IN('pending', 'publish', 'private')
                                            AND ( $wpdb->posts.post_author IN(%d) OR (($wpdb->postmeta.meta_key = '_ns_ticket_agent' AND CAST($wpdb->postmeta.meta_value AS CHAR) = '%d')) )
                                        GROUP BY $wpdb->posts.ID",
                                    $post_type,
                                    $user_id,
                                    $user_id
                                )
                    );
    }

    return (int) $count;
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
 * --------------------------------------------------------------------------
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


/**
 * Get all the ticket meta information
 *
 * Ticket status, combining NanoSupport ticket status along with the
 * default 'pending' status. Ticket priority, all the NanoSupport
 * priorities. Agent, if assigned, with ID and name.
 *
 * @since  1.0.0
 * 
 * @param  integer $ticket_id The ticket post ID.
 * @return array              An array of arrays ('status', 'priority', 'agent')
 * --------------------------------------------------------------------------
 */
function ns_get_ticket_meta( $ticket_id = null ) {

    $post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;

    $_ns_ticket_status   = get_post_meta( $post_id, '_ns_ticket_status', true );
    $_ns_ticket_priority = get_post_meta( $post_id, '_ns_ticket_priority', true );
    $_ns_ticket_agent    = get_post_meta( $post_id, '_ns_ticket_agent', true );

    $this_status    = ! empty( $_ns_ticket_status )     ? $_ns_ticket_status    : 'open';
    $this_priority  = ! empty( $_ns_ticket_priority )   ? $_ns_ticket_priority  : 'low';
    $this_agent     = ! empty( $_ns_ticket_agent )      ? $_ns_ticket_agent     : '';

    /**
     * Ticket status
     * ...
     */
    if( 'pending' === get_post_status($post_id) ) {
    
        $ticket_status = array(
                'value' => 'pending',
                'name'  => __( 'Pending', 'nanosupport' ),
                'class' => 'status-pending',
                'label' => '<span class="ns-label ns-label-normal">'. __( 'Pending', 'nanosupport' ) .'</span>',
            );
    
    } else {
        

        if( 'solved' === $this_status ) {
            $ticket_status = array(
                'value' => 'solved',
                'name'  => __( 'Solved', 'nanosupport' ),
                'class' => 'status-solved',
                'label' => '<span class="ns-label ns-label-success">'. __( 'Solved', 'nanosupport' ) .'</span>',
            );
        } elseif( 'inspection' === $this_status ) {
            $ticket_status = array(
                'value' => 'inspection',
                'name'  => __( 'Under Inspection', 'nanosupport' ),
                'class' => 'status-inspection',
                'label' => '<span class="ns-label ns-label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>',
            );
        } elseif( 'open' === $this_status ) {
            $ticket_status = array(
                'value' => 'open',
                'name'  => __( 'Open', 'nanosupport' ),
                'class' => 'status-open',
                'label' => '<span class="ns-label ns-label-warning">'. __( 'Open', 'nanosupport' ) .'</span>',
            );
        }

    }

    /**
     * Ticket priority
     * ...
     */
    $blink_class    = 'solved' === $this_status ? '' : ' blink';

    if( 'low' === $this_priority ) {
        $priority = array(
                'value' => 'low',
                'name'  => __( 'Low', 'nanosupport' ),
                'class' => 'priority-low',
                'label' => '<span class="ns-text-dim"><i class="ns-dot"></i>'. __( 'Low', 'nanosupport' ) .'</span>',
            );
    } elseif( 'medium' === $this_priority ) {
        $priority = array(
                'value' => 'medium',
                'name'  => __( 'Medium', 'nanosupport' ),
                'class' => 'priority-medium',
                'label' => '<span class="ns-text-info"><i class="ns-dot"></i>'. __( 'Medium', 'nanosupport' ) .'</span>',
            );
    } elseif( 'high' === $this_priority ) {
        $priority = array(
                'value' => 'high',
                'name'  => __( 'High', 'nanosupport' ),
                'class' => 'priority-high',
                'label' => '<span class="ns-text-warning"><i class="ns-dot'. esc_attr($blink_class) .'"></i>'. __( 'High', 'nanosupport' ) .'</span>',
            );
    } elseif( 'critical' === $this_priority ) {
        $priority = array(
                'value' => 'critical',
                'name'  => __( 'Critical', 'nanosupport' ),
                'class' => 'priority-critical',
                'label' => '<span class="ns-text-danger"><i class="ns-dot'. esc_attr($blink_class) .'"></i>'. __( 'Critical', 'nanosupport' ) .'</span>',
            );
    }

    /**
     * Agent
     * ...
     */
    if( empty($this_agent) ) {
        $agent = '';
    } else {
        $agent_name = ns_user_nice_name( $this_agent );
        $agent = array(
                'ID'    => absint( $this_agent ),
                'name'  => $agent_name,
            );
    }

    $ticket_meta = array();

    $ticket_meta['status']      = $ticket_status;
    $ticket_meta['priority']    = $priority;
    $ticket_meta['agent']       = $agent;

    return $ticket_meta;

}

/**
 * Predict pretty permalink for pending tickets.
 *
 * @since  1.0.0
 *
 * @link   http://wordpress.stackexchange.com/a/97606/22728
 * 
 * @param  integer $post_id The ticket post ID.
 * @return string           The pretty permalink for the pending post.
 * --------------------------------------------------------------------------
 */
function ns_get_pending_permalink( $post_id ) {

    require_once ABSPATH . '/wp-admin/includes/post.php';
    list( $permalink, $postname ) = get_sample_permalink( $post_id );

    return str_replace( '%pagename%', $postname, $permalink );
}

/**
 * Display date time as per WP Settings.
 *
 * Always pass a strtotime() UNIX string as a parameter.
 *
 * @since  1.0.0
 * 
 * @param  string $datetime UNIX timestamp.
 * @return string           User chosen timestamp as per General Settings.
 * --------------------------------------------------------------------------
 */
function ns_date_time( $datetime = null ) {
    $date_format = get_option( 'date_format' );
    $time_format = get_option( 'time_format' );

    return date( $date_format .' '. $time_format, $datetime );
}


/**
 * NanoSupport User Roles checker.
 *
 * NanoSupport consists 3 user roles:
 * - Support Seeker (read - role support_seeker)
 * - Support Agent (edit assigned tickets only - any user other than support_seeker)
 * - Manager (manage everything - administrator & editor)
 *
 * @since  1.0.0
 * 
 * @param  string $role String to check the User role.
 * @return boolean      Check and return true | false conditionally.
 * --------------------------------------------------------------------------
 */
function ns_is_user( $role ) {
    if( ! is_user_logged_in() )
        return;

    if( 'support_seeker' === $role ) {
        return ( current_user_can( 'read' ) && ! current_user_can( 'edit_nanosupports' ) ) ? true : false;
    }
    else if( 'agent' === $role ) {
        return ( current_user_can( 'edit_nanosupports' ) && ! current_user_can( 'manage_nanosupport' ) ) ? true : false;
    }
    else if( 'agent_and_manager' === $role ) {
        return current_user_can( 'edit_nanosupports' ) ? true : false;
    }
    else if( 'manager' === $role ) {
        return current_user_can( 'manage_nanosupport' ) ? true : false;
    }
    else {
        return current_user_can( $role ) ? true : false;
    }
}
