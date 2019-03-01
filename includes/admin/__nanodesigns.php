<?php
/**
 * Advertisement
 *
 * Advertisement of nanodesigns on Free version.
 * Visible only on the right side of the NanoSupport Settings page.
 *
 * @author  	nanodesigns
 * @category 	Advertisement
 * @package 	NanoSupport
 */

$ns_general_settings       = get_option( 'nanosupport_settings' );
$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );
?>

<h3><?php echo '<i class="ns-icon-nanosupport" aria-hidden="true"></i> '. __( 'NanoSupport', 'nanosupport' ); ?> <small>&mdash; <?php _e( 'complete support ticketing', 'nanosupport' ); ?></small></h3>
<p><?php echo wp_kses( __( '<strong>NanoSupport</strong> is a complete package for a front-end Support Ticketing System in a complete WordPress&rsquo; way. It has a rich back end for ticket maintenance and management. It has built-in Knowledgebase to sort out information that you want to inform publicly.', 'nanosupport' ), array('strong'=>array()) ); ?></p>
<p class="ns-text-center">
	<a class="button button-primary ns-button-block" href="<?php echo get_the_permalink($ns_general_settings['support_desk']); ?>">
		<i class="icon ns-icon-tag" aria-hidden="true"></i> <?php _e( 'Support Desk', 'nanosupport' ); ?>
	</a>
	&nbsp;
	<?php
	/**
     * Display Knowledgebase on demand
     * Display, if enabled in admin panel.
     */
	if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) { ?>
		<a class="button ns-button-info ns-button-block" href="<?php echo esc_url( get_the_permalink($ns_knowledgebase_settings['page']) ); ?>">
			<i class="icon ns-icon-docs" aria-hidden="true"></i> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
		</a>
	<?php } ?>
</p>

<hr>
<p class="ns-text-center">
	<?php printf( '<a href="https://github.com/nanodesigns/nanosupport/issues/new/choose" target="_blank" rel="noopener">'. __( 'Rate us <span aria-label="Five Star">%s</span>', 'nanosupport' ) .'</a>', '<i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i><i class="ns-icon-star-filled" aria-hidden="true"></i>' ); ?> | <?php printf( '<a href="https://github.com/nanodesigns/nanosupport/issues/new/choose" target="_blank" rel="noopener">'. __( 'Get Support', 'nanosupport' ). '</a>' ); ?> | <a href="https://nanodesignsbd.com?ref=nanosupport" target="_blank" rel="noopener" title="nanodesigns - developer of the plugin"><strong>nano</strong>designs</a>
</p>

<hr>
<a href="https://creativemarket.com/nanodesigns/3461090-NanoSupport-Knowledgebase-Search?ref=nssidebar" title="Get NanoSupport Knowledgebase Search Extension" target="_blank" rel="noopener" style="display: block;">
	<img src="<?php echo NS()->plugin_url(); ?>/assets/images/nskb-search-advertisement.png" alt="Get NanoSupport Knowledgebase Search Extension" style="width: 100%; max-width: 100%;">
</a>
