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

    //Get the NanoSupport Settings from Database
    $ns_general_settings        = get_option( 'nanosupport_settings' );
    $ns_knowledgebase_settings  = get_option( 'nanosupport_knowledgebase_settings' );

    $support_desk = $ns_general_settings['support_desk'];
    $submit_ticket = $ns_general_settings['submit_page'];
    $knowledgebase = $ns_knowledgebase_settings['page'];

    /**
     * NanoSupport CSS
     * Compiled and minified from LESS CSS Preprocessor.
     * ...
     */
    wp_register_style( 'nanosupport', NS()->plugin_url() .'/assets/css/nanosupport.css', array(), NS()->version, 'all' );
    
    /**
     * MatchHeight JS v0.7.0
     * @link http://brm.io/jquery-match-height/
     * ...
     */
    wp_register_script( 'equal-height', NS()->plugin_url() .'/assets/js/jquery.matchHeight-min.js', array('jquery'), '0.7.0', true );

    /**
     * NanoSupport JavaScripts
     * Compiled and minified. Depends on 'jQuery'.
     * ...
     */
    wp_register_script(
        'nanosupport',
        NS()->plugin_url() .'/assets/js/nanosupport.min.js',
        array('jquery'),
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
    
    if( is_page( $knowledgebase ) )
        wp_enqueue_script( 'equal-height' );
    
    if( is_page( array( $support_desk, $submit_ticket, $knowledgebase ) ) || is_singular('nanosupport') ) {
        wp_enqueue_style( 'nanosupport' );
        wp_enqueue_script( 'nanosupport' );
    }

}

add_action( 'wp_enqueue_scripts', 'ns_scripts' );


/**
 * Styles & JavaScripts (Admin)
 * 
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 * -----------------------------------------------------------------------
 */
