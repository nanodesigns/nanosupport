<?php
/**
 * Responses meta box.
 * 
 * Adding repeating fields as per the responses.
 *
 * @package  nano_support_ticket
 * =======================================================================
 */


function nst_responses_meta_box() {
    add_meta_box(
        'nanosupport-responses',                // metabox ID
        __('Responses', 'nanodesigns-nst'),     // metabox title
        'nst_reply_specifics',                  // callback function
        'nanosupport',                          // post type (+ CPT)
        'normal',                               // 'normal', 'advanced', or 'side'
        'high'                                  // 'high', 'core', 'default' or 'low'
    );
}
add_action( 'add_meta_boxes', 'nst_responses_meta_box' );


// The Callback
function nst_reply_specifics() {
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
    wp_nonce_field( basename( __FILE__ ), 'nst_responses_nonce' );

    $args = array(
        'post_id'   => $post->ID,
        'post_type' => 'nanosupport',
        'status'    => 'approve',
        'orderby'   => 'comment_date',
        'order'     => 'ASC'
    );
    $response_array = get_comments( $args ); ?>
    
    <div class="row nst-holder">
        <h2><?php _e('Responses to the ticket', 'nanodesigns-nst' ); ?></h2>

        <?php if( $response_array ) {

        	$counter = 1;

	        foreach( $response_array as $response ) {
                $date_human_readable = date( 'd F Y h:i:s A - l', strtotime( $response->comment_date ) );
                ?>
		        <div id="nst-responses-info-<?php echo $counter; ?>" class="nst-response-group">
		        	<div class="nst-row">
		        		<div class="response-user">
                            <input type="hidden" id="nst-responseid" name="nst_responseid[]" value="<?php echo intval( $response->comment_ID ); ?>">
                            <input type="hidden" id="nst-user" name="nst_user[]" value="<?php echo esc_html( $response->user_id ); ?>">
		        			<input type="hidden" id="nst-date" name="nst_date[]" value="<?php echo esc_html( $response->comment_date ); ?>">
		        			<?php
		        			//echo get_avatar( $response['u'], 10 );
		        			echo $response->comment_author;
		        			echo ' &mdash; ';
                            echo '<span>'. esc_html( $date_human_readable ) .'</span>';
                            ?>
		        			<span class="go-right"><?php _e( 'Response', 'nanodesigns-nst' ); ?>
                                <?php echo ' #', $counter; ?>
                                <a id="<?php echo $response->comment_ID; ?>" class="delete-response dashicons dashicons-dismiss" onclick="return confirm('Are you sure you want to delete the response?');" href="<?php echo admin_url('/post.php?post='. $post->ID .'&action=edit&resID='. $response->comment_ID .'&del=true'); ?>"></a>
                            </span>
		        		</div>
		                <div class="nst-box">
		                    <div class="nst-field">
		                        <textarea class="nst-field-item" name="nst_response[]" id="nst-response-<?php echo $counter; ?>" rows="5"><?php echo html_entity_decode( $response->comment_content ); ?></textarea>
		                    </div> <!-- /.nst-field -->
		                </div> <!-- /.nst-box -->
		            </div> <!-- /.nst-row -->
		        </div> <!-- /#nst-responses-info .nst-response-group -->
	        <?php
	        $counter++;
	        } //endforeach ?>

		<?php } //endif ?>

    </div> <!-- .nst-holder -->
	
	<br>
	<div id="nst-add-response" class="button button-large button-primary nst-btn"><span class="dashicons dashicons-plus"></span> <?php _e('Add New Response', 'nanodesigns-nst' ); ?></div>
	<div id="nst-remove-response" style="display:none;" class="button button-large button-default nst-btn"><span class="dashicons dashicons-minus"></span> <?php _e('Remove Last Response', 'nanodesigns-nst' ); ?></div>

    <script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function($) {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
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
                        target_btn.closest('.nst-response-group').slideUp();
                        target_btn.closest('.nst-response-group').find('textarea').val('');
                    }
                }
            });
        });
    });
    </script>

    <?php
}

// Save the Data
function nst_save_reply_meta_data( $post_id ) {
     
    // verify nonce
    if (!isset($_POST['nst_responses_nonce']) || !wp_verify_nonce($_POST['nst_responses_nonce'], basename(__FILE__)))
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

    $response_id_array      = $_POST['nst_responseid'];
    $response_msgs_array	= $_POST['nst_response'];
    $response_date_array    = $_POST['nst_date'];
    $response_users_array	= $_POST['nst_user'];

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

        $existing_comment_ID = nst_response_exists( $response_id_array[$key] );

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

add_action( 'save_post', 'nst_save_reply_meta_data' );
add_action( 'new_to_publish', 'nst_save_reply_meta_data' );