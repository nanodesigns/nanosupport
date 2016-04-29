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
 * -----------------------------------------------------------------------
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
            'submit_page'   => $submit_ticket_page_id,
            'enable_notice'	=> 1,
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
    ns_create_role();
    ns_add_caps();
    
}


/**
 * Create role and assign capabilities
 *
 * Create necessary roles for the plugin.
 * 
 * @since  1.0.0
 * 
 * @return void
 * -----------------------------------------------------------------------
 */
function ns_create_role() {
    global $wp_roles;

    if( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if( ! isset($wp_roles) ) {
        $wp_roles = new WP_Roles();
    }

    //Role: Support Seeker
    add_role(
        'support_seeker',
        __( 'Support Seeker', 'nanosupport' ),
        array(
            'read'                      => true,
            'read_nanosupport'          => true
        )
    );
}


/**
 * Get NanoSupport capabilities
 * 
 * These are assigned to administrator + editor during installation/reset.
 *
 * @since  1.0.0
 *
 * @return array
 * -----------------------------------------------------------------------
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
 * -----------------------------------------------------------------------
 */
function ns_add_caps() {
	global $wp_roles;

	if( ! class_exists( 'WP_Roles' ) )
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
 * -----------------------------------------------------------------------
 */
function ns_remove_caps() {
	global $wp_roles;

	if( ! class_exists( 'WP_Roles' ) )
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

    remove_role( 'support_seeker' );

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
function ns_create_page( $title, $slug, $content, $post_parent = 0 ) {

    global $wpdb;

    //set a default so that we can check nothing happened
    $page_id = false;

    if( strlen( $content ) > 0 ) {
        // Search for an existing page with the specified page content (typically a shortcode)
        $active_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
    } else {
        // Search for an existing page with the specified page slug
        $active_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
    }

    if( $active_page_found )
        return (int) $active_page_found;

    // Search for a matching valid trashed page
    if( strlen( $content ) > 0 ) {
        // Search for an existing page with the specified page content (typically a shortcode)
        $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$content}%" ) );
    } else {
        // Search for an existing page with the specified page slug
        $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
    }

    if ( $trashed_page_found ) :

        $page_id   = $trashed_page_found;
        wp_update_post( array(
                            'ID'             => $page_id,
                            'post_status'    => 'publish',
                        ) );

    else :

        $page_id = wp_insert_post( array(
                                    'post_status'    => 'publish',
                                    'post_type'      => 'page',
                                    'post_author'    => 1,
                                    'post_name'      => $slug,
                                    'post_title'     => $title,
                                    'post_content'   => $content,
                                    'post_parent'    => $post_parent,
                                    'comment_status' => 'closed'
                                ) );

    endif;

    return (int) $page_id;

}


/**
 * Create a Support Seeker user account
 *
 * Create a Support Seeker user account on ticket submission
 * for non-logged in users only.
 *
 * @since  1.0.0
 * 
 * @param  string $email    Email provided by the user.
 * @param  string $username Username | null
 * @param  string $password Password | null
 * @return integer|mixed    Registered user ID | Error
 * -----------------------------------------------------------------------
 */
function ns_create_support_seeker( $email, $username = '', $password = '', $antispam = '' ) {

    /**
     * Make the email address ready
     */
    if ( empty($email) || ! is_email($email) ) {
        return new WP_Error( 'reg-error-email-invalid', __( 'Please provide a valid email address', 'nanosupport' ) );
    }

    if ( email_exists($email) ) {
        return new WP_Error( 'reg-error-email-exists', __( 'An account is already registered with your email address. Please login', 'nanosupport' ) );
    }

    /**
     * Make the username ready
     */
    $options = get_option( 'nanosupport_settings' );
    if( $options['account_creation']['generate_username'] !== 1 || ! empty($username) ) {

        //Get the username
        $username = sanitize_user( $username );

        if( empty($username) || ! validate_username($username) ) {
            return new WP_Error( 'reg-error-username-invalid', __( 'Please enter a valid username for creating an account', 'nanosupport' ) );
        }

        if( username_exists($username) ) {
            return new WP_Error( 'reg-error-username-exists', __( 'An account is already registered with that username. Please choose another', 'nanosupport' ) );
        }

    } else {

        //Generate the username from email
        $username = sanitize_user( current( explode( '@', $email ) ), true );

        //Ensure username is unique
        $append         = 1;
        $temp_username  = $username;

        while( username_exists($username) ) {
            $username = $temp_username . $append;
            $append++;
        }

    }

    /**
     * Make the password ready
     */
    if( $options['account_creation']['generate_password'] === 1 && empty($password) ) {

        //Generate the password automatically
        $password = wp_generate_password();
        $password_generated = true;

    } elseif( empty($password) ) {

        return new WP_Error( 'reg-error-password-missing', __( 'Please enter a password for your account', 'nanosupport' ) );

        $password_generated = false;

    } else {

        if ( strlen($password) < 5 ) {
            return new WP_Error( 'reg-error-password-short', __( 'Password length must be greater than 5 characters', 'nanosupport' ) );
        }

        $password_generated = false;

    }

    //Anti-spam HoneyPot Trap Validation
    if ( ! empty( $antispam ) ) {
        return new WP_Error( 'reg-error-spam-detected', __( 'Anti-spam field was filled in. Spam account cannot pass in', 'nanosupport' ) );
    }

    //WP Validation
    $validation_errors = new WP_Error();

    if( $validation_errors->get_error_code() )
        return $validation_errors;

    /**
     * -----------------------------------------------------------------------
     * HOOK : FILTER HOOK
     * nanosupport_new_support_seeker_data
     * 
     * @since  1.0.0
     *
     * @param array  $text New user data to be enterred for creating account.
     * -----------------------------------------------------------------------
     */
    $new_support_seeker_data = apply_filters( 'nanosupport_new_support_seeker_data', array(
        'user_login' => $username,
        'user_email' => $email,
        'user_pass'  => $password,
        'role'       => 'support_seeker'
    ) );

    $user_id = wp_insert_user( $new_support_seeker_data );

    if( is_wp_error($user_id) ) {
        return new WP_Error( 'reg-error', __( 'Couldn&#8217;t register you', 'nanosupport' ) );
    }

    if( $password_generated )
        $account_opening_email = nanosupport_handle_account_opening_email( $user_id, $password );
    else
        $account_opening_email = nanosupport_handle_account_opening_email( $user_id );

    return $user_id;

}
