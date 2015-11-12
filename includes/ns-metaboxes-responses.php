<?php
/**
 * Responses meta box
 * 
 * Adding repeating fields as per the responses.
 *
 * @package  Nano Support
 */


function ns_responses_meta_box() {
    add_meta_box(
        'nanosupport-responses',                // metabox ID
        __('Responses', 'nano-support-ticket'),     // metabox title
        'ns_reply_specifics',                  // callback function
        'nanosupport',                          // post type (+ CPT)
        'normal',                               // 'normal', 'advanced', or 'side'
        'high'                                  // 'high', 'core', 'default' or 'low'
    );
}
add_action( 'add_meta_boxes', 'ns_responses_meta_box' );


// The Callback
function ns_reply_specifics() {
    global $post;

    if( isset( $_REQUEST['resID'] ) && $_REQUEST['del'] ) {
        if( $_REQUEST['del'] == true ) {
            $del_success = wp_delete_comment( $_REQUEST['resID'], false );
            wp_set_comment_status( $_REQUEST['resID'], 'trash' );

            //if( ! is_wp_error( $del_success ) ) echo "Deleted";
            ! is_wp_error( $del_success ) and print 'Response Deleted!';
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
    
    <div class="row ns-holder">
        <h2><?php _e('Responses to the ticket', 'nano-support-ticket' ); ?></h2>

        <?php if( $response_array ) {

        	$counter = 1;

	        foreach( $response_array as $response ) {
                $date_human_readable = date( 'd F Y h:i:s A - l', strtotime( $response->comment_date ) );
                ?>
		        <div id="ns-responses-info-<?php echo $counter; ?>" class="ns-response-group">
		        	<div class="ns-row">
		        		<div class="response-user">

                            <input type="hidden" id="ns-responseid" name="ns_responseid[]" value="<?php echo intval( $response->comment_ID ); ?>">
                            <input type="hidden" id="ns-user" name="ns_user[]" value="<?php echo esc_html( $response->user_id ); ?>">
		        			<input type="hidden" id="ns-date" name="ns_date[]" value="<?php echo esc_html( $response->comment_date ); ?>">

		        			<?php
		        			//echo get_avatar( $response['u'], 10 );
		        			echo $response->comment_author;
		        			echo ' &mdash; ';
                            echo '<span>'. esc_html( $date_human_readable ) .'</span>';
                            ?>
		        			<span class="go-right"><?php _e( 'Response', 'nano-support-ticket' ); ?>
                                <?php echo ' #', $counter; ?>
                                <a id="<?php echo $response->comment_ID; ?>" class="delete-response dashicons dashicons-dismiss" onclick="return confirm('Are you sure you want to delete the response?');" href="<?php echo admin_url('/post.php?post='. $post->ID .'&action=edit&resID='. $response->comment_ID .'&del=true'); ?>"></a>
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
	<div id="ns-add-response" class="button button-large button-primary ns-btn"><span class="dashicons dashicons-plus"></span> <?php _e('Add New Response', 'nano-support-ticket' ); ?></div>
	<div id="ns-remove-response" style="display:none;" class="button button-large button-default ns-btn"><span class="dashicons dashicons-minus"></span> <?php _e('Remove Last Response', 'nano-support-ticket' ); ?></div>

    <script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function($) {
        var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        $(document).on('click', '.delete-response', function () {
            var id = this.id;
            //$('.bubble span.count').html( '<img src="'+ nano.theme_path +'/images/count-loader.gif" alt="loading">' );

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
        });
    });
    </script>

    <?php
}

// Save the Data
function ns_save_reply_meta_data( $post_id ) {
     
    // verify nonce
    if (!isset($_POST['ns_responses_nonce']) || !wp_verify_nonce($_POST['ns_responses_nonce'], basename(__FILE__)))
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

    $response_id_array      = $_POST['ns_responseid'];
    $response_msgs_array	= $_POST['ns_response'];
    $response_date_array    = $_POST['ns_date'];
    $response_users_array	= $_POST['ns_user'];

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
}

add_action( 'save_post', 'ns_save_reply_meta_data' );
add_action( 'new_to_publish', 'ns_save_reply_meta_data' );


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