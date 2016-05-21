<?php
/**
 * Responses meta box
 * 
 * Adding repeating fields as per the responses.
 *
 * @author      nanodesigns
 * @category    Metaboxes
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function ns_responses_meta_box() {
    add_meta_box(
        'nanosupport-responses',                // metabox ID
        __('Responses', 'nanosupport'),         // metabox title
        'ns_reply_specifics',                   // callback function
        'nanosupport',                          // post type (+ CPT)
        'normal',                               // 'normal', 'advanced', or 'side'
        'high'                                  // 'high', 'core', 'default' or 'low'
    );

    if( current_user_can('manage_nanosupport') ) :

        add_meta_box(
            'nanosupport-internal-notes',       // metabox ID
            __('Internal Notes', 'nanosupport'),// metabox title
            'ns_internal_notes_specifics',      // callback function
            'nanosupport',                      // post type (+ CPT)
            'side',                             // 'normal', 'advanced', or 'side'
            'default'                           // 'high', 'core', 'default' or 'low'
        );

    endif;

    /**
     * Remove Comment Meta Box
     * Remove the default Comment Meta Box if exists.
     */
    remove_meta_box( 'commentsdiv', 'nanosupport', 'normal' );
}

add_action( 'add_meta_boxes', 'ns_responses_meta_box' );


// Responses Callback
function ns_reply_specifics() {
    global $post;

    if( isset( $_REQUEST['resID'] ) && $_REQUEST['del'] ) {
        if( $_REQUEST['del'] == true ) {
            $del_success = wp_delete_comment( $_REQUEST['resID'], false );
            wp_set_comment_status( $_REQUEST['resID'], 'trash' );

            //if( ! is_wp_error( $del_success ) ) echo "Deleted";
            ! is_wp_error( $del_success ) and print __( 'Response Deleted!', 'nanosupport');
        }
    }

    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'ns_responses_nonce' );

    $args = array(
        'post_id'   => $post->ID,
        'post_type' => 'nanosupport',
        'status'    => 'approve',
        'orderby'   => 'comment_date',
        'order'     => 'ASC'
    );
    $response_array = get_comments( $args ); ?>
    
    <div class="ns-row ns-holder">

        <?php if( $response_array ) {

            $counter = 1;

            foreach( $response_array as $response ) {
                $date_human_readable = date( 'd F Y h:i:s A - l', strtotime( $response->comment_date ) );
                ?>
                <div id="ns-responses-info-<?php echo $counter; ?>" class="ns-response-group">
                    <div class="ns-row">
                        <div class="response-user">

                            <input type="hidden" id="ns-responseid" name="ns_responseid[]" value="<?php echo intval( $response->comment_ID ); ?>">
                            <input type="hidden" id="ns-user" name="ns_user[]" value="<?php echo absint( $response->user_id ); ?>">
                            <input type="hidden" id="ns-date" name="ns_date[]" value="<?php echo esc_html( $response->comment_date ); ?>">

                            <?php
                            //echo get_avatar( $response['u'], 10 );
                            echo $response->comment_author;
                            echo ' &mdash; ';
                            echo '<span>'. esc_html( $date_human_readable ) .'</span>';
                            ?>
                            <span class="go-right"><?php _e( 'Response', 'nanosupport' ); ?>
                                <?php echo ' #', $counter; ?>
                                <a id="<?php echo $response->comment_ID; ?>" class="delete-response dashicons dashicons-dismiss" href="<?php echo admin_url('/post.php?post='. $post->ID .'&action=edit&resID='. $response->comment_ID .'&del=true'); ?>"></a>
                            </span>
                        </div>
                        <div class="ns-box">
                            <div class="ns-field">
                                <textarea class="ns-field-item" name="ns_response[]" id="ns-response-<?php echo $counter; ?>" rows="5"><?php echo html_entity_decode( $response->comment_content ); ?></textarea>
                            </div> <!-- /.ns-field -->
                        </div> <!-- /.ns-box -->
                    </div> <!-- /.ns-row -->
                </div> <!-- /#ns-responses-info .ns-response-group -->
            <?php
            $counter++;
            } //endforeach ?>

        <?php } //endif ?>

    </div> <!-- .ns-holder -->
    
    <br>
    <button id="ns-save-response" style="display:none;" class="button button-large button-default ns-btn"><span class="dashicons dashicons-archive"></span> <?php _e('Save Responses', 'nanosupport' ); ?></button>
    <?php if( 'pending' === get_post_status( $post ) ) : ?>
        <?php _e( 'You cannot add response to a pending ticket.', 'nanosupport' ); ?>
    <?php else : ?>
       <div id="ns-add-response" class="button button-large button-primary ns-btn"><span class="dashicons dashicons-plus"></span> <?php _e('Add New Response', 'nanosupport' ); ?></div>
    <?php endif; ?>
    <div id="ns-remove-response" style="display:none;" class="button button-large button-default ns-btn"><span class="dashicons dashicons-minus"></span> <?php _e('Remove Last Response', 'nanosupport' ); ?></div>

    <script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function($) {
        var ajaxurl         = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
            confirmation    = '<?php _e( 'Are you sure you want to delete the response?', 'nanosupport' ); ?>';
        $(document).on('click', '.delete-response', function () {
            var confirmed = confirm(confirmation);
            if( confirmed == true ) {
                var id = this.id;

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {"action": "delete_response",
                                "id": id
                            },
                    success: function (data) {
                        if( data != false ) {
                            var target_btn = $('#'+data);
                            target_btn.closest('.ns-response-group').slideUp();
                            target_btn.closest('.ns-response-group').find('textarea').val('');
                        }
                    }
                });
            }
        });
    });
    </script>

    <?php
}


