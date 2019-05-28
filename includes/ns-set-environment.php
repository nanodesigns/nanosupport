<?php
/**
 * Setup Functions
 *
 * Functions that are used for Setting up the plugin.
 *
 * @author      nanodesigns
 * @category    Core
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Styles & JavaScripts (Front End)
 *
 * Necessary JavaScripts and Styles for Front-end tweaks.
 * -----------------------------------------------------------------------
 */
function ns_scripts() {

    // Get the NanoSupport Settings from Database
	$ns_general_settings        = get_option( 'nanosupport_settings' );
	$ns_knowledgebase_settings  = get_option( 'nanosupport_knowledgebase_settings' );

	$support_desk  = $ns_general_settings['support_desk'];
	$submit_ticket = $ns_general_settings['submit_page'];
	$knowledgebase = $ns_knowledgebase_settings['page'];

    /**
     * NanoSupport CSS
     * Compiled and minified from SASS CSS Preprocessor.
     * ...
     */
    wp_register_style( 'nanosupport', NS()->plugin_url() .'/assets/css/nanosupport.css', array(), NS()->version, 'all' );

    /**
     * MatchHeight JS v0.7.0
     * @link http://brm.io/jquery-match-height/
     * ...
     */
    wp_register_script( 'equal-height', NS()->plugin_url() .'/assets/libs/jQuery.matchHeight/jquery.matchHeight-min.js', array('jquery'), '0.7.0', true );

    /**
     * Focus Visible JS v4.1.3
     * @link https://github.com/WICG/focus-visible/
     * ...
     */
    wp_register_script( 'focus-visible', NS()->plugin_url() .'/assets/libs/focus-visible/focus-visible.min.js', array(), '4.1.3', true );

    /**
     * NanoSupport JavaScripts
     * Compiled and minified. Depends on 'jQuery'.
     * ...
     */
    wp_register_script(
    	'nanosupport',
    	NS()->plugin_url() .'/assets/js/nanosupport.min.js',
    	array('jquery', 'focus-visible'),
    	NS()->version,
    	true
    );

    /**
     * NanoSupport Localize Scripts
     * Translation-ready JS strings and other dynamic parameters.
     * ...
     */
    wp_localize_script(
    	'nanosupport',
    	'ns',
    	array(
    		'plugin_url'    => NS()->plugin_url()
    	)
    );

    if( is_page( $knowledgebase ) ) {
    	wp_enqueue_script( 'equal-height' );
    }

    if( is_page( array( $support_desk, $submit_ticket, $knowledgebase ) ) || is_singular('nanosupport') || is_singular('nanodoc') ) {
    	wp_enqueue_style( 'nanosupport' );
    	wp_enqueue_script( 'nanosupport' );
    }

}

add_action( 'wp_enqueue_scripts', 'ns_scripts' );


/**
 * Styles & JavaScripts (Admin)
 *
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 *
 * @param  string $hook_suffix Current admin page.
 * -----------------------------------------------------------------------
 */
