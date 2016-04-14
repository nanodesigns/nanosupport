<?php
/**
 * Shortcode: Support Desk
 *
 * Showing the common ticket center of all the support tickets to the respective privileges.
 * Show all the tickets at the front end using shortcode [nanosupport_desk]
 *
 * @author  	nanodesigns
 * @category 	Shortcode
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_support_desk_page() {

	/**
	 * Enqueue necessary styles and scripts accordingly
	 */
	wp_enqueue_style( 'nanosupport' );
	wp_enqueue_script('nanosupport');

	remove_query_arg( 'ns_success', $_SERVER['REQUEST_URI'] );
	
	ob_start();

	echo '<div id="nanosupport-desk">';
		if( is_user_logged_in() ) :
			//User is Logged in
			
			//Get the NanoSupport Settings from Database
	    	$ns_general_settings = get_option( 'nanosupport_settings' );

			global $post, $current_user; ?>

			<?php
			/**
			 * -----------------------------------------------------------------------
			 * HOOK : ACTION HOOK
			 * nanosupport_before_support_desk
			 *
			 * To Hook anything before the Support Desk.
			 *
			 * @since  1.0.0
			 *
			 * 10	- ns_support_desk_navigation()
			 * -----------------------------------------------------------------------
			 */
			do_action( 'nanosupport_before_support_desk' );
			?>
			
			<?php
			/*if( is_user_logged_in() ) {

				if( isset( $_GET['my-tickets'] ) ) {
					$u_id = (int) $_GET['my-tickets'];
					if( $u_id === $current_user->ID ) {
						//only my tickets
						$author_id		= $current_user->ID;
						$ticket_status 	= array( 'publish', 'private' );					
					} else {
						$ticket_status 	= 'publish';
						$author_id		= $u_id;				
					}
				} else {
					if( current_user_can('administrator') || current_user_can('editor') ) {
						//site admins
						$ticket_status 	= array( 'publish', 'private' );
						$author_id 		= '';
					} else {
						//general logged in users
						$ticket_status 	= 'publish';
						$author_id		= '';	
					}				
				}

			} else {
				//for visitors
				$ticket_status 		= 'publish';
				$author_id			= '';
			}*/
			if( current_user_can('administrator') || current_user_can('editor') ) {
				//Admin users
				$author_id 		= '';
				$ticket_status 	= array('publish', 'private', 'pending');
			} else {
				//General users
				$author_id		= $current_user->ID;
				$ticket_status 	= array('private', 'pending');
			}

			$posts_per_page = get_option( 'posts_per_page' );
			$paged 			= ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

			$support_ticket_query = new WP_Query( array(
					'post_type'			=> 'nanosupport',
					'post_status'		=> $ticket_status,
					'posts_per_page'	=> $posts_per_page,
					'author'			=> $author_id,
					'paged'				=> $paged
				) );

			if( $support_ticket_query->have_posts() ) :

				while( $support_ticket_query->have_posts() ) : $support_ticket_query->the_post();

					//Get ticket information
					$ticket_control = get_post_meta( get_the_ID(), 'ns_control', true );

					$ticket_status 	= $ticket_control['status'];
					$post_status	= get_post_status(get_the_ID());
					$status_class	= '';

					if( 'pending' === $post_status )
						$status_class = 'status-pending';
					else {
						if( $ticket_status && 'solved' === $ticket_status )
							$status_class = 'status-solved';
						elseif( $ticket_status && 'inspection' === $ticket_status )
							$status_class = 'status-inspection';
						elseif( $ticket_status && 'open' === $ticket_status )
							$status_class = 'status-open';
					}
					?>
					<div class="ticket-cards ns-cards <?php echo esc_attr($status_class); ?>">
						<div class="ns-row">
							<div class="ns-col-sm-4 ns-col-xs-12">
								<h3 class="ticket-head">
									<?php if( 'pending' === $post_status ) : ?>
										<?php the_title(); ?><span class="ns-small ticket-id"> &mdash; <?php printf( '#%s', get_the_ID() ); ?></span>
									<?php else : ?>
										<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
											<?php the_title(); ?><span class="ns-small ticket-id"> &mdash; <?php printf( '#%s', get_the_ID() ); ?></span>
										</a>
									<?php endif; ?>
								</h3>
								<div class="ticket-author">
									<?php
									$author = get_user_by( 'id', $post->post_author );
									echo '<span class="ns-icon-user"></span> '. $author->display_name;
									?>
								</div>
							</div>
							<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
								<div class="text-blocks">
									<strong><?php _e('Department:', 'nanosupport'); ?></strong><br>
									<?php echo ns_get_ticket_departments(); ?>
								</div>
								<div class="text-blocks">
									<strong><?php _e('Created & Updated:', 'nanosupport'); ?></strong><br>
									<?php echo date( 'd M Y h:i A', strtotime( $post->post_date ) ); ?><br>
									<?php echo date( 'd M Y h:i A', strtotime( $post->post_modified ) ); ?>
								</div>
							</div>
							<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
								<div class="text-blocks">
									<strong><?php _e('Responses:', 'nanosupport'); ?></strong><br>
									<?php
									$response_count = wp_count_comments( get_the_ID() );
									echo '<span class="responses-count">'. $response_count->approved .'</span>';
									?>
								</div>
								<div class="text-blocks">
									<strong><?php _e('Last Replied by:', 'nanosupport'); ?></strong><br>
									<?php
									$last_response = ns_get_last_response();
						            $last_responder = get_userdata( $last_response['user_id'] );
						            if ( $last_responder ) {
						                echo $last_responder->display_name, '<br>';
						                printf( __( '%s ago', 'nanosupport' ), human_time_diff( strtotime($last_response['comment_date']), current_time('timestamp') ) );
						            } else {
						                echo '-';
						            }
						            ?>
								</div>
							</div>
							<div class="ns-col-sm-2 ns-col-xs-4 ticket-meta">
								<div class="text-blocks">
									<strong><?php _e('Priority:', 'nanosupport'); ?></strong><br>
									<?php
									$ticket_priority = $ticket_control['priority'];
									if( 'low' === $ticket_priority ) {
										_e( 'Low', 'nanosupport' );
									} else if( 'medium' === $ticket_priority ) {
										echo '<span class="ns-text-info"><i class="ns-icon-info-circled"></i> ' , __( 'Medium', 'nanosupport' ) , '</span>';
									} else if( 'high' === $ticket_priority ) {
										echo '<strong class="ns-text-warning"><span class="ns-icon-info-circled"></span> ' , __( 'High', 'nanosupport' ) , '</strong>';
									} else if( 'critical' === $ticket_priority ) {
										echo '<strong class="ns-text-danger"><span class="ns-icon-info-circled"></span> ' , __( 'Critical', 'nanosupport' ) , '</strong>';
									}
									?>
								</div>
								<div class="text-blocks">
									<strong><?php _e('Ticket Status:', 'nanosupport'); ?></strong><br>
									<?php
									$status = '';
									if( 'pending' === $post_status )
										$status = '<span class="ns-label ns-label-normal">'. __( 'Pending', 'nanosupport' ) .'</span>';
									else {
										if( $ticket_status ) {
											if( 'solved' === $ticket_status ) {
												$status = '<span class="ns-label ns-label-success">'. __( 'Solved', 'nanosupport' ) .'</span>';
											} else if( 'inspection' === $ticket_status ) {
												$status = '<span class="ns-label ns-label-primary">'. __( 'Under Inspection', 'nanosupport' ) .'</span>';
											} else {
												$status = '<span class="ns-label ns-label-warning">'. __( 'Open', 'nanosupport' ) .'</span>';
											}
										}
									}

									echo $status;
									?>
								</div>
							</div>
						</div> <!-- /.ns-row -->
					</div> <!-- /.ticket-cards -->

				<?php
				endwhile;


				/**
				 * Pagination
				 * @see  includes/helper-functions.php
				 */
				ns_pagination( $support_ticket_query );

			else :
				echo '<div class="ns-alert ns-alert-info" role="alert">';
					_e( '<strong>Nice!</strong> You do not have any support ticket to display.', 'nanosupport' );
				echo '</div>';
			endif;
			wp_reset_postdata();

		else :
			//User is not logged in
			printf( __( 'Sorry, you cannot see your tickets without being logged in.<br><a class="ns-btn ns-btn-default ns-btn-sm" href="%1s" title="Site Login"><span class="ns-icon-lock"></span> Login</a> or <a class="ns-btn ns-btn-default ns-btn-sm" href="%2s" title="Site Registration"><span class="ns-icon-lock"></span> Create an account</a>', 'nanosupport' ), wp_login_url(), wp_registration_url() );
			
		endif; //if( is_user_logged_in() )

		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_after_support_desk
		 * 
		 * To Hook anything after the Support Desk.
		 *
		 * @since  1.0.0
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_after_support_desk' );
		
	echo '</div> <!-- /#nanosupport-desk -->';
	
	return ob_get_clean();
}
add_shortcode( 'nanosupport_desk', 'ns_support_desk_page' );