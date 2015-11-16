<?php
/**
 * CPT 'nanosupport' and Taxonomy
 *
 * Functions to initiate the Custom Post Type 'nanosupport'
 * and Taxonomy 'nanosupport_departments'.
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CPT
 * 
 * Creating the 'nanosupport' CPT for tickets.
 * 
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function ns_register_cpt_nanosupport() {

    $labels = array(
        'name'					=> __( 'Tickets', 'nanosupport' ),
        'singular_name'			=> __( 'Ticket', 'nanosupport' ),
        'add_new'				=> __( 'Add New', 'nanosupport' ),
        'add_new_item'			=> __( 'Add New Ticket', 'nanosupport' ),
        'edit_item'				=> __( 'Edit Ticket', 'nanosupport' ),
        'new_item'				=> __( 'New Ticket', 'nanosupport' ),
        'view_item'				=> __( 'View Ticket', 'nanosupport' ),
        'search_items'			=> __( 'Search Ticket', 'nanosupport' ),
        'not_found'				=> __( 'No Ticket found', 'nanosupport' ),
        'not_found_in_trash'	=> __( 'No Ticket found in Trash', 'nanosupport' ),
        'parent_item_colon'		=> __( 'Parent Ticket:', 'nanosupport' ),
        'menu_name'				=> __( 'Supports', 'nanosupport' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'Get the ticket information', 'nanosupport' ),
        'supports'				=> array( 'title', 'editor', 'author' ),
        'taxonomies'            => array(),
        'menu_icon'				=> 'dashicons-universal-access-alt',
        'public'				=> true,
        'show_ui'				=> true,
        'show_in_menu'			=> true,
        'menu_position'			=> 29,
        	'show_in_nav_menus'		=> false,
        'publicly_queryable'	=> true,
        'exclude_from_search'	=> false,
        	'has_archive'			=> false,
        'query_var'				=> true,
        'can_export'			=> true,
        'rewrite'				=> array( 'slug' => 'nanosupport' ),
        'capability_type'       => 'post',
        /*'capabilities'          => array(
                                    'edit_post'             => 'edit_ns',
                                    'edit_posts'            => 'edit_nss',
                                    'edit_others_posts'     => 'edit_other_nss',
                                    'publish_posts'         => 'publish_nss',
                                    'read_post'             => 'read_ns',
                                    'read_private_posts'    => 'read_private_nss',
                                    'delete_post'           => 'delete_ns'
                                ),
        'map_meta_cap'          => true*/
    );

    if( !post_type_exists( 'nanosupport' ) )
        register_post_type( 'nanosupport', $args );

    /**
     * To activate CPT Single page
     * @author  Bainternet
     * @link http://en.bainternet.info/2011/custom-post-type-getting-404-on-permalinks
     * ---
     */
    $set = get_option( 'post_type_rules_flased_nanosupport' );
    if ( $set !== true ){
		flush_rewrite_rules( false );
		update_option( 'post_type_rules_flased_nanosupport', true );
    }

}
add_action( 'init', 'ns_register_cpt_nanosupport' );


/**
 * Declare custom columns
 * @param  array $columns Default columns.
 * @return array          Merged with new columns.
 */
function ns_set_custom_columns( $columns ) {
    $new_columns = array(
            'ticket_priority'   => __( 'Priority', 'nanosupport' ),
            'ticket_responses'  => '<span class="dashicons dashicons-format-chat" title="Responses"></span>',
            'ticket_status'     => __( 'Ticket Status', 'nanosupport' ),
            'last_response'     => __( 'Last Response by', 'nanosupport' )
        );
    return array_merge( $columns, $new_columns );
} 
add_filter( 'manage_nanosupport_posts_columns', 'ns_set_custom_columns' );

/**
 * Populate columns with respective contents.
 * @param  array $column    Columns.
 * @param  integer $post_id Each of the post ID.
 * @return array            Columns with information.
 */
function ns_populate_custom_columns( $column, $post_id ) {
    $ticket_control = get_post_meta( $post_id, 'ns_control', true );
    switch ( $column ) {
        case 'ticket_priority' :
            $ticket_priority = $ticket_control ? $ticket_control['priority'] : false;
            if( $ticket_priority && 'low' === $ticket_priority ) {
                echo '<strong>'. __( 'Low', 'nanosupport' ) .'</strong>';
            } else if( $ticket_priority && 'medium' === $ticket_priority ) {
                echo '<strong class="text-info">' , __( 'Medium', 'nanosupport' ) , '</strong>';
            } else if( $ticket_priority && 'high' === $ticket_priority ) {
                echo '<strong class="text-warning">' , __( 'High', 'nanosupport' ) , '</strong>';
            } else if( $ticket_priority && 'critical' === $ticket_priority ) {
                echo '<strong class="text-danger">' , __( 'Critical', 'nanosupport' ) , '</strong>';
            }
            break;

        case 'ticket_responses' :
            $responses = wp_count_comments( $post_id );
            $response_count = $responses->approved;

            if( !empty($response_count) ) {
                echo '<span class="responses-count" aria-hidden="true">'. $response_count .'</span>';
                echo '<span class="screen-reader-text">'. sprintf( _n( '%s response', '%s responses', $response_count, 'nanosupport' ), $response_count ) .'</span>';
            } else {
                echo '&mdash;';
            }
            break;

        case 'ticket_status' :
            $ticket_status = $ticket_control ? $ticket_control['status'] : false;
            if( $ticket_status ) {
                if( 'solved' === $ticket_status ) {
                    $status = '<span class="label label-success">'. __( 'Solved', 'nanosupport' ) .'</span>';
                } else if( 'inspection' === $ticket_status ) {
                    $status = '<span class="label label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>';
                } else {
                    $status = '<span class="label label-warning">'. __( 'Open', 'nanosupport' ) .'</span>';
                }
            } else {
                $status = '';
            }
            echo $status;
            break;

        case 'last_response' :
            $last_response = ns_get_last_response( $post_id );
            $last_responder = get_userdata( $last_response['user_id'] );
            if ( $last_responder ) {
                echo $last_responder->display_name, '<br>';
                echo ns_time_elapsed($last_response['comment_date']), ' ago';
            } else {
                echo '-';
            }
            break;
    }
}
add_action( 'manage_nanosupport_posts_custom_column' , 'ns_populate_custom_columns', 10, 2 );