function ns_admin_scripts( $hook_suffix ) {

	wp_register_style( 'ns-admin', NS()->plugin_url() .'/assets/css/nanosupport-admin.css', array(), NS()->version, 'all' );

	$screen = get_current_screen();
	if( 'nanosupport' === $screen->post_type || 'nanodoc' === $screen->post_type || 'nanosupport_page_nanosupport-settings' === $screen->base || ('users' === $screen->base && 'users' === $screen->id) ) {

		if( 'edit.php' === $hook_suffix ) {

            // Get Knowledgebase settings from db.
			$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

			if( isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
                /**
                 * nProgress v0.2.0
                 * @link https://github.com/rstacruz/nprogress
                 * ...
                 */
                wp_enqueue_style( 'nprogress', NS()->plugin_url() .'/assets/libs/nprogress/nprogress.css', array(), '0.2.0', 'screen' );
                wp_enqueue_script( 'nprogress', NS()->plugin_url() .'/assets/libs/nprogress/nprogress.min.js', array('jquery'), '0.2.0' );

                /**
                 * NanoSupport Copy Ticket
                 * Copy ticket content to Knowledgebase.
                 * ...
                 */
                wp_enqueue_script( 'nanosupport-copy-ticket', NS()->plugin_url() .'/assets/js/nanosupport-copy-ticket.js', array('jquery'), NS()->version, true );
            }
        }

        wp_enqueue_style( 'ns-admin' );

        /**
         * Select2 v4.0.3
         * @link https://github.com/select2/select2/
         * ...
         */
        wp_enqueue_style( 'select2', NS()->plugin_url() .'/assets/libs/select2/select2.min.css', array(), '4.0.3', 'all' );
        wp_enqueue_script( 'select2', NS()->plugin_url() .'/assets/libs/select2/select2.min.js', array('jquery'), '4.0.3', true );


        /**
         * jQuery ColorPicker
         * WordPress 3.5+ | jQuery dependent.
         * @author  automattic
         * @link    https://automattic.github.io/Iris/
         * ...
         */
        wp_enqueue_style( 'wp-color-picker' );


        /**
         * NanoSupport Admin-specific JavaScripts
         * Compiled and minified. Depends on 'jQuery'.
         * ...
         */
        wp_enqueue_script(
        	'ns-admin',
        	NS()->plugin_url() .'/assets/js/nanosupport-admin.min.js',
        	array(
        		'jquery',
        		'wp-color-picker'
        	),
        	NS()->version,
        	true
        );

        /**
         * NanoSupport Admin-specific Localize Scripts
         * Translation-ready JS strings and other dynamic parameters.
         * ...
         */
        wp_localize_script(
        	'ns-admin',
        	'ns',
        	array(
        		'del_confirmation'  => esc_html__( 'Are you sure you want to delete the response?', 'nanosupport' ),
        	)
        );
    }

    /**
     * C3 Chart v0.4.10
     * @link https://github.com/masayuki0812/c3/
     * ...
     */
    if( 'dashboard' === $screen->base && 'dashboard' === $screen->id ) {
    	wp_enqueue_style( 'c3', NS()->plugin_url() .'/assets/libs/c3/c3.min.css', array(), '0.4.10', 'all' );
    	wp_register_script( 'd3', NS()->plugin_url() .'/assets/libs/c3/d3.min.js', array(), '3.5.16', true );
    	wp_register_script( 'c3', NS()->plugin_url() .'/assets/libs/c3/c3.min.js', array('d3'), '0.4.10', true );
    }

    /**
     * NannoSupport Icon Font
     *
     * Based on Ionicons, Font Awesome, Entypo with
     * Octicons, Foundation Icons, Steadysets etc.
     *
     * Built with Fontastic.me
     * ---------------------------------------------
     */
    wp_enqueue_style( 'nanosupport-icon-styles', NS()->plugin_url() .'/assets/css/nanosupport-icon-styles.css', array(), NS()->version, 'all' );
}

add_action( 'admin_enqueue_scripts', 'ns_admin_scripts' );


/**
 * Mandate Knowledgebase Category
 *
 * As Knowledgebase documents are displayed organized under respective categories,
 * let's force the user to assign the document to a category before publishing.
 *
 * Adopted from Plugin:
 * Require Post Category
 * @link    https://wordpress.org/plugins/require-post-category/
 * @author  Josh Hartman
 * -----------------------------------------------------------------------
 */
function ns_mandate_knowledgebase_category() {
	global $post_type;
	if( 'nanodoc' === $post_type ) {
		echo "<script type=\"text/javascript\">
		jQuery(document).ready(function($) {
			$('#publish').on('click', function(event) {
				if( $('#taxonomy-nanodoc_category input:checked').length == 0 ) {
					alert('". esc_js( __( 'Please assign the document to a Knowledgebase Category, because Knowledgebase documents are displayed under categories.', 'nanosupport' ) ) ."');
					event.stopImmediatePropagation();
					return false;
				} else {
					return true;
				}

					var publish_click_events = $('#publish').data('events').click;
					if( publish_click_events && publish_click_events.length > 1 ) {
						publish_click_events.unshift(publish_click_events.pop());
					}
				});
			});
		</script>";
	}
}

add_action( 'admin_footer-post.php',        'ns_mandate_knowledgebase_category' );
add_action( 'admin_footer-post-new.php',    'ns_mandate_knowledgebase_category' );


