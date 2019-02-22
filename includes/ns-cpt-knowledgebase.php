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
 * @return array to register a post type.
 * -----------------------------------------------------------------------
 */
function ns_register_cpt_nanodoc() {

	$labels = array(
		'name'					=> _x( 'Knowledgebase', 'NanoSupport Knowledgebase', 'nanosupport' ),
		'singular_name'			=> _x( 'Knowledgebase', 'NanoSupport Knowledgebase', 'nanosupport' ),
		'add_new'				=> _x( 'Add New', 'NanoSupport Knowledgebase', 'nanosupport' ),
		'add_new_item'			=> __( 'Add New Doc', 'nanosupport' ),
		'edit_item'				=> __( 'Edit Doc', 'nanosupport' ),
		'new_item'				=> __( 'New Doc', 'nanosupport' ),
		'view_item'				=> __( 'View Doc', 'nanosupport' ),
		'search_items'			=> __( 'Search Knowledgebase', 'nanosupport' ),
		'not_found'				=> __( 'No Knowledgebase Doc found', 'nanosupport' ),
		'not_found_in_trash'	=> __( 'No Knowledgebase Doc found in Trash', 'nanosupport' ),
		'parent_item_colon'		=> __( 'Parent Doc:', 'nanosupport' ),
		'menu_name'				=> _x( 'Knowledgebase', 'NanoSupport Knowledgebase', 'nanosupport' ),
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
        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * ns_nanodoc_arguments
         *
         * To modify/push arguments that are passed to generate CPT 'nanodoc'.
         *
         * @since  1.0.0
         * -----------------------------------------------------------------------
         */
        register_post_type( 'nanodoc', apply_filters( 'ns_nanodoc_arguments', $args ) );
    }

}

//get Knowledgebase settings from db
$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

/**
 * Initiate CPT Knowledgebase on demand
 * Display, if enabled in admin panel.
 */
if( isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
	add_action( 'init', 'ns_register_cpt_nanodoc' );
}



/**
 * Register Custom Taxonomy Knowledgebase Category
 *
 * Create Custom Taxonomy 'nanodoc_category' to sort out the
 * knowledgebase documents.
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
 * @param  object $taxonomy Taxonomy object.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_add_meta_fields( $taxonomy ) { ?>

	<?php add_thickbox(); ?>

	<div class="form-field kb-cat-icon-wrap">
		<label for="text"><?php _e( 'Choose Category Icon', 'nanosupport' ); ?></label>
		<span class="ns-admin-btnlike hide-if-no-js" id="nanosupport-icon-preview"><i class="ns-icon-docs" aria-hidden="true"></i></span>
		<a href="#TB_inline?width=600&height=550&inlineId=ns-kb-icon-modal" class="thickbox hide-if-no-js button button-primary" title="<?php esc_attr_e( 'Choose an icon', 'nanosupport' ); ?>">
			<?php _e( 'Choose Icon', 'nanosupport' ); ?>
		</a>
		<?php /* translators: NanoSupport icon class */ ?>
		<input type="text" name="kb_cat_icon" id="kb-cat-icon" class="hide-if-js nanosupport-icon-textbox" size="40" placeholder="<?php printf( esc_attr__( 'e.g. %s', 'nanosupport' ), 'ns-icon-docs' ); ?>">
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
			<input type="text" name="kb_cat_icon" id="kb-cat-icon" class="hide-if-js nanosupport-icon-textbox" value="<?php echo esc_attr($ns_icon_class); ?>" size="40" placeholder="<?php printf( esc_attr__( 'e.g. %s', 'nanosupport' ), 'ns-icon-docs' ); ?>">
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
 * @param  mixed    $columns
 * @return array    $columns with new columns merged.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_icon_column( $columns ) {
	$new_columns = array();

	if( isset($columns['cb']) ) {
		$new_columns['cb'] = $columns['cb'];
		unset( $columns['cb'] );
	}

	if( isset($columns['name']) ) {
		$new_columns['name'] = $columns['name'];
		unset( $columns['name'] );
	}

	$new_columns['icon'] = __( 'Icon', 'nanosupport' );

	return array_merge( $new_columns, $columns );
}