// Internal Notes Callback
function ns_internal_notes_specifics() {
    global $post;
    $meta_data = get_post_meta( $post->ID, 'ns_internal_note', true );
    ?>
    <div class="ns-row">
        <div class="ns-box">
            <div class="ns-field">
                <textarea class="ns-field-item" name="ns_internal_note" id="ns-internal-note" rows="5" placeholder="<?php esc_attr_e( 'Write down any internal note to pass to any Support Agent internally.', 'nanosupport' ); ?>"><?php echo isset($_POST['ns_internal_note']) ? $_POST['ns_internal_note'] : $meta_data; ?></textarea>
            </div> <!-- /.ns-field -->
        </div> <!-- /.ns-box -->
    </div> <!-- /.ns-row -->
    <?php
}


/**
 * NS Ticket Control Meta Fields.
 *
 * Ticket controlling elements in a custom meta box, hooked on to the
 * admin edit post page, on the side meta widgets.
 *
 * @since  1.0.0
 * 
 * hooked: 'post_submitbox_misc_actions' (10)
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
                    echo ns_tooltip( __( 'Change the ticket status to track unsolved tickets separately.', 'nanosupport' ), 'left' );
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
                    echo ns_tooltip( __( 'Change the priority as per the content and urgency of the ticket.', 'nanosupport' ), 'left' );
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
                    echo ns_tooltip( __( 'Choose agent to assign the ticket. You can make an agent by editing the user from their user profile.', 'nanosupport' ), 'left' );
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


// Save the Data
function ns_save_nanosupport_meta_data( $post_id ) {
     
    // verify nonce
    if (! isset($_POST['ns_responses_nonce']) || ! wp_verify_nonce($_POST['ns_responses_nonce'], basename(__FILE__)))
        return $post_id;
    
    // check autosave
    if ( wp_is_post_autosave( $post_id ) )
        return $post_id;

    //check post revision
    if ( wp_is_post_revision( $post_id ) )
        return $post_id;
    
    // check permissions
    if ( 'nanosupport' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_nanosupport', $post_id ) )
            return $post_id;
    }

    $response_id_array      = $_POST['ns_responseid'];
    $response_msgs_array    = $_POST['ns_response'];
    $response_date_array    = $_POST['ns_date'];
    $response_users_array   = $_POST['ns_user'];

    update_post_meta( $post_id, 'ns_control', array(
            'status'    => sanitize_text_field( $ns_ticket_status ),
            'priority'  => sanitize_text_field( $ns_ticket_priority ),
            'agent'     => absint( $ns_ticket_agent )
        ) );
    foreach ( $response_msgs_array as $key => $message ) {
        
        $user_info = get_userdata( $response_users_array[$key] );
        
        $commentdata = array(
                            'comment_post_ID'       => absint( $post_id ),
                            'comment_author'        => sanitize_text_field( $user_info->display_name ),
                            'comment_author_email'  => sanitize_email( $user_info->user_email ),
                            'comment_author_url'    => esc_url( $user_info->user_url ),
                            'comment_date'          => wp_strip_all_tags( $response_date_array[$key] ),
                            'comment_content'       => htmlentities( $message ),
                            'comment_type'          => 'nanosupport_response',
                            'comment_parent'        => 0,
                            'user_id'               => absint( $response_users_array[$key] ),
                            'comment_approved'      => '1' //approve by default
                        );

        $existing_comment_ID = ns_response_exists( $response_id_array[$key] );

        if( ! $existing_comment_ID ) {
            //insert a new response
            $comment_id = wp_insert_comment( $commentdata );            
        } else {
            if( empty( $message ) ) {
                //delete the response to trash
                wp_delete_comment( $existing_comment_ID, false );
                $commentdata['comment_approved'] = 'trash';
                wp_update_comment( $commentdata );
            } else {
                //the response is changed, so update accordingly
                $commentdata['comment_ID'] = $existing_comment_ID;
                wp_update_comment( $commentdata );
            }
        }
        
    } //endforeach

    /**
     * Save Internal Notes.
     * ...
     */
    $internal_note = $_POST['ns_internal_note'];
    $existing_internal_note = get_post_meta( $post_id, 'ns_internal_note', true );

    if( $internal_note && $internal_note != $existing_internal_note ) {
        update_post_meta( $post_id, 'ns_internal_note', esc_html( $internal_note ) );
    } elseif( '' == $internal_note && $existing_internal_note ) {
        delete_post_meta( $post_id, 'ns_internal_note', $existing_internal_note );
    }
}

add_action( 'save_post',        'ns_save_nanosupport_meta_data' );
add_action( 'new_to_publish',   'ns_save_nanosupport_meta_data' );


/**
 * Delete Response in admin panel.
 * AJAX powered deletion of response.
 * -----------------------------------------------------------------------
 */
function ns_del_response() {
    if( isset( $_POST['id'] ) ) {
        $comment_id = $_POST['id'];
        wp_delete_comment( $comment_id, false ); //trash it only
        wp_set_comment_status( $comment_id, 'trash' );
        echo $comment_id;
        die;
    } else {
        echo false;
        die;
    }
}
add_action( 'wp_ajax_delete_response', 'ns_del_response' );
//add_action( 'wp_ajax_nopriv_delete_response', 'ns_del_response' ); //not logged in users
