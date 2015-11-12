<div class="row">
	<div class="col-md-12">

		<?php
		global $post;
		$author = get_user_by( 'id', $post->post_author );
		$ticket_control = get_post_meta( get_the_ID(), 'ns_control', true );
		$ticket_status = $ticket_control['status'];
		$ticket_list_page = get_page_by_path('support-desk');
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class('ns-single'); ?>>
			
			<div class="panel panel-default panel-<?php echo esc_attr($ticket_status); ?>">
				<div class="panel-heading">
					<header class="ns-header">
						<h1 class="ticket-head">
							<a href="<?php echo esc_url(get_the_permalink()); ?>">
								<span class="ns-icon-link"></span>
							</a>
							&nbsp;
							<?php the_title(); ?>

							<a class="off-ticket pull-right" href="<?php echo esc_url(get_permalink( $ticket_list_page->ID )); ?>"><span class="ns-icon-remove"></span></a>
							<?php edit_post_link( '<span class="edit-ticket ns-icon-edit"></span>', '<div class="pull-right">', '</div>', get_the_ID() ); ?>
							<div class="ns-clearfix"></div>
						</h1>
					</header>
				</div>
				<div class="panel-body">
					<div class="ns-ticket-status-bar">
						<div class="row">
							<div class="col-sm-3">
								<strong><?php _e( 'Created', 'nanosupport' ); ?></strong><br>
								<?php echo date( 'd F Y h:iA', strtotime( $post->post_date ) ); ?>
							</div>
							<div class="col-sm-3">
								<strong><?php _e( 'Updated', 'nanosupport' ); ?></strong><br>
								<?php echo date( 'd F Y h:iA', strtotime( $post->post_modified ) ); ?>
							</div>
							<div class="col-sm-2">
								<strong><?php _e( 'Department', 'nanosupport' ); ?></strong><br>
								<?php
								$departments = get_the_terms( get_the_ID(), 'nanosupport_departments' );
								if ( $departments && ! is_wp_error( $departments ) ) :
									foreach ( $departments as $department ) {
										echo $department->name;
									}
								endif;
								?>
							</div>
							<div class="col-sm-2">
								<strong><?php _e( 'Status', 'nanosupport' ); ?></strong><br>
								<?php
								if( $ticket_status ) {
									if( $ticket_status == 'solved' ) {
										$status = '<span class="label label-success">'. __( 'Solved', 'nanosupport' ) .'</span>';
									} else if( $ticket_status == 'inspection' ) {
										$status = '<span class="label label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>';
									} else {
										$status = '<span class="label label-warning">'. __( 'Open', 'nanosupport' ) .'</span>';
									}
								} else {
									$status = '';
								}
								?>
								<?php echo $status; ?>
							</div>
							<div class="col-sm-2">
								<strong><?php _e( 'Priority', 'nanosupport' ); ?></strong><br>
								<?php
								$ticket_priority = $ticket_control['priority'];
								if( $ticket_priority === 'low' ) {
									_e( 'Low', 'nanosupport' );
								} else if( $ticket_priority === 'medium' ) {
									echo '<span class="text-info">'. __( 'Medium', 'nanosupport' ) .'</span>';
								} else if( $ticket_priority === 'high' ) {
									echo '<span class="text-warning">'. __( 'High', 'nanosupport' ) .'</span>';
								} else if( $ticket_priority === 'critical' ) {
									echo '<span class="text-danger">'. __( 'Critical', 'nanosupport' ) .'</span>';
								}
								?>
							</div>
						</div>
					</div> <!-- /.ns-ticket-status-bar -->
					<hr>
					<div class="panel panel-default panel-question panel-sm">
						<div class="panel-heading">
							<div class="row">
								<div class="col-sm-8">
									<h3 class="ticket-head">
										<?php echo $author->display_name; ?>
									</h3>
								</div>
								<div class="col-sm-4 ticket-date-right">
									<small><?php echo date( 'd F Y h:iA', strtotime( $post->post_date ) ); ?></small>
								</div>
							</div>								
						</div>
						<div class="panel-body">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="ticket-content">
				<?php
				/**
				 * Responses.
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

					echo '<div class="ticket-separator"><span>'. __('Responses', 'nanosupport') .'</span></div>';

					$counter = 1;

			        foreach( $response_array as $response ) { ?>
						<div class="panel panel-default panel-response panel-sm">
							<div class="panel-heading">
								<div class="row">
									<div class="col-sm-8">
										<h3 class="ticket-head" id="response-<?php echo $counter; ?>">
											<?php echo $response->comment_author; ?>
											<?php echo '<a class="response-bookmark" href="#response-'. $counter .'"><small class="glyphicon glyphicon-link"></small></a>'; ?>
										</h3>
									</div>
									<div class="col-sm-4 ticket-date-right">
										<small><?php echo date( 'd F Y h:iA', strtotime( $response->comment_date ) ); ?></small>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<?php echo html_entity_decode( $response->comment_content ); ?>
							</div>
						</div>
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

					    if( 'solved' == $ticket_status && ! isset( $_GET['reopen'] ) ) {

					    	echo '<div class="alert alert-success" role="alert">';
					    		/**
					    		 * Reopen the Ticket
					    		 */
					    		$ropen_url = add_query_arg( 'reopen', '', get_the_permalink() );
								echo __( 'This ticket is already solved.', 'nanosupport' ) . ' <a class="btn btn-sm btn-warning" href="'. esc_url( $ropen_url ) .'#write-message"><span class="ns-icon-reopen"></span> Reopen Ticket</a>';
							echo '</div>';

					    } else {

							if( isset( $_POST['send'] ) && isset( $_POST['nanosupport_response_nonce'] ) && wp_verify_nonce( $_POST['nanosupport_response_nonce'], 'response_nonce' ) ) {

								$error = new WP_Error();

								$response_msg = $_POST['ns_response_msg'];

								if( empty( $response_msg ) ) {
									$error->add( 'message_empty', __("Response field can't be empty.") );
								}
								if ( strlen( $response_msg ) < 30 ) {
									$error->add( 'message_short', __("Your message is too short. Write down at least 30 characters.") );
								}

								if( is_wp_error( $error ) && ! empty( $error->errors ) ) {
									echo '<div class="alert alert-danger" role="alert">';
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
							    	if( 'solved' == $ticket_status && isset( $_GET['reopen'] ) ) {
										$ns_control_array = get_post_meta( get_the_ID(), 'ns_control', true );

									    $ns_ticket_status      = 'open'; //force open again
									    $ns_ticket_priority    = $ns_control_array['priority'];
									    $ns_ticket_agent       = $ns_control_array['agent'] ? $ns_control_array['agent'] : '';

									    $ns_control = array(
									            'status'    => sanitize_text_field( $ns_ticket_status ),
									            'priority'  => sanitize_text_field( $ns_ticket_priority ),
									            'agent'     => absint( $ns_ticket_agent )
									        );

								    	update_post_meta( get_the_ID(), 'ns_control', $ns_control );
								    }
						    	
									if( ! is_wp_error( $comment_id ) ) {
										echo '<div class="alert alert-success" role="alert">';
											echo __( 'Your response is successfully submitted to this ticket.', 'nanosupport' ) . ' <a class="btn btn-sm btn-info" href="'. get_the_permalink() .'"><span class="glyphicon glyphicon-refresh"></span> Reload</a>';
										echo '</div>';
										$hide_form = true;
									} else {
										echo '<div class="alert alert-danger" role="alert">';
											echo $comment_id->get_error_message();
										echo '</div>';
									}
								}


							} //endif ( isset( $_POST['send'] )
							?>

							<?php if( ! $hide_form ) { ?>
						    	<form method="post" enctype="multipart/form-data">
								    <div class="panel panel-default panel-sm">
										<div class="panel-heading">
											<div class="row">
												<div class="col-sm-8">
													<h3 class="ticket-head">
														<?php echo $current_user->display_name; ?>
													</h3>
												</div>
												<div class="col-sm-4 ticket-date-right">
													<small><?php echo date( 'd F Y h:iA', current_time( 'timestamp' ) ); ?></small>
												</div>
											</div>
										</div>
										<div class="panel-body">
											<div class="form-group">
												<textarea name="ns_response_msg" id="write-message" class="form-control" placeholder="<?php _e('Write down your response (at least 30 characters)', 'nanosupport'); ?>" rows="6"><?php echo isset($_POST['ns_response_msg']) ? html_entity_decode( $_POST['ns_response_msg'] ) : ''; ?></textarea>
											</div>
											<?php wp_nonce_field( 'response_nonce', 'nanosupport_response_nonce' ); ?>
											<?php
											if( 'solved' == $ticket_status && isset( $_GET['reopen'] ) ) {
												echo '<div class="alert alert-warning" role="alert">';
													_e( '<strong>Just to inform:</strong> you are about to Reopen the ticket.', 'nanosupport' );
												echo '</div>';
											}
											?>
											<button type="submit" name="send" class="btn btn-primary"><?php _e( 'Submit', 'nanosupport' ); ?></button>
										</div>
									</div>
								</form>
							<?php } //endif( ! $hide_form ) ?>

						<?php } //endif( $ticket_status == 'solved' && ! isset( $_GET['reopen'] ) ) ?>

					<?php } //endif( current_user_can( 'administrator' ) ?>

				<?php } else { ?>

					<div class="alert alert-info text-center" role="alert">
						<?php
						if( 'solved' == $ticket_status ) {
							_e( '<strong>Resolved!</strong> New Responses to this ticket is already closed. Only ticket author can reopen a closed ticket.', 'nanosupport' );
						} else {
							_e( '<strong>Sorry!</strong> Tickets are open for responses only to the Ticket Author.', 'nanosupport' );
						}
						?>
					</div>

				<?php } //endif( is_user_logged_in() ) ?>

			</div> <!-- /.entry-content -->

		</article> <!-- /#post-<?php the_ID(); ?> -->

		<a class="btn btn-sm btn-default" href="<?php echo esc_url(get_permalink( $ticket_list_page->ID )); ?>"><span class="ns-icon-chevron-left"></span> <?php _e( 'Back to ticket index', 'nanosupport' ); ?></a>			

	<?php //wp_reset_postdata(); ?>
	
	</div> <!-- .col-md-12 -->
</div> <!-- /.row -->