<?php
/**
 * Settings: General Settings
 *
 * Showing general settings callback, fields, and validation.
 *
 * @author  	nanodesigns
 * @category 	Settings API
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ns_general_settings_section_callback() {
    //echo "Basic Section Here";
}

// General Settings : Field 1
function ns_support_desk_field() {
    $options = get_option( 'nanosupport_settings' );

    $args = array(
        'hierarchical'  => 0,
        'post_type'     => 'page',
        'post_status'   => 'publish'
    );
    $pages = get_pages( $args );

    if( $pages ) {
        echo '<select name="nanosupport_settings[support_desk]" id="ns_support_desk" class="ns-select">';
            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) {
                if( has_shortcode( $page->post_content, 'nanosupport_desk' ) ) {
                    echo '<option value="'. $page->ID .'" '. selected( $page->ID, $options['support_desk'], false ) .'>'. $page->post_title .'</option>';
                }
            }
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want to display the Support Desk. If no page is in the list, create one with the shortcode [nanosupport_desk] in it.', 'nanosupport' ) .'"></span>';
    }
}

// General Settings : Field 2
function ns_submit_ticket_field() {
    $options = get_option( 'nanosupport_settings' );

    $args = array(
        'hierarchical'  => 0,
        'post_type'     => 'page',
        'post_status'   => 'publish'
    ); 
    $pages = get_pages($args);

    if( $pages ) {
        echo '<select name="nanosupport_settings[submit_page]" id="ns_submit_ticket" class="ns-select">';
            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) {
                if( has_shortcode( $page->post_content, 'nanosupport_submit_ticket' ) ) {
                    echo '<option value="'. $page->ID .'" '. selected( $page->ID, $options['submit_page'], false ) .'>'. $page->post_title .'</option>';
                }
            }
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want show the Ticket Submission page. If no page is in the list, create one with the shortcode [nanosupport_submit_ticket] in it.', 'nanosupport' ) .'"></span>';
    }
}

// General Settings : Field 3
function ns_bootstrap_field() {
    $options = get_option( 'nanosupport_settings' );

    echo '<input name="nanosupport_settings[bootstrap]" id="ns_bootstrap" type="checkbox" value="1" '. checked( 1, $options['bootstrap'], false ) . '/> <label for="ns_bootstrap">'. __( 'Load Bootstrap CSS (default)', 'nanosupport' ) .'</label>';
    echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'If your theme is designed in Bootstrap, just uncheck here to not to load the file again.', 'nanosupport' ) .'"></span>';
}


// Validate General Settings
function ns_general_settings_validate( $input ) {
    $options = get_option('nanosupport_settings');

    //Support Desk page selection
    $support_desk_selection_val = $input['support_desk'] ? absint( $input['support_desk'] ) : '';
    //Submit Ticket page selection
    $nano_add_support_ticket_val = $input['submit_page'] ? absint( $input['submit_page'] ) : '';
    //Bootstrap checkbox
    $bootstrap_check_val = (int) $input['bootstrap'] === 1 ? (int) $input['bootstrap'] : '';

    $options['support_desk'] = absint( $support_desk_selection_val );
    $options['submit_page'] = absint( $nano_add_support_ticket_val );
    $options['bootstrap'] = absint( $bootstrap_check_val );

    return $options;
}