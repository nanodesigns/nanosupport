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
        'add_new_item'			=> __( 'Add New Doc', 'nanosupport' ),
        'edit_item'				=> __( 'Edit Doc', 'nanosupport' ),
        'new_item'				=> __( 'New Doc', 'nanosupport' ),
        'view_item'				=> __( 'View Doc', 'nanosupport' ),
        'search_items'			=> __( 'Search Knowledgebase', 'nanosupport' ),
        'not_found'				=> __( 'No Knowledgebase Doc found', 'nanosupport' ),
        'not_found_in_trash'	=> __( 'No Knowledgebase Doc found in Trash', 'nanosupport' ),
        'parent_item_colon'		=> __( 'Parent Doc:', 'nanosupport' ),
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
        'capability_type'       => 'nanodoc',
        'map_meta_cap'          => true
    );

    if( ! post_type_exists( 'nanodoc' ) ) {
        register_post_type( 'nanodoc', $args );
    }

}

//get Knowledgebase settings from db
$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

/**
 * Initiate CPT Knowledgebase on demand
 * Display, if enabled in admin panel.
 */
if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
    add_action( 'init', 'ns_register_cpt_nanodoc' );
}



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
        'rewrite'           => array( 'slug' => 'knowledgebase-category' ),
        'capabilities'      => array(
                                'manage_terms' => 'manage_nanodoc_terms',
                                'edit_terms'   => 'edit_nanodoc_terms',
                                'delete_terms' => 'delete_nanodoc_terms',
                                'assign_terms' => 'assign_nanodoc_terms',
                            ),
    );

    if( ! taxonomy_exists( 'nanodoc_category' ) ) {
        register_taxonomy( 'nanodoc_category', array( 'nanodoc' ), $cat_args );
    }

}

add_action( 'init', 'ns_create_nanodoc_taxonomies', 0 );


