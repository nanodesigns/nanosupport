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

/**
 * Callback: General Settings section
 */
function ns_general_settings_section_callback() {
    echo '<p class="screen-reader-text">'. __( 'All the general settings including the Support Desk and Support Ticket submission page setup, and other plugin-specific setups.', 'nanosupport' ) .'</p>';
}

// General Tab : General Settings : Field 1 : Support Desk
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
        echo ns_tooltip( __( 'Choose the page where you want to display the Support Desk. If no page is in the list, create one with the shortcode <code>[nanosupport_desk]</code> in it.', 'nanosupport' ), 'right' );
    }
}

// General Tab : General Settings : Field 2 : Submit Ticket
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
        echo ns_tooltip( __( 'Choose the page where you want show the Ticket Submission page. If no page is in the list, create one with the shortcode <code>[nanosupport_submit_ticket]</code> in it.', 'nanosupport' ), 'right' );
    }
}

// General Tab : General Settings : Field 3 : Enable Notice?
function ns_ticket_character_limit() {
    $options = get_option( 'nanosupport_settings' );

    //set the default to 30 characters
    $value = isset($options['ticket_char_limit']) ? $options['ticket_char_limit'] : 30;

    echo '<input type="number" name="nanosupport_settings[ticket_char_limit]" step="1" min="0" id="ticket_char_limit" class="small-text" value="'. absint($value) .'">';

    echo ns_tooltip( __( 'Define the character limit on ticket submission form. <code>0</code> (zero) denotes no character limit applied.', 'nanosupport' ), 'right' );
}

// General Tab : General Settings : Field 4 : Enable Notice?
function ns_enable_notice() {
    $options = get_option( 'nanosupport_settings' );

    $enable_notice = isset($options['enable_notice']) ? $options['enable_notice'] : '';
    echo '<input name="nanosupport_settings[enable_notice]" id="enable_notice" type="checkbox" value="1" '. checked( 1, $enable_notice, false ) . '/> <label for="enable_notice">'. __( 'Yes, enable Notice and Navigation on top of NanoSupport Pages', 'nanosupport' ) .'</label>';
    echo ns_tooltip( __( 'If you check here, it will enable the predefined notice on top of Support Desk, Knowledgebase, and Submit Ticket page', 'nanosupport' ), 'right' );
}

// General Tab : General Settings : Field 5 : Submit Ticket Notice
function ns_submit_ticket_notice() {
    $options = get_option( 'nanosupport_settings' );

    global $ns_submit_ticket_notice;
    
    $submit_ticket_notice = isset($options['submit_ticket_notice']) ? $options['submit_ticket_notice'] : $ns_submit_ticket_notice;
    echo '<textarea class="ns-field-item ns-textbox" rows="3" name="nanosupport_settings[submit_ticket_notice]" id="submit_ticket_notice">'. esc_attr(strip_tags($submit_ticket_notice)) .'</textarea>';
    echo ns_tooltip( __( 'The message that to show on top of the New Ticket submission page. Plain texts only.', 'nanosupport' ), 'right' );
}

// General Tab : General Settings : Field 6 : Support Desk Notice
function ns_support_desk_notice() {
    $options = get_option( 'nanosupport_settings' );

    global $ns_support_desk_notice;
    
    $support_desk_notice = isset($options['support_desk_notice']) ? $options['support_desk_notice'] : $ns_support_desk_notice;
    echo '<textarea class="ns-field-item ns-textbox" rows="3" name="nanosupport_settings[support_desk_notice]" id="support_desk_notice">'. esc_attr(strip_tags($support_desk_notice)) .'</textarea>';
    echo ns_tooltip( __( 'The message that to show on top of the Support Ticket page. Plain texts only.', 'nanosupport' ), 'right' );
}

