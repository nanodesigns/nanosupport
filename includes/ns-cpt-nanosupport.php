<?php
/**
 * CPT 'nanosupport' and Taxonomy
 *
 * Functions to initiate the Custom Post Type 'nanosupport'
 * and Taxonomy 'nanosupport_department'.
 *
 * @author      nanodesigns
 * @category    Tickets
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register CPT Tickets
 * 
 * Creating the custom post type 'nanosupport' for tickets.
 *
 * @since  1.0.0
 * 
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function ns_register_cpt_nanosupport() {

    $labels = array(
        'name'					=> _x( 'Tickets', 'NanoSupport Ticket', 'nanosupport' ),
        'singular_name'			=> _x( 'Ticket', 'NanoSupport Ticket', 'nanosupport' ),
        'add_new'				=> _x( 'Add New', 'NanoSupport Ticket', 'nanosupport' ),
        'add_new_item'			=> __( 'Add New Ticket', 'nanosupport' ),
        'edit_item'				=> __( 'Edit Ticket', 'nanosupport' ),
        'new_item'				=> __( 'New Ticket', 'nanosupport' ),
        'view_item'				=> __( 'View Ticket', 'nanosupport' ),
        'search_items'			=> __( 'Search Ticket', 'nanosupport' ),
        'not_found'				=> __( 'No Ticket found', 'nanosupport' ),
        'not_found_in_trash'	=> __( 'No Ticket found in Trash', 'nanosupport' ),
        'parent_item_colon'		=> __( 'Parent Ticket:', 'nanosupport' ),
        'menu_name'				=> _x( 'NanoSupport', 'NanoSupport Ticket', 'nanosupport' ),
        'name_admin_bar'        => _x( 'Support Ticket', 'NanoSupport Ticket', 'nanosupport' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'Get the ticket information', 'nanosupport' ),
        'supports'				=> array( 'title', 'editor', 'author' ),
        'taxonomies'            => array(),
        'menu_icon'				=> '', //setting this using CSS
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
        'rewrite'				=> array( 'slug' => 'support' ),
        'capability_type'       => 'nanosupport',
        'map_meta_cap'          => true
    );

    if( ! post_type_exists( 'nanosupport' ) ) {
        register_post_type( 'nanosupport', $args );
    }

}

add_action( 'init', 'ns_register_cpt_nanosupport' );


/**
 * Show pending count on menu.
 *
 * @since  1.0.0
 *
 * @see    ns_ticket_status_count()
 * @return integer  Pending count beside menu label.
 * -----------------------------------------------------------------------
 */
function ns_notification_bubble_in_nanosupport_menu() {
    global $menu, $current_user;

    if( ns_is_user( 'agent' ) ) {
        $pending_count = ns_ticket_status_count( 'pending', $current_user->ID );
    } else {
        $pending_items = wp_count_posts( 'nanosupport' );
        $pending_count = $pending_items->pending;
    }

    $menu[29][0] .= ! empty($pending_count) ? " <span class='update-plugins count-1' title='". esc_attr__( 'Pending Tickets', 'nanosupport' ) ."'><span class='update-count'>$pending_count</span></span>" : '';
}

add_action( 'admin_menu', 'ns_notification_bubble_in_nanosupport_menu' );


/**
 * Declare custom columns
 *
 * Made custom columns specific to the Support Tickets.
 *
 * @since  1.0.0
 * 
 * @param  array $columns Default columns.
 * @return array          Merged with new columns.
 * -----------------------------------------------------------------------
 */
function ns_set_custom_columns( $columns ) {
    $new_columns = array(
            'ticket_status'     => __( 'Ticket Status', 'nanosupport' ),
            'ticket_priority'   => __( 'Priority', 'nanosupport' ),
            'ticket_agent'      => '<i class="dashicons dashicons-businessman" title="'. esc_attr__( 'Agent', 'nanosupport' ) .'"></i>',
            'ticket_responses'  => '<i class="dashicons dashicons-format-chat" title="'. esc_attr__( 'Responses', 'nanosupport' ) .'"></i>',
            'last_response'     => __( 'Last Response by', 'nanosupport' )
        );
    return array_merge( $columns, $new_columns );
}

add_filter( 'manage_nanosupport_posts_columns', 'ns_set_custom_columns' );


/**
 * Populate columns with contents
 *
 * Populate support ticket columns with respective contents.
 *
 * @since  1.0.0
 * 
 * @param  array $column    Columns.
 * @param  integer $post_id Each of the post ID.
 * @return array            Columns with information.
 * -----------------------------------------------------------------------
 */
function ns_populate_custom_columns( $column, $post_id ) {

    $ticket_meta = ns_get_ticket_meta( get_the_ID() );

    switch ( $column ) {
        case 'ticket_status' :
            echo $ticket_meta['status']['label'];
            break;

        case 'ticket_priority' :
            echo $ticket_meta['priority']['label'];
            break;

        case 'ticket_agent' :
            echo isset($ticket_meta['agent']['name']) ? $ticket_meta['agent']['name'] : '&mdash;';
            break;

        case 'ticket_responses' :
            $responses = wp_count_comments( $post_id );
            $response_count = $responses->approved;

            if( ! empty($response_count) ) {
                echo '<span class="responses-count" aria-hidden="true">'. $response_count .'</span>';
                /* translators: Response count 1. singular 2. plural */
                echo '<span class="screen-reader-text">'. sprintf( _n( '%s response', '%s responses', $response_count, 'nanosupport' ), $response_count ) .'</span>';
            } else {
                echo '&mdash; <span class="screen-reader-text">'. __( 'No response yet', 'nanosupport' ) .'</span>';
            }
            break;

        case 'last_response' :
            $last_response  = ns_get_last_response( $post_id );
            $last_responder = get_userdata( $last_response['user_id'] );
            if ( $last_responder ) {
                echo $last_responder->display_name, '<br>';
                /* translators: time difference from current time. eg. 12 minutes ago */
                printf( __( '%s ago', 'nanosupport' ), human_time_diff( strtotime($last_response['comment_date']), current_time('timestamp') ) );
            } else {
                echo '&mdash; <span class="screen-reader-text">'. __( 'No response yet', 'nanosupport' ) .'</span>';
            }
            break;
    }
}

