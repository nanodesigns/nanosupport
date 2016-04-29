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
        'menu_name'				=> __( 'NanoSupport', 'nanosupport' ),
        'name_admin_bar'        => __( 'Support Ticket', 'nanosupport' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'Get the ticket information', 'nanosupport' ),
        'supports'				=> array( 'title', 'editor' ),
        'taxonomies'            => array(),
        'menu_icon'				=> '', //setting this using CSS
        'public'				=> true,
        'show_ui'				=> true,
        'show_in_menu'			=> true,
        'menu_position'			=> 28.5,
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

    if( ! post_type_exists( 'nanosupport' ) )
        register_post_type( 'nanosupport', $args );

}

add_action( 'init', 'ns_register_cpt_nanosupport' );


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
            'ticket_priority'   => __( 'Priority', 'nanosupport' ),
            'ticket_responses'  => '<span class="dashicons dashicons-format-chat" title="Responses"></span>',
            'ticket_status'     => __( 'Ticket Status', 'nanosupport' ),
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
    $ticket_control = get_post_meta( $post_id, 'ns_control', true );
    switch ( $column ) {
        case 'ticket_priority' :
            $ticket_priority = $ticket_control ? $ticket_control['priority'] : false;
            if( $ticket_priority && 'low' === $ticket_priority )
                echo '<strong>'. __( 'Low', 'nanosupport' ) .'</strong>';
            else if( $ticket_priority && 'medium' === $ticket_priority )
                echo '<strong class="ns-text-info">' , __( 'Medium', 'nanosupport' ) , '</strong>';
            else if( $ticket_priority && 'high' === $ticket_priority )
                echo '<strong class="ns-text-warning">' , __( 'High', 'nanosupport' ) , '</strong>';
            else if( $ticket_priority && 'critical' === $ticket_priority )
                echo '<strong class="ns-text-danger">' , __( 'Critical', 'nanosupport' ) , '</strong>';
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
                    $status = '<span class="ns-label ns-label-success">'. __( 'Solved', 'nanosupport' ) .'</span>';
                } else if( 'inspection' === $ticket_status ) {
                    $status = '<span class="ns-label ns-label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>';
                } else {
                    $status = '<span class="ns-label ns-label-warning">'. __( 'Open', 'nanosupport' ) .'</span>';
                }
            } else {
                $status = '';
            }
            echo $status;
            break;

        case 'last_response' :
            $last_response  = ns_get_last_response( $post_id );
            $last_responder = get_userdata( $last_response['user_id'] );
            if ( $last_responder ) {
                echo $last_responder->display_name, '<br>';
                printf( __( '%s ago', 'nanosupport' ), human_time_diff( strtotime($last_response['comment_date']), current_time('timestamp') ) );
            } else {
                echo '-';
            }
            break;
    }
}

add_action( 'manage_nanosupport_posts_custom_column' , 'ns_populate_custom_columns', 10, 2 );


/**
 * NS Ticket Control Meta Fields.
 *
 * Ticket controlling elements in a custom meta box, hooked on to the
 * admin edit post page, on the side meta widgets.
 *
 * @since  1.0.0
 * 
 * hooked: 'post_submitbox_misc_actions' (10)
 *
 * @return  void
 * -----------------------------------------------------------------------
 */
