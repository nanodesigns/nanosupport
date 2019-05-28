<?php
/**
 * Email Template
 *
 * Common Template for all the types of emails used for nanosupport.
 *
 * This template can be overridden by copying it to:
 * your-theme/nanosupport/content-email.php
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
 * @category    Email
 * @package     NanoSupport/Templates/
 * @version     1.0.0
 */

//Email settings from db
$email_settings = get_option('nanosupport_email_settings');
?>
<!DOCTYPE html>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr'?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	</head>
	<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div id="email_container" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'?>" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
							<tr>
								<td align="center" valign="top">

								<!-- Header -->
								<?php
								$email_header_image = isset($email_settings['header_image']) && !empty($email_settings['header_image']) ? $email_settings['header_image'] : false;
								$email_header_bg_color = isset($email_settings['header_bg_color']) && !empty($email_settings['header_bg_color']) ? $email_settings['header_bg_color'] : '#1c5daa';
								$email_header_text_color = isset($email_settings['header_text_color']) && !empty($email_settings['header_text_color']) ? $email_settings['header_text_color'] : '#ffffff';
								?>
								<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header" style='background-color: <?php echo $email_header_bg_color; ?>; border-radius: 3px 3px 0 0 !important; color: <?php echo $email_header_text_color; ?>; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;'>
									<tr>
										<td id="header_wrapper" background="<?php if($email_header_image) echo $email_header_image; ?>" bgcolor="<?php echo $email_header_bg_color; ?>" width="600" height="120" valign="center" style="background-position: center center; background-size: cover; background-repeat: no-repeat;">
	                                        <!--[if gte mso 9]>
	                                        <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:120px;">
	                                            <v:fill type="tile" src="<?php if($email_header_image) echo $email_header_image; ?>" color="<?php echo $email_header_bg_color; ?>" />
	                                            <v:textbox inset="0,0,0,0">
	                                        <![endif]-->
	                                        <div style="padding: 20px 48px; display: block;">
	                                        	<h1 style='color: <?php echo $email_header_text_color; ?>; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0 0 16px 0; text-align: center; text-shadow: 0 1px 0 #7797b4; -webkit-font-smoothing: antialiased; display: block;'>
	                                        		<?php
	                                        		echo isset($email_settings['header_text']) && !empty($email_settings['header_text']) ? esc_html($email_settings['header_text']) : get_bloginfo( 'name', 'display' );
	                                        		?>
	                                        	</h1>
	                                        	<p style='color: <?php echo $email_header_text_color; ?>; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: 300; line-height: 18px; margin: 0; text-align: center; text-shadow: 0 1px 0 #7797b4; -webkit-font-smoothing: antialiased;'>
	                                        		%%NS_MAIL_SUBHEAD%%
	                                        	</p>
	                                        </div>
	                                        <!--[if gte mso 9]>
	                                            </v:textbox>
	                                            </v:rect>
	                                        <![endif]-->
	                                    </td>
	                                </tr>
	                            </table>
	                            <!-- /Header -->

	                            </td>
	                        </tr>
	                        <tr>
	                        	<td align="center" valign="top">

	                        		<!-- Body -->
	                        		<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
	                        			<tr>
	                        				<td valign="top" id="body_content" style="background-color: #fdfdfd;">

	                        					<!-- Content -->
	                        					<table border="0" cellpadding="20" cellspacing="0" width="100%">
	                        						<tr>
	                        							<td valign="top" style="padding: 48px;">
	                        								<div id="body_content_inner" style='color: #737373; font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;'>
	                        									%%NS_MAIL_CONTENT%%
	                        								</div>
	                        							</td>
	                        						</tr>
	                        					</table>
	                        					<!-- /Content -->

	                        				</td>
	                        			</tr>
	                        		</table>
	                        		<!-- /Body -->

	                        	</td>
	                        </tr>
	                    </table>
	                </td>
	            </tr>
	        </table>

	        <!-- Footer -->
	        <table border="0" cellpadding="10" cellspacing="0" width="100%" id="template_footer">
	        	<tr>
	        		<td valign="top" style="padding: 0; -webkit-border-radius: 6px;">
	        			<table border="0" cellpadding="10" cellspacing="0" width="100%">
	        				<tr>
	        					<td colspan="2" valign="middle" id="footer_text" style="padding: 0 48px 48px 48px; -webkit-border-radius: 6px; border: 0; color: #99b1c7; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;">
	        						<?php
	        						/* translators: 1. site title 2. developer company name */
	        						$default_footer_text = sprintf( __('%1$s &mdash; Powered by %2$s', 'nanosupport'), get_bloginfo( 'name', 'display' ), NS()->plugin );
	        						$email_footer_text = isset($email_settings['footer_text']) && !empty($email_settings['footer_text']) ? $email_settings['footer_text'] : $default_footer_text;
	        						?>
	        						<p><?php echo wpautop( wp_kses_post( wptexturize($email_footer_text) ) ); ?></p>
	        					</td> <!-- /#footer_text -->
	        				</tr>
	        			</table>
	        		</td>
	        	</tr>
	        </table>
	        <!-- /Footer -->

	    </div> <!-- /#email_container -->
	</body>
</html>
