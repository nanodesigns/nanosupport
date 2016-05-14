<?php
/**
 * Single Ticket Content
 *
 * Content of Ticket Details page - the single template's content.
 *
 * This template can be overridden by copying it to:
 * your-theme/nanosupport/content-single-ticket.php
 *
 * Template Update Notice:
 * However on occasion NanoSupport may need to update template files, and
 * the theme developers will need to copy the new files to their theme to
 * maintain compatibility.
 *
 * Though we try to do this not very often, but it does happen. And the
 * version below will reflect any changes made to the template file. And
 * for any major changes the Upgrade Notice will inform you pointing this.
 *
 * @author      nanodesigns
 * @category    Content
 * @package     NanoSupport/Templates/
 * @version     1.0.0
 */
?>
<div>

	<?php
	//Get the NanoSupport Settings from Database
	$ns_general_settings = get_option( 'nanosupport_settings' );
	
	global $post;
	$author 			= get_user_by( 'id', $post->post_author );
	$support_desk 		= $ns_general_settings['support_desk'];
	$highlight_choice	= isset($ns_general_settings['highlight_ticket']) ? $ns_general_settings['highlight_ticket'] : 'status';
	$ticket_meta 		= ns_get_ticket_meta( get_the_ID() );

	$highlight_class = 'priority' === $highlight_choice ? $ticket_meta['priority']['class'] : $ticket_meta['status']['class'];
	?>

	<article id="post-<?php echo $post->ID; ?>" <?php post_class('ns-single'); ?>>

		<div class="ticket-question-card ns-cards <?php echo esc_attr($highlight_class); ?>">
			<div class="ns-row">
				<div class="ns-col-sm-11">
					<h1 class="ticket-head"><?php the_title(); ?></h1>
					<p class="ticket-author"><span class="ns-icon-user"></span> <?php echo $author->display_name; ?></p>

					<div class="ns-row ticket-meta">
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Created:', 'nanosupport' ); ?></strong><br>
								<span class="ns-small"><?php echo date( 'd M Y h:iA', strtotime( $post->post_date ) ); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Updated:', 'nanosupport' ); ?></strong><br>
								<span class="ns-small"><?php echo date( 'd M Y h:iA', strtotime( $post->post_modified ) ); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Department:', 'nanosupport' ); ?></strong><br>
								<span class="ns-small"><?php echo ns_get_ticket_departments(); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Assigned to:', 'nanosupport' ); ?></strong><br>
								<?php
								if( ! empty($ticket_meta['agent']) ) {
									echo '<span class="ns-small">'. $ticket_meta['agent']['name'] .'</span>';
								} else {
									echo '<span class="ns-small">---</span>';
								}
								?>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Status:', 'nanosupport' ); ?></strong><br>
								<?php echo $ticket_meta['status']['label']; ?>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php _e( 'Priority:', 'nanosupport' ); ?></strong><br>
								<span class="ns-small"><?php echo $ticket_meta['priority']['label']; ?></span>
							</p>
						</div>
					</div> <!-- /.ns-row -->
				</div>
				<div class="ns-col-sm-1 ns-right-portion">
					<a class="ns-btn ns-btn-danger ns-btn-xs ns-round-btn off-ticket-btn" href="<?php echo esc_url(get_permalink( $support_desk )); ?>" title="<?php esc_attr_e('Close the ticket', 'nanosupport'); ?>">
						<span class="ns-icon-remove"></span>
					</a>
					<a class="ns-btn ns-btn-default ns-btn-xs ns-round-btn ticket-link-btn" href="<?php echo esc_url(get_the_permalink()); ?>" title="<?php esc_attr_e('Permanent link to the Ticket', 'nanosupport'); ?>">
						<span class="ns-icon-link"></span>
					</a>
					<?php edit_post_link( '<span class="ns-icon-edit" title="'. esc_attr__('Edit the Ticket', 'nanosupport') .'"></span>', '', '', get_the_ID() ); ?>
				</div>
			</div> <!-- /.ns-row -->
			<div class="ticket-question">
				<?php the_content(); ?>
			</div>
		</div> <!-- /.ticket-question-card -->


		<!-- -------------------- RESPONSES ---------------------- -->


		<div class="ticket-responses">
			<?php
			/**
			 * Responses
			 * Load all the responses that are denoted to the ticket.
			 */
			$args = array(
		        'post_id'   	=> get_the_ID(),
		        'post_type' 	=> 'nanosupport',
		        'status'    	=> 'approve',
		        'orderby'   	=> 'comment_date',
		        'order'     	=> 'ASC'
		    );
		    $response_array = get_comments( $args );

			if( $response_array ) {

				echo '<div class="ticket-separator ticket-separator-center ns-text-uppercase">'. __('Responses', 'nanosupport') .'</div>';

				$counter = 1;

		        foreach( $response_array as $response ) { ?>
					
					<div class="ticket-response-cards ns-cards">
						<div class="ns-row">
							<div class="ns-col-sm-9">
								<div class="response-head">
									<h3 class="ticket-head" id="response-<?php echo esc_attr($counter); ?>">
										<?php echo $response->comment_author; ?>
									</h3>
								</div> <!-- /.response-head -->
							</div>
							<div class="ns-col-sm-3 response-dates">
								<a href="#response-<?php echo esc_attr($counter); ?>" class="response-bookmark ns-small"><strong class="ns-hash">#</strong> <?php echo date( 'd M Y h:iA', strtotime( $response->comment_date ) ); ?></a>
							</div>
						</div> <!-- /.ns-row -->
						<div class="ticket-response">
							<?php echo wpautop( $response->comment_content ); ?>
						</div>
						
					</div> <!-- /.ticket-response-cards -->

					<?php
		        $counter++;
		        } //endforeach ?>
		    <?php } //endif ?>

		    <?php
		    global $current_user;
		    $hide_form = false;

		    if( is_user_logged_in() ) {

			    /**
			     * Tickets are only available to edit to ticket author
			     * and the administrator.
			     */
				if( current_user_can( 'administrator' ) || $post->post_author == $current_user->ID ) {

				    if( 'solved' === $ticket_meta['status']['value'] && ! isset( $_GET['reopen'] ) ) {

				    	echo '<div class="ns-alert ns-alert-success" role="alert">';
				    		/**
				    		 * Reopen the Ticket
				    		 */
				    		$ropen_url = add_query_arg( 'reopen', '', get_the_permalink() );
				    		printf( __( 'This ticket is already solved. <a class="ns-btn ns-btn-sm ns-btn-warning" href="%s#write-message"><span class="ns-icon-repeat"></span> Reopen Ticket</a>', 'nanosupport' ), esc_url( $ropen_url ) );
						echo '</div>';

				    } else {

						if( isset( $_POST['send'] ) && isset( $_POST['nanosupport_response_nonce'] ) && wp_verify_nonce( $_POST['nanosupport_response_nonce'], 'response_nonce' ) ) {

							$error = new WP_Error();

							$response_msg = $_POST['ns_response_msg'];

							if( empty( $response_msg ) ) {
								$error->add( 'message_empty', __("Response field can't be empty.", 'nanosupport') );
							}
							if ( strlen( $response_msg ) < 30 ) {
								$error->add( 'message_short', __("Your message is too short. Write down at least 30 characters.", 'nanosupport') );
							}

							if( is_wp_error( $error ) && ! empty( $error->errors ) ) {

								echo '<div class="ns-alert ns-alert-danger" role="alert">';
									echo $error->get_error_message();
								echo '</div>';

							} else {

								$commentdata = array(
									'comment_post_ID'		=> absint( get_the_ID() )	,
									'comment_author'		=> wp_strip_all_tags( $current_user->display_name ), 
									'comment_author_email' 	=> sanitize_email( $current_user->user_email ),
									'comment_author_url'	=> esc_url( $current_user->user_url ),
									'comment_content'		=> htmlentities( $response_msg ),
									'comment_type'			=> 'nanosupport_response',
									'comment_parent'		=> 0,
									'user_id'				=> absint( $current_user->ID ),
									'comment_approved'		=> '1'
								);

								//Insert new response as a comment and get the comment ID
								$comment_id = wp_insert_comment( $commentdata );


								/**
								 * If ticket to ReOpen,
								 * make the ticket status to 'Open'
								 */
						    	if( 'solved' === $ticket_meta['status']['value'] && isset( $_GET['reopen'] ) ) {

								    $ns_ticket_status      = 'open'; //force open again
								    $ns_ticket_priority    = $ticket_meta['priority']['value'];
								    $ns_ticket_agent       = isset($ticket_meta['agent']['ID']) ? $ticket_meta['agent']['ID'] : '';

								    $ns_control = array(
								            'status'    => wp_strip_all_tags( $ns_ticket_status ),
								            'priority'  => sanitize_text_field( $ns_ticket_priority ),
								            'agent'     => absint( $ns_ticket_agent )
								        );

							    	update_post_meta( get_the_ID(), 'ns_control', $ns_control );
							    }
					    	
								if( ! is_wp_error( $comment_id ) ) {
									echo '<div class="ns-alert ns-alert-success" role="alert">';
										echo __( 'Your response is successfully submitted to this ticket.', 'nanosupport' ) . ' <a class="ns-btn ns-btn-sm ns-btn-info" href="'. get_the_permalink() .'"><span class="ns-icon-refresh"></span> Reload</a>';
									echo '</div>';
									$hide_form = true;
								} else {
									echo '<div class="ns-alert ns-alert-danger" role="alert">';
										echo $comment_id->get_error_message();
									echo '</div>';
								}
								
							}


						} //endif ( isset( $_POST['send'] )
						?>

						<?php if( ! $hide_form ) { ?>
					    	<form method="post" enctype="multipart/form-data">

								<div class="ns-cards ns-feedback">
									<div class="ns-row">
										<div class="ns-col-sm-9">
											<div class="response-head">
												<h3 class="ticket-head" id="new-response">
													<?php printf( __('Responding as: %s','nanosupport'), $current_user->display_name ); ?>
												</h3>
											</div> <!-- /.response-head -->
										</div>
										<div class="ns-col-sm-3 response-dates ns-small">
											<?php echo date( 'd M Y h:iA', current_time('timestamp') ); ?>
										</div>
									</div> <!-- /.ns-row -->
									<div class="ns-feedback-form">
										<div class="ns-form-group">
											<textarea name="ns_response_msg" id="write-message" class="ns-form-control" placeholder="<?php _e('Write down your response (at least 30 characters)', 'nanosupport'); ?>" rows="6" aria-label="<?php esc_attr_e('Write down the response to the ticket', 'nanosupport'); ?>"><?php echo isset($_POST['ns_response_msg']) ? stripslashes_deep( $_POST['ns_response_msg'] ) : ''; ?></textarea>
										</div> <!-- /.ns-form-group -->
										<?php wp_nonce_field( 'response_nonce', 'nanosupport_response_nonce' ); ?>
										<?php
										if( 'solved' === $ticket_meta['status']['value'] && isset( $_GET['reopen'] ) ) {
											echo '<div class="ns-alert ns-alert-warning" role="alert">';
												_e( '<strong>Just to inform:</strong> you are about to Reopen the ticket.', 'nanosupport' );
											echo '</div>';
										}
										?>
										<button type="submit" name="send" class="ns-btn ns-btn-primary"><?php _e( 'Submit', 'nanosupport' ); ?></button>
									</div>
								</div> <!-- /.ns-feedback-form -->

							</form>
						<?php } //endif( ! $hide_form ) ?>

					<?php } //endif( 'solved' === $ticket_meta['status']['value'] && ! isset( $_GET['reopen'] ) ) ?>

				<?php } //endif( current_user_can( 'administrator' ) ?>

			<?php } else { ?>

				<div class="ns-alert ns-alert-info ns-text-center" role="alert">
					<?php
					if( 'solved' === $ticket_meta['status']['value'] ) {
						_e( '<strong>Resolved!</strong> New Responses to this ticket is already closed. Only ticket author can reopen a closed ticket.', 'nanosupport' );
					} else {
						_e( '<strong>Sorry!</strong> Tickets are open for responses only to the Ticket Author.', 'nanosupport' );
					}
					?>
				</div>

			<?php } //endif( is_user_logged_in() ) ?>

		</div> <!-- /.ticket-responses -->

	</article> <!-- /#post-<?php the_ID(); ?> -->

	<a class="ns-btn ns-btn-sm ns-btn-default" href="<?php echo esc_url(get_permalink( $support_desk )); ?>"><span class="ns-icon-chevron-left"></span> <?php _e( 'Back to ticket index', 'nanosupport' ); ?></a>

</div>
