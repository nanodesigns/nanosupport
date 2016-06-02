<?php
/**
 * Advertisement
 *
 * Advertisement of nanodesigns on Free version.
 * Visible only on the right side of the NanoSupport Settings page.
 *
 * @since  		1.0.0
 *
 * @author  	nanodesigns
 * @category 	Advertisement
 * @package 	NanoSupport
 */

$ns_general_settings 		= get_option( 'nanosupport_settings' );
$ns_knowledgebase_settings  = get_option( 'nanosupport_knowledgebase_settings' );

?>

<h3><?php echo '<span class="ns-icon-nanosupport"></span> '. __( 'NanoSupport', 'nanosupport' ); ?> <small>&mdash; <?php _e( 'complete support ticketing plugin', 'nanosupport' ); ?></small></h3>
<p><?php _e( '<strong>NanoSupport</strong> is a complete package for a front-end Support Ticketing System in a complete WordPress&rsquo; way. It has a rich back end for ticket maintenance and management.', 'nanosupport' ); ?></p>
<p class="ns-text-center"><a class="button button-primary ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_general_settings['support_desk']) ); ?>"><span class="icon ns-icon-tag"></span> <?php _e( 'Support Desk', 'nanosupport' ); ?></a> <a class="button ns-button-info ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_knowledgebase_settings['page']) ); ?>"><strong><span class="icon ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?></strong></a></p>

<hr>
<p class="ns-text-center"><?php printf( __( '<a href="%1s" targe="_blank">Rate us %2s</a>', 'nanosupport' ), 'https://wordpress.org/support/view/plugin-reviews/nanosupport#postform' , '<i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i>' ); ?> | <?php printf( __( '<a href="%1s" targe="_blank">Get Support</a>', 'nanosupport' ), 'https://wordpress.org/support/plugin/nanosupport' ); ?> | <a href="http://nanodesignsbd.com?ref=nanosupport" target="_blank" title="nanodesigns - developer of the plugin"><strong>nano</strong>designs</a></p>