/**
 * Support Agent User Meta Field
 *
 * Support Agent selection user meta field.
 *
 * @param  obj $user Get the user data from WP_User object.
 * -----------------------------------------------------------------------
 */
function ns_user_fields( $user ) { ?>
	<?php
    //Don't display the section except nanosupport 'manager'
	if( ns_is_user('manager') && 'support_seeker' !== $user->roles[0] ) : ?>

		<h3><?php echo NS()->plugin; ?></h3>

		<table class="form-table">
			<tr>
				<th scope="row">
					<i class="dashicons dashicons-businessman" aria-hidden="true"></i> <?php esc_html_e( 'Make Support Agent', 'nanosupport' ); ?>
				</th>
				<td>
					<label>
						<input type="checkbox" name="ns_make_agent" id="ns-make-agent" value="1" <?php checked( get_the_author_meta( 'ns_make_agent', $user->ID ), 1 ); ?> /> <?php esc_html_e( 'Yes, make this user a Support Agent', 'nanosupport' ); ?>
					</label>
				</td>
			</tr>
		</table>

	<?php else : ?>

		<?php $db_value = get_the_author_meta( 'ns_make_agent', $user->ID ) ? 1 : 0; ?>
		<input type="hidden" name="ns_make_agent" value="<?php echo $db_value; ?>"/>

	<?php endif; ?>
	<?php
}

add_action( 'show_user_profile', 'ns_user_fields' );
add_action( 'edit_user_profile', 'ns_user_fields' );


/**
 * Saving the user meta fields
 *
 * Saving the user agent checkmarking choice to the user meta table.
 * If there's no checkmark, a value from hidden field will come.
 *
 * @param  integer $user_id User id.
 * -----------------------------------------------------------------------
 */
function ns_saving_user_fields( $user_id ) {

	update_user_meta( $user_id, 'ns_make_agent', intval( $_POST['ns_make_agent'] ) );

    /**
     * For an agent, enable Support Ticket
     * @var WP_User
     */
    $capability_type = 'nanosupport';
    $ns_agent_user = new WP_User($user_id);
    if( 1 == intval( $_POST['ns_make_agent'] ) ) :
    	$ns_agent_user->add_cap( "read_{$capability_type}" );
    	$ns_agent_user->add_cap( "edit_{$capability_type}" );
    	$ns_agent_user->add_cap( "edit_{$capability_type}s" );
    	$ns_agent_user->add_cap( "edit_others_{$capability_type}s" );
    	$ns_agent_user->add_cap( "read_private_{$capability_type}s" );
    	$ns_agent_user->add_cap( "edit_private_{$capability_type}s" );
    	$ns_agent_user->add_cap( "edit_published_{$capability_type}s" );

    	$ns_agent_user->add_cap( "assign_{$capability_type}_terms" );
    else :
    	$ns_agent_user->remove_cap( "read_{$capability_type}" );
    	$ns_agent_user->remove_cap( "edit_{$capability_type}" );
    	$ns_agent_user->remove_cap( "edit_{$capability_type}s" );
    	$ns_agent_user->remove_cap( "edit_others_{$capability_type}s" );
    	$ns_agent_user->remove_cap( "read_private_{$capability_type}s" );
    	$ns_agent_user->remove_cap( "edit_private_{$capability_type}s" );
    	$ns_agent_user->remove_cap( "edit_published_{$capability_type}s" );

    	$ns_agent_user->remove_cap( "assign_{$capability_type}_terms" );
    endif;

}

add_action( 'personal_options_update', 	'ns_saving_user_fields' );
add_action( 'edit_user_profile_update', 'ns_saving_user_fields' );


/**
 * Support agent user column
 *
 * Add a new column to display support agent status.
 *
 * @param  array $columns  Array of user columns.
 * @return array           Modified user columns.
 * -----------------------------------------------------------------------
 */
function ns_add_support_agent_user_column( $columns ) {
	$columns['ns_agent'] = '<i class="ns-icon-nanosupport" aria-label="'. esc_attr__( 'NanoSupport Agent', 'nanosupport' ) .'"></i>';
	return $columns;
}

add_filter( 'manage_users_columns', 'ns_add_support_agent_user_column' );

