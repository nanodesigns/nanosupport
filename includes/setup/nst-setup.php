<?php
/**
 * Setup Functions.
 * 
 * Functions that are used for Setting up the plugin.
 *
 * @package  nano_support_ticket
 * =======================================================================
 */


/**
 * Styles & JavaScripts (Admin).
 * 
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 * -----------------------------------------------------------------------
 */
function nst_admin_scripts() {
	global $_nst_version, $_nst_plugin_url;
	$screen = get_current_screen();

	if( 'nanosupport' === $screen->post_type ) {
		wp_enqueue_style( 'nst-admin-styles', $_nst_plugin_url .'/assets/css/nst-admin.css', array(), $_nst_version, 'all' );
		
		wp_enqueue_script( 'nst-admin-scripts', $_nst_plugin_url .'/assets/js/nst-admin.min.js', array('jquery'), $_nst_version, true );

		wp_localize_script(
			'nst-admin-scripts',
			'nst',
			array() );		
	}
}
add_action( 'admin_enqueue_scripts', 'nst_admin_scripts' );


/**
 * Styles & JavaScripts (Front End).
 * 
 * Necessary JavaScripts and Styles for Front-end tweaks.
 * -----------------------------------------------------------------------
 */
function nst_scripts() {
	global $_nst_version;
    //if( is_page($nst_basic_options['support_desk']) || is_page($nst_basic_options['submit_ticket']) ) {
    $bootstrap = 1;
        if( 1 === $bootstrap ) {
            wp_enqueue_style( 'nst-bootstrap', $_nst_plugin_url .'assets/css/bootstrap.min.css', array(), $_nst_version, 'all' );
        }
		wp_enqueue_style( 'nst-styles', $_nst_plugin_url .'assets/css/nst-styles.css', array(), $_nst_version, 'all' );
	//}
}
add_action( 'wp_enqueue_scripts', 'nst_scripts' );


/**
 * Support Agent User Meta Field.
 * 
 * Support Agent selection user meta field.
 * 
 * @param  obj $user Get the user data from WP_User object.
 * -----------------------------------------------------------------------
 */
function nst_user_fields( $user ) { ?>
        <h3><?php _e( 'Nanodesigns Support', 'nanodesigns-nst' ); ?></h3>

        <table class="form-table">
            <tr>
                <th scope="row">
                	<span class="dashicons dashicons-businessman"></span> <?php _e( 'Make Support Agent', 'nanodesigns-nst' ); ?>
                </th>
                <td>
                	<label>
                		<input type="checkbox" name="nst_make_agent" id="nst-make-agent" value="1" <?php checked( get_the_author_meta( 'nst_make_agent', $user->ID ), 1 ); ?> /> <?php _e( 'Yes, make this user a Support Agent', 'nanodesigns-nst' ); ?>
                	</label>
                </td>
            </tr>
        </table>
<?php
}
add_action( 'show_user_profile', 'nst_user_fields' );
add_action( 'edit_user_profile', 'nst_user_fields' );


/**
 * Saving the user meta fields.
 * 
 * @param  integer $user_id User id.
 * -----------------------------------------------------------------------
 */
function nst_saving_user_fields( $user_id ) {
    update_user_meta( $user_id, 'nst_make_agent', intval( $_POST['nst_make_agent'] ) );
}
add_action( 'personal_options_update', 	'nst_saving_user_fields' );
add_action( 'edit_user_profile_update', 'nst_saving_user_fields' );