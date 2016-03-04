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
 * Navigation on Knowledgebase
 *
 * Disply a NanoSupport navigation on the Knowledgebase.
 *
 * hooked: nanosupport_before_knowledgebase (10)
 * 
 * @return void
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


/**
 * Navigation on New Ticket
 *
 * Disply a NanoSupport navigation on the New Ticket page.
 *
 * hooked: nanosupport_before_new_ticket (10)
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_new_ticket_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    ob_start(); ?>

    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-5">
                <?php
                if( current_user_can('administrator') || current_user_can('editor') )
                    $all_tickets_label = __( 'All the Tickets', 'nanosupport' );
                else
                    $all_tickets_label = __( 'My Tickets', 'nanosupport' );             
                ?>

                <a href="<?php echo esc_url( get_permalink( $ns_general_settings['support_desk'] ) ); ?>" class="btn btn-sm btn-primary">
                    <span class="ns-icon-tag"></span> <?php esc_html_e( $all_tickets_label ); ?>
                </a>
                <a class="btn btn-sm btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( get_page_by_path( 'knowledgebase' ) ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
                </a>
            </div>
            <div class="col-sm-7 text-muted">
                <small><?php _e( 'Consult the Knowledgebase for your query. If they are <em>not</em> close to you, then submit a new ticket here.', 'nanosupport' ); ?></small>
            </div>
        </div>
    </div>
    
    <?php
    echo ob_get_clean();
}

add_action( 'nanosupport_before_new_ticket', 'ns_new_ticket_navigation', 10 );


/**
 * Navigation on Support Desk
 *
 * Disply a NanoSupport navigation on the Support Desk page.
 *
 * hooked: nanosupport_before_support_desk (10)
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_support_desk_navigation() {

    //Get the NanoSupport Settings from Database
    $ns_general_settings = get_option( 'nanosupport_settings' );

    ob_start(); ?>

    <div class="well well-sm">
        <div class="row">
            <div class="col-sm-7 text-muted">
                <?php _e( 'Tickets are visible to the admins, designated support assistant and/or to the ticket owner only.', 'nanosupport' ); ?>
            </div>
            <div class="col-sm-5 text-right">
                <a class="btn btn-sm btn-info btn-knowledgebase" href="<?php echo esc_url( get_permalink( get_page_by_path( 'knowledgebase' ) ) ); ?>">
                    <span class="ns-icon-docs"></span> <?php _e( 'Knowledgebase', 'nanosupport' ); ?>
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

add_action( 'nanosupport_before_support_desk', 'ns_support_desk_navigation', 10 );
