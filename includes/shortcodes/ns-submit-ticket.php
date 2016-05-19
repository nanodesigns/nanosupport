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
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_submit_support_ticket() {

	//Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

	global $ns_errors;
    
    //Display error message[s], if any
    if( !empty( $ns_errors ) ){
        foreach( $ns_errors as $error ){
    		echo '<div class="ns-alert ns-alert-danger" role="alert">';
            	printf( '<strong>Error:</strong> %s', $error );
        	echo '</div>';
        }
    }

    //Display success message, if any
    if( isset($_GET['ns_success']) && $_GET['ns_success'] == 1 ) {
		echo '<div class="ns-alert ns-alert-success" role="alert">';
			printf( __( '<strong>Success!</strong> Your ticket is submitted successfully! It will be reviewed shortly and replied as early as possible. <a href="%s"><span class="ns-icon-tag"></span> Check your ticket[s]</a>', 'nanosupport' ), esc_url( get_permalink( $ns_general_settings['support_desk'] ) ) );
	    echo '</div>';
	}

	//Clean up request URI from temporary args for alert[s].
	$_SERVER['REQUEST_URI'] = remove_query_arg( 'ns_success', $_SERVER['REQUEST_URI'] );

	ob_start();
	?>

	<div id="nanosupport-add-ticket" class="nano-support-ticket nano-add-ticket">

		<?php
		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_before_new_ticket
		 *
		 * To Hook anything before the Add New Ticket Form.
		 *
		 * @since  1.0.0
		 *
		 * 10	- ns_new_ticket_navigation()
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_before_new_ticket' );
		?>

		<div class="ns-row">
			<div class="ns-col-md-12">

				<form class="ns-form-horizontal" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">

					<div class="ns-form-group">
						<label for="ns-ticket-subject" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
							<?php _e( 'Subject', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
						</label>
						<div class="ns-col-md-10 ns-col-sm-9">
							<input type="text" class="ns-form-control" name="ns_ticket_subject" id="ns-ticket-subject" placeholder="<?php esc_attr_e( 'Subject in brief', 'nanosupport' ); ?>" value="<?php echo !empty($_POST['ns_ticket_subject']) ? stripslashes_deep( $_POST['ns_ticket_subject'] ) : ''; ?>" required>
						</div>
					</div> <!-- /.ns-form-group -->

					<div class="ns-form-group">
						<label for="ns-ticket-details" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
							<?php _e( 'Details', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
						</label>
						<div class="ns-col-md-10 ns-col-sm-9">
							<textarea id="ns-ticket-details" class="ns-form-control" name="ns_ticket_details" cols="30" rows="10" placeholder="<?php esc_attr_e( 'Write down your issue in details... At least 30 characters is a must.', 'nanosupport' ); ?>" required><?php if( !empty($_POST['ns_ticket_details']) ) echo stripslashes_deep( $_POST['ns_ticket_details'] ); ?></textarea>
						</div>
					</div> <!-- /.ns-form-group -->

					<div class="ns-form-group">
						<label for="ns-ticket-priority" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
							<?php _e( 'Priority', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
						</label>
						<div class="ns-col-md-10 ns-col-sm-9 ns-form-inline">
							<?php $sub_val = !empty($_POST['ns_ticket_priority']) ? $_POST['ns_ticket_priority'] : 'low'; ?>
							<select class="ns-form-control" name="ns_ticket_priority" id="ns-ticket-priority" required>
								<option value="low" <?php selected( $sub_val, 'low' ); ?>><?php _e( 'Low', 'nanosupport' ); ?></option>
								<option value="medium" <?php selected( $sub_val, 'medium' ); ?>><?php _e( 'Medium', 'nanosupport' ); ?></option>
								<option value="high" <?php selected( $sub_val, 'high' ); ?>><?php _e( 'High', 'nanosupport' ); ?></option>
								<option value="critical" <?php selected( $sub_val, 'critical' ); ?>><?php _e( 'Critical', 'nanosupport' ); ?></option>
							</select>
						</div>
					</div> <!-- /.ns-form-group -->


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
							 * REGISTRATION
							 * Show the user registration form.
							 */
							?>

							<div class="ns-form-group">
								<p class="ns-col-sm-12 ns-text-center">
									<span class="ns-icon-info-circled"></span> <?php _e( '<strong>Note:</strong> With these information below, we will create an account on your behalf to track the ticket for further enquiry.', 'nanosupport' ); ?>
								</p>
							</div> <!-- /.ns-form-group -->

							<?php
							/**
							 * Display when Auto Username Generation is OFF
							 */
							$generate_username = isset($ns_general_settings['account_creation']['generate_username']) ? $ns_general_settings['account_creation']['generate_username'] : 0;
							if( $generate_username !== 1 ) : ?>

								<div class="ns-form-group">
									<label for="reg-name" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
										<?php _e( 'Username', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
									</label>
									<div class="ns-col-md-10 ns-col-sm-9">
										<input name="reg_name" type="text" class="ns-form-control login-field" value="<?php echo( isset($_POST['reg_name']) ? $_POST['reg_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="reg-name" required>
									</div>
								</div> <!-- /.ns-form-group -->

							<?php endif; ?>

							<div class="ns-form-group">
								<label for="reg-email" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
									<?php _e( 'Your email', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
								</label>
								<div class="ns-col-md-10 ns-col-sm-9">
									<input name="reg_email" type="email" class="ns-form-control login-field" value="<?php echo( isset($_POST['reg_email']) ? $_POST['reg_email'] : null ); ?>" placeholder="<?php esc_attr_e( 'Email', 'nanosupport' ); ?>" id="reg-email" required>
								</div>
							</div> <!-- /.ns-form-group -->

							<?php
							/**
							 * Display when Auto Password Generation is OFF
							 */
							$generate_password = isset($ns_general_settings['account_creation']['generate_password']) ? $ns_general_settings['account_creation']['generate_password'] : 0;
							if( $generate_password !== 1 ) : ?>

								<div class="ns-form-group">
									<label for="reg-pass" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
										<?php _e( 'Password', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
									</label>
									<div class="ns-col-md-10 ns-col-sm-9">
										<input name="reg_password" type="password" class="ns-form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="reg-pass" required>
									</div>
								</div> <!-- /.ns-form-group -->

							<?php endif; ?>

							<!-- HoneyPot - Spam Trap -->
							<div style="<?php echo ( (is_rtl()) ? 'right' : 'left' ); ?>: -999em; position: absolute;">
								<label for="come-to-trap"><?php _e( 'Anti-spam HoneyPot', 'nanosupport' ); ?></label>
								<input type="text" name="repeat_email" id="come-to-trap" tabindex="-1" />
							</div>
							<!-- /HoneyPot - Spam Trap -->

							<?php
							/**
							 * -----------------------------------------------------------------------
							 * HOOK : ACTION HOOK
							 * nanosupport_register_form
							 * 
							 * To display anything below registration fields
							 *
							 * @since  1.0.0
							 * -----------------------------------------------------------------------
							 */
							do_action( 'nanosupport_register_form' ); ?>

							<?php
							/**
							 * -----------------------------------------------------------------------
							 * WP ACTION HOOK
							 * register_form
							 *
							 * WordPress' core action hook to display anything below user registration
							 * form.
							 *
							 * @link   https://codex.wordpress.org/Plugin_API/Action_Reference/register_form
							 * -----------------------------------------------------------------------
							 */
							do_action( 'register_form' ); ?>

							<div class="ns-form-group">
								<p class="ns-col-md-10 ns-col-sm-9 ns-col-md-offset-2 ns-col-sm-offset-3">
									<?php printf( __( 'Already have an account? <a href="%1s">Login</a>', 'nanosupport' ), esc_url( add_query_arg( 'action', 'login', get_the_permalink() ) ) ); ?>
								</p>
							</div> <!-- /.ns-form-group -->

							<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
							<input type="hidden" name="ns_registration_submit">

							<?php wp_nonce_field( 'nanosupport-registration' ); ?>

						<?php } else {
							/**
							 * Login
							 * Show the user login form.
							 */
							?>

							<div class="ns-form-group">
								<label for="login-name" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
									<?php _e( 'Username', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
								</label>
								<div class="ns-col-md-10 ns-col-sm-9">
									<input name="login_name" type="text" class="ns-form-control login-field" value="<?php echo( isset($_POST['login_name']) ? $_POST['login_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="login-name" required>
								</div>
							</div> <!-- /.ns-form-group -->

							<div class="ns-form-group">
								<label for="login-pass" class="ns-col-md-2 ns-col-sm-3 ns-control-label">
									<?php _e( 'Password', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
								</label>
								<div class="ns-col-md-10 ns-col-sm-9">
									<input name="login_password" type="password" class="ns-form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="login-pass" required>
								</div>
							</div> <!-- /.ns-form-group -->

							<div class="ns-form-group">
								<div class="ns-col-md-10 ns-col-sm-9 ns-col-md-offset-2 ns-col-sm-offset-3">
									<label><input type="checkbox" name="rememberme"> <?php _e( 'Remember me', 'nanosupport' ); ?></label>
								</div>
							</div> <!-- /.ns-form-group -->

							<div class="ns-form-group">
								<p class="ns-col-md-10 ns-col-sm-9 ns-col-md-offset-2 ns-col-sm-offset-3"><?php printf( __( 'Don&#8217;t have an account? <a href="%1s">Create one</a>', 'nanosupport' ), esc_url( get_the_permalink() ) ); ?></p>
							</div> <!-- /.ns-form-group -->

							<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
							<input type="hidden" name="ns_login_submit">

							<?php wp_nonce_field( 'nanosupport-login' ); ?>

						<?php } //endif( ! $login ) ?>

					<?php } //endif( ! is_user_logged_in() ) ?>
					
					<div class="ns-form-group">
						<div class="ns-col-md-offset-2 ns-col-sm-offset-3 ns-col-md-10 ns-col-sm-9">
							<button type="submit" name="ns_submit" class="ns-btn ns-btn-primary">
								<?php _e( 'Submit', 'nanosupport' ); ?>
							</button>

							<?php if( is_user_logged_in() ) : ?>
								<span class="ns-text-muted ns-small">
									&nbsp;
									<?php printf( __('<strong>Submitting as:</strong> %s', 'nanosupport'), wp_strip_all_tags(ns_user_nice_name()) ); ?>
									&nbsp;(<a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Log out', 'nanosupport') ?></a>)
								</span>
							<?php endif; ?>
						</div>
					</div> <!-- /.ns-form-group -->

				</form> <!-- /.ns-form-horizontal -->

			</div>
		</div> <!-- /.ns-row -->

		<?php
		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_after_new_ticket
		 * 
		 * To Hook anything after the Add New Ticket Form.
		 *
		 * @since  1.0.0
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_after_new_ticket' );
		?>

	</div> <!-- /.nano-support-ticket .nano-add-ticket -->
	
	<?php
	return ob_get_clean();
}

add_shortcode( 'nanosupport_submit_ticket', 'ns_submit_support_ticket' );
