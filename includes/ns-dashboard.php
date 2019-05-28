<?php

/**
 * NanoSupport Dashboard Scripts
 *
 * Scripts specific to NanoSupport Dashboard widget only.
 * -----------------------------------------------------------------------
 */
function ns_dashboard_scripts() {
	$screen = get_current_screen();
	if( 'dashboard' === $screen->base ) {
		wp_enqueue_style( 'ns-admin' );
		wp_enqueue_script( 'ns-dashboard', NS()->plugin_url() .'/assets/js/nanosupport-dashboard.min.js', array('d3','c3'), NS()->version, true );
		global $current_user;
		wp_localize_script(
			'ns-dashboard',
			'ns',
			array(
				'pending'           => ns_ticket_status_count( 'pending' ),
				'solved'            => ns_ticket_status_count( 'solved' ),
				'inspection'        => ns_ticket_status_count( 'inspection' ),
				'open'              => ns_ticket_status_count( 'open' ),
				'pending_label'     => esc_html__( 'Pending Tickets', 'nanosupport' ),
				'solved_label'      => esc_html__( 'Solved Tickets', 'nanosupport' ),
				'open_label'        => esc_html__( 'Open Tickets', 'nanosupport' ),
				'inspection_label'  => esc_html__( 'Under Inspection', 'nanosupport' ),
				'my_pending'        => ns_ticket_status_count( 'pending', $current_user->ID ),
				'my_solved'         => ns_ticket_status_count( 'solved', $current_user->ID ),
				'my_inspection'     => ns_ticket_status_count( 'inspection', $current_user->ID ),
				'my_open'           => ns_ticket_status_count( 'open', $current_user->ID ),
			)
		);
	}
}

add_action( 'admin_enqueue_scripts', 'ns_dashboard_scripts' );

/**
 * NanoSupport Widget
 *
 * Add a dashboard widget for the plugin to display general
 * information as per the user privilige.
 * -----------------------------------------------------------------------
 */
function nanosupport_dashboard_widget() {
	wp_add_dashboard_widget(
        'nanosupport_widget',                   //dashboard ID
        '<i class="ns-icon-nanosupport" aria-hidden="true"></i> '. esc_html__( 'NanoSupport', 'nanosupport' ),     //widget name
        'nanosupport_widget_callback'           //callback function
    );
}

add_action( 'wp_dashboard_setup', 'nanosupport_dashboard_widget' );

/**
 * NanoSupport widget callback
 * ...
 */