add_action( 'manage_nanosupport_posts_custom_column' , 'ns_populate_custom_columns', 10, 2 );


/**
 * Register Custom Taxonomy
 * 
 * Create Custom Taxonomy 'nanosupport_department' to sort out the tickets.
 *
 * @since  1.0.0
 * 
 * @return array To register the custom taxonomy.
 * -----------------------------------------------------------------------
 */
function ns_create_nanosupport_taxonomies() {

    $labels = array(
        'name'              => __( 'Departments', 'nanosupport' ),
        'singular_name'     => __( 'Department', 'nanosupport' ),
        'search_items'      => __( 'Search Departments', 'nanosupport' ),
        'all_items'         => __( 'All Departments', 'nanosupport' ),
        'parent_item'       => __( 'Parent Department', 'nanosupport' ),
        'parent_item_colon' => __( 'Parent Department:', 'nanosupport' ),
        'edit_item'         => __( 'Edit Departments', 'nanosupport' ),
        'update_item'       => __( 'Update Departments', 'nanosupport' ),
        'add_new_item'      => __( 'Add New Department', 'nanosupport' ),
        'new_item_name'     => __( 'New Department Name', 'nanosupport' ),
        'menu_name'         => __( 'Departments', 'nanosupport' ),
    );

    $args = array(
        'hierarchical'      => true,
        'public'            => false,
        'show_tagcloud'     => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'support-departments' ),
        'capabilities'      => array(
                                'manage_terms' => 'manage_nanosupport_terms',
                                'edit_terms'   => 'edit_nanosupport_terms',
                                'delete_terms' => 'delete_nanosupport_terms',
                                'assign_terms' => 'assign_nanosupport_terms',
                            ),
    );

    if( ! taxonomy_exists( 'nanosupport_department' ) )
        register_taxonomy( 'nanosupport_department', array( 'nanosupport' ), $args );

}

add_action( 'init', 'ns_create_nanosupport_taxonomies', 0 );


/**
 * Copy Ticket button.
 *
 * Add a 'copy to kb' button to each ticket in admin panel.
 *
 * @since  1.0.0
 * 
 * @param  array $actions  WP Post actions.
 * @param  object $post    WP Post Object.
 * @return array           Modified action buttons.
 * -----------------------------------------------------------------------
 */
function ns_copy_ticket_button( $actions, $post ) {
    if( 'nanosupport' === $post->post_type ) {
        // pass nonce to check and verify false request
        $nonce = wp_create_nonce( 'ns_copy_ticket_nonce' );

        // add our button
        $actions['copy_ticket'] = '<a class="ns-copy-post" data-ticket="'. $post->ID .'" data-nonce="'. $nonce .'" href="javascript:">'. esc_html__( 'Copy to KB', 'nanosupport' ) .'</a>';
    }

    return $actions;
}

add_filter( 'post_row_actions', 'ns_copy_ticket_button', 10, 2 );


/**
 * Hack the Post Author Override
 *
 * Hack to modify the Core Post Author Override to add
 * support seeker role, and the others.
 * 
 * @param  string $select_field The core HTML.
 * @return string               Modified HTML.
 * -----------------------------------------------------------------------
 */
function ns_hack_post_author_override( $select_field )  {
    global $post;

    if( 'nanosupport' === $post->post_type ) {
        $users = get_users(
            array(
                /**
                 * -----------------------------------------------------------------------
                 * HOOK : FILTER HOOK
                 * nanosupport_assigned_user_role
                 *
                 * The user roles that are passed to generate override HTML.
                 * You can add/modify the roles using the hook.
                 * 
                 * @since  1.0.0
                 *
                 * @param array  The user roles.
                 * -----------------------------------------------------------------------
                 */
                'role__in'=> apply_filters( 'nanosupport_assigned_user_role', array(
                                'support_seeker',
                                'administrator',
                                'author',
                                'editor',
                            )
                        )
                )
            );

        // Get ticket user.
        global $post;
        $selected_author = !empty($post->post_author) ? $post->post_author : get_current_user_id();

        // Add some help text here.
        $select_field .= '<p>'. __( 'Add the ticket on behalf of anybody', 'nanosupport' ) .'</p>';

        $select_field .= '<select name="post_author_override" id="post_author_override-nanosupport" class="">';
            foreach( $users as $user ) :
                $select_field .= '<option value="'. $user->id .'" '. selected( $user->id, $selected_author, false ) .'>';
                    $select_field .= $user->display_name;
                    $select_field .= ' ('. $user->roles[0] .')'; // display the user role
                $select_field .= '</option>';
            endforeach;
        $select_field .= '</select>';
    }

    return $select_field;
}

add_filter( 'wp_dropdown_users', 'ns_hack_post_author_override' );
