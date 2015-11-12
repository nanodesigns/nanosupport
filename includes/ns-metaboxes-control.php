<?php
/**
 * Ticket control meta box
 * 
 * Adding control fields per support ticket.
 *
 * @package  NanoSupport
 */


function ns_control_meta_box() {
    add_meta_box(
        'nanosupport-control',                      // metabox ID
        __('Ticket Control', 'nanosupport'),    // metabox title
        'ns_control_specifics',                    // callback function
        'nanosupport',                              // post type (+ CPT)
        'side',                                     // 'normal', 'advanced', or 'side'
        'high'                                      // 'high', 'core', 'default' or 'low'
    );
}
add_action( 'add_meta_boxes', 'ns_control_meta_box' );


// The Callback
function ns_control_specifics() {
    global $post;

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

        <div class="ns-row">
            <div class="ns-head-col">
                <span class="dashicons dashicons-shield"></span> <?php _e( 'Ticket Status', 'nanosupport' ); ?>
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

        <div class="ns-row">
            <div class="ns-head-col">
                <span class="dashicons dashicons-sort"></span> <?php _e( 'Priority', 'nanosupport' ); ?>
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

        <div class="ns-row">
            <div class="ns-head-col">
                <span class="dashicons dashicons-businessman"></span> <?php _e( 'Agent', 'nanosupport' ); ?>
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
}

// Save the Data
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