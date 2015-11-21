<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_knowledgebase_settings_section_callback() {
	//echo "Knowledgebase section";
}

function ns_knowledgebase_page_field() {
    $options = get_option('nanosupport_knowledgebase_settings');

    $args = array(
        'hierarchical'	=> 0,
        'post_type'		=> 'page',
        'post_status'	=> 'publish'
    );
    $pages = get_pages( $args );

    if( $pages ) {
        echo '<select name="nanosupport_settings[knowledgebase]" id="ns_knowledgebase" class="ns-select">';
            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) {
                if( has_shortcode( $page->post_content, 'nanosupport_knowledgebase' ) ) {
                    echo '<option value="'. $page->ID .'" '. selected( $page->ID, $options['knowledgebase'], false ) .'>'. $page->post_title .'</option>';
                }
            }
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want to display the Knowledgebase. If no page is in the list, create one with the shortcode [nanosupport_knowledgebase] in it.', 'nanosupport' ) .'"></span>';
    }
}