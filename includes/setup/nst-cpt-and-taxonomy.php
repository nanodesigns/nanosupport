<?php
/**
 * CPT and Taxonomy.
 *
 * Functions to initiate the Custom Post Type and Taxonomy.
 *
 * @package nano_support_ticket
 * =======================================================================
 */

/**
 * CPT.
 * 
 * Creating the 'nanosupport' CPT for tickets.
 * 
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function nst_register_cpt_nanosupport() {

    $labels = array(
        'name'					=> __( 'Tickets', 'nanodesigns-nst' ),
        'singular_name'			=> __( 'Ticket', 'nanodesigns-nst' ),
        'add_new'				=> __( 'Add New', 'nanodesigns-nst' ),
        'add_new_item'			=> __( 'Add New Ticket', 'nanodesigns-nst' ),
        'edit_item'				=> __( 'Edit Ticket', 'nanodesigns-nst' ),
        'new_item'				=> __( 'New Ticket', 'nanodesigns-nst' ),
        'view_item'				=> __( 'View Ticket', 'nanodesigns-nst' ),
        'search_items'			=> __( 'Search Ticket', 'nanodesigns-nst' ),
        'not_found'				=> __( 'No Ticket found', 'nanodesigns-nst' ),
        'not_found_in_trash'	=> __( 'No Ticket found in Trash', 'nanodesigns-nst' ),
        'parent_item_colon'		=> __( 'Parent Ticket:', 'nanodesigns-nst' ),
        'menu_name'				=> __( 'Supports', 'nanodesigns-nst' ),
    );

    $args = array(
        'labels'				=> $labels,
        'hierarchical'			=> false,
        'description'			=> __( 'To get ticket information', 'nanodesigns-nst' ),
        'supports'				=> array( 'title', 'editor', 'author' ), //'title', 'editor' (content), 'author', 'thumbnail' (featured image), 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes' (menu order, hierarchical must be true to show Parent option), 'post-formats'
        'taxonomies'            => array( 'nanosupport_departments' ),
        'menu_icon'				=> 'dashicons-universal-access-alt',
        'public'				=> true,
        'show_ui'				=> true,
        'show_in_menu'			=> true,
        'menu_position'			=> 29,
        	'show_in_nav_menus'		=> false,
        'publicly_queryable'	=> true,
        'exclude_from_search'	=> false,
        	'has_archive'			=> false,
        'query_var'				=> true,
        'can_export'			=> true,
        'rewrite'				=> array( 'slug' => 'nanosupport' ),
        'capability_type'		=> 'post'
    );

    register_post_type( 'nanosupport', $args );

    /**
     * To activate CPT Single page
     * @author  Bainternet
     * @link http://en.bainternet.info/2011/custom-post-type-getting-404-on-permalinks
     * ---
     */
    $set = get_option( 'post_type_rules_flased_nanosupport' );
    if ( $set !== true ){
		flush_rewrite_rules( false );
		update_option( 'post_type_rules_flased_nanosupport', true );
    }

}
add_action( 'init', 'nst_register_cpt_nanosupport' );


/**
 * Show Pending Tickets count in CPT.
 *
 * @author  Samik Chattopadhyay
 * @link http://stackoverflow.com/a/8625696/1743124
 *
 * @see  nst_pending_tickets_count()
 * 
 * @return string Menu texts with Pending count.
 * -----------------------------------------------------------------------
 */
function nst_show_pending_count_in_cpt() {
    global $menu;

    $pending_count  = nst_pending_tickets_count();
    $pending_title  = sprintf( '%d Pending Tickets', $pending_count );

    $menu_label     = sprintf( __( 'Supports %s', 'nanodesigns-nst' ), '<span class="update-plugins count-$pending_count" title="'. esc_attr( $pending_title ) .'"><span class="pending-count">'. number_format_i18n($pending_count) .'</span></span>' );

    $fallback_label = __( 'Supports', 'nanodesigns-nst' );

    $menu[29][0] = $pending_count ? $menu_label : $fallback_label;
}
add_action( 'admin_menu', 'nst_show_pending_count_in_cpt' );


/**
 * Register Custom Taxonomy.
 * 
 * Create Custom Taxonomy 'nanosupport_departments' to sort out the tickets.
 * 
 * @return array To register the custom taxonomy.
 * -----------------------------------------------------------------------
 */
function nst_create_nanosupport_taxonomies() {

    $labels = array(
        'name'              => __( 'Departments', 'nanodesigns-nst' ),
        'singular_name'     => __( 'Department Type', 'nanodesigns-nst' ),
        'search_items'      => __( 'Search Departments', 'nanodesigns-nst' ),
        'all_items'         => __( 'All Departments', 'nanodesigns-nst' ),
        'parent_item'       => __( 'Parent Department Type', 'nanodesigns-nst' ),
        'parent_item_colon' => __( 'Parent Department Type:', 'nanodesigns-nst' ),
        'edit_item'         => __( 'Edit Departments', 'nanodesigns-nst' ),
        'update_item'       => __( 'Update Departments', 'nanodesigns-nst' ),
        'add_new_item'      => __( 'Add New Department Type', 'nanodesigns-nst' ),
        'new_item_name'     => __( 'New Department Type Name', 'nanodesigns-nst' ),
        'menu_name'         => __( 'Departments', 'nanodesigns-nst' ),
    );

    $args = array(
        'hierarchical'      => true,
        'public'            => false,
        'show_tagcloud'     => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'support-departments' ),
    );

    register_taxonomy( 'nanosupport_departments', array( 'nanosupport' ), $args );



    /**
     * Insert default term
     *
     * Insert default term 'Support' to the taxonomy 'nanosupport_departments'.
     *
     * Term: Support
     * ---
     */
    wp_insert_term(
        'Support', // the term 
        'nanosupport_departments', // the taxonomy
        array(
            'description'=> 'Support department is dedicated to provide the necessary support',
            'slug' => 'support'
        )
    );

}
add_action( 'init', 'nst_create_nanosupport_taxonomies', 0 );


/**
 * Make a Default Taxonomy Term for 'nanosupport_departments'
 *
 * @link http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
 *
 * @author    Michael Fields     http://wordpress.mfields.org/
 * @props     John P. Bloch      http://www.johnpbloch.com/
 *
 * @since     2010-09-13
 * @alter     2010-09-14
 *
 * @license   GPLv2
 * -----------------------------------------------------------------------
 */
function nst_set_default_object_terms( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
                'nanosupport_departments' => array( 'support' )
            );
        
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}
add_action( 'save_post', 'nst_set_default_object_terms', 100, 2 );