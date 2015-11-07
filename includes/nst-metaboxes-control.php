<?php
/**
 * Ticket control meta box
 * 
 * Adding control fields per support ticket.
 *
 * @package  Nano Support Ticket
 * =======================================================================
 */


function nst_control_meta_box() {
    add_meta_box(
        'nanosupport-control',                      // metabox ID
        __('Ticket Control', 'nanodesigns-nst'),    // metabox title
        'nst_control_specifics',                    // callback function
        'nanosupport',                              // post type (+ CPT)
        'side',                                     // 'normal', 'advanced', or 'side'
        'high'                                      // 'high', 'core', 'default' or 'low'
    );
}
add_action( 'add_meta_boxes', 'nst_control_meta_box' );


// The Callback
function nst_control_specifics() {
    global $post;

    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'nst_control_nonce' );


    $nst_control_array = get_post_meta( $post->ID, 'nst_control', true );

    if( ! $nst_control_array ) {
        
        //default
        $nst_control_array = array(
                                'status'    => 'open',
                                'priority'  => 'low',
                                'agent'     => ''
                            );

    }
    ?>
    <div class="row nst-control-holder">

        <div class="nst-row">
            <div class="nst-head-col">
                <span class="dashicons dashicons-shield"></span> <?php _e( 'Ticket Status', 'nanodesigns-nst' ); ?>
            </div>
            <div class="nst-body-col">
                <div class="nst-field">
                    <select name="nst_ticket_status" class="nst-field-item" id="nst-ticket-status">
                        <option value="open" <?php selected( $nst_control_array['status'], 'open' ); ?>><?php _e( 'Open', 'nanodesigns-nst' ); ?></option>
                        <option value="inspection"<?php selected( $nst_control_array['status'], 'inspection' ); ?>><?php _e( 'Under Inspection', 'nanodesigns-nst' ); ?></option>
                        <option value="solved"<?php selected( $nst_control_array['status'], 'solved' ); ?>><?php _e( 'Solved', 'nanodesigns-nst' ); ?></option>
                    </select>
                </div> <!-- /.nst-field -->                    
            </div>
        </div> <!-- /.nst-row -->

        <div class="nst-row">
            <div class="nst-head-col">
                <span class="dashicons dashicons-sort"></span> <?php _e( 'Priority', 'nanodesigns-nst' ); ?>
            </div>
            <div class="nst-body-col">
                <div class="nst-field">
                    <select name="nst_ticket_priority" class="nst-field-item" id="nst-ticket-priority">
                        <option value="low" <?php selected( $nst_control_array['priority'], 'low' ); ?>><?php _e( 'Low', 'nanodesigns-nst' ); ?></option>
                        <option value="medium" <?php selected( $nst_control_array['priority'], 'medium' ); ?>><?php _e( 'Medium', 'nanodesigns-nst' ); ?></option>
                        <option value="high" <?php selected( $nst_control_array['priority'], 'high' ); ?>><?php _e( 'High', 'nanodesigns-nst' ); ?></option>
                        <option value="critical" <?php selected( $nst_control_array['priority'], 'critical' ); ?>><?php _e( 'Critical', 'nanodesigns-nst' ); ?></option>
                    </select>
                </div> <!-- /.nst-field -->                    
            </div>
        </div> <!-- /.nst-row -->

        <div class="nst-row">
            <div class="nst-head-col">
                <span class="dashicons dashicons-businessman"></span> <?php _e( 'Agent', 'nanodesigns-nst' ); ?>
            </div>
            <div class="nst-body-col">
                <?php
                $agent_query = new WP_User_Query( array(
                        'meta_key'      => 'nst_make_agent',
                        'meta_value'    => 1,
                        'orderby'       => 'display_name'
                    ) );
                ?>
                <div class="nst-field">
                    <select name="nst_ticket_agent" class="nst-field-item" id="nst-ticket-agent">
                        <?php
                        if ( ! empty( $agent_query->results ) ) {
                            echo '<option value="">'. __( 'Assign an agent', 'nanodesigns-nst' ) .'</option>';
                            foreach ( $agent_query->results as $user ) {
                                echo '<option value="'. $user->ID .'" '. selected( $nst_control_array['agent'], $user->ID ) .'>'. $user->display_name .'</option>';
                            }
                        } else {
                            echo '<option value="">'. __( 'No agent found', 'nanodesigns-nst' ) .'</option>';
                        }
                        ?>
                    </select>
                </div> <!-- /.nst-field -->                    
            </div>
        </div> <!-- /.nst-row -->

    </div> <!-- .nst-control-holder -->
    <?php
}

// Save the Data
function nst_save_control_meta_data( $post_id ) {
     
    // verify nonce
    if (!isset($_POST['nst_control_nonce']) || !wp_verify_nonce($_POST['nst_control_nonce'], basename(__FILE__)))
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

    $nst_ticket_status      = $_POST['nst_ticket_status'];
    $nst_ticket_priority    = $_POST['nst_ticket_priority'];
    $nst_ticket_agent       = $_POST['nst_ticket_agent'];

    $nst_control = array(
            'status'    => sanitize_text_field( $nst_ticket_status ),
            'priority'  => sanitize_text_field( $nst_ticket_priority ),
            'agent'     => absint( $nst_ticket_agent )
        );

    update_post_meta( $post_id, 'nst_control', $nst_control );
}

add_action( 'save_post',        'nst_save_control_meta_data' );
add_action( 'new_to_publish',   'nst_save_control_meta_data' );