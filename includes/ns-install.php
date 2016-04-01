<?php
/**
 * Installation related functions and actions
 *
 * @author   	nanodesigns
 * @category 	Core
 * @package  	NanoSupport/Classes
 * @version  	1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initiate the plugin
 * 
 * Register all the necessary things when the plugin get activated.
 *
 * @since   1.0.0
 *
 * @return  void
 */
function nanosupport_install() {

    //create a page to display all the tickets or my tickets
	$support_desk_page_id = ns_create_page(
                                'Support Desk',                 //page title
                                'support-desk',                 //page slug
                                '[nanosupport_desk]'            //content (shortcode)
                            );

    //create another page to show support ticket-taking form to get the support tickets
    $submit_ticket_page_id = ns_create_page(
                                'Submit Ticket',                //page title
                                'submit-ticket',                //page slug
                                '[nanosupport_submit_ticket]'   //content (shortcode)
                            );

    //create another page to show the knowledgebase
    $knowledgebase_page_id = ns_create_page(
                                'Knowledgebase',                //page title
                                'knowledgebase',                //page slug
                                '[nanosupport_knowledgebase]'   //content (shortcode)
                            );

    /**
     * Set up the default Settings
     * ...
     */
    
    // General Settings
    $ns_gen_settings = array(
            'support_desk'	=> $support_desk_page_id,
            'submit_page'	=> $submit_ticket_page_id
        );
    update_option( 'nanosupport_settings', $ns_gen_settings );

    // Knowledgebase settings
    $ns_kb_settings = array(
            'page'   		=> $knowledgebase_page_id,
            'terms'			=> array(''),
            'ppc'			=> get_option( 'posts_per_page' )
        );
    update_option( 'nanosupport_knowledgebase_settings', $ns_kb_settings );

    // Email settings
    $ns_email_settings = array(
            'notification_email'	=> get_option( 'admin_email' )
        );
    update_option( 'nanosupport_email_settings', $ns_email_settings );

    /**
     * Update db version to current
     * @since  1.0.0
     * ...
     */
    delete_option( 'nanosupport_version' );
	add_option( 'nanosupport_version', NS()->version );

    /**
     * Flush the rewrite rules, soft
     * 
     * To activate custom post types' single templates,
     * we are flushing the rewrite rules, once.
     *
     * @since  1.0.0
     * ...
     */
    ns_register_cpt_nanosupport();
    ns_register_cpt_nanodoc();
    flush_rewrite_rules( false );


    /**
     * Custom Roles and Capabilities
     * Assigning all the NanoSupport user-capabilities to the Upper level users.
     * ...
     */
    ns_add_caps();
    
}

/**
 * Cross Check Requirements when active
 *
 * Cross check for Current WordPress version is
 * greater than 3.9.0. Cross check whether the user
 * has privilege to activate_plugins, so that notice
 * cannot be visible to any non-admin user.
 *
 * @link   http://10up.com/blog/2012/wordpress-plug-in-self-deactivation/
 * 
 * @since  1.0.0
 * 
 * @return void
 */
function ns_cross_check_on_activation() {
	if ( version_compare( get_bloginfo( 'version' ), '3.9.0', '<=' ) ) {

		if ( current_user_can( 'activate_plugins' ) ) {

			add_action( 'admin_init',		'ns_force_deactivate' );
			add_action( 'admin_notices',	'ns_fail_dependency_admin_notice' );

			function ns_force_deactivate() {
				deactivate_plugins( NS_PLUGIN_BASENAME );
			}

			function ns_fail_dependency_admin_notice() {
				echo '<div class="updated"><p>';
					printf( __('<strong>NanoSupport</strong> requires WordPress core version <strong>3.9.0</strong> or greater. The plugin has been <strong>deactivated</strong>. Consider <a href="%s">upgrading WordPress</a>.', 'nanosupport' ), admin_url('/update-core.php') );
				echo '</p></div>';

				if ( isset( $_GET['activate'] ) )
					unset( $_GET['activate'] );
			}

		}

	}
}

add_action( 'plugins_loaded', 'ns_cross_check_on_activation' );


/**
 * Add Settings link on plugin page
 *
 * Add a 'Settings' link to the Admin Plugin page after the activation
 * of the plugin. So the user can easily get to the Settings page, and
 * can setup the plugin as necessary.
 *
 * @since  1.0.0
 * 
 * @param  array $links  Links on the plugin page per plugin.
 * @return array         Modified with our link.
 */
