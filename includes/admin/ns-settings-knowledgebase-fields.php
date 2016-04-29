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

// Callback: Knowledgebase Settings Page
function ns_knowledgebase_settings_section_callback() {
    echo '<p class="screen-reader-text">'. __( 'Knowledgebase is to store all the Frequently Asked Questions (FAQs) for public access to learn/research deeply about your products/services.', 'nanosupport' ) .'</p>';
}

// Knowledgebase Settings : Field 1 : Knowledgebase
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
        echo ns_tooltip( __( 'Choose the page where you want to display the Knowledgebase. If no page is in the list, create one with the shortcode <code>[nanosupport_knowledgebase]</code> in it.', 'nanosupport' ) );
    
    endif;
}

// Knowledgebase Settings : Field 2 : Featured Categories
function ns_doc_terms_field() {
    $options        = get_option('nanosupport_knowledgebase_settings');
    $ns_doc_terms   = get_terms( 'nanodoc_category', array( 'hide_empty' => false ) );

    echo '<select name="nanosupport_knowledgebase_settings[terms][]" id="ns_doc_terms" class="ns-select" multiple="multiple">';

        echo '<option value="">'. __( 'Select Categories', 'nanosupport' ) .'</option>';
        foreach ( $ns_doc_terms as $term ) :
            $selected = is_array($options['terms']) && in_array( $term->term_id, $options['terms'] ) ? ' selected="selected" ' : '';
            echo '<option value="'. esc_attr($term->term_id) .'" '. $selected .'>'. $term->name .'</option>';
        endforeach;

    echo '</select>';
    echo ns_tooltip( __( 'Choose the Knowledgebase categories you want to promote to the knowledgebase head section.', 'nanosupport' ) );
}

// Knowledgebase Settings : Field 3 : Posts per Category
function ns_doc_ppc_field() {
    $options        = get_option('nanosupport_knowledgebase_settings');

    //set the default to Settings > Reading > Posts Per Page
    $value = isset($options['ppc']) ? $options['ppc'] : get_option('posts_per_page');

    echo '<input type="number" name="nanosupport_knowledgebase_settings[ppc]" step="1" min="1" id="ns_doc_ppc" class="small-text" value="'. intval($value) .'">';

    echo ns_tooltip( __( 'Choose the number of entries to display per Knowledgebase category. Default is Settings &raquo; Reading &raquo; Blog pages show at most.', 'nanosupport' ) );
}

/**
 * Validate Knowledgebase Settings
 * @param  array $input  Array of all the settings fields' value.
 * @return array         Validated settings fields.
 */
function ns_knowledgebase_settings_validate( $input ) {
    $options = get_option('nanosupport_knowledgebase_settings');

    //Knowledgebase page selection
    $kb_page_selection_val = $input['page'] ? absint( $input['page'] ) : '';
    //Knowledgebase Featured Categories choice
    $kb_categories = $input['terms'] ? (array) $input['terms'] : '';
    //Knowledgebase items per category
    $kb_posts_per_category = $input['ppc'] ? (int) $input['ppc'] : '';

    $options['page']    = absint( $kb_page_selection_val );
    $options['terms']   = (array) $kb_categories;
    $options['ppc']     = intval($kb_posts_per_category);

    return $options;
}
