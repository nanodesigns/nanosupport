<?php
/**
 * Custom Columns.
 *
 * To add/manage custom columns in admin panel.
 * 
 * @package  nano_support_ticket
 * =======================================================================
 */

function nst_set_custom_columns( $columns ) {
	$new_columns = array(
			'ticket_priority'	=> __( 'Priority', 'nanodesigns-nst' ),
			'ticket_responses'	=> '<span class="dashicons dashicons-format-chat" title="Responses"></span>',
			'ticket_status'		=> __( 'Ticket Status', 'nanodesigns-nst' ),
			'last_response'		=> __( 'Last Response by', 'nanodesigns-nst' )
		);
	return array_merge( $columns, $new_columns );
} 
add_filter( 'manage_nanosupport_posts_columns', 'nst_set_custom_columns' );

function nst_populate_custom_columns( $column, $post_id ) {
    $ticket_control = get_post_meta( $post_id, 'nst_control', true );
    switch ( $column ) {
        case 'ticket_priority' :
        	$ticket_priority = $ticket_control['priority'];
            if( $ticket_priority === 'low' ) {
				echo '<strong>'. __( 'Low', 'nanodesigns-nst' ) .'</strong>';
			} else if( $ticket_priority === 'medium' ) {
				echo '<strong class="text-info">' , __( 'Medium', 'nanodesigns-nst' ) , '</strong>';
			} else if( $ticket_priority === 'high' ) {
				echo '<strong class="text-warning">' , __( 'High', 'nanodesigns-nst' ) , '</strong>';
			} else if( $ticket_priority === 'critical' ) {
				echo '<strong class="text-danger">' , __( 'Critical', 'nanodesigns-nst' ) , '</strong>';
			}
            break;

        case 'ticket_responses' :
            $responses = wp_count_comments( $post_id );
            $response_count = $responses->approved;

            if( !empty($response_count) ) {
            	echo '<span class="responses-count" aria-hidden="true">'. $response_count .'</span>';
            	echo '<span class="screen-reader-text">'. sprintf( _n( '%s response', '%s responses', $response_count, 'nanodesigns-nst' ), $response_count ) .'</span>';
            } else {
            	echo '&mdash;';
            }
			break;

        case 'ticket_status' :
            $ticket_status = $ticket_control['status'];
			if( $ticket_status ) {
				if( $ticket_status == 'solved' ) {
					$status = '<span class="label label-success">'. __( 'Solved', 'nanodesigns-nst' ) .'</span>';
				} else if( $ticket_status == 'inspection' ) {
					$status = '<span class="label label-primary">'. __( 'Under Inspection', 'nanodesigns-nst' ) .'</span>';
				} else {
					$status = '<span class="label label-warning">'. __( 'Open', 'nanodesigns-nst' ) .'</span>';
				}
			} else {
				$status = '';
			}
			echo $status;
            break;

        case 'last_response' :
        	$last_response = nst_get_last_response( $post_id );
		    $last_responder = get_userdata( $last_response['user_id'] );
		    if ( $last_responder ) {
		    	echo $last_responder->display_name, '<br>';
		    	echo nst_time_elapsed($last_response['comment_date']), ' ago';
		    } else {
		    	echo '-';
		    }
            break;
    }
}
add_action( 'manage_nanosupport_posts_custom_column' , 'nst_populate_custom_columns', 10, 2 );