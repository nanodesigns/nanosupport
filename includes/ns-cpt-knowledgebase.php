<?php
/**
 * CPT 'nanodoc' and Taxonomy
 *
 * Functions to initiate the Custom Post Type 'nanodoc'
 * and Taxonomy 'nanodoc_category'.
 *
 * @package NanoSupport
 */

/**
 * CPT
 * 
 * Creating the 'nanosupport' CPT for tickets.
 * 
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function ns_register_cpt_nanodoc() {

    $labels = array(
        'name'					=> __( 'Knowledgebase', 'nanosupport' ),
        'singular_name'			=> __( 'Knowledgebase', 'nanosupport' ),
        'add_new'				=> __( 'Add New', 'nanosupport' ),
        'add_new_item'			=> __( 'Add New Knowledgebase', 'nanosupport' ),
        'edit_item'				=> __( 'Edit Knowledgebase', 'nanosupport' ),
        'new_item'				=> __( 'New Knowledgebase', 'nanosupport' ),
        'view_item'				=> __( 'View Knowledgebase', 'nanosupport' ),
        'search_items'			=> __( 'Search Knowledgebase', 'nanosupport' ),
        'not_found'				=> __( 'No Knowledgebase found', 'nanosupport' ),
        'not_found_in_trash'	=> __( 'No Knowledgebase found in Trash', 'nanosupport' ),
        'parent_item_colon'		=> __( 'Parent Knowledgebase:', 'nanosupport' ),
        'menu_name'				=> __( 'Knowledgebase', 'nanosupport' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'Make a complete FAQ section supporting your Support Forum', 'nanosupport' ),
        'supports'				=> array( 'title', 'editor' ),
        'taxonomies'            => array( 'nanodoc_category' ),
        'menu_icon'				=> 'dashicons-book-alt',
        'public'				=> true,
        'show_ui'				=> true,
        'show_in_menu'			=> true,
        'menu_position'			=> 30,
        	'show_in_nav_menus'		=> false,
        'publicly_queryable'	=> true,
        'exclude_from_search'	=> false,
        	'has_archive'			=> false,
        'query_var'				=> true,
        'can_export'			=> true,
        'rewrite'				=> array( 'slug' => 'knowledgebase' ),
        'capability_type'       => 'post',
        /*'capabilities'          => array(
                                    'edit_post'             => 'edit_ns',
                                    'edit_posts'            => 'edit_nss',
                                    'edit_others_posts'     => 'edit_other_nss',
                                    'publish_posts'         => 'publish_nss',
                                    'read_post'             => 'read_ns',
                                    'read_private_posts'    => 'read_private_nss',
                                    'delete_post'           => 'delete_ns'
                                ),
        'map_meta_cap'          => true*/
    );

    if( !post_type_exists( 'nanodoc' ) )
        register_post_type( 'nanodoc', $args );

    /**
     * To activate CPT Single page
     * @author  Bainternet
     * @link http://en.bainternet.info/2011/custom-post-type-getting-404-on-permalinks
     * ---
     */
    $set = get_option( 'post_type_rules_flased_nanodoc' );
    if ( $set !== true ){
		flush_rewrite_rules( false );
		update_option( 'post_type_rules_flased_nanodoc', true );
    }

}
add_action( 'init', 'ns_register_cpt_nanodoc' );



/**
 * Register Custom Taxonomy
 * 
 * Create Custom Taxonomy 'nanodoc_category' to sort out the tickets.
 * 
 * @return array To register the custom taxonomy.
 * -----------------------------------------------------------------------
 */
function ns_create_nanodoc_taxonomies() {

    $cat_labels = array(
        'name'              => __( 'Categories', 'nanosupport' ),
        'singular_name'     => __( 'Category', 'nanosupport' ),
        'search_items'      => __( 'Search Categories', 'nanosupport' ),
        'all_items'         => __( 'All Categories', 'nanosupport' ),
        'parent_item'       => __( 'Parent Category', 'nanosupport' ),
        'parent_item_colon' => __( 'Parent Category:', 'nanosupport' ),
        'edit_item'         => __( 'Edit Categories', 'nanosupport' ),
        'update_item'       => __( 'Update Categories', 'nanosupport' ),
        'add_new_item'      => __( 'Add New Category', 'nanosupport' ),
        'new_item_name'     => __( 'New Category Name', 'nanosupport' ),
        'menu_name'         => __( 'Categories', 'nanosupport' ),
    );

    $cat_args = array(
        'hierarchical'      => true,
        'public'            => true,
        'show_tagcloud'     => false,
        'labels'            => $cat_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'kb-category' ),
    );

    if( !taxonomy_exists( 'nanodoc_category' ) )
        register_taxonomy( 'nanodoc_category', array( 'nanodoc' ), $cat_args );


    $tag_labels = array(
        'name'              => __( 'Tags', 'nanosupport' ),
        'singular_name'     => __( 'Tag', 'nanosupport' ),
        'search_items'      => __( 'Search Tags', 'nanosupport' ),
        'all_items'         => __( 'All Tags', 'nanosupport' ),
        'parent_item'       => __( 'Parent Tag', 'nanosupport' ),
        'parent_item_colon' => __( 'Parent Tag:', 'nanosupport' ),
        'edit_item'         => __( 'Edit Tags', 'nanosupport' ),
        'update_item'       => __( 'Update Tags', 'nanosupport' ),
        'add_new_item'      => __( 'Add New Tag', 'nanosupport' ),
        'new_item_name'     => __( 'New Tag Name', 'nanosupport' ),
        'menu_name'         => __( 'Tags', 'nanosupport' ),
    );

    $tag_args = array(
        'hierarchical'      => false,
        'public'            => true,
        'show_tagcloud'     => true,
        'labels'            => $tag_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'kb-tag' ),
    );

    if( !taxonomy_exists( 'nanodoc_tag' ) )
        register_taxonomy( 'nanodoc_tag', array( 'nanodoc' ), $tag_args );

}
add_action( 'init', 'ns_create_nanodoc_taxonomies', 0 );