// General Tab : General Settings : Field 7 : Knowledgebase Notice
function ns_knowledgebase_notice() {
    $options = get_option( 'nanosupport_settings' );
    
    //class to handle out-of-DOM field show/hide control
    $ns_knowledgebase_settings = get_option('nanosupport_knowledgebase_settings');
    $enable_knowledgebase = isset($ns_knowledgebase_settings['isactive_kb']) ? absint($ns_knowledgebase_settings['isactive_kb']) : true;
    $hide_class =  $enable_knowledgebase ? '' : ' ns-hide';

    global $ns_knowledgebase_notice;
    
    $knowledgebase_notice = isset($options['knowledgebase_notice']) ? $options['knowledgebase_notice'] : $ns_knowledgebase_notice;
    echo '<textarea class="ns-field-item ns-textbox '. esc_attr($hide_class) .'" rows="3" name="nanosupport_settings[knowledgebase_notice]" id="knowledgebase_notice">'. esc_attr(strip_tags($knowledgebase_notice)) .'</textarea>';
    echo ns_tooltip( __( 'The message that to show on top of the Knowledgebase page. Plain texts only.', 'nanosupport' ), 'right' );
}

// General Tab : General Settings : Field 8 : Highlight Ticket
function ns_highlight_ticket_field() {
    $options = get_option( 'nanosupport_settings' );

    $highlight_option = isset($options['highlight_ticket']) ? $options['highlight_ticket'] : 'status';
    echo '<select name="nanosupport_settings[highlight_ticket]" id="ns_highlight_ticket" class="ns-select">';
        echo '<option value="status" '. selected( 'status', $highlight_option, false ) .'>'. __( 'Ticket Status', 'nanosupport' ) .'</option>';
        echo '<option value="priority" '. selected( 'priority', $highlight_option, false ) .'>'. __( 'Ticket Priority', 'nanosupport' ) .'</option>';
    echo '</select>';
    echo ns_tooltip( __( 'Choose on which you want to highlight your ticket. Default: status', 'nanosupport' ), 'right' );

}

// General Tab : General Settings : Field 9 : Enable Embedded Login?
function ns_embedded_login_field() {
    $options = get_option( 'nanosupport_settings' );

    $embedded_login = isset($options['embedded_login']) ? $options['embedded_login'] : '';
    echo '<input name="nanosupport_settings[embedded_login]" id="embedded_login" type="checkbox" value="1" '. checked( 1, $embedded_login, false ) . '/> <label for="embedded_login">'. __( 'Yes, enable Login below ticket submission form', 'nanosupport' ) .' <sup class="ns-red"><strong>('. __( 'BETA FEATURE', 'nanosupport' ) .')</strong></sup></label>';
    echo ns_tooltip( __( 'If you check here, it will enable login form below ticket submission form. Please note that, it&rsquo;s a beta feature till now', 'nanosupport' ), 'right' );
}


/**
 * Callback: Account Settings Section
 */
function ns_account_settings_section_callback() {
    echo '<p class="screen-reader-text">'. __( 'All the user account related settings can be managed from here.', 'nanosupport' ) .'</p>';
}

// General Tab : Account Settings : Field 1 : Account Creation
function ns_account_creation_field() {
    $options = get_option( 'nanosupport_settings' );

    $gen_username_val = isset($options['account_creation']['generate_username']) ? $options['account_creation']['generate_username'] : '';
    echo '<input name="nanosupport_settings[account_creation][generate_username]" id="generate_username" type="checkbox" value="1" '. checked( 1, $gen_username_val, false ) . '/> <label for="generate_username">'. __( 'Yes, automatically generate username from user email', 'nanosupport' ) .'</label>';
    echo ns_tooltip( __( 'If you check here, an automatted username will be created for the user from their email', 'nanosupport' ), 'right' );

    echo '<br><br>';

    $gen_password_val = isset($options['account_creation']['generate_password']) ? $options['account_creation']['generate_password'] : '';
    echo '<input name="nanosupport_settings[account_creation][generate_password]" id="generate_password" type="checkbox" value="1" '. checked( 1, $gen_password_val, false ) . '/> <label for="generate_password">'. __( 'Yes, automatically generate password for the user', 'nanosupport' ) .'</label>';
    echo ns_tooltip( __( 'If you check here, a password will be automatically created for the user', 'nanosupport' ), 'right' );
}


