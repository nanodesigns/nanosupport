<?php
/**
 * Shortcode: Submit Ticket
 *
 * Showing the functionality for submitting a ticket from the the front end
 * using shortcode [nanosupport_submit_ticket]
 *
 * @author  	nanodesigns
 * @category 	Shortcode
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_submit_support_ticket() {

	global $ns_errors;
    
    if( !empty( $ns_errors ) ){
        foreach( $ns_errors as $error ){
    		echo '<div class="alert alert-danger" role="alert">';
            	printf( '<strong>Error:</strong> %s', $error );
        	echo '</div>';
        }
    }

	ob_start();
	?>

	<div class="well well-sm">
		<div class="row">
			<div class="col-sm-2">
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'support-desk' ) ) ); ?>" class="btn btn-sm btn-primary">
					<span class="ns-icon-tag"></span> <?php _e( 'All the Tickets', 'nanosupport' ); ?>
				</a>
			</div>
			<div class="col-sm-10 text-muted">
				<small><?php _e( 'See all the Public support tickets to see already resolved issues. If they are <em>not</em> close to you, then submit a new ticket here.', 'nanosupport' ); ?></small>
			</div>
		</div>
	</div>

	<div class="nano-support-ticket nano-add-ticket">
		<div class="row">
			<div class="col-md-12">
				
				<form class="form-horizontal" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

					<div class="form-group">
						<label for="ns-ticket-subject" class="col-sm-2 control-label">
							<?php _e( 'Subject', 'nanosupport' ); ?>
						</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="ns_ticket_subject" id="ns-ticket-subject" placeholder="<?php esc_attr_e( 'Subject', 'nanosupport' ); ?>" value="<?php echo !empty($_POST['ns_ticket_subject']) ? $_POST['ns_ticket_subject'] : ''; ?>" required>
						</div>
					</div> <!-- /.form-group -->

					<div class="form-group">
						<label for="ns-ticket-details" class="col-sm-2 control-label">
							<?php _e( 'Description', 'nanosupport' ); ?>
						</label>
						<div class="col-sm-10">
							<textarea class="form-control" name="ns_ticket_details" id="ns-ticket-details" placeholder="<?php _e( 'Details of the issue', 'nanosupport' ); ?>" rows="5" required><?php echo !empty($_POST['ns_ticket_details']) ? html_entity_decode( stripslashes( $_POST['ns_ticket_details'] ) ) : ''; ?></textarea>
						</div>
					</div> <!-- /.form-group -->

					<div class="form-group">
						<label for="ns-ticket-priority" class="col-sm-2 control-label">
							<?php _e( 'Priority', 'nanosupport' ); ?>
						</label>
						<div class="col-sm-10">
							<?php $sub_val = !empty($_POST['ns_ticket_priority']) ? $_POST['ns_ticket_priority'] : ''; ?>
							<select class="form-control" name="ns_ticket_priority" id="ns-ticket-priority">
								<option value="low" <?php selected( $sub_val, 'low' ); ?>>
									⇩ <?php _e( 'Low', 'nanosupport' ); ?>
								</option>
								<option value="medium" <?php selected( $sub_val, 'medium' ); ?>>
									⇔ <?php _e( 'Medium', 'nanosupport' ); ?>
								</option>
								<option value="high" <?php selected( $sub_val, 'high' ); ?>>
									⇧ <?php _e( 'High', 'nanosupport' ); ?>
								</option>
								<option value="critical" <?php selected( $sub_val, 'critical' ); ?>>
									⇪ <?php _e( 'Critical', 'nanosupport' ); ?>
								</option>
							</select>
						</div>
					</div> <!-- /.form-group -->


					<?php if( ! is_user_logged_in() ) { ?>

						<?php
						/**
						 * Get parameter to load proper portion of code
						 */
						$login = false;
						if( isset($_GET['action']) ) {
							$login 		= 'login' == $_GET['action'] ? true : false;
						}
						?>

						<hr>

						<?php if( ! $login ) {
							/**
							 * Registration
							 * Show the user registration form.
							 */
							?>

							<div class="form-group">
								<p class="col-sm-12 text-center">
									<span class="ns-icon-info-circled"></span> <?php _e( '<strong>Note:</strong> With these information below, we will create an account on your behalf to track the ticket for further enquiry.', 'nanosupport' ); ?>
								</p>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<label for="reg-name" class="col-sm-2 control-label">
									<?php _e( 'Username', 'nanosupport' ); ?>
								</label>
								<div class="col-sm-10">
									<input name="reg_name" type="text" class="form-control login-field" value="<?php echo( isset($_POST['reg_name']) ? $_POST['reg_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="reg-name" required/>
								</div>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<label for="reg-email" class="col-sm-2 control-label">
									<?php _e( 'Your email', 'nanosupport' ); ?>
								</label>
								<div class="col-sm-10">
									<input name="reg_email" type="email" class="form-control login-field" value="<?php echo( isset($_POST['reg_email']) ? $_POST['reg_email'] : null ); ?>" placeholder="<?php esc_attr_e( 'Email', 'nanosupport' ); ?>" id="reg-email" required/>
								</div>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<label for="reg-pass" class="col-sm-2 control-label">
									<?php _e( 'Password', 'nanosupport' ); ?>
								</label>
								<div class="col-sm-10">
									<input name="reg_password" type="password" class="form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="reg-pass" required/>
								</div>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<p class="col-sm-10 col-sm-offset-2">
									<?php printf( __( 'Already have an account? <a href="%1s">Login</a>', 'nanosupport' ), esc_url( add_query_arg( 'action', 'login', get_the_permalink() ) ) ); ?>
								</p>
							</div> <!-- /.form-group -->

							<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
							<input type="hidden" name="ns_registration_submit">

						<?php } else {
							/**
							 * Login
							 * Show the user login form.
							 */
							?>

							<div class="form-group">
								<label for="login-name" class="col-sm-2 control-label"><?php _e( 'Username', 'nanosupport' ); ?></label>
								<div class="col-sm-10">
									<input name="login_name" type="text" class="form-control login-field" value="<?php echo( isset($_POST['login_name']) ? $_POST['login_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="login-name" required />
								</div>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<label for="login-pass" class="col-sm-2 control-label"><?php _e( 'Password', 'nanosupport' ); ?></label>
								<div class="col-sm-10">
									<input name="login_password" type="password" class="form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="login-pass" required />
								</div>
							</div> <!-- /.form-group -->

							<div class="form-group">
								<p class="col-sm-10 col-sm-offset-2"><?php printf( __( "Don't have an account? <a href=\"%1s\">Create one</a>", 'nanosupport' ), esc_url( get_the_permalink() ) ); ?></p>
							</div> <!-- /.form-group -->

							<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
							<input type="hidden" name="ns_login_submit">

						<?php } //endif( ! $login ) ?>

					<?php } //endif( ! is_user_logged_in() ) ?>
					
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" name="ns_submit" class="btn btn-primary">
								<?php _e( 'Submit', 'nanosupport' ); ?>
							</button>
						</div>
					</div> <!-- /.form-group -->

				</form>

			</div>
		</div>
	</div> <!-- /.nano-support-ticket .nano-add-ticket -->
	
	<?php
	return ob_get_clean();
}
add_shortcode( 'nanosupport_submit_ticket', 'ns_submit_support_ticket' );