/**
 * Support agent user column content
 *
 * Display an icon if the user is a support agent.
 *
 * @param  mixed $value        Default value of the columns.
 * @param  string $column_name The ID of columns.
 * @param  integer $user_id    The user ID of specific column.
 * @return mixed               The column data.
 * -----------------------------------------------------------------------
 */
function ns_support_agent_user_column_content( $value, $column_name, $user_id ) {
	if ( 'ns_agent' == $column_name ) {
		if( 1 == get_user_meta( $user_id, 'ns_make_agent', true ) )
			return '<span class="ns-label ns-label-warning"><i class="dashicons dashicons-businessman" aria-label="'. esc_attr__( 'NanoSupport Agent', 'nanosupport' ) .'"></i> '. esc_html__( 'Agent', 'nanosupport' ) .'</span>';
		else
			return '&mdash;';
	}
	return $value;
}

add_action( 'manage_users_custom_column', 'ns_support_agent_user_column_content', 10, 3 );


/**
 * Force Post Status to Private
 *
 * Force all the ticket post status default to 'Private' instead of 'Publish'.
 * As to make tickets outstand from Knowledgebase (public) docs domain.
 *
 * @link   https://www.isitwp.com/force-custom-post-type-to-be-private/
 *
 * @param  object $post Post object.
 * @return object       Modified post object.
 * -----------------------------------------------------------------------
 */
function ns_force_ticket_post_status_to_private( $post ) {
	if ( 'nanosupport' === $post['post_type'] && 'publish' === $post['post_status'] ) {
		$post['post_status'] = 'private';
	}

	return $post;
}

add_filter( 'wp_insert_post_data', 'ns_force_ticket_post_status_to_private' );


/**
 * Template loader
 *
 * @param  string $template The template that is called.
 * @return string           Template, that is thrown per modification.
 * -----------------------------------------------------------------------
 */
function ns_template_loader( $template ) {
	$find = array('nano-support.php');
	$file = '';

	if ( is_single() && 'nanosupport' === get_post_type() ) {

		$file   = 'single-nanosupport.php';
		$find[] = $file;
		$find[] = NS()->template_path() . $file;

	}

	if ( $file ) {
		$template = locate_template( array_unique( $find ) );
		if ( ! $template ) {
			$template = NS()->plugin_path() .'/templates/'. $file;
		}
	}

	return $template;
}

add_filter( 'template_include', 'ns_template_loader' );


if ( ! function_exists( 'ns_content' ) ) {

    /**
     * Output NanoSupport content.
     *
     * This function is only used in the optional 'nanosupport.php' template
     * which people can add to their themes to add basic nanosupport support
     * without hooks or modifying core templates.
     *
     */
    function ns_content() {

    	if ( is_singular( 'nanosupport' ) ) {

    		while ( have_posts() ) : the_post();

    			ns_get_template_part( 'content-single-nanosupport.php' );

    		endwhile;

    	} else { ?>

    		<h1 class="page-title"><?php the_title(); ?></h1>

    		<?php if ( have_posts() ) : ?>

    			<?php while ( have_posts() ) : the_post(); ?>

    				<?php ns_get_template_part( 'content-ticket.php' ); ?>

    			<?php endwhile; // end of the loop. ?>

    			<?php esc_html_e( 'Ticket has no content', 'nanosupport' ); ?>

    		<?php endif;

    	}
    }
}


/**
 * Trim "Private" & "Protected" from Title
 *
 * WordPress displays these terms beside post titles on the front-end.
 * We don't want to show them on our tickets. So, trim the word
 * "Private" and "Protected" from Title of CPT 'nanosupport'.
 *
 * As the `the_title` filter was causing issue for i18n strings
 * so we revised the filters that works best instead.
 *
 * @author birgire
 * @link   http://wordpress.stackexchange.com/a/236397/22728
 *
 * @param  string $title Post title.
 * @return string        Post title trimmed.
 * -----------------------------------------------------------------------
 */
function ns_the_title_trim( $format, \WP_Post $post ) {
	return  'nanosupport' === get_post_type( $post ) ? '%s' : $format;
}

add_filter( 'protected_title_format', 'ns_the_title_trim', 10, 2 );
add_filter( 'private_title_format',   'ns_the_title_trim', 10, 2 );