add_filter( 'manage_edit-nanodoc_category_columns', 'ns_nanodoc_taxonomy_icon_column' );


/**
 * Icon column value for Offer Categories.
 *
 * @param mixed   $columns
 * @param mixed   $column
 * @param mixed   $id
 * @return string $columns Column icon.
 * -----------------------------------------------------------------------
 */
function ns_nanodoc_taxonomy_icon_column_data( $columns, $column, $id ) {
	if( 'icon' === $column ) {
		$icon       = get_term_meta( $id, '_ns_kb_cat_icon', true );
		$icon_class = $icon ? $icon : 'ns-icon-docs';
		$columns    = '<i class="'. esc_attr($icon_class) .'"></i>';
	}

	return $columns;
}

add_filter( 'manage_nanodoc_category_custom_column', 'ns_nanodoc_taxonomy_icon_column_data', 10, 3 );


/**
 * Change the 'Post Title' in Admin
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


/**
 * Back to the Knowledgebase button
 *
 * @param  string $content WordPress post content.
 * @return string          Filtered content for Knowledgebase Docs.
 * -----------------------------------------------------------------------
 */
function ns_back_to_knowledgebase_link( $content ) {
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );
	$knowledgebase_link        = isset($ns_knowledgebase_settings['page']) ? get_permalink($ns_knowledgebase_settings['page']) : '#';

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * ns_back_to_knowledgebase
     *
     * To modify the link visible below the Knowledgebase documents typically
     * targetted toward the Knowledgebase. You may want to modify the link
     * to the respective Knowledgebase Category, the filter will help you.
     *
     * @since  1.0.0
     * -----------------------------------------------------------------------
     */
    $back_link = apply_filters( 'ns_back_to_knowledgebase', $knowledgebase_link );

    if( is_singular('nanodoc') && isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {

        // Concatenating below the content with a break to differentiate better
    	$content .= '<br><a href="'. esc_url($back_link) .'" class="ns-btn ns-btn-sm ns-btn-default">&laquo; '. __( 'Back to the Knowledgebase', 'nanosupport' ) .'</a>';
    }

    return $content;
}

add_filter( 'the_content', 'ns_back_to_knowledgebase_link' );


/**
 * Copy Ticket to Knowledgebase.
 *
 * AJAX copy ticket content into Knowledgebase doc.
 * -----------------------------------------------------------------------
 */
function ns_copy_ticket_to_knowledgebase_doc() {
    // Check the nonce
	check_ajax_referer( 'ns_copy_ticket_nonce', 'nonce' );

	$ticket_id = $_POST['ticket_id'];

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * nanosupport_copied_content
     *
     * Filter hook to override the default setup for the copied ticket
     * to save as a Knowledgebase doc.
     *
     * @since  1.0.0
     *
     * @param array  The copied post object as an associative array.
     * -----------------------------------------------------------------------
     */
    $copied_post_array = apply_filters( 'nanosupport_copied_content', get_post($ticket_id, 'ARRAY_A') );

    // Insert the post into the database
    wp_insert_post( $copied_post_array );

    echo 'Knowledgebase Doc Created from NanoSupport ticket!';

    die(); // this is required to return a proper result
}

if( isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
	add_action( 'wp_ajax_ns_copy_ticket', 'ns_copy_ticket_to_knowledgebase_doc' );
}


/**
 * Alter the copied Ticket.
 *
 * Hooked to 'nanosupport_copied_content' with priority 10.
 * Any hooking needs to be used higher priority.
 *
 * @param  array $copied_post  Copied ticket post.
 * @return array               Altered ticket post.
 * -----------------------------------------------------------------------
 */
function ns_alter_copied_content( $copied_post ) {
    // Prepare KB doc things
	$copied_post['post_title']  = $copied_post['post_title'] .' - copied';
	$copied_post['post_status'] = 'draft';
	$copied_post['post_type']   = 'nanodoc';
	$copied_post['post_date']   = date( 'Y-m-d H:i:s', current_time('timestamp') );
	$copied_post['post_author'] = get_current_user_id();

    // Remove some of the keys
	unset( $copied_post['ID'] );
	unset( $copied_post['guid'] );
	unset( $copied_post['comment_count'] );
	unset( $copied_post['comment_status'] );
	unset( $copied_post['post_modified'] );
	unset( $copied_post['post_modified_gmt'] );

	return $copied_post;
}

if( isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
	add_filter( 'nanosupport_copied_content', 'ns_alter_copied_content', 10 );
}


/**
 * Knowledgebase doc URL rewriting.
 *
 * @see    ns_get_taxonomy_parents() Using recursive function to get all parents.
 *
 * @param  string $post_link Doc link.
 * @param  object $post      Doc post.
 * @return string            Modified link.
 * --------------------------------------------------------------------------
 */
function ns_modify_nanodoc_link( $post_link, $post ) {
	if ($post->post_type != 'nanodoc') {
		return $post_link;
	}

	$nanodoc_cats = get_the_terms($post->ID, 'nanodoc_category');
	if( $nanodoc_cats ) {
		$post_link = str_replace('%nanodoc_category%', ns_get_taxonomy_parents(array_pop($nanodoc_cats)->term_id, 'nanodoc_category', ''), $post_link);
	}

	return $post_link;
}

/**
 * Rewrite rules declaration.
 *
 * Rewrite rules for KB docs to respond on particular URL call.
 *
 * @param  array $existing_rules    Array of existing rules.
 * @return array                    Array of newly added rules with existing.
 * --------------------------------------------------------------------------
 */
function ns_knowledgebase_rewrite_rules( $existing_rules ) {
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

	if( isset($ns_knowledgebase_settings['rewrite_url']) && $ns_knowledgebase_settings['rewrite_url'] === 1 ) {
		$new_rules = array();
		$new_rules['knowledgebase/(.+)/(.+)/?$'] = 'index.php?nanodoc=$matches[2]';
		$new_rules['knowledgebase/(.+)/?$']      = 'index.php?nanodoc=$matches[1]';

		return array_merge( $new_rules, $existing_rules );
	}

	return $existing_rules;
}

add_filter( 'post_type_link',       'ns_modify_nanodoc_link',       10, 2 );
add_filter( 'rewrite_rules_array',  'ns_knowledgebase_rewrite_rules' );

/**
 * Initiate the Flush Rewrite Rules on Settings change.
 *
 * Tell the system to flush the rewrite rules, and an 'admin_init'
 * function will take care of this.
 *
 * @author TheDeadMedic
 * @link   https://wordpress.stackexchange.com/a/266078/22728
 *
 * @param  array $new_values  Array of newly changed values.
 * @param  array $old_values  Array of old values.
 * @return array              Modified new values.
 * --------------------------------------------------------------------------
 */
function ns_init_flush_rules_on_rewrite_change( $new_values, $old_values ) {
	if( empty( $new_values['rewrite_url'] ) && ! empty( $old_values['rewrite_url'] ) || ! empty( $new_values['rewrite_url'] ) && empty( $old_values['rewrite_url'] ) ) {
		$new_values['flush_rewrite_rules'] = true;
	}

	return $new_values;
}

add_filter( 'pre_update_option_nanosupport_knowledgebase_settings', 'ns_init_flush_rules_on_rewrite_change', 11, 2 );

/**
 * Flush ReWrite Rules on Settings update.
 *
 * Flush the rewrite rules, if the Knowledgebase doc URL rewriting
 * is chosen to take affect.
 *
 * @author TheDeadMedic
 * @link   https://wordpress.stackexchange.com/a/266078/22728
 *
 * @param  array $old_values  Array of previous values.
 * @param  array $new_values  Array of values going to save.
 * --------------------------------------------------------------------------
 */
function ns_flush_rules_while_rewrite_changed() {
	$settings = get_option( 'nanosupport_knowledgebase_settings' );

	if ( ! empty( $settings['flush_rewrite_rules'] ) ) {
		flush_rewrite_rules(false);
		unset( $settings['flush_rewrite_rules'] );

		update_option( 'nanosupport_knowledgebase_settings', $settings );
	}
}

add_action( 'admin_init', 'ns_flush_rules_while_rewrite_changed' );
