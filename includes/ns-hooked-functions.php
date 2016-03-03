<?php
/**
 * Hooked Functions
 *
 * All the Functions that are hooked to front end to display
 * conditional contents of NanoSupport.
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hide all the Responses in Comments page (Admin Panel)
 *
 * @link http://wordpress.stackexchange.com/a/56657/22728
 * 
 * @param  array $comments All the comments from comments table.
 * @return array           Filtering 'nanosupport_response' hiding them.
 * -----------------------------------------------------------------------
 */
function ns_knowledgebase_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    ob_start(); ?>
    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-7 text-muted">
                <?php _e( "Find your desired question in the knowledgebase. If you can't find your question, submit a new support ticket.", 'nanosupport' ); ?>
            </div>
            <div class="col-sm-5 text-right">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="btn btn-sm btn-primary">
                    <span class="ns-icon-tag"></span> <?php echo $all_tickets_label; ?>
                </a>
                <a class="btn btn-sm btn-danger btn-submit-new-ticket" href="<?php echo esc_url( get_permalink( $ns_general_settings['submit_page'] ) ); ?>">
                    <span class="ns-icon-tag"></span> <?php _e( 'Submit Ticket', 'nanosupport' ); ?>
                </a>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}
add_action( 'nanosupport_before_knowledgebase', 'ns_knowledgebase_navigation', 10 );