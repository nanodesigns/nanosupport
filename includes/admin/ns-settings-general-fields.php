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

// Callback: General Settings page
function ns_general_settings_section_callback() {
    echo '<p>'. __( 'All the general settings including the Support Desk and Support Ticket submission page setup, and other plugin-specific setups.', 'nanosupport' ) .'</p>';
}

// General Settings : Field 1 : Support Desk
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

// General Settings : Field 2 : Submit Ticket
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

// General Settings : Field 3 : Delete Data?
function ns_delete_data_field() {
    $options = get_option( 'nanosupport_settings' );

    echo '<input name="nanosupport_settings[delete_data]" id="ns_delete_data" type="checkbox" value="1" '. checked( 1, $options['delete_data'], false ) . '/> <label for="ns_delete_data">'. __( 'Delete all the Data on Uninstallation?', 'nanosupport' ) .'</label>';
    echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'If you check here, on uninstallation of the plugin, it will wipe out all the data from the database', 'nanosupport' ) .'"></span>';
}


/**
 * Validate General Settings
 * @param  array $input  Array of all the settings fields' value.
 * @return array         Validated settings fields.
 */
function ns_general_settings_validate( $input ) {
    $options = get_option('nanosupport_settings');

    //Support Desk page selection
    $support_desk_selection_val = $input['support_desk'] ? absint( $input['support_desk'] ) : '';
    //Submit Ticket page selection
    $nano_add_support_ticket_val = $input['submit_page'] ? absint( $input['submit_page'] ) : '';
    //Delete Data checkbox
    $del_data_check_val = (int) $input['delete_data'] === 1 ? (int) $input['delete_data'] : '';

    $options['support_desk']    = absint( $support_desk_selection_val );
    $options['submit_page']     = absint( $nano_add_support_ticket_val );
    $options['delete_data']     = absint( $del_data_check_val );

    return $options;
}