function ns_control_specifics() {
    global $post;

    if( 'nanosupport' === $post->post_type ) :

        // Use nonce for verification
        wp_nonce_field( basename( __FILE__ ), 'ns_control_nonce' );


        $ns_control_array = get_post_meta( $post->ID, 'ns_control', true );

        if( ! $ns_control_array ) {
            
            //default
            $ns_control_array = array(
                                    'status'    => 'open',
                                    'priority'  => 'low',
                                    'agent'     => ''
                                );

        }
        ?>
        <div class="row ns-control-holder">

            <div class="ns-row misc-pub-section">
                <div class="ns-head-col">
                    <span class="dashicons dashicons-shield"></span> <?php _e( 'Ticket Status', 'nanosupport' );
                    echo ns_tooltip( __( 'Change the ticket status to track unsolved tickets separately.', 'nanosupport' ), 'below' );
                    ?>
                </div>
                <div class="ns-body-col">
                    <div class="ns-field">
                        <select name="ns_ticket_status" class="ns-field-item" id="ns-ticket-status">
                            <option value="open" <?php selected( $ns_control_array['status'], 'open' ); ?>><?php _e( 'Open', 'nanosupport' ); ?></option>
                            <option value="inspection"<?php selected( $ns_control_array['status'], 'inspection' ); ?>><?php _e( 'Under Inspection', 'nanosupport' ); ?></option>
                            <option value="solved"<?php selected( $ns_control_array['status'], 'solved' ); ?>><?php _e( 'Solved', 'nanosupport' ); ?></option>
                        </select>
                    </div> <!-- /.ns-field -->                    
                </div>
            </div> <!-- /.ns-row -->

            <div class="ns-row misc-pub-section">
                <div class="ns-head-col">
                    <span class="dashicons dashicons-sort"></span> <?php _e( 'Priority', 'nanosupport' );
                    echo ns_tooltip( __( 'Change the priority as per the content and urgency of the ticket.', 'nanosupport' ), 'below' );
                    ?>
                </div>
                <div class="ns-body-col">
                    <div class="ns-field">
                        <select name="ns_ticket_priority" class="ns-field-item" id="ns-ticket-priority">
                            <option value="low" <?php selected( $ns_control_array['priority'], 'low' ); ?>><?php _e( 'Low', 'nanosupport' ); ?></option>
                            <option value="medium" <?php selected( $ns_control_array['priority'], 'medium' ); ?>><?php _e( 'Medium', 'nanosupport' ); ?></option>
                            <option value="high" <?php selected( $ns_control_array['priority'], 'high' ); ?>><?php _e( 'High', 'nanosupport' ); ?></option>
                            <option value="critical" <?php selected( $ns_control_array['priority'], 'critical' ); ?>><?php _e( 'Critical', 'nanosupport' ); ?></option>
                        </select>
                    </div> <!-- /.ns-field -->                    
                </div>
            </div> <!-- /.ns-row -->

            <div class="ns-row misc-pub-section">
                <div class="ns-head-col">
                    <span class="dashicons dashicons-businessman"></span> <?php _e( 'Agent', 'nanosupport' );
                    echo ns_tooltip( __( 'Choose agent to assign the ticket. You can make an agent by editing the user from their user profile.', 'nanosupport' ), 'below' );
                    ?>
                </div>
                <div class="ns-body-col">
                    <?php
                    $agent_query = new WP_User_Query( array(
                            'meta_key'      => 'ns_make_agent',
                            'meta_value'    => 1,
                            'orderby'       => 'display_name'
                        ) );
                    ?>
                    <div class="ns-field">
                        <select name="ns_ticket_agent" class="ns-field-item" id="ns-ticket-agent">
                            <?php
                            if ( ! empty( $agent_query->results ) ) {
                                echo '<option value="">'. __( 'Assign an agent', 'nanosupport' ) .'</option>';
                                foreach ( $agent_query->results as $user ) {
                                    echo '<option value="'. $user->ID .'" '. selected( $ns_control_array['agent'], $user->ID ) .'>'. $user->display_name .'</option>';
                                }
                            } else {
                                echo '<option value="">'. __( 'No agent found', 'nanosupport' ) .'</option>';
                            }
                            ?>
                        </select>
                    </div> <!-- /.ns-field -->                    
                </div>
            </div> <!-- /.ns-row -->

        </div> <!-- .ns-control-holder -->
        <?php

    endif;
}

add_action('post_submitbox_misc_actions', 'ns_control_specifics');


/**
 * Save NS Ticket Control Meta Fields
 *
 * Saving the NanoSupport Ticket Control meta fields' values to the
 * postmeta table.
 *
 * @since  1.0.0
 * 
 * @param  integer $post_id Ticket Post ID.
 * -----------------------------------------------------------------------
 */
function ns_save_control_meta_data( $post_id ) {
     
    // verify nonce
    if (!isset($_POST['ns_control_nonce']) || !wp_verify_nonce($_POST['ns_control_nonce'], basename(__FILE__)))
        return $post_id;
    
    // check autosave
    if ( wp_is_post_autosave( $post_id ) )
        return $post_id;

    //check post revision
    if ( wp_is_post_revision( $post_id ) )
        return $post_id;
    
    // check permissions
    if ( 'nanosupport' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return $post_id;
    }

    $ns_ticket_status      = $_POST['ns_ticket_status'];
    $ns_ticket_priority    = $_POST['ns_ticket_priority'];
    $ns_ticket_agent       = $_POST['ns_ticket_agent'];

    $ns_control = array(
            'status'    => sanitize_text_field( $ns_ticket_status ),
            'priority'  => sanitize_text_field( $ns_ticket_priority ),
            'agent'     => absint( $ns_ticket_agent )
        );

    update_post_meta( $post_id, 'ns_control', $ns_control );
}

add_action( 'save_post',        'ns_save_control_meta_data' );
add_action( 'new_to_publish',   'ns_save_control_meta_data' );


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



    /**
     * Insert default term
     *
     * Insert default term 'Support' to the taxonomy 'nanosupport_department'.
     *
     * Term: 'support'
     * ...
     */
    wp_insert_term(
        __( 'Support', 'nanosupport' ), // the term 
        'nanosupport_department',      // the taxonomy
        array(
            'description'=> __( 'Support department is dedicated to provide the necessary support', 'nanosupport' ),
            'slug' => 'support'
        )
    );

}

add_action( 'init', 'ns_create_nanosupport_taxonomies', 0 );


/**
 * Make a Default Taxonomy Term for 'nanosupport_department'
 *
 * @link http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
 *
 * @author    Michael Fields     http://wordpress.mfields.org/
 * @props     John P. Bloch      http://www.johnpbloch.com/
 *
 * @since     2010-09-13
 * @alter     2010-09-14
 *
 * @since     1.0.0
 *
 * @license   GPLv2
 * -----------------------------------------------------------------------
 */
function ns_set_default_object_terms( $post_id ) {
    if ( 'publish' === get_post_status( $post_id ) || 'private' === get_post_status( $post_id ) ) {
        $defaults = array(
                'nanosupport_department' => array( 'support' )
            );
        
        $taxonomies = get_object_taxonomies( get_post_type( $post_id ) );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}

add_action( 'save_post',        'ns_set_default_object_terms', 100 );
add_action( 'new_to_publish',   'ns_set_default_object_terms', 100 );
