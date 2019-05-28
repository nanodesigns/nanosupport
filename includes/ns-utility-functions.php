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
 * @param  integer 	$ticket_id 	NanoSupport post ID.
 * @return array 				comment_ID, user_id, comment_date
 * -----------------------------------------------------------------------
 */
function ns_get_last_response( $ticket_id = null ) {

	$post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;

	global $wpdb;

	$cache_key = "nanosupport_last_response_{$post_id}";

	$max_comment_array = wp_cache_get( $cache_key );

	if( false === $max_comment_array ) {

		$query = "SELECT comment_ID, user_id, comment_date
		FROM $wpdb->comments
		WHERE comment_type = 'nanosupport_response'
		AND comment_post_ID = $post_id
		AND comment_approved = 1
		ORDER BY comment_ID DESC
		LIMIT 1";
		$max_comment_array = $wpdb->get_results( $query, ARRAY_A );

		wp_cache_set( $cache_key, $max_comment_array );

	}

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
 * NanoSupport Pagination
 *
 * paginate_links enabled with Pagination CSS.
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

    $big   = 999999999; // need an unlikely integer
    $total = $query->max_num_pages;
    if( $total > 1 ) {
    	echo '<nav class="nanosupport-pagination" aria-label="Pagination Navigation">';
    	$pages = paginate_links( array(
    		'base'		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    		'format'	=> '?paged=%#%',
    		'show_all'	=> false,
    		'prev_next'	=> true,
    		'prev_text'	=> '&laquo; <span class="screen-reader-only">'. __( 'Previous Page', 'nanosupport' ) .'</span>',
    		'next_text'	=> '<span class="screen-reader-only">'. __( 'Next Page', 'nanosupport' ) .'</span> &raquo;',
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
 * @param  integer $post_id Ticket Post ID.
 * @return string           Comma-separated names of the departments.
 * -----------------------------------------------------------------------
 */
function ns_get_ticket_departments( $post_id = null ) {

	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$_departments = get_the_terms( $post_id, 'nanosupport_department' );

	if ( $_departments && ! is_wp_error( $_departments ) ) :
		$counter          = 1;
		$department_count = count($_departments);
		$departments      = '';

		foreach ( $_departments as $department ) {
			$departments .= $department->name;
			if( $department_count != $counter )
				$departments .= ', ';

			$counter++;
		}
	else :
		$departments = '&mdash;';
	endif;

	return $departments;
}


/**
 * Making onDomain Email from Host URL.
 *
 * @author  Sisir Kanti Adhikari
 * @link    https://github.com/nanodesigns/download-via-email/
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
 * @return string the display name of the user.
 * --------------------------------------------------------------------------
 */
function ns_user_nice_name( $user_id = false ) {
	if( ! $user_id )
		return;

	$user = get_user_by( 'id', (int) $user_id );

	if( $user ) {
		return $user->display_name;
	}
}


/**
 * Get count on Ticket Status
 *
 * Used in NanoSupport Dashboard for displaying the graph.
 *
 * @param  string $status Ticket status.
 * @return integer        Number of tickets with that status.
 * --------------------------------------------------------------------------
 */
function ns_ticket_status_count( $status = '', $user_id = '' ) {
	if( empty($status) ) return;

	$cache_key = 'ns_dash_count_'. $status . $user_id;

	$count = wp_cache_get( $cache_key );
	if ( false === $count ) {

		global $wpdb;
		if( 'pending' === $status ) {
			if( empty($user_id) ) {
                // Get all the tickets count.
				$count = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'nanosupport' AND post_status = %s",
						$status
					)
				);
			} else {
                // Get tickets to specific user.
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

		wp_cache_set( $cache_key, $count );

	}

	return (int) $count;
}


/**
 * Get count of all the tickets.
 *
 * Used basically on admin dashboard.
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
 * NanoSupport Tooltip
 *
 * Display a responsive, and mobile devices-friendly, conflict-free CSS tooltip dynamically.
 *
 * @param  string $id       HTML id to connect aria-describedby.
 * @param  string $message  The i18 string/plain text.
 * @param  string $position left | right | top
 * @param  string $icon     Icon class.
 * @return string           Formatted tooltip that needs proper CSS.
 * ------------------------------------------------------------------------------
 */
function ns_tooltip( $id = '', $message = '', $position = 'top', $icon = 'ns-icon-question' ) {

	if( empty($message) )
		return;

	switch ($position) {
		case 'left':
		$class = 'ns-tooltip-left ';
		break;

		case 'right':
		$class = 'ns-tooltip-right ';
		break;

		case 'bottom':
		$class = 'ns-tooltip-bottom ';
		break;

		default:
		$class = 'ns-tooltip-top ';
		break;
	}

	ob_start(); ?>

	<span class="ns-tooltip <?php echo esc_attr( $class ) . esc_attr( $icon ); ?>">
		<span id="<?php echo esc_attr( $id ); ?>" class="ns-tooltip-message" role="tooltip">
			<?php echo $message; ?>
		</span>
	</span>

	<?php
	return ob_get_clean();

}


/**
 * Ticket Status Information.
 *
 * @param  string $ticket_status_raw Ticket Status value.
 * @return array                     Array with UI features.
 * ------------------------------------------------------------------------------
 */
function ns_get_ticket_status_info($ticket_status_raw) {
	switch ($ticket_status_raw) {
		case 'pending':
		$ticket_status = array(
			'value' => 'pending',
			'name'  => __( 'Pending', 'nanosupport' ),
			'class' => 'status-pending',
			'label' => '<span class="ns-label ns-label-normal">'. __( 'Pending', 'nanosupport' ) .'</span>',
		);
		break;

		case 'solved':
		$ticket_status = array(
			'value' => 'solved',
			'name'  => __( 'Solved', 'nanosupport' ),
			'class' => 'status-solved',
			'label' => '<span class="ns-label ns-label-success">'. __( 'Solved', 'nanosupport' ) .'</span>',
		);
		break;

		case 'inspection':
		$ticket_status = array(
			'value' => 'inspection',
			'name'  => __( 'Under Inspection', 'nanosupport' ),
			'class' => 'status-inspection',
			'label' => '<span class="ns-label ns-label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>',
		);
		break;

		case 'open':
		default:
		$ticket_status = array(
			'value' => 'open',
			'name'  => __( 'Open', 'nanosupport' ),
			'class' => 'status-open',
			'label' => '<span class="ns-label ns-label-warning">'. __( 'Open', 'nanosupport' ) .'</span>',
		);
		break;
	}

	return $ticket_status;
}

/**
 * Get Ticket Priority Information.
 *
 * @param  string $priority_raw Raw string for priority.
 * @param  string $status_raw   Raw string for status (for blink class) (default: '').
 * @return array                Array with UI features.
 * ------------------------------------------------------------------------------
 */
function ns_get_ticket_priority_info($priority_raw, $status_raw = '') {

	$blink_class = ('solved' !== $status_raw) ? ' blink' : '';

	switch ($priority_raw) {

		case 'critical':
		$priority = array(
			'value' => 'critical',
			'name'  => esc_html__( 'Critical', 'nanosupport' ),
			'class' => 'priority-critical',
			'label' => '<span class="ns-text-danger"><i class="ns-dot'. esc_attr($blink_class) .'"></i>'. esc_html__( 'Critical', 'nanosupport' ) .'</span>',
		);
		break;

		case 'high':
		$priority = array(
			'value' => 'high',
			'name'  => esc_html__( 'High', 'nanosupport' ),
			'class' => 'priority-high',
			'label' => '<span class="ns-text-warning"><i class="ns-dot'. esc_attr($blink_class) .'"></i>'. esc_html__( 'High', 'nanosupport' ) .'</span>',
		);
		break;

		case 'medium':
		$priority = array(
			'value' => 'medium',
			'name'  => esc_html__( 'Medium', 'nanosupport' ),
			'class' => 'priority-medium',
			'label' => '<span class="ns-text-info"><i class="ns-dot"></i>'. esc_html__( 'Medium', 'nanosupport' ) .'</span>',
		);
		break;

		case 'low':
		default:
		$priority = array(
			'value' => 'low',
			'name'  => esc_html__( 'Low', 'nanosupport' ),
			'class' => 'priority-low',
			'label' => '<span class="ns-text-dim"><i class="ns-dot"></i>'. esc_html__( 'Low', 'nanosupport' ) .'</span>',
		);
		break;
	}

	return $priority;
}


/**
 * Get all the ticket meta information
 *
 * Ticket status, combining NanoSupport ticket status along with the
 * default 'pending' status. Ticket priority, all the NanoSupport
 * priorities. Agent, if assigned, with ID and name.
 *
 * @param  integer $ticket_id The ticket post ID.
 * @return array              An array of arrays ('status', 'priority', 'agent')
 * --------------------------------------------------------------------------
 */
function ns_get_ticket_meta( $ticket_id = null ) {

	$post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;

	$_ns_ticket_status   = get_post_meta( $post_id, '_ns_ticket_status',            true );
	$_ns_ticket_priority = get_post_meta( $post_id, '_ns_ticket_priority',          true );
	$_ns_ticket_agent    = get_post_meta( $post_id, '_ns_ticket_agent',             true );
	$_ns_ticket_product  = get_post_meta( $post_id, '_ns_ticket_product',           true );
	$_ns_ticket_receipt  = get_post_meta( $post_id, '_ns_ticket_product_receipt',   true );

	$this_status    = ! empty( $_ns_ticket_status )     ? $_ns_ticket_status    : 'open';
	$this_priority  = ! empty( $_ns_ticket_priority )   ? $_ns_ticket_priority  : 'low';
	$this_agent     = ! empty( $_ns_ticket_agent )      ? $_ns_ticket_agent     : '';
	$this_product   = ! empty( $_ns_ticket_product )    ? $_ns_ticket_product   : '';
	$this_receipt   = ! empty( $_ns_ticket_receipt )    ? $_ns_ticket_receipt   : '';

    /**
     * Ticket status
     * ...
     */
    if( 'pending' === get_post_status($post_id) ) {
    	$ticket_status = ns_get_ticket_status_info('pending');
    } else {
    	$ticket_status = ns_get_ticket_status_info($this_status);
    }

    /**
     * Ticket priority
     * ...
     */
    $priority =  ns_get_ticket_priority_info($this_priority);

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

    return array(
    	'status'   => $ticket_status,
    	'priority' => $priority,
    	'agent'    => $agent,
    	'product'  => $this_product,
    	'receipt'  => $this_receipt
    );

}

/**
 * Predict pretty permalink for pending tickets.
 *
 * It's not easy to find non-published post's permalink, so
 * we need to hack that.
 *
 * This function is using a core function get_sample_permalink()
 * and we are hacking that to make the forecasted permalink.
 *
 * Note: it's not bulletproof :( .
 *
 * @link   https://wordpress.stackexchange.com/a/97606/22728
 *
 * @see    get_sample_permalink() Core function to have permalink using postname
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
 * Display the date time in NanoSupport according to the WordPress' General Settings.
 * Let the user modify if they want using a filter hook.
 *
 * @param  integer|string   $datetime   DateTime, or UNIX timestamp.
 * @param  boolean          $time       True if to display the time portion.
 * @return string                       Formated datetime.
 * --------------------------------------------------------------------------
 */
function ns_date_time( $datetime, $time = true ) {

    /**
     * Check and make sure it's a UNIX timestamp.
     * @link https://stackoverflow.com/a/2524710/1743124
     * ...
     */
    if( ! is_numeric($datetime) ) {
    	$datetime = strtotime($datetime);
    }

    // Grab the date-time format from WordPress Settings.
    $date_time_format = get_option( 'date_format' );

    if( $time ) {
    	$date_time_format .= ' ';
    	$date_time_format .= get_option( 'time_format' );
    }

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * ns_date_time_format
     *
     * Hook to moderate the date-time format.
     * -----------------------------------------------------------------------
     */
    apply_filters( 'ns_date_time_format', $date_time_format );

    return date( $date_time_format, $datetime );
}


/**
 * NanoSupport User Roles checker.
 *
 * NanoSupport consists 3 user roles:
 * - Support Seeker (read - role support_seeker)
 * - Support Agent (edit assigned tickets only - any user other than support_seeker)
 * - Manager (manage everything - administrator & editor)
 *
 * @param  string           $role   String to check the User role.
 * @param  object|integer   $user   User object or ID.
 * @return boolean                  Check and return true | false conditionally.
 * --------------------------------------------------------------------------
 */
function ns_is_user( $role, $user = null ) {
	if( 'support_seeker' === $role ) :
		if( null === $user ) {
			return ( current_user_can( 'read' ) && ! current_user_can( 'edit_nanosupports' ) ) ? true : false;
		} else {
			return ( user_can( $user, 'read' ) && ! user_can( $user, 'edit_nanosupports' ) ) ? true : false;
		}

	elseif( 'agent' === $role ) :
		if( null === $user ) {
			return ( current_user_can( 'edit_nanosupports' ) && ! current_user_can( 'manage_nanosupport' ) ) ? true : false;
		} else {
			return ( user_can( $user, 'edit_nanosupports' ) && ! user_can( $user, 'manage_nanosupport' ) ) ? true : false;
		}

	elseif( 'agent_and_manager' === $role ) :
		if( null === $user ) {
			return current_user_can( 'edit_nanosupports' ) ? true : false;
		} else {
			return user_can( $user, 'edit_nanosupports' ) ? true : false;
		}

	elseif( 'manager' === $role ) :
		if( null === $user ) {
			return current_user_can( 'manage_nanosupport' ) ? true : false;
		} else {
			return user_can( $user, 'manage_nanosupport' ) ? true : false;
		}

	else :
		if( null === $user ) {
			return current_user_can( $role ) ? true : false;
		} else {
			return user_can( $user, $role ) ? true : false;
		}

	endif;
}


/**
 * Get all the NanoSupport Icons
 *
 * @return array
 * --------------------------------------------------------------------------
 */
function ns_get_all_icon() {
	return array('ns-icon-responses','ns-icon-search','ns-icon-trashcan','ns-icon-question','ns-icon-notification','ns-icon-no-notification','ns-icon-remove','ns-icon-edit','ns-icon-refresh','ns-icon-repeat','ns-icon-chevron-down','ns-icon-chevron-left','ns-icon-chevron-right','ns-icon-chevron-up','ns-icon-chevron-circle-down','ns-icon-chevron-circle-left','ns-icon-chevron-circle-right','ns-icon-chevron-circle-up','ns-icon-minus-circle','ns-icon-minus-square','ns-icon-plus-square','ns-icon-plus-circle','ns-icon-link','ns-icon-tags','ns-icon-tag','ns-icon-docs','ns-icon-attachment','ns-icon-lock','ns-icon-unlock','ns-icon-settings','ns-icon-wrench','ns-icon-gears','ns-icon-info-circled','ns-icon-info','ns-icon-help','ns-icon-help-circled','ns-icon-pie-chart','ns-icon-graph-bar','ns-icon-line-chart','ns-icon-bar-chart','ns-icon-user','ns-icon-user-outline','ns-icon-users','ns-icon-users-outline','ns-icon-favorite','ns-icon-favorite-outline','ns-icon-desktop','ns-icon-globe','ns-icon-hand','ns-icon-happy','ns-icon-laptop','ns-icon-locate','ns-icon-mail','ns-icon-microphone','ns-icon-microphone-off','ns-icon-options','ns-icon-phone-landscape','ns-icon-phone-portrait','ns-icon-pin','ns-icon-plane','ns-icon-stopwatch','ns-icon-sunny','ns-icon-android-time','ns-icon-bonfire','ns-icon-bluetooth','ns-icon-bug','ns-icon-clipboard','ns-icon-coffee','ns-icon-compass','ns-icon-cube','ns-icon-flask','ns-icon-flask-bubbles','ns-icon-female','ns-icon-flag','ns-icon-fork','ns-icon-hammer','ns-icon-help-buoy','ns-icon-alarm','ns-icon-americanfootball','ns-icon-flame','ns-icon-game-controller','ns-icon-infinite','ns-icon-lightbulb','ns-icon-nutrition','ns-icon-paw','ns-icon-pulse','ns-icon-toggle','ns-icon-wineglass','ns-icon-jet','ns-icon-leaf','ns-icon-mic','ns-icon-mouse','ns-icon-paper-airplane','ns-icon-planet','ns-icon-ribbon','ns-icon-thumbsdown','ns-icon-thumbsup','ns-icon-buffer','ns-icon-display-contrast','ns-icon-power','ns-icon-wordpress','ns-icon-gift','ns-icon-github','ns-icon-microscope','ns-icon-scholar','ns-icon-plugin','ns-icon-book','ns-icon-photo','ns-icon-trees','ns-icon-shield','ns-icon-star-filled','ns-icon-star-empty','ns-icon-atom','ns-icon-responsive','ns-icon-facebook','ns-icon-twitter','ns-icon-linkedin','ns-icon-gplus','ns-icon-pinterest','ns-icon-tumblr','ns-icon-stumbleupon','ns-icon-ming','ns-icon-nanosupport','ns-icon-cart','ns-icon-truck','ns-icon-wheelchair','ns-icon-radio-waves','ns-icon-paperclip','ns-icon-slack','ns-icon-screen');
}


/**
 * Set allowed HTML tags
 *
 * Set all the HTML tags and attributes allowed for tickets, ticket
 * responses, and internal notes for proper sanitization.
 *
 * @return array
 * --------------------------------------------------------------------------
 */
function ns_allowed_html() {
	$allowed_html = array(
        //paragraph and formatting
		'p'         => array(),
		'span'      => array(),
		'small'     => array(),
		'strong'    => array(),
		'em'        => array(),
		'b'         => array(),
		'i'         => array(),
		'u'         => array(),
		'sub'       => array(),
		'sup'       => array(),

        //headers
		'h2'        => array(),
		'h3'        => array(),
		'h4'        => array(),
		'h5'        => array(),
		'h6'        => array(),

        //links
		'a'         => array(
			'href'  => true,
			'title' => true,
		),

        //quote
		'blockquote'=> array(
			'cite'  => true,
		),
		'q'         => array(
			'cite' => true,
		),
		'cite'      => array(),

        //code
		'code'      => array(),
		'pre'       => array(),

        //lists
		'dl'        => array(),
		'dt'        => array(),
		'dd'        => array(),
		'ol'        => array(),
		'ul'        => array(),
		'li'        => array(),

        //utility
		'br'        => array(),
		'hr'        => array(),
		'strike'    => array(),
		'kbd'       => array(),
	);

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * ns_allowed_html
     *
     * Hook to moderate over the core NanoSupport allowed HTML tags and
     * attributes for tickets and ticket responses.
     *
     * @since  1.0.0
     * -----------------------------------------------------------------------
     */
    return apply_filters( 'ns_allowed_html', $allowed_html );
}


/**
 * Get allowed HTML tags to display
 *
 * @see    ns_allowed_html() To get the default/filtered allowed HTML tags.
 *
 * @return string HTML tags as string.
 * --------------------------------------------------------------------------
 */
function ns_get_allowed_html_tags() {
	$allowed_html = ns_allowed_html();
	$tags         = array();

	foreach ( array_keys($allowed_html) as $tag_name ) {
        $tags[] = '&lt;'. $tag_name .'&gt;'; //adding < and > before and after
    }

    return implode( ', ', $tags );
}


/**
 * Is ticket character limit active?
 *
 * Check whether the ticket character limit is active or not.
 * If active, returns the character limit, else false.
 *
 * @return  integer|boolean Character limit || false
 * --------------------------------------------------------------------------
 */
function ns_is_character_limit() {
	$option = get_option( 'nanosupport_settings' );

    //default
	if( ! isset($option['ticket_char_limit']) ) {
		return 30;
	}

	if( 0 == $option['ticket_char_limit'] ) {
		return false;
	} else {
		return $option['ticket_char_limit'];
	}
}


/**
 * Numeric transformation
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 * Adopted from WooCommerce
 *
 * @param   $size
 * @return  int
 * --------------------------------------------------------------------------
 */
function ns_transform_to_numeric( $size ) {
	$l   = substr( $size, -1 );
	$ret = substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
		$ret *= 1024;
		case 'T':
		$ret *= 1024;
		case 'G':
		$ret *= 1024;
		case 'M':
		$ret *= 1024;
		case 'K':
		$ret *= 1024;
	}

	return $ret;
}


/**
 * Get Ticket Modified Date
 *
 * Ticket modified date in comparison to Response date.
 *
 * @param  integer $ticket_id Ticket post ID.
 * @return string             Date/Time.
 * --------------------------------------------------------------------------
 */
function ns_get_ticket_modified_date( $ticket_id = null ) {

	$post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;

	$last_response      = ns_get_last_response($post_id);
	$last_response_date = $last_response['comment_date'];

	$this_post          = get_post( $post_id );
	$post_modified_date = $this_post->post_modified;

    // If there's no response, return the post_modified_date
	if( empty($last_response_date) ) {
		return $post_modified_date;
	}

	$date_modified = strtotime($last_response_date) > strtotime($post_modified_date) ? $last_response_date : $post_modified_date;

	return $date_modified;

}


/**
 * Compare the Dates
 *
 * @param  array $a First array.
 * @param  array $b Second array.
 * @return integer  Larger value between two.
 * --------------------------------------------------------------------------
 */
function ns_date_compare( $a, $b ) {
	$date1 = strtotime($a['date']);
	$date2 = strtotime($b['date']);
	return $date2 - $date1;
}


/**
 * Updating Ticket Modified Date
 *
 * Helper function to updating the post modified date. We are using this
 * function to update ticket modified date when the ticket was set
 * closed from the front end.
 *
 * @author  nofearinc
 * @link    https://core.trac.wordpress.org/attachment/ticket/24266/24266.diff
 *
 * @param  integer $post_id Post ID.
 * --------------------------------------------------------------------------
 */
function ns_update_post_modified_date( $post_id ) {
	$post_modified     = current_time( 'mysql' );
	$post_modified_gmt = current_time( 'mysql', 1 );

	$updated_fields = array(
		'post_modified'     => $post_modified,
		'post_modified_gmt' => $post_modified_gmt
	);

	$ticket_updated_data = array(
		'ID'                => $post_id,
		'post_modified'     => $post_modified,
		'post_modified_gmt' => $post_modified_gmt,
	);

    // Update the ticket into the database
	wp_update_post( $ticket_updated_data );
}


/**
 * Get Taxonomy Parents.
 *
 * Recursive function to generate taxonomy slugs for all the terms
 * of a specific post.
 *
 * @author Jeff
 * @link   https://wordpress.stackexchange.com/q/39500/22728
 *
 * @param  integer $id        Taxonomy term ID.
 * @param  string  $taxonomy  Taxonomy.
 * @param  string  $separator Seperator, if needed.
 * @param  array   $visited   Visited array.
 * @return string             Taxonomy parents.
 * --------------------------------------------------------------------------
 */
function ns_get_taxonomy_parents( $id, $taxonomy, $separator = '/', $visited = array() ) {
	$chain  = '';
	$parent = get_term($id, $taxonomy);

	if (is_wp_error($parent)) {
		return $parent;
	}

	if ($parent->parent && ($parent->parent != $parent->term_id) && !in_array($parent->parent, $visited)) {
		$visited[] = $parent->parent;
        // call recursively to make the parent/child URLs.
        // forcing the slash (/) to make slash separated parents.
		$chain     .= ns_get_taxonomy_parents( $parent->parent, $taxonomy, '/', $visited );
	}

	$chain .= $parent->slug . $separator;

	return $chain;
}
