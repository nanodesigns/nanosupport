<?php
/**
 * CPT 'nanodoc' and Taxonomy
 *
 * Functions to initiate the Custom Post Type 'nanodoc'
 * and Taxonomy 'nanodoc_category'.
 *
 * @package Nano Support Ticket
 */

/**
 * CPT
 * 
 * Creating the 'nanosupport' CPT for tickets.
 * 
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function nst_register_cpt_nanodoc() {

    $labels = array(
        'name'					=> __( 'Documentations', 'nano-support-ticket' ),
        'singular_name'			=> __( 'Documentation', 'nano-support-ticket' ),
        'add_new'				=> __( 'Add New', 'nano-support-ticket' ),
        'add_new_item'			=> __( 'Add New Documentation', 'nano-support-ticket' ),
        'edit_item'				=> __( 'Edit Documentation', 'nano-support-ticket' ),
        'new_item'				=> __( 'New Documentation', 'nano-support-ticket' ),
        'view_item'				=> __( 'View Documentation', 'nano-support-ticket' ),
        'search_items'			=> __( 'Search Documentations', 'nano-support-ticket' ),
        'not_found'				=> __( 'No Documentation found', 'nano-support-ticket' ),
        'not_found_in_trash'	=> __( 'No Documentation found in Trash', 'nano-support-ticket' ),
        'parent_item_colon'		=> __( 'Parent Documentation:', 'nano-support-ticket' ),
        'menu_name'				=> __( 'Support Docs', 'nano-support-ticket' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'Make a complete FAQ section supporting your Support Forum', 'nano-support-ticket' ),
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
        'rewrite'				=> array( 'slug' => 'nanodoc' ),
        'capability_type'       => 'post',
        /*'capabilities'          => array(
                                    'edit_post'             => 'edit_nst',
                                    'edit_posts'            => 'edit_nsts',
                                    'edit_others_posts'     => 'edit_other_nsts',
                                    'publish_posts'         => 'publish_nsts',
                                    'read_post'             => 'read_nst',
                                    'read_private_posts'    => 'read_private_nsts',
                                    'delete_post'           => 'delete_nst'
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
add_action( 'init', 'nst_register_cpt_nanodoc' );