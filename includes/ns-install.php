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

    //get the default notice texts
	global $ns_submit_ticket_notice, $ns_support_desk_notice, $ns_knowledgebase_notice;

    /**
     * Set up the default Settings
     * ...
     */

    // General Settings
    $ns_gen_settings = array(
    	'support_desk'         => $support_desk_page_id,
    	'submit_page'          => $submit_ticket_page_id,
    	'enable_notice'        => absint(1),
    	'submit_ticket_notice' => esc_attr(strip_tags($ns_submit_ticket_notice)),
    	'support_desk_notice'  => esc_attr(strip_tags($ns_support_desk_notice)),
    	'knowledgebase_notice' => esc_attr(strip_tags($ns_knowledgebase_notice)),
    );
    add_option( 'nanosupport_settings', $ns_gen_settings );

    // Knowledgebase settings
    $ns_kb_settings = array(
    	'isactive_kb'   => 1,
    	'page'   		=> $knowledgebase_page_id,
    	'terms'			=> array(''),
    	'ppc'			=> get_option( 'posts_per_page' )
    );
    add_option( 'nanosupport_knowledgebase_settings', $ns_kb_settings );

    // Email settings
    $ns_email_settings = array(
    	'notification_email' => get_option( 'admin_email' ),
    	'email_choices'      => array(
    		'new_ticket'        => 1,
    		'response'          => 1,
    		'agent_response'    => 1
    	)
    );
    add_option( 'nanosupport_email_settings', $ns_email_settings );

    /**
     * Update db version to current
     * ...
     */
    delete_option( 'nanosupport_version' );
    add_option( 'nanosupport_version', NS()->version );

    /**
     * Flush the rewrite rules, soft
     *
     * To activate custom post types' single templates, and
     * taxonomies, we are flushing the rewrite rules, once.
     * ...
     */
    ns_register_cpt_nanosupport();
    ns_register_cpt_nanodoc();
    ns_create_nanodoc_taxonomies();
    ns_create_nanosupport_taxonomies();
    flush_rewrite_rules( false );


    /**
     * Custom Roles and Capabilities
     * Assigning all the NanoSupport user-capabilities to the Upper level users.
     * ...
     */
    ns_create_role();
    ns_add_caps();


    /**
     * -----------------------------------------------------------------------
     * HOOK : ACTION HOOK
     * nanosupport_installed
     *
     * Hook fired just after the completion of installing NanoSupport
     *
     * @since  1.0.0
     * -----------------------------------------------------------------------
     */
    do_action( 'nanosupport_installed' );

}


/**
 * Create role and assign capabilities
 *
 * Create necessary roles for the plugin.
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
			'read'             => true,
			'read_nanosupport' => true
		)
	);
}


/**
 * Get NanoSupport capabilities
 *
 * These are assigned to nanosupport users during installation/reset.
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
			"read_{$capability_type}",
			"read_private_{$capability_type}s",
			"edit_private_{$capability_type}s",
			"edit_{$capability_type}",
			"edit_{$capability_type}s",
			"edit_published_{$capability_type}s",
			"edit_others_{$capability_type}s",
			"publish_{$capability_type}s",
			"delete_{$capability_type}",
			"delete_{$capability_type}s",
			"delete_private_{$capability_type}s",
			"delete_published_{$capability_type}s",
			"delete_others_{$capability_type}s",

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
 *     - 'editor', and
 *     - 'administrator'
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
 *      - 'editor',
 *      - 'administrator',
 *      - 'support_seeker', and
 *      - Support Agents
 * on uninstallation of the plugin.
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
			$wp_roles->remove_cap( 'support_seeker', $cap );
		endforeach;
	endforeach;

	remove_role( 'support_seeker' );

    /**
     * Remove Capacities from Agents
     * ...
     */

    $agent_query = new WP_User_Query( array(
		'meta_key'   => 'ns_make_agent',
		'meta_value' => 1,
    ) );
    if ( ! empty( $agent_query->results ) ) {
    	$capability_type = 'nanosupport';
    	foreach ( $agent_query->results as $user ) {
    		$ns_agent_user = new WP_User($user->ID);

    		$ns_agent_user->remove_cap( "read_{$capability_type}" );
    		$ns_agent_user->remove_cap( "edit_{$capability_type}" );
    		$ns_agent_user->remove_cap( "edit_{$capability_type}s" );
    		$ns_agent_user->remove_cap( "edit_others_{$capability_type}s" );
    		$ns_agent_user->remove_cap( "read_private_{$capability_type}s" );
    		$ns_agent_user->remove_cap( "edit_private_{$capability_type}s" );
    		$ns_agent_user->remove_cap( "edit_published_{$capability_type}s" );

    		$ns_agent_user->remove_cap( "assign_{$capability_type}_terms" );
    	}
    }

}


