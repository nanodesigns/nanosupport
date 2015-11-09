<?php
/**
 * Setup Functions
 * 
 * Functions that are used for Setting up the plugin.
 *
 * @package  Nano Support Ticket
 * =======================================================================
 */

/**
 * Styles & JavaScripts (Admin)
 * 
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 * -----------------------------------------------------------------------
 */
function nst_admin_scripts() {
    $screen = get_current_screen();
    if( 'nanosupport' === $screen->post_type ) {

        global $current_user;
        
        $date_time_row          = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $date_time_formatted    = date( 'd F Y h:i:s A - l', current_time( 'timestamp' ) );

		wp_enqueue_style( 'nst-admin-styles', NST()->plugin_url() .'/assets/css/nst-admin.css', array(), NST()->version, 'all' );
		
		wp_enqueue_script( 'nst-admin-scripts', NST()->plugin_url() .'/assets/js/nst-admin.min.js', array('jquery'), NST()->version, true );

		wp_localize_script(
			'nst-admin-scripts',
			'nst',
			array(
                'current_user'          => $current_user->display_name,
                'user_id'               => $current_user->ID,
                'date_time_now'         => $date_time_row,
                'date_time_formatted'   => $date_time_formatted
            ) );		
	}
}
add_action( 'admin_enqueue_scripts', 'nst_admin_scripts' );


/**
 * Styles & JavaScripts (Front End)
 * 
 * Necessary JavaScripts and Styles for Front-end tweaks.
 * -----------------------------------------------------------------------
 */
function nst_scripts() {
    if( is_page('support-desk') || is_page('submit-ticket') || is_singular('nanosupport') ) {
        wp_enqueue_style( 'nst-bootstrap', NST()->plugin_url() .'/assets/css/bootstrap.min.css', array(), NST()->version, 'all' );
		wp_enqueue_style( 'nst-styles', NST()->plugin_url() .'/assets/css/nst-styles.css', array(), NST()->version, 'all' );
	}
}
add_action( 'wp_enqueue_scripts', 'nst_scripts' );


/**
 * Support Agent User Meta Field
 * 
 * Support Agent selection user meta field.
 * 
 * @param  obj $user Get the user data from WP_User object.
 * -----------------------------------------------------------------------
 */
function nst_user_fields( $user ) { ?>
        <h3><?php _e( 'Nanodesigns Support', 'nano-support-ticket' ); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row">
                	<span class="dashicons dashicons-businessman"></span> <?php _e( 'Make Support Agent', 'nano-support-ticket' ); ?>
                </th>
                <td>
                	<label>
                		<input type="checkbox" name="nst_make_agent" id="nst-make-agent" value="1" <?php checked( get_the_author_meta( 'nst_make_agent', $user->ID ), 1 ); ?> /> <?php _e( 'Yes, make this user a Support Agent', 'nano-support-ticket' ); ?>
                	</label>
                </td>
            </tr>
        </table>
<?php
}
add_action( 'show_user_profile', 'nst_user_fields' );
add_action( 'edit_user_profile', 'nst_user_fields' );


/**
 * Saving the user meta fields
 * 
 * @param  integer $user_id User id.
 * -----------------------------------------------------------------------
 */
function nst_saving_user_fields( $user_id ) {
    update_user_meta( $user_id, 'nst_make_agent', intval( $_POST['nst_make_agent'] ) );
}
add_action( 'personal_options_update', 	'nst_saving_user_fields' );
add_action( 'edit_user_profile_update', 'nst_saving_user_fields' );


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
function nst_force_ticket_post_status_to_private( $post ) {
    if ( 'nanosupport' === $post['post_type'] )
        $post['post_status'] = 'private';
    return $post;
}
add_filter( 'wp_insert_post_data', 'nst_force_ticket_post_status_to_private' );


if ( ! function_exists( 'nst_content' ) ) {

    /**
     * Output WooCommerce content.
     *
     * This function is only used in the optional 'woocommerce.php' template
     * which people can add to their themes to add basic woocommerce support
     * without hooks or modifying core templates.
     *
     */
    function nst_content() {

        if ( is_singular( 'nanosupport' ) ) {

            while ( have_posts() ) : the_post();

                nst_get_template_part( 'content', 'single-nanosupport' );

            endwhile;

        } else { ?>

            <h1 class="page-title"><?php the_title(); ?></h1>

            <?php if ( have_posts() ) : ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php nst_get_template_part( 'content', 'ticket' ); ?>

                    <?php endwhile; // end of the loop. ?>

                <?php _e('Ticket has no content'); ?>

            <?php endif;

        }
    }
}


function template_loader( $template ) {
    $find = array('nano-support-ticket.php');
    $file = '';

    if ( is_single() && get_post_type() == 'nanosupport' ) {

        $file   = 'single-nanosupport.php';
        $find[] = $file;
        $find[] = NST()->template_path() . $file;

    }

    if ( $file ) {
        $template       = locate_template( array_unique( $find ) );
        if ( ! $template ) {
            $template = NST()->plugin_path() .'/templates/'. $file;
        }
    }

    return $template;
}
add_filter( 'template_include', 'template_loader' );