/**
 * Knowledgebase Category Icon (add form).
 *
 * @since  1.0.0
 * 
 * @param  object $taxonomy Taxonomy object.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_add_meta_fields( $taxonomy ) { ?>

    <?php add_thickbox(); ?>

    <div class="form-field kb-cat-icon-wrap">
        <label for="text"><?php _e( 'Choose Category Icon', 'nanosupport' ); ?></label>
        <span class="ns-admin-btnlike hide-if-no-js" id="nanosupport-icon-preview"><i class="ns-icon-docs"></i></span>
        <a href="#TB_inline?width=600&height=550&inlineId=ns-kb-icon-modal" class="thickbox hide-if-no-js button button-primary" title="<?php esc_attr_e( 'Choose an icon', 'nanosupport' ); ?>">
            <?php _e( 'Choose Icon', 'nanosupport' ); ?>
        </a>
        <input type="text" name="kb_cat_icon" id="kb-cat-icon" class="hide-if-js nanosupport-icon-textbox" size="40" placeholder="<?php esc_attr_e( 'i.e. ns-icon-docs', 'nanosupport' ); ?>">
        <p><?php _e( 'Choose an Icon to display with the Category', 'nanosupport' ); ?></p>

        <div id="ns-kb-icon-modal" style="display:none;">
            <?php
            $icons = ns_get_all_icon();
            foreach( $icons as $icon ) {
                echo '<button class="button button-large nanosupport-icon-button" value="'. esc_attr($icon) .'"><i class="'. $icon .'"></i></button>';
            }
            ?>
        </div>
    </div>

<?php
}

add_action( 'nanodoc_category_add_form_fields', 'ns_nanodoc_taxonomy_add_meta_fields' );


/**
 * Saving Knowledgebase Category Icon.
 *
 * @since  1.0.0
 * 
 * @param  integer $term_id  Term ID.
 * @param  integer $tt_id    Term Taxonomy ID.
 * @param  string  $taxonomy Taxonomy slug.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_save_meta_fields( $term_id, $tt_id, $taxonomy ) {
    //Handle the 'nanodoc_category' only
    if( 'nanodoc_category' === $taxonomy ) {
        if( isset($_POST['kb_cat_icon']) ) {

            update_term_meta( $term_id, '_ns_kb_cat_icon', sanitize_text_field( $_POST['kb_cat_icon'] ) );

        }
    }
}

add_action( 'edit_term',    'ns_nanodoc_taxonomy_save_meta_fields', 10, 3 );
add_action( 'create_term',  'ns_nanodoc_taxonomy_save_meta_fields', 10, 3 );


/**
 * Knowledgebase Category Icon (edit form).
 *
 * @since  1.0.0
 * 
 * @param  object $taxonomy Taxonomy object.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_edit_meta_fields( $taxonomy ) {
    $saved_meta    = get_term_meta( $taxonomy->term_id, '_ns_kb_cat_icon', true );
    $ns_icon_class = $saved_meta ? $saved_meta : 'ns-icon-docs';

    add_thickbox();
    ?>

    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="kb-cat-icon"><?php _e( 'Change Category Icon', 'nanosupport' ); ?></label>
        </th>
        <td>
            <span class="ns-admin-btnlike hide-if-no-js" id="nanosupport-icon-preview"><i class="<?php echo esc_attr($ns_icon_class); ?>"></i></span>
            <a href="#TB_inline?width=600&height=550&inlineId=ns-kb-icon-modal" class="thickbox hide-if-no-js button button-primary" title="<?php esc_attr_e( 'Choose an icon', 'nanosupport' ); ?>">
                <?php _e( 'Choose Icon', 'nanosupport' ); ?>
            </a>
            <input type="text" name="kb_cat_icon" id="kb-cat-icon" class="hide-if-js nanosupport-icon-textbox" value="<?php echo esc_attr($ns_icon_class); ?>" size="40" placeholder="<?php esc_attr_e( 'i.e. ns-icon-docs', 'nanosupport' ); ?>">
            <p class="description"><?php _e( 'Choose an Icon to display with the Category', 'nanosupport' ); ?></p>

            <div id="ns-kb-icon-modal" style="display:none;">
                <?php
                $icons = ns_get_all_icon();
                foreach( $icons as $icon ) {
                    echo '<button class="button button-large nanosupport-icon-button" value="'. esc_attr($icon) .'"><i class="'. $icon .'"></i></button>';
                }
                ?>
            </div>
        </td>
    </tr>

<?php
}

add_action( 'nanodoc_category_edit_form_fields', 'ns_nanodoc_taxonomy_edit_meta_fields' );


/**
 * Icon column added to Offer Taxonomies (Offer Categories, Offer Types) admin.
 *
 * @access public
 * @param  mixed    $columns
 * @return array    $columns with new columns merged.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_icon_column( $columns ) {
    unset( $columns['cb'] );
    unset( $columns['name'] );
    
    $new_columns = array();
    $new_columns['cb']      = $columns['cb'];
    $new_columns['name']    = $columns['name'];
    $new_columns['icon']    = __( 'Icon', 'nanosupport' );

    return array_merge( $new_columns, $columns );
}

add_filter( 'manage_edit-nanodoc_category_columns', 'ns_nanodoc_taxonomy_icon_column' );


/**
 * Icon column value for Offer Categories.
 *
 * @access public
 * @param mixed   $columns
 * @param mixed   $column
 * @param mixed   $id
 * @return string $columns Column icon.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_icon_column_data( $columns, $column, $id ) {
    if( 'icon' === $column ) {
        $icon = get_term_meta( $id, '_ns_kb_cat_icon', true );
        $icon_class = $icon ? $icon : 'ns-icon-docs';
        $columns = '<span class="'. esc_attr($icon_class) .'"></span>';
    }

    return $columns;
}

add_filter( 'manage_nanodoc_category_custom_column', 'ns_nanodoc_taxonomy_icon_column_data', 10, 3 );


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
 
     if( 'nanodoc' === $screen->post_type ) {
          $title = __( 'Knowledgebase Question', 'nanosupport' );
     }
 
     return $title;
}

add_filter( 'enter_title_here', 'ns_change_nanodoc_title_text' );