/**
 * Redirect visitors from Support Desk
 *
 * Redirect non-logged-in users from the support desk to the Knowledgebase,
 * if KB is active. Or, to the Submit Ticket page if KB is not active.
 * Only the logged in users are allowed to see the Support Desk page.
 * -----------------------------------------------------------------------
 */
function ns_redirect_user_to_correct_place( $query ) {
    //Get the NanoSupport Settings from Database
	$ns_general_settings       = get_option( 'nanosupport_settings' );
	$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

	if( $query->is_main_query() && ! is_admin() && ! is_user_logged_in() && is_page($ns_general_settings['support_desk']) ) {

		if( $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
            //i.e. http://example.com/knowledgebase?from=sd
			wp_redirect( add_query_arg( 'from', 'sd', get_permalink($ns_knowledgebase_settings['page']) ) );
			exit();
		} else {
            //i.e. http://example.com/submit-ticket?from=sd
			wp_redirect( add_query_arg( 'from', 'sd', get_permalink($ns_general_settings['submit_page']) ) );
			exit();
		}
	}
}

add_action( 'pre_get_posts', 'ns_redirect_user_to_correct_place' );


/**
 * Add class to Ticket Edit button
 *
 * Add some NS classes to the WP-default post edit link on the
 * front end to match the UI.
 *
 * @param  string $output Default link.
 * @return string         Modified link with modified class.
 * -----------------------------------------------------------------------
 */
function ns_ticket_edit_post_link( $output ) {
	global $post;
	if( is_singular('nanosupport') && 'nanosupport' === $post->post_type ) {
		$output = str_replace(
			'class="post-edit-link"',
			'class="post-edit-link ns-btn ns-btn-default ns-btn-xs ns-round-btn edit-ticket-btn"',
			$output
		);
	}

	return $output;
}

add_filter( 'edit_post_link', 'ns_ticket_edit_post_link' );


/**
 * NanoSupport Admin Bar menu.
 *
 * @param  object $wp_admin_bar Default admin bar object.
 * @return object               Admin bar object with Added menu.
 * -----------------------------------------------------------------------
 */
function ns_admin_bar_menu( $wp_admin_bar ) {

	if( ! is_admin() || ! is_user_logged_in() )
		return;

    // Show only when the user is a member of this site, or they're a super admin.
	if( ! is_user_member_of_blog() && ! is_super_admin() )
		return;

	$ns_general_settings = get_option( 'nanosupport_settings' );

    // Don't display when Support Desk is set as the Front Page.
	if( get_option( 'page_on_front' ) == $ns_general_settings['support_desk'] )
		return;

    // Add an option to visit the Support Desk.
	$wp_admin_bar->add_node( array(
		'parent' => 'site-name',
		'id'     => 'view-support-desk',
		'title'  => esc_html__( 'Visit Support Desk', 'nanosupport' ),
		'href'   => get_the_permalink( $ns_general_settings['support_desk'] )
	) );
}

/**
 * -----------------------------------------------------------------------
 * HOOK : FILTER HOOK
 * nanosupport_show_admin_bar_visit_support_desk
 *
 * @since  1.0.0
 *
 * @param boolean  True to display the Support Desk link under site name.
 * -----------------------------------------------------------------------
 */
if( apply_filters( 'nanosupport_show_admin_bar_visit_support_desk', true ) ) {
	add_action( 'admin_bar_menu', 'ns_admin_bar_menu', 32 );
}


/**
 * Display Agent Ticket count on Admin Bar.
 *
 * @param  object $wp_admin_bar Default admin bar object.
 * @return object               Admin bar object with Added menu.
 * -----------------------------------------------------------------------
 */
function ns_agent_admin_bar( $wp_admin_bar ) {
	if( ! ns_is_user('agent') )
		return;

	global $current_user;
	$my_total_tickets   = ns_total_ticket_count( 'nanosupport', $current_user->ID );
	$my_solved_tickets  = ns_ticket_status_count( 'solved', $current_user->ID );
	$my_open_tickets    = $my_total_tickets - $my_solved_tickets;

	if( absint($my_open_tickets) > 0 ) {
		$wp_admin_bar->add_node(array(
			'parent'    => null,
			'group'     => null,
			'title'     => '<i class="ab-icon ns-icon-nanosupport" aria-label="'. __('My Open Tickets', 'nanosupport') .'" style="font-size: 17px;"></i> ' . absint( $my_open_tickets ),
			'id'        => 'ns-agent-ticket-count',
			'href'      => add_query_arg( 'post_type', 'nanosupport', admin_url('/edit.php') ),
			'meta'      => array(
				'target' => '_self',
				'title'  => esc_html__( 'Open tickets assigned to me', 'nanosupport' ),
				'class'  => 'agent-open-tickets',
			),
		));
	}

}

