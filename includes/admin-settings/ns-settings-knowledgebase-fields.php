<?php
/**
 * Settings: Knowledgebase Settings
 *
 * Showing Knowledgebase settings callback, fields, and validation.
 *
 * @author  	nanodesigns
 * @category 	Settings API
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_knowledgebase_settings_section_callback() {
    //echo "Knowledgebase section";
}

function ns_knowledgebase_page_field() {
    $options = get_option('nanosupport_knowledgebase_settings');

    $args = array(
        'hierarchical'  => 0,
        'post_type'     => 'page',
        'post_status'   => 'publish'
    );
    $pages = get_pages( $args );

    if( $pages ) :
    
        echo '<select name="nanosupport_knowledgebase_settings[page]" id="ns_knowledgebase" class="ns-select">';

            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) :
                if( has_shortcode( $page->post_content, 'nanosupport_knowledgebase' ) ) {
                    echo '<option value="'. esc_attr($page->ID) .'" '. selected( $page->ID, $options['page'], false ) .'>'. $page->post_title .'</option>';
                }
            endforeach;
            
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want to display the Knowledgebase. If no page is in the list, create one with the shortcode [nanosupport_knowledgebase] in it.', 'nanosupport' ) .'"></span>';
    
    endif;
}


function ns_doc_terms_field() {
    $options        = get_option('nanosupport_knowledgebase_settings');
    $ns_doc_terms   = get_terms( 'nanodoc_category', array( 'hide_empty' => false ) );

    if( $ns_doc_terms ) :

        echo '<select name="nanosupport_knowledgebase_settings[terms][]" id="ns_doc_terms" class="ns-select" multiple="multiple">';

            echo '<option value="">'. __( 'Select Categories', 'nanosupport' ) .'</option>';
            foreach ( $ns_doc_terms as $term ) :
                $selected = is_array($options['terms']) && in_array( $term->term_id, $options['terms'] ) ? ' selected="selected" ' : '';
                echo '<option value="'. esc_attr($term->term_id) .'" '. $selected .'>'. $term->name .'</option>';
            endforeach;

        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the Knowledgebase categories you want to promote to the knowledgebase head section.', 'nanosupport' ) .'"></span>';

    endif;
}

// Validate Knowledgebase Settings
function ns_knowledgebase_settings_validate( $input ) {
    $options = get_option('nanosupport_knowledgebase_settings');

    //Knowledgebase page selection
    $kb_page_selection_val = $input['page'] ? absint( $input['page'] ) : '';
    //KB Categories selection
    $kb_categories = $input['terms'] ? (array) $input['terms'] : '';

    $options['page'] = absint( $kb_page_selection_val );
    $options['terms'] = (array) $kb_categories;

    return $options;
}