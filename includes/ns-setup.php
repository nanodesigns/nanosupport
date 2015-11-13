<?php
/**
 * Setup Functions
 * 
 * Functions that are used for Setting up the plugin.
 *
 * @package  NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Styles & JavaScripts (Admin)
 * 
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 * -----------------------------------------------------------------------
 */
function ns_admin_scripts() {
    $screen = get_current_screen();
    if( 'nanosupport' === $screen->post_type || 'nanosupport_page_nanosupport-settings' === $screen->base ) {

        global $current_user;
        
        $date_time_row          = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $date_time_formatted    = date( 'd F Y h:i:s A - l', current_time( 'timestamp' ) );

        wp_enqueue_style( 'ns-admin-styles', NS()->plugin_url() .'/assets/css/ns-admin.css', array(), NS()->version, 'all' );
		
        /** Select2 **/
        wp_enqueue_style( 'select2-styles', NS()->plugin_url() .'/assets/css/select2.min.css', array(), '4.0.1-rc-1', 'all' );
        wp_enqueue_script( 'select2-scripts', NS()->plugin_url() .'/assets/js/select2.min.js', array('jquery'), '4.0.1-rc-1', true );

        wp_enqueue_script( 'ns-admin-scripts', NS()->plugin_url() .'/assets/js/ns-admin.min.js', array('jquery'), NS()->version, true );

		wp_localize_script(
			'ns-admin-scripts',
			'ns',
			array(
                'current_user'          => $current_user->display_name,
                'user_id'               => $current_user->ID,
                'date_time_now'         => $date_time_row,
                'date_time_formatted'   => $date_time_formatted
            ) );		
	}
}
add_action( 'admin_enqueue_scripts', 'ns_admin_scripts' );


/**
 * Styles & JavaScripts (Front End)
 * 
 * Necessary JavaScripts and Styles for Front-end tweaks.
 * -----------------------------------------------------------------------
 */
function ns_scripts() {
    if( is_page('support-desk') || is_page('submit-ticket') || is_singular('nanosupport') ) {
        wp_enqueue_style( 'ns-bootstrap', NS()->plugin_url() .'/assets/css/bootstrap.min.css', array(), NS()->version, 'all' );
		wp_enqueue_style( 'ns-styles', NS()->plugin_url() .'/assets/css/ns-styles.css', array(), NS()->version, 'all' );
	}
}
add_action( 'wp_enqueue_scripts', 'ns_scripts' );


/**
 * Support Agent User Meta Field
 * 
 * Support Agent selection user meta field.
 * 
 * @param  obj $user Get the user data from WP_User object.
 * -----------------------------------------------------------------------
 */
function ns_user_fields( $user ) { ?>
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
<?php
}
add_action( 'show_user_profile', 'ns_user_fields' );
add_action( 'edit_user_profile', 'ns_user_fields' );


/**
 * Saving the user meta fields
 * 
 * @param  integer $user_id User id.
 * -----------------------------------------------------------------------
 */
function ns_saving_user_fields( $user_id ) {
    update_user_meta( $user_id, 'ns_make_agent', intval( $_POST['ns_make_agent'] ) );
}
add_action( 'personal_options_update', 	'ns_saving_user_fields' );
add_action( 'edit_user_profile_update', 'ns_saving_user_fields' );


/**
 * Force Post Status to Private.
 *
 * Force all the ticket post status default to 'Private' instead of 'Publish'.
 *
 * @link http://wpsnipp.com/index.php/functions-php/force-custom-post-type-to-be-private/
 * 
 * @param  object $post Post object.
 * @return object       Modified post object.
 * -----------------------------------------------------------------------
 */
function ns_force_ticket_post_status_to_private( $post ) {
    if ( 'nanosupport' === $post['post_type'] )
        $post['post_status'] = 'private';
    return $post;
}
add_filter( 'wp_insert_post_data', 'ns_force_ticket_post_status_to_private' );


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

                <?php _e('Ticket has no content'); ?>

            <?php endif;

        }
    }
}


function ns_template_loader( $template ) {
    $find = array('nano-support.php');
    $file = '';

    if ( is_single() && get_post_type() == 'nanosupport' ) {

        $file   = 'single-nanosupport.php';
        $find[] = $file;
        $find[] = NS()->template_path() . $file;

    }

    if ( $file ) {
        $template       = locate_template( array_unique( $find ) );
        if ( ! $template ) {
            $template = NS()->plugin_path() .'/templates/'. $file;
        }
    }

    return $template;
}
add_filter( 'template_include', 'ns_template_loader' );


function ns_the_title_trim( $title ) {

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