<?php
/**
 * CPT 'nanodoc' and Taxonomy
 *
 * Functions to initiate the Custom Post Type 'nanodoc'
 * and the Taxonomy 'nanodoc_category'.
 *
 * @author      nanodesigns
 * @category    Knowledgebase
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register CPT Knowledgebase
 * 
 * Creating the custom post type 'nanosupport' for tickets.
 *
 * @since  1.0.0
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

}

add_action( 'init', 'ns_register_cpt_nanodoc' );



/**
 * Register Custom Taxonomy Knowledgebase Category
 * 
 * Create Custom Taxonomy 'nanodoc_category' to sort out the
 * knowledgebase documents.
 *
 * @since  1.0.0
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

}

add_action( 'init', 'ns_create_nanodoc_taxonomies', 0 );


/**
 * Change the 'Post Title' in Admin
 *
 * @since  1.0.0
 * 
 * @param  string $title Default string.
 * @return string        Modified string.
 * -----------------------------------------------------------------------
 */
function ns_change_nanodoc_title_text( $title ){
     $screen = get_current_screen();
 
     if  ( 'nanodoc' === $screen->post_type ) {
          $title = __( 'Knowledgebase Question', 'nanosupport' );
     }
 
     return $title;
}

add_filter( 'enter_title_here', 'ns_change_nanodoc_title_text' );
