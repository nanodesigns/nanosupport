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

// Knowledgebase Settings : Field 1 : isActive Knowledgebase?
function ns_isactive_knowledgebase_field() {
	$options = get_option('nanosupport_knowledgebase_settings');

	$isactive_kb = isset($options['isactive_kb']) ? $options['isactive_kb'] : '';
	echo '<input name="nanosupport_knowledgebase_settings[isactive_kb]" id="isactive_kb" type="checkbox" value="1" aria-describedby="ns-isactive-kb" '. checked( 1, $isactive_kb, false ) . '/> <label for="isactive_kb">'. __( 'Yes, enable Knowledgebase', 'nanosupport' ) .'</label>';
	echo ns_tooltip( 'ns-isactive-kb', __( 'Check or uncheck the Knowledgebase feature of NanoSupport', 'nanosupport' ), 'right' );
}

// Knowledgebase Settings : Field 2 : Knowledgebase
function ns_knowledgebase_page_field() {
	$options = get_option('nanosupport_knowledgebase_settings');

	$args = array(
		'hierarchical'  => 0,
		'post_type'     => 'page',
		'post_status'   => 'publish'
	);
	$pages = get_pages( $args );

	if( $pages ) :

		echo '<select name="nanosupport_knowledgebase_settings[page]" id="ns_knowledgebase" class="ns-select" aria-describedby="ns-kb-page">';

			echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';
			foreach ( $pages as $page ) :
				if( has_shortcode( $page->post_content, 'nanosupport_knowledgebase' ) ) {
					echo '<option value="'. esc_attr($page->ID) .'" '. selected( $page->ID, $options['page'], false ) .'>'. $page->post_title .'</option>';
				}
			endforeach;

		echo '</select>';
		/* translators: Knowledgebase page shortcode */
		echo ns_tooltip( 'ns-kb-page', sprintf( __( 'Choose the page where you want to display the Knowledgebase. If no page is in the list, create one with the shortcode %s in it.', 'nanosupport' ), '<code>[nanosupport_knowledgebase]</code>' ), 'right' );

	endif;
}

// Knowledgebase Settings : Field 3 : Featured Categories
function ns_doc_terms_field() {
	$options        = get_option('nanosupport_knowledgebase_settings');
	$ns_doc_terms   = get_terms( 'nanodoc_category', array( 'hide_empty' => false ) );

	echo '<select name="nanosupport_knowledgebase_settings[terms][]" id="ns_doc_terms" class="ns-select-search" multiple="multiple" data-placeholder="'. esc_attr__( 'Select Categories', 'nanosupport' ) .'" aria-describedby="ns-kb-terms">';

		echo '<option value="">'. __( 'Select Categories', 'nanosupport' ) .'</option>';
		foreach ( $ns_doc_terms as $term ) :
			$selected = is_array($options['terms']) && in_array( $term->term_id, $options['terms'] ) ? ' selected="selected" ' : '';
			echo '<option value="'. esc_attr($term->term_id) .'" '. $selected .'>'. $term->name .'</option>';
		endforeach;

	echo '</select>';
	echo ns_tooltip( 'ns-kb-terms', __( 'Choose the Knowledgebase categories you want to promote to the knowledgebase head section.', 'nanosupport' ), 'right' );
}

// Knowledgebase Settings : Field 4 : Posts per Category
function ns_doc_ppc_field() {
	$options = get_option('nanosupport_knowledgebase_settings');

    //set the default to Settings > Reading > Posts Per Page
	$value = isset($options['ppc']) ? $options['ppc'] : get_option('posts_per_page');

	echo '<input type="number" name="nanosupport_knowledgebase_settings[ppc]" step="1" min="1" id="ns_doc_ppc" class="small-text" value="'. absint($value) .'" aria-describedby="ns-kb-ppc">';

	echo ns_tooltip( 'ns-kb-ppc', __( 'Choose the number of entries to display per Knowledgebase category. Default is Settings &raquo; Reading &raquo; Blog pages show at most.', 'nanosupport' ), 'right' );
}

// Knowledgebase Settings : Field 5 : Rewrite URL
function ns_doc_url_rewrite_field() {
	$options = get_option('nanosupport_knowledgebase_settings');

	$rewrite_url = isset($options['rewrite_url']) ? $options['rewrite_url'] : '';
	echo '<input name="nanosupport_knowledgebase_settings[rewrite_url]" id="rewrite_url" type="checkbox" value="1" aria-describedby="ns-kb-url-rewrite" '. checked( 1, $rewrite_url, false ) . '/> <label for="rewrite_url">'. __( 'Yes, rewrite Knowledgebase Doc URL (caution required)', 'nanosupport' ) .'</label>';

	echo ns_tooltip( 'ns-kb-url-rewrite', __( 'Check to add Knowledgebase categories to the URL of Knowledgebase Doc entries.<br><strong>WARNING:</strong> changing public URL might lead huge amount of 404 and can affect SEO, if not properly redirected.', 'nanosupport' ), 'right' );
}

/**
 * Validate Knowledgebase Settings
 * @param  array $input  Array of all the settings fields' value.
 * @return array         Validated settings fields.
 */
function ns_knowledgebase_settings_validate( $input ) {
	$options = get_option('nanosupport_knowledgebase_settings');

    //Enable Knowledgebase checkbox
	$isactive_kb           = isset($input['isactive_kb']) && (int) $input['isactive_kb'] === 1 ? (int) $input['isactive_kb'] : '';
	//Knowledgebase page selection
	$kb_page_selection_val = $input['page'] ? absint( $input['page'] ) : '';
	//Knowledgebase Featured Categories choice
	$kb_categories         = $input['terms'] ? (array) $input['terms'] : '';
	//Knowledgebase items per category
	$kb_posts_per_category = $input['ppc'] ? absint($input['ppc']) : '';
	//Enable Knowledgebase URL Rewriting
	$rewrite_url           = isset($input['rewrite_url']) && (int) $input['rewrite_url'] === 1 ? (int) $input['rewrite_url'] : '';

	$options['isactive_kb'] = absint( $isactive_kb );
	$options['page']        = absint( $kb_page_selection_val );
	$options['terms']       = (array) $kb_categories;
	$options['ppc']         = absint( $kb_posts_per_category );
	$options['rewrite_url'] = absint( $rewrite_url );

	return $options;
}