function nanosupport_widget_callback() { ?>
	<section id="nanosupport-widget">

		<?php
        //Support Seekers
		if( ns_is_user('support_seeker') ) { ?>

			<?php
			$ns_general_settings       = get_option( 'nanosupport_settings' );
			$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );
			?>

			<div class="ns-row">
				<div class="nanosupport-left-column">
					<h4 class="dashboard-head ns-text-center"><i class="ns-icon-tag" aria-hidden="true"></i> <?php esc_html_e( 'Welcome to Support Ticketing', 'nanosupport' ); ?></h4>
					<p><?php
					/* translators: Link to the user profile 1. link URL 2. user profile icon */
					printf( wp_kses( __( 'This is the back end of the support ticketing system. If you want to edit your profile, you can do that from <a href="%1$s">%2$s Your Profile</a>.', 'nanosupport' ), array( 'a' => array('href' => array()) ) ), get_edit_user_link(get_current_user_id()), '<i class="ns-icon-user" aria-hidden="true"></i>' ); ?></p>

					<?php
                    /**
                     * Display Knowledgebase on demand
                     * Display, if enabled in admin panel.
                     */
                    if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
                    	<p><?php esc_html_e( 'Use the links here for exploring knowledgebase, visiting your support desk, or submitting new ticket. Before submitting new ticket, we prefer you to consider exploring the Knowledgebase for existing resources.', 'nanosupport' ); ?></p>
                    <?php } else { ?>
                    	<p><?php esc_html_e( 'Use the links here for visiting your support desk, or submitting new ticket.', 'nanosupport' ); ?></p>
                    <?php } ?>
                </div>
                <div class="nanosupport-right-column ns-text-center">
                	<h4 class="dashboard-head"><i class="ns-icon-mouse" aria-hidden="true"></i> <?php esc_html_e( 'My Tools', 'nanosupport' ); ?></h4>
                	<a class="button button-primary ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_general_settings['support_desk']) ); ?>">
                		<i class="icon ns-icon-tag" aria-hidden="true"></i> <?php esc_html_e( 'Support Desk', 'nanosupport' ); ?>
                	</a>
                	<a class="button ns-button-danger ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_general_settings['submit_page']) ); ?>">
                		<i class="icon ns-icon-tag" aria-hidden="true"></i> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                	</a>

                	<?php
                    /**
                     * Display Knowledgebase on demand
                     * Display, if enabled in admin panel.
                     */
                    if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
                    	<a class="button ns-button-info ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_knowledgebase_settings['page']) ); ?>">
                    		<strong><i class="icon ns-icon-docs" aria-hidden="true"></i> <?php esc_html_e( 'Knowledgebase', 'nanosupport' ); ?></strong>
                    	</a>
                    <?php } ?>
                </div>
            </div>

        <?php } //support_seekers ?>

        <?php
        //Agent & Manager
        if( ns_is_user('agent_and_manager') ) { ?>
        	<div class="ns-row">
        		<div class="nanosupport-50-left ns-text-center">
        			<h4 class="dashboard-head">
        				<i class="ns-icon-pie-chart" aria-hidden="true"></i> <?php esc_html_e( 'Current Status', 'nanosupport' ); ?>
        			</h4>
        			<?php
        			$total_tickets = ns_total_ticket_count('nanosupport');
        			if( 0 === $total_tickets ) : ?>
        				<div id="ns-no-activity">
        					<p class="smiley"></p>
        					<p><?php _e( 'Yet nothing to display!', 'nanosupport' ) ?></p>
        				</div>
        				<?php else : ?>
        					<div id="ns-chart"></div>
        					<div class="ns-total-ticket-count ns-text-center">
        						<?php
        						/* translators: Count in numbers */
        						printf( esc_html( _n( 'Total Ticket: %d', 'Total Tickets: %d', $total_tickets, 'nanosupport' ) ), $total_tickets );
        						?>
        					</div>
        				<?php endif; ?>
        			</div> <!-- /.nanosupport-50-left -->

        			<?php
                /**
                 * Manager only
                 * ...
                 */
                if( ns_is_user('manager') ) { ?>
                	<div class="nanosupport-50-right">
                		<h4 class="dashboard-head ns-text-center">
                			<i class="ns-icon-pulse" aria-hidden="true"></i> <?php _e( 'Recent Activity', 'nanosupport' ); ?>
                		</h4>
                		<?php
                		$activity_arr = array();
                		$response_activity = get_comments( array(
                			'type'   => 'nanosupport_response',
                			'number' => 5,
                			'orderby'=> 'comment_date'
                		) );
                		foreach( $response_activity as $response ) {
                			$activity_arr[$response->comment_ID]['id']        = intval($response->comment_ID);
                			$activity_arr[$response->comment_ID]['type']      = 'response';
                			$activity_arr[$response->comment_ID]['date']      = $response->comment_date;
                			$activity_arr[$response->comment_ID]['author_id'] = intval($response->user_id);
                			$activity_arr[$response->comment_ID]['author']    = $response->comment_author;
                			$activity_arr[$response->comment_ID]['ticket']    = intval($response->comment_post_ID);
                		}

                		$ticket_activity = get_posts( array(
                			'post_type'     => 'nanosupport',
                			'post_status'   => array('pending', 'private', 'publish'),
                			'posts_per_page'=> 5,
                		) );
                		foreach( $ticket_activity as $ticket ) {
                			$activity_arr[$ticket->ID]['id']        = $ticket->ID;
                			$activity_arr[$ticket->ID]['type']      = 'ticket';
                			$activity_arr[$ticket->ID]['date']      = $ticket->post_date;
                			$activity_arr[$ticket->ID]['author_id'] = intval($ticket->post_author);
                			$activity_arr[$ticket->ID]['author']    = ns_user_nice_name( $ticket->post_author );
                			$activity_arr[$ticket->ID]['modified']  = $ticket->post_modified;
                			$activity_arr[$ticket->ID]['status']    = $ticket->post_status;
                		}

                		usort( $activity_arr, 'ns_date_compare' );

                		$counter = 0;
                		if( empty( $activity_arr ) ) {
                			echo '<div id="ns-no-activity">';
                			echo '<p class="smiley"></p>';
                			echo '<p>' . esc_html__( 'No activity yet!', 'nanosupport' ) . '</p>';
                			echo '</div>';
                		} else {
                			foreach( $activity_arr as $activity ) {
                				$counter++;

                				if( $counter <= 5 ) { ?>

                					<div>
                						<strong><?php echo ns_date_time( $activity['date'] ); ?></strong><br>
                						<?php
                						if( 'response' === $activity['type'] ) {
                							/* translators: 1. link URL 2. ticket title 3. ticket author */
                							printf(
                								'<i class="ns-icon-responses" aria-hidden="true"></i> '. wp_kses( __( 'Ticket <a href="%1$s">%2$s</a> is responded by %3$s', 'nanosupport' ), array('a' => array('href'=>array()) ) ),
                								get_edit_post_link($activity['ticket']),
                								get_the_title($activity['ticket']),
                								$activity['author']
                							);
                						} elseif( 'ticket' === $activity['type'] ) {
                							/* translators: 1. link URL 2. ticket title 3. ticket author */
                							printf(
                								'<i class="ns-icon-tag" aria-hidden="true"></i> '. wp_kses( __( 'New Ticket <a href="%1$s">%2$s</a> submitted by %3$s', 'nanosupport' ), array('a' => array('href'=>array()) ) ),
                								get_edit_post_link($activity['id']),
                								get_the_title($activity['id']),
                								$activity['author']
                							);
                						}
                						?>
                						<hr>
                					</div>

                					<?php
                				}

                			}
                		}
                		?>
                	</div> <!-- /.nanosupport-50-right -->

                <?php } //manager only
                /**
                 * Agent only
                 * ...
                 */
                elseif( ns_is_user('agent') ) { ?>

                	<div class="nanosupport-50-right">
                		<h4 class="dashboard-head ns-text-center">
                			<i class="ns-icon-pulse" aria-hidden="true"></i> <?php _e( 'My Activity Status', 'nanosupport' ); ?>
                		</h4>
                		<?php
                		global $current_user;
                		$my_total_tickets = ns_total_ticket_count('nanosupport', $current_user->ID);
                		if( 0 === $my_total_tickets ) : ?>
                			<div id="ns-no-activity">
                				<p class="smiley"></p>
                				<p><?php esc_html_e( 'You&rsquo;ve not assigned any ticket yet!', 'nanosupport' ) ?></p>
                			</div>
            			<?php else : ?>
            				<div id="ns-activity-chart"></div>
            				<div class="ns-total-ticket-count ns-text-center">
            					<?php
            					/* translators: Count in numbers */
            					printf( esc_html( _n( 'My Total Ticket: %d', 'My Total Tickets: %d', $my_total_tickets, 'nanosupport'  ) ), $my_total_tickets );
            					?>
            				</div>
            			<?php endif; ?>
            		</div> <!-- /.nanosupport-50-right -->

            	<?php } //agent only ?>
            </div>
        <?php } //administrator/editor ?>

        <hr>
        <div class="ns-text-center">
        	<?php printf( '<a href="https://github.com/nanodesigns/nanosupport/issues/new/choose" target="_blank" rel="noopener">'. __( 'Rate us <span aria-label="Five Star">%s</span>', 'nanosupport' ) .'</a>', '<i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i>' ); ?> | <?php printf( '<a href="https://github.com/nanodesigns/nanosupport/issues/new/choose" target="_blank" rel="noopener">'. __( 'Get Support', 'nanosupport' ). '</a>' ); ?> | <a href="https://nanodesignsbd.com?ref=nanosupport" target="_blank" rel="noopener" title="nanodesigns - developer of the plugin"><strong>nano</strong>designs</a>
        </div>

    </section>
    <?php
}


/**
 * Remove Dashboard Widgets
 *
 * Remove unnecessary dashboard widgets for 'support_seeker' user role.
 *
 * @author  WPBeginner
 * @author  Rajesh B
 *
 * @link    http://www.wpbeginner.com/wp-tutorials/how-to-remove-wordpress-dashboard-widgets/
 * @link    http://wpsnippy.com/how-to-remove-wordpress-dashboard-welcome-panel/
 * -----------------------------------------------------------------------
 */
function ns_remove_dashboard_widgets() {
	global $wp_meta_boxes;

	if ( ns_is_user('support_seeker') ) {
		remove_action('welcome_panel', 'wp_welcome_panel');
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']); //WordPress News
    }

}

add_action( 'wp_dashboard_setup', 'ns_remove_dashboard_widgets' );