function ns_plugin_settings_link( $links ) {
	//$settings_link = '/wp-admin/edit.php?post_type=nanosupport&page=nanosupport-settings';
	$settings_link = '<a href="'. esc_url( admin_url( 'edit.php?post_type=nanosupport&page=nanosupport-settings' ) ) .'" title="'. esc_attr__( 'Set the NanoSupport settings', 'nanosupport' ) .'">'. __( 'Settings', 'nanosupport' ) .'</a>';

	array_unshift($links, $settings_link); //make the settings link be first item
	return $links;
}

add_filter( 'plugin_action_links_'. NS_PLUGIN_BASENAME, 'ns_plugin_settings_link' );


/**
 * Get NanoSupport capabilities
 * 
 * These are assigned to administrator + editor during installation/reset.
 *
 * @since  1.0.0
 *
 * @return array
 */
function ns_get_caps() {

	$capabilities = array();

	$capabilities['core'] = array(
		'manage_nanosupport',
		'view_nanosupport_reports'
	);

	$capability_types = array( 'nanosupport', 'nanodoc' );

	foreach ( $capability_types as $capability_type ) {

		$capabilities[ $capability_type ] = array(
			// Post type
			"edit_{$capability_type}",
			"read_{$capability_type}",
			"delete_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"read_private_{$capability_type}s",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_published_{$capability_type}s",

			// Terms
			"manage_{$capability_type}_terms",
			"edit_{$capability_type}_terms",
			"delete_{$capability_type}_terms",
			"assign_{$capability_type}_terms"
		);
	}

	return $capabilities;
}


/**
 * Adding Custom Capabilities
 * 
 * Adding Custom Capabilities to:
 * 	- 'editor', and
 * 	- 'administrator'
 *
 * @since   1.0.0
 *
 * @return  void
 */
function ns_add_caps() {
	global $wp_roles;

	if( ! class_exists(WP_Roles) )
		return;

	if( ! isset($wp_roles) )
		$wp_roles = new WP_Roles();

	$ns_capabilities = ns_get_caps();

	foreach ( $ns_capabilities as $cap_group ) :
		foreach ( $cap_group as $cap ) :
			$wp_roles->add_cap( 'editor', $cap );
			$wp_roles->add_cap( 'administrator', $cap );
		endforeach;
	endforeach;
}


/**
 * Removing Custom Capabilities
 * 
 * Removing Custom Capabilities from:
 * 	- 'editor', and
 * 	- 'administrator'
 * on uninstallation of the plugin.
 *
 * @since   1.0.0
 *
 * @return  void
 */
function ns_remove_caps() {
	global $wp_roles;

	if( ! class_exists(WP_Roles) )
		return;

	if( ! isset($wp_roles) )
		$wp_roles = new WP_Roles();

	$ns_capabilities = ns_get_caps();

	foreach( $ns_capabilities as $cap_group ) :
		foreach( $cap_group as $cap ) :
			$wp_roles->remove_cap( 'editor', $cap );
			$wp_roles->remove_cap( 'administrator', $cap );
		endforeach;
	endforeach;

}


/**
 * Create Pages
 * 
 * Create necessary pages for the plugin.
 *
 * @since  1.0.0
 * 
 * @param  string $title   Title of the page.
 * @param  string $slug    Hyphenated slug of the page.
 * @param  string $content Anything of a wide range of alphanumeric contents.
 * @return integer         ID of the page that is created or already exists.
 * -----------------------------------------------------------------------
 */
function ns_create_page( $title, $slug, $content ) {

    global $current_user;

    //set a default so that we can check nothing happened
    $page_id = false;

    $ns_check_page = get_page_by_path( $slug ); //default post type 'page'

    if( null === $ns_check_page ) {

        //set the page_id as the page created
        $page_id = wp_insert_post( array(
                                        'post_title'        => sanitize_text_field( $title ),
                                        'post_name'         => sanitize_text_field( $slug ),
                                        'post_content'      => htmlentities( $content ),
                                        'post_status'       => 'publish',
                                        'post_type'         => 'page',
                                        'post_author'       => absint( $current_user->ID ),
                                        'comment_status'    => 'closed',
                                        'ping_status'       => 'closed'
                                    ) );

        return $page_id;

    } else {

        return $ns_check_page->ID;

    }

}