function ns_admin_scripts() {

    wp_register_style( 'ns-admin', NS()->plugin_url() .'/assets/css/nanosupport-admin.css', array(), NS()->version, 'all' );

    $screen = get_current_screen();
    if( 'nanosupport' === $screen->post_type || 'nanodoc' === $screen->post_type || 'nanosupport_page_nanosupport-settings' === $screen->base ) {

        wp_enqueue_style( 'ns-admin' );
		
        /**
         * Select2 v4.0.1-rc-1
         * @link https://github.com/select2/select2/
         * ...
         */
        wp_enqueue_style( 'select2', NS()->plugin_url() .'/assets/css/select2.min.css', array(), '4.0.1-rc-1', 'all' );
        wp_enqueue_script( 'select2', NS()->plugin_url() .'/assets/js/select2.min.js', array('jquery'), '4.0.1-rc-1', true );

        /**
         * NanoSupport Admin-specific JavaScripts
         * Compiled and minified. Depends on 'jQuery'.
         * ...
         */
        wp_enqueue_script( 'ns-admin', NS()->plugin_url() .'/assets/js/nanosupport-admin.min.js', array('jquery'), NS()->version, true );

        /**
         * NanoSupport Admin-specific Localize Scripts
         * Translation-ready JS strings and other dynamic parameters.
         * ...
         */
        global $current_user;
        
        $date_time_row          = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $date_time_formatted    = date( 'd F Y h:i:s A - l', current_time( 'timestamp' ) );

		wp_localize_script(
    		'ns-admin',
    		'ns',
    		array(
                'current_user'          => $current_user->display_name,
                'user_id'               => $current_user->ID,
                'date_time_now'         => $date_time_row,
                'date_time_formatted'   => $date_time_formatted,
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
     *
     * @since 1.0.0
     * ---------------------------------------------
     */
    wp_enqueue_style( 'nanosupport-icon-styles', NS()->plugin_url() .'/assets/css/nanosupport-icon-styles.css', array(), NS()->version, 'all' );
}

add_action( 'admin_enqueue_scripts', 'ns_admin_scripts' );


/**
 * Support Agent User Meta Field
 * 
 * Support Agent selection user meta field.
 *
 * @since  1.0.0
 * 
 * @param  obj $user Get the user data from WP_User object.
 * -----------------------------------------------------------------------
 */
function ns_user_fields( $user ) { ?>
    <?php if( ! current_user_can( 'support_seeker' ) ) : ?>

        <h3><?php _e( 'NanoSupport', 'nanosupport' ); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row">
                	<span class="dashicons dashicons-businessman"></span> <?php _e( 'Make Support Agent', 'nanosupport' ); ?>
                </th>
                <td>
                	<label>
                		<input type="checkbox" name="ns_make_agent" id="ns-make-agent" value="1" <?php checked( get_the_author_meta( 'ns_make_agent', $user->ID ), 1 ); ?> /> <?php _e( 'Yes, make this user a Support Agent', 'nanosupport' ); ?>
                	</label>
                </td>
            </tr>
        </table>
    <?php endif; ?>
<?php
}

add_action( 'show_user_profile', 'ns_user_fields' );
add_action( 'edit_user_profile', 'ns_user_fields' );


/**
 * Saving the user meta fields
 *
 * Saving the user agent checkmarking choice to the user meta table.
 *
 * @since  1.0.0
 * 
 * @param  integer $user_id User id.
 * -----------------------------------------------------------------------
 */
function ns_saving_user_fields( $user_id ) {

    if( ! current_user_can( 'support_seeker' ) ) {

        update_user_meta( $user_id, 'ns_make_agent', intval( $_POST['ns_make_agent'] ) );

        /**
         * For an agent, enable Support Ticket
         * @var WP_User
         */
        $ns_agent_user = new WP_User($user_id);
        if( 1 == intval( $_POST['ns_make_agent'] ) ) :
            $ns_agent_user->add_cap( 'read_nanosupport' );
            $ns_agent_user->add_cap( 'edit_nanosupport' );
            $ns_agent_user->add_cap( 'edit_nanosupports' );
            $ns_agent_user->add_cap( 'assign_nanosupport_terms' );
        else :
            $ns_agent_user->remove_cap( 'read_nanosupport' );
            $ns_agent_user->remove_cap( 'edit_nanosupport' );
            $ns_agent_user->remove_cap( 'edit_nanosupports' );
            $ns_agent_user->remove_cap( 'assign_nanosupport_terms' );
        endif;
        
    }

}

add_action( 'personal_options_update', 	'ns_saving_user_fields' );
add_action( 'edit_user_profile_update', 'ns_saving_user_fields' );


/**
 * Force Post Status to Private
 *
 * Force all the ticket post status default to 'Private' instead of 'Publish'.
 * As to make tickets outstand from Knowledgebase (public) docs domain.
 *
 * @link http://wpsnipp.com/index.php/functions-php/force-custom-post-type-to-be-private/
 *
 * @since  1.0.0
 * 
 * @param  object $post Post object.
 * @return object       Modified post object.
 * -----------------------------------------------------------------------
 */
function ns_force_ticket_post_status_to_private( $post ) {
    if ( 'nanosupport' === $post['post_type'] ) :
        if( 'publish' === $post['post_status'] )
            $post['post_status'] = 'private';
    endif;
    
    return $post;
}

add_filter( 'wp_insert_post_data', 'ns_force_ticket_post_status_to_private' );


/**
 * Template loader
 *
 * @since  1.0.0
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
     * Output WooCommerce content.
     *
     * This function is only used in the optional 'woocommerce.php' template
     * which people can add to their themes to add basic woocommerce support
     * without hooks or modifying core templates.
     *
     */
    function ns_content() {

        if ( is_singular( 'nanosupport' ) ) {

            while ( have_posts() ) : the_post();

                ns_get_template_part( 'content', 'single-nanosupport' );

            endwhile;

        } else { ?>

            <h1 class="page-title"><?php the_title(); ?></h1>

            <?php if ( have_posts() ) : ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php ns_get_template_part( 'content', 'ticket' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php _e( 'Ticket has no content', 'nanosupport' ); ?>

            <?php endif;

        }
    }
}


/**
 * Trim "Private" & "Protected" from Title
 * 
 * WordPress displays these terms beside post titles on the front-end.
 * We don't want to show them on our tickets. So, trim the word "Private"
 * and "Protected" from Title of CPT 'nanosupport'.
 *
 * @since  1.0.0
 * 
 * @param  string $title Post Title.
 * @return string        Post Title trimmed.
 * -----------------------------------------------------------------------
 */
function ns_the_title_trim( $title ) {

    if( is_admin() )
        return $title;

    $title = esc_attr($title);

    $findthese = array(
        '#Protected:#',
        '#Private:#'
    );

    $replacewith = array(
        '', // What to replace "Protected:" with
        '' // What to replace "Private:" with
    );

    global $post;
    if( 'nanosupport' === $post->post_type )
        $title = preg_replace($findthese, $replacewith, $title);

    return $title;
}

add_filter( 'the_title', 'ns_the_title_trim' );


/**
 * Redirect visitors from Support Desk
 *
 * Redirect non-logged-in users to the Knowledgebase, from the support desk.
 * Only the logged in users are allowed to see the Support Desk page.
 *
 * @since  1.0.0
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_redirect_user_to_correct_place() {
    //Get the NanoSupport Settings from Database
    $ns_general_settings    = get_option( 'nanosupport_settings' );
    $ns_kb_settings         = get_option( 'nanosupport_knowledgebase_settings' );
    
    if( ! is_user_logged_in() && is_page($ns_general_settings['support_desk']) ) {
        //i.e. http://example.com/knowledgebase?from=sd
        wp_redirect( add_query_arg( 'from', 'sd', get_permalink($ns_kb_settings['page']) ) );
        exit();
    }
}

add_action( 'pre_get_posts', 'ns_redirect_user_to_correct_place' );


/**
 * Add class to Ticket Edit button
 *
 * Add some NS classes to the WP-default post edit link on the
 * front end to match the UI.
 *
 * @since  1.0.0
 * 
 * @param  string $output Default link.
 * @return string         Modified link with modified class.
 * -----------------------------------------------------------------------
 */
function ns_ticket_edit_post_link( $output ) {
    global $post;
    if( is_single() && 'nanosupport' === $post->post_type ) {
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
 * @since  1.0.0
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
        'title'  => __( 'Visit Support Desk', 'nanosupport' ),
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
if( apply_filters( 'nanosupport_show_admin_bar_visit_support_desk', true ) )
    add_action( 'admin_bar_menu', 'ns_admin_bar_menu', 32 );
