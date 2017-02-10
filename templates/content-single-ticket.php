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

	<article id="ticket-<?php echo $post->ID; ?>" <?php post_class('ns-single'); ?>>

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
								<span class="ns-small"><?php echo date( 'd M Y h:iA', strtotime( ns_get_ticket_modified_date($post->ID) ) ); ?></span>
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


		<!-- +++++++++++++++++++ RESPONSES +++++++++++++++++++ -->


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

		    $found_count = count($response_array);

			if( $response_array ) {

				echo '<div class="ticket-separator ticket-separator-center ns-text-uppercase">'. __('Responses', 'nanosupport') .'</div>';

				$counter = 1;


		        foreach( $response_array as $response ) {
					
				//highlight the latest response on successful submission of new response
	        	$fresh_response = (isset($_GET['ns_success']) || isset($_GET['ns_cm_success']) ) && $found_count == $counter ? 'new-response' : '';
	        	?>

					<div class="ticket-response-cards ns-cards <?php echo esc_attr($fresh_response); ?>">
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


		    <!-- ++++++++++++ NEW RESPONSE FORM ++++++++++++ -->

		    <?php get_nanosupport_response_form(); ?>

		</div> <!-- /.ticket-responses -->

	</article> <!-- /#ticket-<?php the_ID(); ?> -->

	<a class="ns-btn ns-btn-sm ns-btn-default" href="<?php echo esc_url(get_permalink( $support_desk )); ?>"><span class="ns-icon-chevron-left"></span> <?php _e( 'Back to ticket index', 'nanosupport' ); ?></a>

</div>