add_action( 'admin_bar_menu', 'ns_agent_admin_bar', 999 );


/**
 * Modifying SQL clauses to show assigned tickets to Agents.
 *
 * @param  array $clauses       Array of SQL segments.
 * @param  object $query_object WP Query object.
 * @return array                Modified array of SQL segments.
 * -----------------------------------------------------------------------
 */
function display_assigned_tickets_modifying_query( $clauses, $query_object ) {
	if( is_admin() && 'nanosupport' === $query_object->get('post_type') ) {
		global $wpdb, $current_user;

		if( ns_is_user('agent') ) {

			$priority_filter = filter_input(INPUT_GET, 'ticket_priority', FILTER_SANITIZE_STRING);
			$status_filter   = filter_input(INPUT_GET, 'ticket_status', FILTER_SANITIZE_STRING);

			$clauses['where'] = " AND ";
			$clauses['where'] .= "( {$wpdb->posts}.post_author IN ({$current_user->ID})
			OR (({$wpdb->postmeta}.meta_key = '_ns_ticket_agent' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) = '{$current_user->ID}')) )";
			$clauses['where'] .= " AND {$wpdb->posts}.post_type = 'nanosupport' ";
			$clauses['where'] .= " AND ({$wpdb->posts}.post_status = 'publish'
			OR {$wpdb->posts}.post_status = 'future'
			OR {$wpdb->posts}.post_status = 'draft'
			OR {$wpdb->posts}.post_status = 'pending'
			OR {$wpdb->posts}.post_status = 'private') ";

			if( $priority_filter ) {
				$clauses['join']  .= " LEFT JOIN {$wpdb->postmeta} AS PM2 ON ({$wpdb->posts}.ID = PM2.post_id) ";
				$clauses['where'] .= " AND (PM2.meta_key = '_ns_ticket_priority' AND PM2.meta_value = '{$priority_filter}') ";
			}

			if( $status_filter ) {
				$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS PM3 ON ({$wpdb->posts}.ID = PM3.post_id) ";
				if( 'pending' === $status_filter ) :
					$clauses['where'] .= " AND ({$wpdb->posts}.post_status = 'pending') ";
				else :
					$clauses['where'] .= " AND ({$wpdb->posts}.post_status = 'private') ";
					$clauses['where'] .= " AND (PM3.meta_key = '_ns_ticket_status' AND PM3.meta_value = '{$status_filter}') ";
				endif;
			}

		}

	}
	return $clauses;
}

add_filter( 'posts_clauses', 'display_assigned_tickets_modifying_query', 10, 2 );


/**
 * TinyMCE buttons modified for Ticket Details.
 *
 * @link https://codex.wordpress.org/Function_Reference/wp_editor
 * @link https://codex.wordpress.org/TinyMCE
 * @link https://wordpress.stackexchange.com/a/29480
 *
 * @param  array $ed Default editor.
 * @return array     Modified buttons.
 * -----------------------------------------------------------------------
 */
function ns_modified_TinyMCE( $ed ) {
    //Get the NanoSupport Settings from Database
	$ns_general_settings = get_option( 'nanosupport_settings' );

	if( is_page( $ns_general_settings['submit_page'] ) ) {
        // Items to display under 'formatselect' dropdown
		$ed['block_formats'] = "Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Preformatted=pre";

        // Prepare the toolbar
		$ed['toolbar1'] = 'formatselect,bold,italic,strikethrough,hr,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,pastetext,wp_adv ';
		$ed['toolbar2'] = 'spellchecker,removeformat,charmap,outdent,indent,undo,redo,fullscreen ';
	}

	return $ed;
}

add_filter( 'tiny_mce_before_init', 'ns_modified_TinyMCE' );
