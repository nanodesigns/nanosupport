<?php
/**
 * Settings Page
 *
 * Showing a settings page for the Plugin setup.
 *
 * @author  	nanodesigns
 * @category 	Settings API
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page using WP Settings API.
 *
 * @link   http://ottopress.com/2009/wordpress-settings-api-tutorial/
 * @link   https://www.chipbennett.net/2011/02/17/incorporating-the-settings-api-in-wordpress-themes/?all=1
 * @link   https://wordpress.stackexchange.com/a/127499/22728
 * -----------------------------------------------------------------------
 */
function ns_settings_page() {
	add_submenu_page(
        'edit.php?post_type=nanosupport',       //$parent_slug
        esc_html__( 'Settings', 'nanosupport' ),        //$page_title
        esc_html__( 'Settings', 'nanosupport' ),        //$menu_title
        'manage_nanosupport',                   //$capability
        'nanosupport-settings',                 //$menu_slug
        'nanosupport_settings_page_callback'    //callback function
    );
}
add_action( 'admin_menu', 'ns_settings_page' );

/**
 * Register the Settings Options.
 * ...
 */
function nanosupport_settings_options_init(){

    /**
     * Tab: Basic Settings
     *  - Support Desk Page
     *  - Submit Ticket Page
     *  - Set ticket character limit
     *  - Enable/Disable notices
     *  - Submit Ticket Notice
     *  - Support Desk Notice
     *  - Knowledgebase Notice
     *  - Ticket highlight choice
     *  - Priority visibility setup
     *  - Department visibility setup
     *  - Enable/Disable embedded login
     * ----------------------------------
     */
    add_settings_section(
        'ns_general',                                   // ID/Slug*
        esc_html__( 'General Settings', 'nanosupport' ), // Name*
        'ns_general_settings_section_callback',         // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );
    register_setting(
        'nanosupport_settings',                         // Option group*
        'nanosupport_settings',                         // Option Name (db)*
        'ns_general_settings_validate'                  // Sanitize Callback Function
    );
	    add_settings_field(
	            'support_desk_page',                        // ID*
	            '<label for="ns_support_desk">'. esc_html__( 'Support Desk Page', 'nanosupport' ) .'</label>', // Title*
	            'ns_support_desk_field',                    // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'submit_ticket_page',                       // ID*
	            '<label for="ns_submit_ticket">'. esc_html__( 'Ticket Submission Page', 'nanosupport' ) .'</label>', // Title*
	            'ns_submit_ticket_field',                   // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'ticket_char_limit',                        // ID*
	            /* translators: context: ...limit to # characters */
	            '<label for="ticket_char_limit">'. esc_html__( 'Set ticket character limit to', 'nanosupport' ) .'</label>', // Title*
	            'ns_ticket_character_limit',                // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'enable_notice',                            // ID*
	            esc_html__( 'Enable Notice?', 'nanosupport' ), // Title*
	            'ns_enable_notice',                         // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'submit_ticket_notice',                     // ID*
	            '<label for="submit_ticket_notice">'. esc_html__( 'Submit Ticket Notice', 'nanosupport' ) .'</label>', // Title*
	            'ns_submit_ticket_notice',                  // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'support_desk_notice',                      // ID*
	            '<label for="support_desk_notice">'. esc_html__( 'Support Desk Notice', 'nanosupport' ) .'</label>', // Title*
	            'ns_support_desk_notice',                   // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'knowledgebase_notice',                     // ID*
	            '<label for="knowledgebase_notice">'. esc_html__( 'Knowledgebase Notice', 'nanosupport' ) .'</label>', // Title*
	            'ns_knowledgebase_notice',                  // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'highligh_ticket',                          // ID*
	            '<label for="ns_highlight_ticket">'. esc_html__( 'Highlight Ticket with', 'nanosupport' ) .'</label>', // Title*
	            'ns_highlight_ticket_field',                // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'is_priority_visible',                      // ID*
	            esc_html__( 'Is Priority Visible?', 'nanosupport' ), // Title*
	            'ns_is_priority_visible_field',             // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'is_department_visible',                    // ID*
	            esc_html__( 'Is Departments Visible?', 'nanosupport' ), // Title*
	            'ns_is_department_visible_field',           // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );
	    add_settings_field(
	            'embedded_login',                           // ID*
	            esc_html__( 'Enable Embedded Login?', 'nanosupport' ), // Title*
	            'ns_embedded_login_field',                  // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_general'                                // Section
	        );

    /**
     * Section: Account Settings
     *  - Enable Login/Registration?
     *  - Account Creation
     *      - Generate Username?
     *      - Generate Password?
     * ----------------------------------
     */
    add_settings_section(
        'ns_account',                                   // ID/Slug*
        esc_html__( 'Account Settings', 'nanosupport' ), // Name*
        'ns_account_settings_section_callback',         // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );
	    add_settings_field(
	            'account_creation',                         // ID*
	            esc_html__( 'Account Creation', 'nanosupport' ), // Title*
	            'ns_account_creation_field',                // Callback Function*
	            'nanosupport_settings',                     // Page (Plugin)*
	            'ns_account'                                // Section
	        );

    /**
     * Section: Ecommerce Settings
     *  - Enable ecommerce product support?
     * ----------------------------------
     */
    add_settings_section(
        'ns_ecommerce',                                 // ID/Slug*
        esc_html__( 'E-commerce Settings', 'nanosupport' ),  // Name*
        'ns_ecommerce_settings_section_callback',       // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );

	    $NSECommerce = new NSECommerce();

	    if( $NSECommerce->is_plugins_active() ) {
            /**
             * Tab: General Settings
             * Section: Ecommerce Settings
             *  - Enable Ecommerce
             * ----------------------------------
             */
            add_settings_field(
                'enable_ecommerce',                             // ID*
                esc_html__( 'Enable E-commerce Support?', 'nanosupport' ), // Title*
                'ns_enable_ecommerce_field',                    // Callback Function*
                'nanosupport_settings',                         // Page (Plugin)*
                'ns_ecommerce'                                  // Section
            );

            /**
             * Tab: General Settings
             * Section: Ecommerce Settings
             *  - Excluded Products
             * ----------------------------------
             */
            add_settings_field(
                'excluded_products',                             // ID*
                '<label for="ns_excluded_products">'. esc_html__( 'Exclude Products', 'nanosupport' ) .'</label>', // Title*
                'ns_excluded_products_field',                    // Callback Function*
                'nanosupport_settings',                          // Page (Plugin)*
                'ns_ecommerce'                                   // Section
            );
        }

    /**
     * Section: Other Settings
     *  - Delete Data?
     * ----------------------------------
     */
    add_settings_section(
        'ns_others',                                    // ID/Slug*
        esc_html__( 'Other Settings', 'nanosupport' ),  // Name*
        'ns_other_settings_section_callback',           // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );
    	add_settings_field(
            'delete_data',                              // ID*
            esc_html__( 'Delete Data?', 'nanosupport' ), // Title*
            'ns_delete_data_field',                     // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_others'                                 // Section
        );


    /**
     * Tab: Knowledgebase Settings
     * Section: Knowledgebase
     *  - Enable knowledgebase.
     *  - Knowledgebase page.
     *  - Featured categories.
     *  - Number of items per category.
     *  - Knowledgebase URL rewriting.
     * ----------------------------------
     */
    add_settings_section(
        'ns_knowledgebase',                                     // ID/Slug*
        esc_html__( 'Knowledgebase Settings', 'nanosupport' ),  // Name*
        'ns_knowledgebase_settings_section_callback',           // Callback*
        'nanosupport_knowledgebase_settings'                    // Page/Tab where to add the section of options*
    );
    register_setting(
        'nanosupport_knowledgebase_settings',                   // Option group*
        'nanosupport_knowledgebase_settings',                   // Option Name*
        'ns_knowledgebase_settings_validate'                    // Sanitize Callback Function
    );
        /**
         * Tab: Knowledgebase Settings
         *  - Activate Knowledgebase
         * ----------------------------------
         */
        add_settings_field(
            'isactive_knowledgebase',                           // ID*
            esc_html__( 'Activate Knowledgebase?', 'nanosupport' ), // Title*
            'ns_isactive_knowledgebase_field',                  // Callback Function*
            'nanosupport_knowledgebase_settings',               // Page (Plugin)*
            'ns_knowledgebase'                                  // Section
        );

        /**
         * Tab: Knowledgebase Settings
         *  - Knowledgebase page
         * ----------------------------------
         */
        add_settings_field(
            'knowledgebase',                                    // ID*
            '<label for="ns_knowledgebase">'. esc_html__( 'Knowledgebase Page', 'nanosupport' ) .'</label>',  // Title*
            'ns_knowledgebase_page_field',                      // Callback Function*
            'nanosupport_knowledgebase_settings',               // Page (Plugin)*
            'ns_knowledgebase'                                  // Section
        );
        /**
         * Tab: Knowledgebase Settings
         *  - Knowledgebase featured terms
         * ----------------------------------
         */
        add_settings_field(
            'knowledgebase_terms',                              // ID*
            '<label for="ns_doc_terms">'. esc_html__( 'Featured Categories', 'nanosupport' ) .'</label>', // Title*
            'ns_doc_terms_field',                               // Callback Function*
            'nanosupport_knowledgebase_settings',               // Page (Plugin)*
            'ns_knowledgebase'                                  // Section
        );
        /**
         * Tab: Knowledgebase Settings
         *  - Knowledgebase posts per category
         * ----------------------------------
         */
        add_settings_field(
            'knowledgebase_ppc',                                // ID*
            '<label for="ns_doc_ppc">'. esc_html__( 'Items per category', 'nanosupport' ) .'</label>',   // Title*
            'ns_doc_ppc_field',                                 // Callback Function*
            'nanosupport_knowledgebase_settings',               // Page (Plugin)*
            'ns_knowledgebase'                                  // Section
        );
        /**
         * Tab: Knowledgebase Settings
         *  - Knowledgebase URL rewriting
         * ----------------------------------
         */
        add_settings_field(
            'knowledgebase_url_rewrite',                        // ID*
            esc_html__( 'Add Categories to the URL', 'nanosupport' ),   // Title*
            'ns_doc_url_rewrite_field',                         // Callback Function*
            'nanosupport_knowledgebase_settings',               // Page (Plugin)*
            'ns_knowledgebase'                                  // Section
        );


    /**
     * Tab: Email Settings
     * Section: Email
     *  - Enable email notifications
     * ----------------------------------
     */
    add_settings_section(
        'ns_email',                                     // ID/Slug*
        esc_html__( 'Email Settings', 'nanosupport' ),  // Name*
        'ns_email_settings_section_callback',           // Callback*
        'nanosupport_email_settings'                    // Page/Tab where to add the section of options*
    );
    register_setting(
        'nanosupport_email_settings',                   // Option group*
        'nanosupport_email_settings',                   // Option Name*
        'ns_email_settings_validate'                    // Sanitize Callback Function
    );
    	add_settings_field(
            'email',                                    // ID*
            '<label for="ns-email-field">'. esc_html__( 'Notification Email', 'nanosupport' ) .'</label>',  // Title*
            'ns_notification_email_field',              // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email'                                  // Section
        );
    	add_settings_field(
            'email_choice',                             // ID*
            esc_html__( 'Email Choices', 'nanosupport' ), // Title*
            'ns_email_choices_field',                   // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email'                                  // Section
        );

    /**
     * Tab: Email Template Settings
     *  - Header Image
     *  - Header Text
     *  - Footer Text
     * ----------------------------------
     */
    add_settings_section(
        'ns_email_template',                            // ID/Slug*
        esc_html__( 'Email Template', 'nanosupport' ),  // Name*
        'ns_email_template_section_callback',           // Callback*
        'nanosupport_email_settings'                    // Page/Tab where to add the section of options*
    );
    	add_settings_field(
            'header_bg_color',                          // ID*
            '<label for="ns-email-header-bg-color">'. esc_html__( 'Header Background Color', 'nanosupport' ) .'</label>',        // Title*
            'ns_email_header_bg_color_field',           // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
    	add_settings_field(
            'header_text_color',                        // ID*
            '<label for="email-header-color">'. esc_html__( 'Header Text Color', 'nanosupport' ) .'</label>',   // Title*
            'ns_email_header_text_color_field',         // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
    	add_settings_field(
            'header_image',                             // ID*
            '<label for="ns-email-header-image-url">'. esc_html__( 'Header Image', 'nanosupport' ) .'</label>', // Title*
            'ns_email_header_image_field',              // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
    	add_settings_field(
            'header_text',                              // ID*
            '<label for="ns-email-header-text">'. esc_html__( 'Header Text', 'nanosupport' ) .'</label>', // Title*
            'ns_email_header_text_field',               // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
    	add_settings_field(
            'footer_text',                              // ID*
            '<label for="ns-email-footer-text">'. esc_html__( 'Footer Text', 'nanosupport' ) .'</label>', // Title*
            'ns_email_footer_text_field',               // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
}

add_action( 'admin_init', 'nanosupport_settings_options_init' );


//Tab 1: General Settings Fields
require_once 'ns-settings-general-fields.php';

//Tab 2: Knowledgebase Settings Fields
require_once 'ns-settings-knowledgebase-fields.php';

//Tab 3: Email Settings Fields
require_once 'ns-settings-emails-fields.php';


/**
 * Callback: Settings Page
 *
 * Showing the complete Settings page.
 * ...
 */
function nanosupport_settings_page_callback() {
	global $plugin_page; ?>

	<div class="wrap">
		<h1><i class="ns-icon-nanosupport" aria-hidden="true"></i> <?php _e( 'NanoSupport Settings', 'nanosupport' ); ?></h1>

		<?php
        //tabs
		$tabs = array(
			'general_settings'       => esc_html__( 'General', 'nanosupport' ),
			'knowledgebase_settings' => esc_html__( 'Knowledgebase', 'nanosupport' ),
			'email_settings'         => esc_html__( 'Emails', 'nanosupport' ),
		);
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_settings';

		foreach( $tabs as $tab => $name ) :
			$active_class = $active_tab == $tab ? 'nav-tab-active' : '';
			$tab_links[]  = '<a class="nav-tab '. esc_attr($active_class) .'" href="edit.php?post_type=nanosupport&page='. $plugin_page .'&tab='. $tab .'">'. $name .'</a>';
		endforeach;
		?>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $tab_links as $tab_link ) {
				echo $tab_link;
			}
			?>
		</h2>

		<?php settings_errors(); ?>

		<form method="post" action="options.php">

			<?php if( 'general_settings' === $active_tab ) { ?>

				<div class="nanosupport-left-column ns-general-settings">

					<?php settings_fields('nanosupport_settings'); ?>
					<?php do_settings_sections('nanosupport_settings'); ?>

					<?php submit_button(); ?>

				</div> <!-- /.nanosupport-left-column nanosupport-general-settings -->
				<div class="nanosupport-right-column">

					<?php require_once '__nanodesigns.php'; ?>

				</div> <!-- /.nanosupport-right-column -->
				<div class="ns-clearfix"></div>

			<?php } else if( 'knowledgebase_settings' === $active_tab ) { ?>

				<div class="nanosupport-left-column ns-knowledgebase-settings">

					<?php settings_fields('nanosupport_knowledgebase_settings'); ?>
					<?php do_settings_sections('nanosupport_knowledgebase_settings'); ?>

					<?php submit_button(); ?>

				</div> <!-- /.nanosupport-left-column nanosupport-knowledgebase-settings -->
				<div class="nanosupport-right-column">

					<?php require_once '__nanodesigns.php'; ?>

				</div> <!-- /.nanosupport-right-column -->
				<div class="ns-clearfix"></div>

			<?php } else if( 'email_settings' === $active_tab ) { ?>

				<div class="nanosupport-left-column ns-email-settings">

					<?php settings_fields('nanosupport_email_settings'); ?>
					<?php do_settings_sections('nanosupport_email_settings'); ?>

					<?php submit_button(); ?>

				</div> <!-- /.nanosupport-left-column nanosupport-email-settings -->
				<div class="nanosupport-right-column">

					<?php require_once '__nanodesigns.php'; ?>

				</div> <!-- /.nanosupport-right-column -->
				<div class="ns-clearfix"></div>

			<?php } //endif ?>

		</form>

	</div> <!-- /.wrap -->
	<?php
}