// General Tab : Other Settings : Field 1 : Delete Data?
function ns_delete_data_field() {
    $options = get_option( 'nanosupport_settings' );

    $del_data_val = isset($options['delete_data']) ? $options['delete_data'] : '';
    echo '<input name="nanosupport_settings[delete_data]" id="ns_delete_data" type="checkbox" value="1" '. checked( 1, $del_data_val, false ) . '/> <label for="ns_delete_data" class="ns-red"><strong>'. __( 'Delete all the Data on Uninstallation?', 'nanosupport' ) .'</strong></label>';
    echo ns_tooltip( __( 'If you check here, on uninstallation of the plugin, it will wipe out all the data from the database', 'nanosupport' ), 'right' );
}


/**
 * Callback: Other Settings Section
 */
function ns_other_settings_section_callback() {
    echo '<p class="screen-reader-text">'. __( 'Miscellaneous settings can be managed from here.', 'nanosupport' ) .'</p>';
}


/**
 * Validate General Settings
 * 
 * @param  array $input  Array of all the settings fields' value.
 * @return array         Validated settings fields.
 */
function ns_general_settings_validate( $input ) {
    $options = get_option('nanosupport_settings');

    //Support Desk page selection
    $support_desk_selection_val = $input['support_desk'] ? absint( $input['support_desk'] ) : '';
    //Submit Ticket page selection
    $add_support_ticket_val     = $input['submit_page'] ? absint( $input['submit_page'] ) : '';
    //Ticket character limit
    $ticket_char_limit          = $input['ticket_char_limit'] ? absint($input['ticket_char_limit']) : '';
    //Enable Notice checkbox
    $enable_notice              = (int) $input['enable_notice'] === 1 ? absint($input['enable_notice']) : '';
    //Submit Ticket notice message
    $submit_ticket_notice       = $input['submit_ticket_notice'] ? $input['submit_ticket_notice'] : '';
    //Support Desk notice message
    $support_desk_notice        = $input['support_desk_notice'] ? $input['support_desk_notice'] : '';
    //Knowledgebase notice message
    $knowledgebase_notice       = $input['knowledgebase_notice'] ? $input['knowledgebase_notice'] : '';
    //Enable Embedded Login checkbox
    $embedded_login = isset($input['embedded_login']) && (int) $input['embedded_login'] === 1 ? (int) $input['embedded_login'] : '';
    //Highlight ticket choice
    $highlight_ticket_val = $input['highlight_ticket'] ? esc_html($input['highlight_ticket']) : 'status';
    
    //Generate Username checkbox
    $generate_username = isset($input['account_creation']['generate_username']) && (int) $input['account_creation']['generate_username'] === 1 ? (int) $input['account_creation']['generate_username'] : '';
    //Generate Password checkbox
    $generate_password = isset($input['account_creation']['generate_password']) && (int) $input['account_creation']['generate_password'] === 1 ? (int) $input['account_creation']['generate_password'] : '';
    
    //Delete Data checkbox
    $del_data_check_val = (int) $input['delete_data'] === 1 ? (int) $input['delete_data'] : '';

    /**
     * Set the values finally
     */
    $options['support_desk']         = absint( $support_desk_selection_val );
    $options['submit_page']          = absint( $add_support_ticket_val );
    $options['ticket_char_limit']    = absint( $ticket_char_limit );
    $options['enable_notice']        = absint( $enable_notice );
    $options['submit_ticket_notice'] = esc_attr(strip_tags($submit_ticket_notice));
    $options['support_desk_notice']  = esc_attr(strip_tags($support_desk_notice));
    $options['knowledgebase_notice'] = esc_attr(strip_tags($knowledgebase_notice));
    $options['highlight_ticket']     = esc_html( $highlight_ticket_val );
    $options['embedded_login']       = absint( $embedded_login );

    $options['account_creation']['generate_username'] = absint( $generate_username );
    $options['account_creation']['generate_password'] = absint( $generate_password );

    $options['delete_data']          = absint( $del_data_check_val );

    return $options;
}
