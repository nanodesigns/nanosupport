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

$ns_general_settings       = get_option( 'nanosupport_settings' );
$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );
?>

<h3><?php echo '<span class="ns-icon-nanosupport"></span> '. __( 'NanoSupport', 'nanosupport' ); ?> <small>&mdash; <?php _e( 'complete support ticketing', 'nanosupport' ); ?></small></h3>
<p><?php echo wp_kses( __( '<strong>NanoSupport</strong> is a complete package for a front-end Support Ticketing System in a complete WordPress&rsquo; way. It has a rich back end for ticket maintenance and management. It has built-in Knowledgebase to sort out information that you want to inform publicly.', 'nanosupport' ), array('strong'=>array()) ); ?></p>
<p class="ns-text-center">
	<a class="button button-primary ns-button-block" href="<?php echo get_the_permalink($ns_general_settings['support_desk']); ?>">
		<span class="icon ns-icon-tag"></span> <?php _e( 'Support Desk', 'nanosupport' ); ?>
	</a>
	&nbsp;
	<?php
	/**
     * Display Knowledgebase on demand
     * Display, if enabled in admin panel.
     */
	if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
		<a class="button ns-button-info ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_knowledgebase_settings['page']) ); ?>">
			<span class="icon ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
		</a>
	<?php } ?>
</p>

<hr>
<p class="ns-text-center">
	<?php printf( '<a href="https://wordpress.org/support/plugin/nanosupport/reviews#new-post" target="_blank">'. __( 'Rate us %s', 'nanosupport' ) .'</a>', '<i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i><i class="ns-icon-star-filled"></i>' ); ?> | <?php printf( '<a href="https://github.com/nanodesigns/nanosupport/issues/new" targe="_blank">'. __( 'Get Support', 'nanosupport' ). '</a>' ); ?> | <a href="http://nanodesignsbd.com?ref=nanosupport" target="_blank" title="nanodesigns - developer of the plugin"><strong>nano</strong>designs</a>
</p>
