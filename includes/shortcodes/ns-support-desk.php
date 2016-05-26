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
	
	ob_start();

	echo '<div id="nanosupport-desk">';
		if( is_user_logged_in() ) :
			//User is Logged in

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
			if( current_user_can('edit_nanosupports') ) {
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

				//Get the NanoSupport Settings from Database
				$ns_general_settings = get_option( 'nanosupport_settings' );
				$highlight_choice	 = isset($ns_general_settings['highlight_ticket']) ? $ns_general_settings['highlight_ticket'] : 'status';

				while( $support_ticket_query->have_posts() ) : $support_ticket_query->the_post();

					//Get ticket information
					$ticket_meta 	 = ns_get_ticket_meta( get_the_ID() );
					$highlight_class = 'priority' === $highlight_choice ? $ticket_meta['priority']['class'] : $ticket_meta['status']['class'];
					?>

					<div class="ticket-cards ns-cards <?php echo esc_attr($highlight_class); ?>">
						<div class="ns-row">
							<div class="ns-col-sm-4 ns-col-xs-12">
								<h3 class="ticket-head">
									<?php if( 'pending' === $ticket_meta['status']['value'] ) : ?>
										<?php if( current_user_can('edit_nanosupports') ) : ?>
											<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
												<?php the_title(); ?><?php edit_post_link( '<span class="ns-icon-edit" title="'. esc_attr__('Edit the Ticket', 'nanosupport') .'"></span>', '', '', get_the_ID() ); ?>
											</a>
										<?php else : ?>
											<?php the_title(); ?><?php edit_post_link( '<span class="ns-icon-edit" title="'. esc_attr__('Edit the Ticket', 'nanosupport') .'"></span>', '', '', get_the_ID() ); ?>
										<?php endif; ?>
									<?php else : ?>
										<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
											<?php the_title(); ?><?php edit_post_link( '<span class="ns-icon-edit" title="'. esc_attr__('Edit the Ticket', 'nanosupport') .'"></span>', '', '', get_the_ID() ); ?>
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
							<div class="ns-col-sm-2 ns-col-xs-4 ticket-meta">
								<div class="text-blocks ns-question-50">
									<strong><?php _e( 'Priority:', 'nanosupport' ); ?></strong><br>
									<?php echo $ticket_meta['priority']['label']; ?>
								</div>
								<div class="text-blocks ns-question-50">
									<strong><?php _e( 'Ticket Status:', 'nanosupport' ); ?></strong><br>
									<?php echo $ticket_meta['status']['label']; ?>
								</div>
							</div>
							<div class="toggle-ticket-additional">
								<span class="ns-toggle-icon ns-icon-chevron-circle-down" title="<?php esc_attr_e( 'Load more', 'nanosupport' ); ?>"></span>
							</div>
							<div class="ticket-additional">
								<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
									<div class="text-blocks">
										<strong><?php _e( 'Department:', 'nanosupport' ); ?></strong><br>
										<?php echo ns_get_ticket_departments(); ?>
									</div>
									<div class="text-blocks">
										<strong><?php _e( 'Created &amp; Updated:', 'nanosupport' ); ?></strong><br>
										<?php echo date( 'd M Y h:i A', strtotime( $post->post_date ) ); ?><br>
										<?php echo date( 'd M Y h:i A', strtotime( $post->post_modified ) ); ?>
									</div>
								</div>
								<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
									<div class="text-blocks">
										<strong><?php _e( 'Responses:', 'nanosupport' ); ?></strong><br>
										<?php
										$response_count = wp_count_comments( get_the_ID() );
										echo '<span class="responses-count">'. $response_count->approved .'</span>';
										?>
									</div>
									<div class="text-blocks">
										<strong><?php _e( 'Last Replied by:', 'nanosupport' ); ?></strong><br>
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
							</div> <!-- /.ticket-additional -->
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
