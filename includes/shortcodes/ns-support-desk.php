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

	echo '<div id="nanosupport-desk" class="ns-no-js">';
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
			if( ns_is_user('manager') ) {
				//Admin users
				$author_id 		= '';
				$ticket_status 	= array('publish', 'private', 'pending');
			} elseif( ns_is_user('agent') ) {
				//Agent
				$author_id 		= $current_user->ID;
				$ticket_status	= array('publish', 'private', 'pending');
			} else {
				//General users
				$author_id		= $current_user->ID;
				$ticket_status 	= array('private', 'pending');
			}

			$posts_per_page = get_option( 'posts_per_page' );
			$paged 			= ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

			if( ns_is_user('agent') ) {
				$meta_query = array(
					array(
						'key'     => '_ns_ticket_agent',
						'value'   => $current_user->ID,
						'compare' => '=',
					)
				);
			} else {
				$meta_query = array('');
			}

			$args = array(
				'post_type'			=> 'nanosupport',
				'post_status'		=> $ticket_status,
				'posts_per_page'	=> $posts_per_page,
				'author'			=> $author_id,
				'paged'				=> $paged,
				'meta_query'		=> $meta_query
			);

			add_filter( 'posts_clauses', 'ns_change_query_to_include_agents_tickets', 10, 2 );

			/**
			 * -----------------------------------------------------------------------
			 * HOOK : FILTER HOOK
			 * ns_filter_support_desk_query
			 *
			 * To alter the Support Desk query arguments.
			 *
			 * @since  1.0.0
			 * -----------------------------------------------------------------------
			 */
			$support_ticket_query = new WP_Query( apply_filters( 'ns_filter_support_desk_query', $args ) );

			remove_filter( 'posts_clauses', 'ns_change_query_to_include_agents_tickets', 10 );

			if( $support_ticket_query->have_posts() ) :

				//Get the NanoSupport Settings from Database
				$ns_general_settings = get_option( 'nanosupport_settings' );
				$highlight_choice    = isset($ns_general_settings['highlight_ticket']) ? $ns_general_settings['highlight_ticket'] : 'status';

				while( $support_ticket_query->have_posts() ) : $support_ticket_query->the_post();

					//Get ticket information
					$ticket_meta 	 = ns_get_ticket_meta( get_the_ID() );
					$highlight_class = 'priority' === $highlight_choice ? $ticket_meta['priority']['class'] : $ticket_meta['status']['class'];

					$NSECommerce = new NSECommerce();
					$product_icon = '';
					if( $NSECommerce->ecommerce_enabled() ) {
						$product_info = $NSECommerce->get_product_info($ticket_meta['product'], $ticket_meta['receipt']);
						$product_icon = false !== $product_info ? '&nbsp;<i class="ns-icon-cart ns-small" aria-hidden="true"></i>' : '';
					}
					?>

					<div class="ticket-cards ns-cards <?php echo esc_attr($highlight_class); ?>">
						<div class="ns-row">
							<div class="ns-col-sm-4 ns-col-xs-12">
								<h3 class="ticket-head">
									<?php if( 'pending' === $ticket_meta['status']['value'] ) : ?>
										<?php if( ns_is_user('agent_and_manager') ) : ?>
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); echo $product_icon; ?>
											</a>
											<?php else : ?>
												<?php the_title(); echo $product_icon; ?>
											<?php endif; ?>
										<?php else : ?>
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); echo $product_icon; ?>
											</a>
										<?php endif; ?>

										<?php if( ns_is_user('agent_and_manager') ) : ?>
											<span class="ticket-tools">
												<?php edit_post_link( 'Edit', '', '', get_the_ID() ); ?>
												<a class="ticket-view-link" href="<?php echo esc_url(get_the_permalink()); ?>" title="<?php esc_attr_e( 'Permanent link to the Ticket', 'nanosupport' ); ?>">
													<?php esc_html_e( 'View', 'nanosupport' ); ?>
												</a>
											</span> <!-- /.ticket-tools -->
									<?php endif; ?>
								</h3>
								<div class="ticket-author">
									<?php
									$author = get_user_by( 'id', $post->post_author );
									echo '<i class="ns-icon-user" aria-hidden="true"></i> '. $author->display_name;
									?>
								</div>
							</div>
							<div class="ns-col-sm-2 ns-col-xs-4 ticket-meta">
								<div class="text-blocks ns-question-50">
									<strong><?php esc_html_e( 'Priority:', 'nanosupport' ); ?></strong>
									<div class="ns-small">
										<?php echo $ticket_meta['priority']['label']; ?>
									</div>
								</div>
								<div class="text-blocks ns-question-50">
									<strong><?php esc_html_e( 'Ticket Status:', 'nanosupport' ); ?></strong><br>
									<?php echo $ticket_meta['status']['label']; ?>
								</div>
							</div>
							<div class="toggle-ticket-additional">
								<i class="ns-toggle-icon ns-icon-chevron-circle-down" aria-label="<?php esc_attr_e( 'Load more', 'nanosupport' ); ?>"></i>
							</div>
							<div class="ticket-additional ns-hide-mobile">
								<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
									<div class="text-blocks">
										<strong><?php esc_html_e( 'Department:', 'nanosupport' ); ?></strong>
										<div class="ns-small">
											<?php echo ns_get_ticket_departments(); ?>
										</div>
									</div>
									<div class="text-blocks">
										<strong><?php esc_html_e( 'Created &amp; Updated:', 'nanosupport' ); ?></strong>
										<div class="ns-small">
											<?php echo ns_date_time( $post->post_date ); ?><br>
											<?php echo ns_date_time( ns_get_ticket_modified_date($post->ID) ); ?>
										</div>
									</div>
								</div>
								<div class="ns-col-sm-3 ns-col-xs-4 ticket-meta">
									<div class="text-blocks">
										<strong><?php esc_html_e( 'Responses:', 'nanosupport' ); ?></strong><br>
										<?php
										$response_count = wp_count_comments( get_the_ID() );
										echo '<span class="responses-count">'. $response_count->approved .'</span>';
										?>
									</div>
									<div class="text-blocks">
										<strong><?php esc_html_e( 'Last Replied by:', 'nanosupport' ); ?></strong>
										<?php
										$last_response  = ns_get_last_response();
										$last_responder = get_userdata( $last_response['user_id'] );
										echo '<div class="ns-small">';
											if ( $last_responder ) {
												echo $last_responder->display_name, '<br>';
												/* translators: time difference from current time. eg. 12 minutes ago */
												printf( esc_html__( '%s ago', 'nanosupport' ), human_time_diff( strtotime($last_response['comment_date']), current_time('timestamp') ) );
											} else {
												echo '&mdash;';
											}
										echo '</div>';
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
					esc_html_e( 'Nice! You do not have any support ticket to display.', 'nanosupport' );
				echo '</div>';
			endif;
			wp_reset_postdata();

		else :
			//User is not logged in
			esc_html_e( 'Sorry, you cannot see your tickets without being logged in.', 'nanosupport' );
			echo '<br>';
			echo '<a class="ns-btn ns-btn-default ns-btn-sm" href="'. wp_login_url() .'"><i class="ns-icon-lock" aria-hidden="true"></i>&nbsp;';
				esc_html_e( 'Login', 'nanosupport' );
			echo '</a>&nbsp;';
			/* translators: context: login 'or' register */
			esc_html_e( 'or', 'nanosupport' );
			echo '&nbsp;<a class="ns-btn ns-btn-default ns-btn-sm" href="'. wp_registration_url() .'"><i class="ns-icon-lock" aria-hidden="true"></i>&nbsp;';
				esc_html_e( 'Create an account', 'nanosupport' );
			echo '</a>';

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
