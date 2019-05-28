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
			/* translators: error message */
				printf( __( '<strong>Error:</strong> %s', 'nanosupport' ), $error );
			echo '</div>';
		}
	}

    /**
	 * Show a Redirection Message
	 * while redirected.
	 */
    if( isset($_GET['from']) && 'sd' === $_GET['from'] ) {
    	echo '<div class="ns-alert ns-alert-info" role="alert">';
    		esc_html_e( 'You are redirected from the Support Desk, because you are not logged in, and have no permission to view any ticket.', 'nanosupport' );
    	echo '</div>';
    }

    //Display success message, if any
    if( isset($_GET['ns_success']) && $_GET['ns_success'] == 1 ) {
    	echo '<div class="ns-alert ns-alert-success" role="alert">';
	    	echo wp_kses( __( '<strong>Success!</strong> Your ticket is submitted successfully! It will be reviewed shortly and replied as early as possible.', 'nanosupport' ), array('strong' => array()) );
	    	echo '&nbsp;<a href="'. get_permalink( $ns_general_settings['support_desk'] ) .'" class="link-to-desk"><i class="ns-icon-tag" aria-hidden="true"></i>&nbsp;';
	    		esc_html_e( 'Check your tickets', 'nanosupport' );
	    	echo '</a>';
    	echo '</div>';
    }

	//Clean up request URI from temporary args for alert[s].
    $_SERVER['REQUEST_URI'] = remove_query_arg( 'ns_success', $_SERVER['REQUEST_URI'] );

    ob_start();
    ?>

    <div id="nanosupport-add-ticket" class="nano-support-ticket nano-add-ticket ns-no-js">

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

				<form class="ns-form-horizontal" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>"<?php
					/**
					 * -----------------------------------------------------------------------
					 * HOOK : ACTION HOOK
					 * nanosupport_new_ticket_form_tag
					 *
					 * Fires inside the Add New Ticket Form tag.
					 *
					 * @since  1.0.0
					 *
					 * 10	- ns_change_form_type_for_rich_media()
					 * -----------------------------------------------------------------------
					 */
					do_action( 'nanosupport_new_ticket_form_tag' );
					?>>

					<!-- SUBJECT -->
					<div class="ns-form-group">
						<label for="ns-ticket-subject" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
							<?php esc_html_e( 'Subject', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
						</label>
						<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
							<?php echo ns_tooltip( 'ns-subject', esc_html__( 'Write down a self-descriptive brief subject to the ticket', 'nanosupport' ), 'bottom' ); ?>
						</div>
						<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
							<input type="text" class="ns-form-control" name="ns_ticket_subject" id="ns-ticket-subject" placeholder="<?php esc_attr_e( 'Subject in brief', 'nanosupport' ); ?>" value="<?php echo !empty($_POST['ns_ticket_subject']) ? stripslashes_deep( $_POST['ns_ticket_subject'] ) : ''; ?>" aria-describedby="ns-subject" required autocomplete="off">
						</div>
					</div> <!-- /.ns-form-group -->

					<!-- TICKET DETAILS -->
					<div class="ns-form-group">
						<label for="ns-ticket-details" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
							<?php esc_html_e( 'Details', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
						</label>
						<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
							<?php
							/**
							 * WP Editor array.
							 * Declare the array here, so that we can conditionally
							 * display tooltip content.
							 * @var array
							 * ...
							 */
							$wp_editor_array = array(
								'media_buttons'		=> false,
								'textarea_name'		=> 'ns_ticket_details',
								'textarea_rows'		=> 10,
								'editor_class'		=> 'ns-form-control',
								'quicktags'			=> false,
								'tinymce'			=> true
							);

							/**
						     * -----------------------------------------------------------------------
						     * HOOK : FILTER HOOK
						     * ns_wp_editor_specs
						     *
						     * Hook to moderate the specs of the wp_editor().
						     *
						     * @since  1.0.0
						     * -----------------------------------------------------------------------
						     */
							$wp_editor_specs = apply_filters( 'ns_wp_editor_specs', $wp_editor_array );

							$character_limit = ns_is_character_limit();
							if( $character_limit ) {
								/* translators: character limit to the ticket content, in number */
								$content_tooltip_msg = sprintf( esc_html__( 'Write down your issue in details... At least %s characters is a must.', 'nanosupport' ), $character_limit );
								// allowed HTML tags are not necessary if rich text editor is disabled.
								if( $wp_editor_specs['tinymce'] != true ) {
									$content_tooltip_msg .= '<br><small>';
									/* translators: allowed HTML tags to the plugin */
									$content_tooltip_msg .= sprintf( __( '<strong>Allowed HTML Tags:</strong><br> %s', 'nanosupport' ), ns_get_allowed_html_tags() );
									$content_tooltip_msg .= '</small>';
								}

								echo ns_tooltip( 'ns-details', $content_tooltip_msg, 'bottom' );
							} else {
								$content_tooltip_msg = esc_html__( 'Write down your issue in details...', 'nanosupport' );
								// allowed HTML tags are not necessary if rich text editor is disabled.
								if( $wp_editor_specs['tinymce'] != true ) {
									$content_tooltip_msg .= '<br><small>';
									/* translators: allowed HTML tags to the plugin */
									$content_tooltip_msg .= sprintf( __( '<strong>Allowed HTML Tags:</strong><br> %s', 'nanosupport' ), ns_get_allowed_html_tags() );
									$content_tooltip_msg .= '</small>';
								}

								echo ns_tooltip( 'ns-details', $content_tooltip_msg, 'bottom' );
							}
							?>
						</div>
						<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
							<?php
							$ticket_content = !empty($_POST['ns_ticket_details']) ? $_POST['ns_ticket_details'] : '';

							// initiate the editor.
							wp_editor(
								$content   = $ticket_content,
								$editor_id = 'ns-ticket-details',
								$wp_editor_specs
							);
							?>
						</div>
					</div> <!-- /.ns-form-group -->

					<?php
					$display_priority = isset($ns_general_settings['is_priority_visible']) ? absint($ns_general_settings['is_priority_visible']) : false;

					if( $display_priority  ) { ?>

						<!-- TICKET PRIORITY -->
						<div class="ns-form-group">
							<label for="ns-ticket-priority" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
								<?php esc_html_e( 'Priority', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
							</label>
							<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
								<?php echo ns_tooltip( 'ns-priority', esc_html__( 'Choose the priority of the issue', 'nanosupport' ), 'bottom' ); ?>
							</div>
							<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12 ns-form-inline">
								<?php $submit_val = !empty($_POST['ns_ticket_priority']) ? $_POST['ns_ticket_priority'] : 'low'; ?>
								<select class="ns-form-control" name="ns_ticket_priority" id="ns-ticket-priority" aria-describedby="ns-priority" required>
									<option value="" <?php selected( $submit_val, '' ); ?>><?php esc_html_e( 'Select a priority', 'nanosupport' ); ?></option>
									<option value="low" <?php selected( $submit_val, 'low' ); ?>><?php esc_html_e( 'Low', 'nanosupport' ); ?></option>
									<option value="medium" <?php selected( $submit_val, 'medium' ); ?>><?php esc_html_e( 'Medium', 'nanosupport' ); ?></option>
									<option value="high" <?php selected( $submit_val, 'high' ); ?>><?php esc_html_e( 'High', 'nanosupport' ); ?></option>
									<option value="critical" <?php selected( $submit_val, 'critical' ); ?>><?php esc_html_e( 'Critical', 'nanosupport' ); ?></option>
								</select>
							</div>
						</div> <!-- /.ns-form-group -->

					<?php } //endif( $display_priority  ) ?>

					<?php
					$display_department = isset($ns_general_settings['is_department_visible']) ? absint($ns_general_settings['is_department_visible']) : false;

					if( $display_department  ) { ?>

						<!-- TICKET DEPARTMENTS -->
						<div class="ns-form-group">
							<label for="ns-ticket-department" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
								<?php esc_html_e( 'Department', 'nanosupport' ); ?>
							</label>
							<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
								<?php echo ns_tooltip( 'ns-department', esc_html__( 'Choose a department to which you want to notify about the ticket', 'nanosupport' ), 'bottom' ); ?>
							</div>
							<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12 ns-form-inline">
								<?php $submit_val = ! empty($_POST['ns_ticket_department']) ? $_POST['ns_ticket_department'] : ''; ?>
								<?php
								$ns_dept_args = array(
									'show_option_all'    => '',
									'show_option_none'   => esc_html__( 'Select a Department', 'nanosupport' ),
									'option_none_value'	 => '',
									'orderby'            => 'ID',
									'order'              => 'ASC',
									'show_count'         => 0,
									'hide_empty'         => 0,
									'child_of'           => 0,
									'exclude'            => '',
									'echo'               => true,
									'selected'           => $submit_val,
									'hierarchical'       => 0,
									'name'               => 'ns_ticket_department',
									'id'                 => 'ns-ticket-department',
									'class'              => 'postform ns-form-control',
									'depth'              => 0,
									'tab_index'          => 0,
									'taxonomy'           => 'nanosupport_department',
									'hide_if_empty'      => false
								);
								wp_dropdown_categories( $ns_dept_args );
								?>
							</div>
						</div> <!-- /.ns-form-group -->

					<?php } //endif( $display_department  ) ?>

					<?php
					$NSECommerce = new NSECommerce();
					if( $NSECommerce->ecommerce_enabled() ) {
						$products = $NSECommerce->get_products();

						/**
					     * -----------------------------------------------------------------------
					     * HOOK : FILTER HOOK
					     * ns_mandate_product_fields
					     *
					     * Hook to moderate the permission for mandating product-specifc fields,
					     * or not.
					     *
					     * @since  1.0.0
					     * -----------------------------------------------------------------------
					     */
						$mandate_product_fields = apply_filters( 'ns_mandate_product_fields', true );
						?>

						<!-- TICKET PRODUCTS -->
						<div class="ns-form-group">
							<label for="ns-ticket-product" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
								<?php esc_html_e( 'Product', 'nanosupport' ); ?>
								<?php if( $mandate_product_fields ) echo '<sup class="ns-required">*</sup>'; ?>
							</label>
							<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
								<?php echo ns_tooltip( 'ns-product', esc_html__( 'Select the product the ticket is about.', 'nanosupport' ), 'bottom' ); ?>
							</div>
							<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12 ns-form-inline">
								<?php $submit_val = !empty($_POST['ns_ticket_product']) ? $_POST['ns_ticket_product'] : ''; ?>
								<select class="ns-form-control" name="ns_ticket_product" id="ns-ticket-product" aria-describedby="ns-product" <?php if( $mandate_product_fields ) echo 'required'; ?>>
									<option value="" <?php selected( $submit_val, '' ); ?>><?php esc_html_e( 'Select a product', 'nanosupport' ); ?></option>
									<?php foreach($products as $id => $product_name) { ?>
										<option value="<?php echo $id; ?>" <?php selected( $submit_val, $id ); ?>>
											<?php echo esc_html($product_name); ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</div> <!-- /.ns-form-group -->

						<!-- TICKET PRODUCT RECEIPT -->
						<div class="ns-form-group">
							<label for="ns-ticket-product-receipt" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
								<?php esc_html_e( 'Purchase Receipt', 'nanosupport' ); ?>
								<?php if( $mandate_product_fields ) echo '<sup class="ns-required">*</sup>'; ?>
							</label>
							<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
								<?php echo ns_tooltip( 'ns-product-receipt', esc_html__( 'Enter the receipt number of purchasing the product.', 'nanosupport' ), 'bottom' ); ?>
							</div>
							<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12 ns-form-inline">
								<?php $submit_val = !empty($_POST['ns_ticket_product_receipt']) ? $_POST['ns_ticket_product_receipt'] : ''; ?>
								<input type="number" name="ns_ticket_product_receipt" class="ns-form-control" id="ns-ticket-product-receipt" aria-describedby="ns-product-receipt" value="<?php echo $submit_val; ?>" min="0" <?php if( $mandate_product_fields ) echo 'required'; ?>>
							</div>
						</div> <!-- /.ns-form-group -->

					<?php } // endif( $NSECommerce->ecommerce_enabled ) ?>

					<?php if( ! is_user_logged_in() ) { ?>

						<?php
						/**
						 * Get parameter to load proper portion of code
						 */
						$login = false;
						if( isset($_GET['action']) ) {
							$login = 'login' == $_GET['action'] ? true : false;
						}

						/**
						 * Embedded Login
						 * Default is false (direct to system login URL)
						 * ...
						 */
						$embedded_login = isset($ns_general_settings['embedded_login']) ? absint($ns_general_settings['embedded_login']) : false;
						?>

						<?php
						/**
						 * If embedded login is enabled.
						 * ...
						 */
						if( $embedded_login ) {
							$login_link 	= add_query_arg( 'action', 'login', get_the_permalink() );
							$login_title 	= esc_html__( 'Login', 'nanosupport' );
						} else {
							$login_link 	= wp_login_url( get_the_permalink() );
							$login_title 	= esc_html__( 'Login first', 'nanosupport' );
						}
						?>

						<?php if( ! $login ) {
							/**
							 * REGISTRATION
							 * Show the user registration form.
							 */
							?>

							<?php
							/**
							 * If registration is activated
							 * ...
							 */
							if( 1 == get_option('users_can_register') ) { ?>

								<div class="ns-form-group">
									<p class="ns-col-sm-9 ns-col-sm-offset-3">
										<i class="ns-icon-info-circled" aria-hidden="true"></i> <?php esc_html_e( 'With these information below, we will create an account on your behalf to track the ticket for further enquiry.', 'nanosupport' ); ?>
									</p>
								</div> <!-- /.ns-form-group -->

								<?php
								/**
								 * Display when Auto Username Generation is OFF
								 */
								$generate_username = isset($ns_general_settings['account_creation']['generate_username']) ? $ns_general_settings['account_creation']['generate_username'] : 0;
								if( $generate_username !== 1 ) : ?>

									<div class="ns-form-group">
										<label for="reg-name" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
											<?php esc_html_e( 'Username', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
										</label>
										<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
											<?php echo ns_tooltip( 'ns-username', esc_html__( 'Username for the user account', 'nanosupport' ), 'bottom' ); ?>
										</div>
										<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
											<input name="reg_name" type="text" class="ns-form-control login-field" value="<?php echo( isset($_POST['reg_name']) ? $_POST['reg_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="reg-name" aria-describedby="ns-username" required>
										</div>
									</div> <!-- /.ns-form-group -->

								<?php endif; ?>

								<div class="ns-form-group">
									<label for="reg-email" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
										<?php esc_html_e( 'Your email', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
									</label>
									<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
										<?php echo ns_tooltip( 'ns-user-mail', esc_html__( 'Your email for the user account and for further communication', 'nanosupport' ), 'bottom' ); ?>
									</div>
									<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
										<input name="reg_email" type="email" class="ns-form-control login-field" value="<?php echo( isset($_POST['reg_email']) ? $_POST['reg_email'] : null ); ?>" placeholder="<?php esc_attr_e( 'Email', 'nanosupport' ); ?>" id="reg-email" aria-describedby="ns-user-mail" required>
									</div>
								</div> <!-- /.ns-form-group -->

								<?php
								/**
								 * Display when Auto Password Generation is OFF
								 */
								$generate_password = isset($ns_general_settings['account_creation']['generate_password']) ? $ns_general_settings['account_creation']['generate_password'] : 0;
								if( $generate_password !== 1 ) : ?>

									<div class="ns-form-group">
										<label for="reg-pass" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
											<?php esc_html_e( 'Password', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
										</label>
										<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
											<?php echo ns_tooltip( 'ns-password', esc_html__( 'Set a password for your account. Password must be at least 5 characters. Strong password should contain numbers, alphabets, and alphanumeric characters with a mixture of uppercase and lowercase', 'nanosupport' ), 'bottom' ); ?>
										</div>
										<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
											<input name="reg_password" type="password" class="ns-form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="reg-pass" aria-describedby="ns-password" required>
										</div>
									</div> <!-- /.ns-form-group -->

								<?php endif; ?>

								<!-- HoneyPot - Spam Trap -->
								<div style="<?php echo ( (is_rtl()) ? 'right' : 'left' ); ?>: -999em; position: absolute;">
									<label for="come-to-trap"><?php esc_html_e( 'Anti-spam HoneyPot', 'nanosupport' ); ?></label>
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

								<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
								<input type="hidden" name="ns_registration_submit">

								<?php wp_nonce_field( 'nanosupport-registration' ); ?>

								<div class="ns-form-group">
									<p class="ns-col-sm-9 ns-col-sm-offset-3">
										<?php esc_html_e( 'Already have an account?', 'nanosupport' ); ?> <a href="<?php echo esc_url($login_link); ?>"><?php echo esc_html( $login_title ); ?></a>
									</p>
								</div> <!-- /.ns-form-group -->

							<?php } else { ?>

								<!-- REGISTRATION IS INACTIVE -->
								<div class="ns-form-group">
									<p class="ns-col-sm-9 ns-col-sm-offset-3 ns-text-dim">
										<?php
										/* translators: if you have account, login */
										esc_html_e( 'Registration is closed now. If you already have an account', 'nanosupport' ); ?> <a href="<?php echo esc_url($login_link); ?>"><?php echo esc_html( $login_title ); ?></a>
									</p>
								</div> <!-- /.ns-form-group -->

							<?php } //endif ?>

						<?php } else {
							/**
							 * Login
							 * Show the user login form.
							 */

							if( $embedded_login ) {
								?>

								<div class="ns-form-group">
									<label for="login-name" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
										<?php esc_html_e( 'Username', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
									</label>
									<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
										<?php echo ns_tooltip( 'ns-login-name', esc_html__( 'Write down your username of your account', 'nanosupport' ), 'bottom' ); ?>
									</div>
									<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
										<input name="login_name" type="text" class="ns-form-control login-field" value="<?php echo( isset($_POST['login_name']) ? $_POST['login_name'] : null ); ?>" placeholder="<?php esc_attr_e( 'Username', 'nanosupport' ); ?>" id="login-name" aria-describedby="ns-login-name" required>
									</div>
								</div> <!-- /.ns-form-group -->

								<div class="ns-form-group">
									<label for="login-pass" class="ns-col-md-2 ns-col-sm-2 ns-col-xs-10 ns-control-label">
										<?php esc_html_e( 'Password', 'nanosupport' ); ?> <sup class="ns-required">*</sup>
									</label>
									<div class="ns-col-md-1 ns-col-sm-1 ns-col-xs-2 ns-text-center">
										<?php echo ns_tooltip( 'ns-login-pass', esc_html__( 'Write down the password of your account to login', 'nanosupport' ), 'bottom' ); ?>
									</div>
									<div class="ns-col-md-9 ns-col-sm-9 ns-col-xs-12">
										<input name="login_password" type="password" class="ns-form-control login-field" value="" placeholder="<?php esc_attr_e( 'Password', 'nanosupport' ); ?>" id="login-pass" aria-describedby="ns-login-pass" required>
									</div>
								</div> <!-- /.ns-form-group -->

								<div class="ns-form-group">
									<div class="ns-col-sm-offset-3 ns-col-sm-9 ns-col-xs-12 ns-checkbox">
										<label><input type="checkbox" name="rememberme"> <?php esc_html_e( 'Remember me', 'nanosupport' ); ?></label>
									</div>
								</div> <!-- /.ns-form-group -->

								<div class="ns-form-group">
									<?php if( 1 == get_option('users_can_register') ) { ?>
										<p class="ns-col-sm-offset-3 ns-col-sm-9 ns-col-xs-12">
											<?php
											/* translators: submit ticket with registration URL */
											printf( wp_kses( __( 'Don&rsquo;t have an account? <a href="%1s">Create one</a>', 'nanosupport' ), array('a'=>array('href'=>true)) ), esc_url( get_the_permalink() ) ); ?>
										</p>
									<?php } else { ?>
										<p class="ns-col-sm-offset-3 ns-col-sm-9 ns-col-xs-12 ns-text-dim">
											<?php
											/* translators: submit ticket with registration URL */
											printf( wp_kses( __( '<a href="%1s">Cancel Login</a>. But sorry, registration is closed now', 'nanosupport' ), array('a'=>array('href'=>true)) ), get_the_permalink() ); ?>
										</p>
									<?php } //endif ?>
								</div> <!-- /.ns-form-group -->

								<!-- HIDDEN INPUT TO TREAT FORM SUBMIT APPROPRIATELY -->
								<input type="hidden" name="ns_login_submit">

								<?php wp_nonce_field( 'nanosupport-login' ); ?>

							<?php } //endif( $embedded_login ) ?>

						<?php } //endif( ! $login ) ?>

					<?php } //endif( ! is_user_logged_in() ) ?>

					<div class="ns-form-group">
						<div class="ns-col-sm-offset-3 ns-col-sm-9 ns-col-xs-12">
							<button type="submit" name="ns_submit" class="ns-btn ns-btn-primary">
								<?php esc_html_e( 'Submit', 'nanosupport' ); ?>
							</button>

							<?php if( is_user_logged_in() ) : ?>
								<span class="ns-text-dim ns-small">
									&nbsp;
									<?php
									$current_user = wp_get_current_user();
									/* translators: logged in user display name */
									printf( wp_kses( __('<strong>Submitting as:</strong> %s', 'nanosupport'), array('strong'=>array()) ), $current_user->display_name ); ?>
									&nbsp;(<a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php esc_html_e('Log out', 'nanosupport') ?></a>)
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