/**
 * Create Pages
 *
 * Create necessary pages for the plugin.
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
    if( (isset($options['account_creation']['generate_username']) && $options['account_creation']['generate_username'] !== 1) || ! empty($username) ) {

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
    if( (isset($options['account_creation']['generate_password']) && $options['account_creation']['generate_password'] === 1) && empty($password) ) {

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
    	return new WP_Error( 'reg-error', __( 'Couldn&rsquo;t register you', 'nanosupport' ) );
    }

    if( $password_generated )
    	$account_opening_email = nanosupport_handle_account_opening_email( $user_id, $password );
    else
    	$account_opening_email = nanosupport_handle_account_opening_email( $user_id );

    return $user_id;

}


/**
 * Check if plugin dependencies are ready.
 *
 * @return boolean True of dependencies are here, false otherwise.
 * --------------------------------------------------------------------------
 */
function ns_is_dependency_loaded() {
	if( ! file_exists( NS()->plugin_path() .'/assets/css/nanosupport.css' ) ) {
		return false;
	} else if( ! file_exists( NS()->plugin_path() .'/assets/css/nanosupport-admin.css' ) ) {
		return false;
	} else if( ! file_exists( NS()->plugin_path() .'/assets/js/nanosupport.min.js' ) ) {
		return false;
	} else if( ! file_exists( NS()->plugin_path() .'/assets/js/nanosupport-admin.min.js' ) ) {
		return false;
	} else if( ! file_exists( NS()->plugin_path() .'/assets/js/nanosupport-dashboard.min.js' ) ) {
		return false;
	}

	return true;
}


/**
 * Check whether the plugin is compatible to WordPress version
 *
 * @return boolean True of WordPress version supported, false otherwise.
 * --------------------------------------------------------------------------
 */
function ns_is_version_supported() {
	if ( version_compare( get_bloginfo( 'version' ), NS()->wp_version, '<=' ) ) {
		return false;
	}

	return true;
}


/**
 * Admin notices: Failed version dependency.
 * --------------------------------------------------------------------------
 */
function ns_fail_version_admin_notice() {
	echo '<div class="updated"><p>';
		printf(
			/* translators: 1. minimum WordPress core version 2. WordPress update page URL */
			wp_kses( __('NanoSupport requires WordPress core version <strong>%1$s</strong> or greater. The plugin has been deactivated. Consider <a href="%2$s">upgrading WordPress</a>.', 'nanosupport' ),
				array( 'a' => array('href' => true), 'strong' => array() )
			),
			NS()->wp_version,
			admin_url('update-core.php')
		);
	echo '</p></div>';
}

/**
 * Admin notices: Failed resouces dependency.
 * --------------------------------------------------------------------------
 */
function ns_fail_dependency_admin_notice() {
	echo '<div class="updated"><p>';
		printf(
			/* translators: 1. first command 2. second command 3. plugin installation link with popup thickbox (modal) */
			wp_kses( __( 'NanoSupport&rsquo;s required dependencies are not loaded - plugin cannot function properly. Open the command console and run %1$s and then %2$s before anything else. If you are unaware what this is, please <a href="%3$s" class="thickbox">install the production version</a> instead.', 'nanosupport' ),
				array( 'a' => array('href' => true, 'class' => true) )
			),
			'<code>npm install</code>',
			'<code>grunt</code>',
			esc_url( add_query_arg( array(
				'tab'           => 'plugin-information',
				'plugin'        => 'nanosupport',
				'TB_iframe'     => 'true',
				'width'         => '600',
				'height'        => '800'
			), admin_url('plugin-install.php') ) )
		);
	echo '</p></div>';
}


/**
 * Deactivate the plugin
 * Deactivate the plugin forcefully on unmet dependencies.
 * --------------------------------------------------------------------------
 */
function ns_force_deactivate() {
	deactivate_plugins( NS()->plugin_basename() );
}
