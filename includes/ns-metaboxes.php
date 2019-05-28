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
        'nanosupport-responses',                    // metabox ID
        esc_html__( 'Responses', 'nanosupport' ),   // metabox title
        'ns_reply_specifics',                       // callback function
        'nanosupport',                              // post type (+ CPT)
        'normal',                                   // 'normal', 'advanced', or 'side'
        'high'                                      // 'high', 'core', 'default' or 'low'
    );

	if( ns_is_user('agent_and_manager') ) :

		add_meta_box(
            'nanosupport-internal-notes',           // metabox ID
            esc_html__( 'Internal Notes', 'nanosupport' ),  // metabox title
            'ns_internal_notes_specifics',          // callback function
            'nanosupport',                          // post type (+ CPT)
            'side',                                 // 'normal', 'advanced', or 'side'
            'default'                               // 'high', 'core', 'default' or 'low'
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

    // Use nonce for verification
	wp_nonce_field( basename( __FILE__ ), 'ns_responses_nonce' );

	$args = array(
		'post_id'   => $post->ID,
		'post_type' => 'nanosupport',
		'status'    => 'approve',
		'orderby'   => 'comment_date',
		'order'     => 'ASC',
		'type'      => array('nanosupport_response', 'nanosupport_change')
	);

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * ns_ticket_responses_arg
     *
     * Hook to change the query for the ticket responses.
     *
     * @since  1.0.0
     * -----------------------------------------------------------------------
     */
    $response_array = get_comments( apply_filters( 'ns_ticket_responses_arg', $args ) ); ?>

    <div class="ns-row ns-holder">

    	<?php if( $response_array ) {

    		$counter = 1;

    		$NS_Ticket_Changelog = new NS_Ticket_Changelog();
    		if( $NS_Ticket_Changelog::is_active() ) : ?>
    			<nav class="ticket-separator ticket-separator-center reply-toggler" aria-label="<?php _e('NanoSupport Responses Toggler', 'nanosupport'); ?>">
    				<div class="ns-btn-group">
    					<button type="button" class="ns-btn ns-btn-default ns-btn-xs ns-btn-reply-toggler active" value="all" aria-selected="true" aria-label="<?php _ex('Click to Display both Replies and Changes', 'Responses Toggler', 'nanosupport'); ?>">
    						<?php _e('All', 'nanosupport'); ?>
    					</button>
    					<button type="button" class="ns-btn ns-btn-default ns-btn-xs ns-btn-reply-toggler" value="replies" aria-selected="false" aria-label="<?php _ex('Click to Display Replies Only', 'Responses Toggler', 'nanosupport'); ?>">
    						<?php _e('Replies Only', 'nanosupport'); ?>
    					</button>
    					<button type="button" class="ns-btn ns-btn-default ns-btn-xs ns-btn-reply-toggler" value="changelog" aria-selected="false" aria-label="<?php _ex('Click to Display Changelog Only', 'Responses Toggler', 'nanosupport'); ?>">
    						<?php _e('Changelog Only', 'nanosupport'); ?>
    					</button>
    				</div>
    			</nav>
    		<?php endif;

    		foreach( $response_array as $response ) {

    			if( 'nanosupport_response' === $response->comment_type ) {  ?>

    				<div class="ns-cards ticket-response-cards">
    					<div class="ns-row">
    						<div class="response-user">
    							<div class="response-head">
    								<h3 class="ticket-head" id="response-<?php echo esc_attr($counter); ?>">
    									<?php echo ns_user_nice_name($response->user_id) .' &mdash; <small>'. ns_date_time( strtotime($response->comment_date) ) .'</small>'; ?>
    								</h3>
    							</div> <!-- /.response-head -->
    						</div> <!-- /.response-user -->
    						<?php
    						$del_response_link = add_query_arg( 'del_response', $response->comment_ID, $_SERVER['REQUEST_URI'] );
    						$del_response_link = wp_nonce_url( $del_response_link, 'delete-ticket-response' );
    						?>
    						<div class="response-handle ns-text-muted">
    							<?php
    							/* translators: counting number of the response */
    							printf( esc_html__( 'Response #%s', 'nanosupport' ), $counter ); ?>
    							<a id="<?php echo $response->comment_ID; ?>" class="delete-response dashicons dashicons-dismiss ns-text-danger" href="<?php echo esc_url($del_response_link); ?>" title="<?php printf( __( 'Delete this Response number %s from %s', 'nanosupport' ), $counter, ns_user_nice_name($response->user_id)); ?>" role="button"></a>
    						</div> <!-- /.response-handle -->
    					</div> <!-- /.ns-row -->
    					<div class="ticket-response">
    						<?php echo wpautop( $response->comment_content ); ?>
    					</div>
    				</div>
    				<!-- /.ns-cards -->

    			<?php } else if( 'nanosupport_change' === $response->comment_type ) { ?>

    				<div class="ticket-log ns-small ns-text-muted"><?php echo $NS_Ticket_Changelog::translate_changes($response); ?></div>

    			<?php } ?>

    			<?php
    			$counter++;
    		} //endforeach ?>

    	<?php } //endif ?>

    	<?php global $current_user; ?>

    	<?php $ticket_meta = ns_get_ticket_meta( $post->ID ); ?>

    	<?php
    	if( 'pending' === $ticket_meta['status']['value'] ) {

    		echo '<div class="ns-alert ns-alert-info" role="alert">';
    		echo '<i class="dashicons dashicons-info" aria-hidden="true"></i>&nbsp;';
    		echo wp_kses( __( 'You cannot add response to a pending ticket. <strong>Publish</strong> it first.', 'nanosupport' ), array('strong' => array()) );
    		echo '</div>';

    	} elseif( 'solved' === $ticket_meta['status']['value'] ) {

    		echo '<div class="ns-alert ns-alert-success" role="alert">';
    		echo '<i class="dashicons dashicons-info" aria-hidden="true"></i>&nbsp;';
    		echo wp_kses( __( 'Ticket is already solved. <strong>ReOpen</strong> it to add new response.', 'nanosupport' ), array('strong' => array()) );
    		echo '</div>';

    	} else { ?>

    		<div class="ns-cards ns-feedback">
    			<div class="ns-row">
    				<div class="response-user">
    					<div class="response-head">
    						<h3 class="ticket-head" id="new-response">
    							<?php printf( esc_html__( 'Responding as: %s', 'nanosupport' ), $current_user->display_name ); ?>
    						</h3>
    					</div> <!-- /.response-head -->
    				</div>
    				<div class="response-handle">
    					<?php echo ns_date_time( current_time('timestamp') ); ?>
    				</div>
    			</div> <!-- /.ns-row -->
    			<div class="ns-feedback-form">

    				<div class="ns-form-group">
    					<textarea class="ns-field-item" name="ns_new_response" id="ns-new-response" rows="6" aria-label="<?php esc_attr_e('Write down the response to the ticket', 'nanosupport'); ?>" placeholder="<?php esc_attr_e('Write down your response', 'nanosupport'); ?>"><?php echo isset($_POST['ns_new_response']) ? $_POST['ns_new_response'] : ''; ?></textarea>
    				</div> <!-- /.ns-form-group -->
    				<button id="ns-save-response" class="button button-large button-primary ns-btn"><?php esc_html_e( 'Save Response', 'nanosupport' ); ?></button>

    			</div>
    		</div> <!-- /.ns-feedback-form -->

    		<?php
    	} //endif( 'pending' === $ticket_meta['value'] ) { ?>

	</div> <!-- .ns-holder -->

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
				<label for="ns-internal-note" class="screen-reader-text"><?php esc_html_e( 'Internal Notes', 'nanosupport' ) ?></label>
				<textarea class="ns-field-item" name="ns_internal_note" id="ns-internal-note" rows="5" placeholder="<?php esc_attr_e( 'Write down any internal note to pass to any Support Agent internally.', 'nanosupport' ); ?>" aria-describedby="internal-notes-description"><?php echo isset($_POST['ns_internal_note']) ? $_POST['ns_internal_note'] : esc_html( $meta_data ); ?></textarea>
				<?php echo '<p class="description" id="internal-notes-description">'. esc_html__( 'Internal notes are not visible to Support Seekers. It&rsquo;s to pass important notes within the support team.', 'nanosupport' ) .'</p>'; ?>
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
 * hooked: 'post_submitbox_misc_actions' (10)
 * -----------------------------------------------------------------------
 */
function ns_control_specifics() {
	global $post;

	if( 'nanosupport' === $post->post_type ) :

        // Use nonce for verification
		wp_nonce_field( basename( __FILE__ ), 'ns_control_nonce' );

        //get meta values from db
		$_ns_ticket_status   = get_post_meta( $post->ID, '_ns_ticket_status', true );
		$_ns_ticket_priority = get_post_meta( $post->ID, '_ns_ticket_priority', true );
		$_ns_ticket_agent    = get_post_meta( $post->ID, '_ns_ticket_agent', true );
		$_ns_ticket_product  = get_post_meta( $post->ID, '_ns_ticket_product', true );
		$_ns_ticket_receipt  = get_post_meta( $post->ID, '_ns_ticket_product_receipt', true );

        //set default values
		$_ns_ticket_status   = ! empty($_ns_ticket_status)    ? $_ns_ticket_status     : 'open';
		$_ns_ticket_priority = ! empty($_ns_ticket_priority)  ? $_ns_ticket_priority   : 'low';
		$_ns_ticket_agent    = ! empty($_ns_ticket_agent)     ? $_ns_ticket_agent      : '';
		$_ns_ticket_product  = ! empty($_ns_ticket_product)   ? $_ns_ticket_product    : '';
		$_ns_ticket_receipt  = ! empty($_ns_ticket_receipt)   ? $_ns_ticket_receipt    : '';
		?>

		<div class="row ns-control-holder">

			<div class="ns-row misc-pub-section">
				<div class="ns-head-col">
					<label for="ns-ticket-status">
						<i class="dashicons dashicons-shield" aria-hidden="true"></i> <?php esc_html_e( 'Ticket Status', 'nanosupport' );
						echo ns_tooltip( 'ns-ticket-status-tooltip', esc_html__( 'Change the ticket status to track unsolved tickets separately.', 'nanosupport' ), 'left' );
						?>
					</label>
				</div>
				<div class="ns-body-col">
					<div class="ns-field">
						<select name="ns_ticket_status" class="ns-field-item" id="ns-ticket-status" aria-describedby="ns-ticket-status-tooltip" required>
							<option value="open" <?php selected( $_ns_ticket_status, 'open' ); ?>><?php esc_html_e( 'Open', 'nanosupport' ); ?></option>
							<option value="inspection"<?php selected( $_ns_ticket_status, 'inspection' ); ?>><?php esc_html_e( 'Under Inspection', 'nanosupport' ); ?></option>
							<option value="solved"<?php selected( $_ns_ticket_status, 'solved' ); ?>><?php esc_html_e( 'Solved', 'nanosupport' ); ?></option>
						</select>
					</div> <!-- /.ns-field -->
				</div>
			</div> <!-- /.ns-row -->

			<div class="ns-row misc-pub-section">
				<div class="ns-head-col">
					<label for="ns-ticket-priority">
						<i class="dashicons dashicons-sort" aria-hidden="true"></i> <?php esc_html_e( 'Priority', 'nanosupport' );
						echo ns_tooltip( 'ns-ticket-priority-tooltip', esc_html__( 'Change the priority as per the content and urgency of the ticket.', 'nanosupport' ), 'left' );
						?>
					</label>
				</div>
				<div class="ns-body-col">
					<div class="ns-field">
						<select name="ns_ticket_priority" class="ns-field-item" id="ns-ticket-priority" aria-describedby="ns-ticket-priority-tooltip" required>
							<option value="low" <?php selected( $_ns_ticket_priority, 'low' ); ?>><?php esc_html_e( 'Low', 'nanosupport' ); ?></option>
							<option value="medium" <?php selected( $_ns_ticket_priority, 'medium' ); ?>><?php esc_html_e( 'Medium', 'nanosupport' ); ?></option>
							<option value="high" <?php selected( $_ns_ticket_priority, 'high' ); ?>><?php esc_html_e( 'High', 'nanosupport' ); ?></option>
							<option value="critical" <?php selected( $_ns_ticket_priority, 'critical' ); ?>><?php esc_html_e( 'Critical', 'nanosupport' ); ?></option>
						</select>
					</div> <!-- /.ns-field -->
				</div>
			</div> <!-- /.ns-row -->

			<?php
            /**
             * Agent assignment is an administrative power.
             */
            if( ns_is_user( 'manager' ) ) : ?>

            	<div class="ns-row misc-pub-section">
            		<div class="ns-head-col">
            			<label for="ns-ticket-agent">
            				<i class="dashicons dashicons-businessman" aria-hidden="true"></i> <?php esc_html_e( 'Agent', 'nanosupport' );
            				echo ns_tooltip( 'ns-ticket-agent-tooltip', esc_html__( 'Choose agent to assign the ticket. You can make an agent by editing the user from their user profile.', 'nanosupport' ), 'left' );
            				?>
            			</label>
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
            				<select name="ns_ticket_agent" class="ns-field-item ns-auto-select-search" id="ns-ticket-agent" aria-describedby="ns-ticket-agent-tooltip">
            					<?php
            					if ( ! empty( $agent_query->results ) ) {
            						echo '<option value="">'. esc_html__( 'Assign an agent', 'nanosupport' ) .'</option>';
            						foreach ( $agent_query->results as $user ) {
            							echo '<option value="'. $user->ID .'" '. selected( $_ns_ticket_agent, $user->ID ) .'>'. $user->display_name .'</option>';
            						}
            					} else {
            						echo '<option value="">'. esc_html__( 'No agent found', 'nanosupport' ) .'</option>';
            					}
            					?>
            				</select>
            			</div> <!-- /.ns-field -->
            		</div>
            	</div> <!-- /.ns-row -->

            <?php endif; ?>

            <?php
            $NSECommerce = new NSECommerce();
            $products    = $NSECommerce->get_products();
            if( $NSECommerce->ecommerce_enabled() ) { ?>

            	<hr>

            	<?php if( !empty($_ns_ticket_product) ) {
            		$product_info = $NSECommerce->get_product_info($_ns_ticket_product, $_ns_ticket_receipt);
            		?>

            		<div id="ns-product-display-panel">
            			<h2>
            				<i class="dashicons dashicons-cart" aria-hidden="true"></i> <?php esc_html_e( 'Product', 'nanosupport' ); ?>
            				<?php /* translators: Button text to open product-specific fields on-demand */ ?>
            				<div id="ns-btn-edit-product" class="hide-if-no-js"><?php _ex( 'Edit', 'NanoSupport Product', 'nanosupport' ); ?></div>
            			</h2>
            			<div class="ns-row misc-pub-section">

            				<?php if( 'publish' !== $product_info->status ) { ?>

            					<p class="ns-text-muted ns-text-center">
            						&mdash; <?php esc_html_e( 'Product is not available', 'nanosupport' ); ?> &mdash;
            					</p>

            				<?php } else { ?>

            					<p>
            						<a href="<?php echo esc_url($product_info->link); ?>" target="_blank" rel="noopener">
            							<strong><?php echo $product_info->name ?></strong>
            						</a>
            					</p>

            					<?php
                                // If it's a valid receipt.
            					if( !empty($product_info->purchase_date) ) {

            						/* translators: Product purchase date */
            						printf( __('<strong>Purchased at:</strong> %s', 'nanosupport'), $product_info->purchase_date );
            						echo '<br>';

            						/* translators: User's first name and last name */
            						printf( __('<strong>Purchased by:</strong> %s', 'nanosupport'), $product_info->purchase_by );
            						echo '<br>';
            						?>

            						<a class="button button-small button-default" href="<?php echo esc_url($product_info->payment_url); ?>" target="_blank" rel="noopener">
            							<?php esc_html_e( 'Payment Details', 'nanosupport' ); ?>
            						</a>

            					<?php } //endif ?>

            				<?php } //endif('publish' !== $product_info->status) ?>

            			</div> <!-- /.ns-row -->
            		</div>
            		<!-- /#ns-product-display-panel -->

            	<?php } ?>

            	<div id="ns-product-edit-panel" <?php echo !empty($_ns_ticket_product) ? 'class="hide-if-js"' : ''; ?>>

            		<div class="ns-row misc-pub-section">
            			<div class="ns-head-col">
            				<label for="ns-ticket-product">
            					<i class="dashicons dashicons-cart" aria-hidden="true"></i> <?php esc_html_e( 'Product', 'nanosupport' );
            					echo ns_tooltip( 'ns-ticket-product-tooltip', esc_html__( 'Select the product the ticket is about.', 'nanosupport' ), 'left' );
            					?>
            				</label>
            			</div>
            			<div class="ns-body-col">
            				<div class="ns-field">
            					<select name="ns_ticket_product" class="ns-field-item" id="ns-ticket-product" aria-describedby="ns-ticket-product-tooltip">
            						<option value=""><?php esc_html_e( 'Select a Product', 'nanosupport' ); ?></option>
            						<?php foreach($products as $id => $product_name) { ?>
            							<option value="<?php echo $id; ?>" <?php selected( $_ns_ticket_product, $id ); ?>>
            								<?php echo esc_html($product_name); ?>
            							</option>
            						<?php } ?>
            					</select>
            				</div> <!-- /.ns-field -->
            			</div>
            		</div> <!-- /.ns-row -->

            		<div class="ns-row misc-pub-section">
            			<div class="ns-head-col">
            				<label for="ns-ticket-product-receipt">
            					<i class="dashicons dashicons-tag" aria-hidden="true"></i> <?php esc_html_e( 'Receipt Number', 'nanosupport' );
            					echo ns_tooltip( 'ns-ticket-product-receipt-tooltip', esc_html__( 'Enter the receipt number of purchasing the product.', 'nanosupport' ), 'left' );
            					?>
            				</label>
            			</div>
            			<div class="ns-body-col">
            				<div class="ns-field">
            					<input type="number" name="ns_ticket_product_receipt" class="ns-field-item" id="ns-ticket-product-receipt" aria-describedby="ns-ticket-product-receipt-tooltip" value="<?php echo $_ns_ticket_receipt; ?>" min="0">
            				</div> <!-- /.ns-field -->
            			</div>
            		</div> <!-- /.ns-row -->

            	</div>
            	<!-- /#ns-product-edit-panel -->


            <?php } ?>

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
		if ( ! current_user_can( 'edit_nanosupport', $post_id ) ) {
			return $post_id;
		}
	}

	global $current_user;


    /**
     * Save NanoSupport Ticket Meta.
     * ...
     */
    $ns_ticket_status      = $_POST['ns_ticket_status'];
    $ns_ticket_priority    = $_POST['ns_ticket_priority'];
    $ns_ticket_agent       = filter_input(INPUT_POST, 'ns_ticket_agent', FILTER_SANITIZE_NUMBER_INT);

    update_post_meta( $post_id, '_ns_ticket_status',   sanitize_text_field( $ns_ticket_status ) );
    update_post_meta( $post_id, '_ns_ticket_priority', sanitize_text_field( $ns_ticket_priority ) );
    if( ns_is_user('manager') ) {
    	$existing_agent = (int) get_post_meta( $post_id, '_ns_ticket_agent', true );

        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * nanosupport_notify_agent_assignment
         *
         * @since  1.0.0
         *
         * @param boolean  True to send email notification on ticket assignment.
         * -----------------------------------------------------------------------
         */
        if( apply_filters( 'nanosupport_notify_agent_assignment', true ) ) {

            // Notify the support agent that, they're assigned for the first time
            // if there's no agent assigned already, but we're going to add a new one, or
            // if we're changing agent from existing to someone new
        	if( (! empty($ns_ticket_agent) && empty($existing_agent)) || $existing_agent !== absint( $ns_ticket_agent ) ) {
        		ns_notify_agent_assignment( $ns_ticket_agent, $post_id );
        	}

        } //endif( apply_filters(...))

        // Add a ticket agent always, if assigned
        update_post_meta( $post_id, '_ns_ticket_agent', absint( $ns_ticket_agent ) );
    }

    $NSECommerce = new NSECommerce();
    if( $NSECommerce->ecommerce_enabled() ) {
    	$ns_ticket_product     = $_POST['ns_ticket_product'];
    	$ns_ticket_receipt     = $_POST['ns_ticket_product_receipt'];
    	update_post_meta( $post_id, '_ns_ticket_product',           sanitize_text_field( $ns_ticket_product ) );
    	update_post_meta( $post_id, '_ns_ticket_product_receipt',   sanitize_text_field( $ns_ticket_receipt ) );
    }


    /**
     * Save Response.
     * ...
     */
    $new_response = isset($_POST['ns_new_response']) && ! empty($_POST['ns_new_response']) ? $_POST['ns_new_response'] : false;

    if( $new_response ) :

        /**
         * Sanitize ticket response content
         * @var string
         */
        $new_response = wp_kses( $new_response, ns_allowed_html() );

        //Insert new response as a comment and get the comment ID
        $commentdata = array(
        	'comment_post_ID'       => absint( $post_id )   ,
        	'comment_author'        => wp_strip_all_tags( $current_user->display_name ),
        	'comment_author_email'  => sanitize_email( $current_user->user_email ),
        	'comment_author_url'    => esc_url( $current_user->user_url ),
        	'comment_content'       => $new_response,
        	'comment_type'          => 'nanosupport_response',
        	'comment_parent'        => 0,
        	'user_id'               => absint( $current_user->ID ),
        );

        $comment_id = wp_new_comment( $commentdata );

    endif;


    /**
     * Save Internal Notes.
     * ...
     */
    $internal_note          = $_POST['ns_internal_note'];
    $existing_internal_note = get_post_meta( $post_id, 'ns_internal_note', true );

    if( $internal_note && $internal_note != $existing_internal_note ) {
        // Sanitize internal note
    	$internal_note = wp_kses( $internal_note, ns_allowed_html() );

    	update_post_meta( $post_id, 'ns_internal_note', $internal_note );
    } elseif( '' == $internal_note && $existing_internal_note ) {
    	delete_post_meta( $post_id, 'ns_internal_note', $existing_internal_note );
    }
}

add_action( 'save_post',        'ns_save_nanosupport_meta_data' );
add_action( 'new_to_publish',   'ns_save_nanosupport_meta_data' );
