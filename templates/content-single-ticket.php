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

	$NSECommerce = new NSECommerce();
	if( $NSECommerce->ecommerce_enabled() ) {
		$product_info = $NSECommerce->get_product_info($ticket_meta['product'], $ticket_meta['receipt']);
	}

	$highlight_class = 'priority' === $highlight_choice ? $ticket_meta['priority']['class'] : $ticket_meta['status']['class'];
	?>

	<article id="ticket-<?php echo $post->ID; ?>" <?php post_class('ns-single'); ?>>

		<div class="ticket-question-card ns-cards <?php echo esc_attr($highlight_class); ?>">

			<div class="ns-row">
				<div class="ns-col-sm-11">
					<h1 class="ticket-head"><?php the_title(); ?></h1>
					<p class="ticket-author"><i class="ns-icon-user"></i> <?php echo $author->display_name; ?></p>

					<div class="ns-row ticket-meta">
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Created', 'nanosupport' ); ?>:</strong><br>
								<span class="ns-small"><?php echo ns_date_time( $post->post_date ); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Updated', 'nanosupport' ); ?>:</strong><br>
								<span class="ns-small"><?php echo ns_date_time( ns_get_ticket_modified_date($post->ID) ); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Department', 'nanosupport' ); ?>:</strong><br>
								<span class="ns-small"><?php echo ns_get_ticket_departments(); ?></span>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Assigned to', 'nanosupport' ); ?>:</strong><br>
								<?php
								if( ! empty($ticket_meta['agent']) ) {
									echo '<span class="ns-small">'. $ticket_meta['agent']['name'] .'</span>';
								} else {
									echo '<span class="ns-small">&mdash;</span>';
								}
								?>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Status', 'nanosupport' ); ?>:</strong><br>
								<?php echo $ticket_meta['status']['label']; ?>
							</p>
						</div>
						<div class="ns-col-sm-2 ns-col-xs-6">
							<p>
								<strong><?php esc_html_e( 'Priority', 'nanosupport' ); ?>:</strong><br>
								<span class="ns-small"><?php echo $ticket_meta['priority']['label']; ?></span>
							</p>
						</div>
					</div> <!-- /.ns-row -->
				</div>
				<div class="ns-col-sm-1 ns-right-portion">
					<a class="ns-btn ns-btn-danger ns-btn-xs ns-round-btn off-ticket-btn" href="<?php echo esc_url(get_permalink( $support_desk )); ?>" title="<?php esc_attr_e('Close the ticket', 'nanosupport'); ?>">
						<i class="ns-icon-remove"></i> <span class="screen-reader-only"><?php esc_attr_e('Close the ticket', 'nanosupport'); ?></span>
					</a>
					<a class="ns-btn ns-btn-default ns-btn-xs ns-round-btn ticket-link-btn" href="<?php echo esc_url(get_the_permalink()); ?>" title="<?php esc_attr_e('Permanent link to the Ticket', 'nanosupport'); ?>">
						<i class="ns-icon-link"></i> <span class="screen-reader-only"><?php esc_attr_e('Permanent link to the Ticket', 'nanosupport'); ?></span>
					</a>
					<?php edit_post_link( '<i class="ns-icon-edit" title="'. esc_attr__('Edit the Ticket', 'nanosupport') .'"></i> <span class="screen-reader-only">'. esc_attr__('Edit the Ticket', 'nanosupport') .'</span>', '', '', get_the_ID() ); ?>
				</div>
			</div> <!-- /.ns-row -->

			<?php if( $NSECommerce->ecommerce_enabled() && false !== $product_info ) { ?>

				<div class="ns-clearfix ticket-product-block">

					<?php if( 'publish' !== $product_info->status ) { ?>

						<div class="ns-text-muted ns-text-center">
		                    &mdash; <?php esc_html_e('Product attached is not available', 'nanosupport' ); ?> &mdash;
		                </div>

            		<?php } else { ?>
			
				
						<a href="<?php echo esc_url($product_info->link); ?>" target="_blank">
						    <i class="ns-icon-cart" aria-hidden="true"></i>
						    <strong><?php
						    /* translators: Product name */
						    printf( esc_html__('Product: %s', 'nanosupport'), $product_info->name ); ?></strong>
						</a>
						<small>(<?php
						/* translators: Receipt number */
						printf( esc_html__('Receipt: %d', 'nanosupport'), $ticket_meta['receipt'] ); ?>)</small>

					<?php } // endif( 'publish' !== $product_info->status ) ?>
					
				</div> <!-- /.ns-clearfix -->

			<?php } //endif( $NSECommerce->ecommerce_enabled() )	?>

			<div class="ticket-question">
				<?php the_content(); ?>
			</div>

		</div> <!-- /.ticket-question-card -->


		<!-- RESPONSES -->


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
			$response_array = get_comments( apply_filters( 'ns_ticket_responses_arg', $args ) );

		    $found_count = count($response_array);

			if( $response_array ) {

				echo '<div class="ticket-separator ticket-separator-center ns-text-uppercase">'. esc_html__('Responses', 'nanosupport') .'</div>';

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
								<a href="#response-<?php echo esc_attr($counter); ?>" class="response-bookmark ns-small"><strong class="ns-hash">#</strong> <?php echo ns_date_time( $response->comment_date ); ?></a>
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


		    <!-- NEW RESPONSE FORM -->

		    <?php get_nanosupport_response_form(); ?>

		</div> <!-- /.ticket-responses -->

	</article> <!-- /#ticket-<?php the_ID(); ?> -->

	<a class="ns-btn ns-btn-sm ns-btn-default" href="<?php echo esc_url(get_permalink( $support_desk )); ?>">
		<i class="ns-icon-chevron-left"></i> <?php esc_html_e( 'Back to ticket index', 'nanosupport' ); ?>
	</a>

</div>
