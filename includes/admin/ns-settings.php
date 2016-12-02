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
 * @since  1.0.0
 *
 * @link   http://ottopress.com/2009/wordpress-settings-api-tutorial/
 * @link   http://www.chipbennett.net/2011/02/17/incorporating-the-settings-api-in-wordpress-themes/?all=1
 * @link   http://wordpress.stackexchange.com/a/127499/22728
 * -----------------------------------------------------------------------
 */
function ns_settings_page() {
    add_submenu_page(
        'edit.php?post_type=nanosupport',       //$parent_slug
        __( 'Settings', 'nanosupport' ),        //$page_title
        __( 'Settings', 'nanosupport' ),        //$menu_title
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
     * ----------------------------------
     */
    add_settings_section(
        'ns_general',                                   // ID/Slug*
        __( 'General Settings', 'nanosupport' ),        // Name*
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
            __( 'Support Desk Page', 'nanosupport' ),   // Title*
            'ns_support_desk_field',                    // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'submit_ticket_page',                       // ID*
            __( 'Ticket Submission Page', 'nanosupport' ),  // Title*
            'ns_submit_ticket_field',                   // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'ticket_char_limit',                        // ID*
            /* translators: context: ...limit to # characters */
            __( 'Set ticket character limit to', 'nanosupport' ),      // Title*
            'ns_ticket_character_limit',                // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'enable_notice',                            // ID*
            __( 'Enable Notice?', 'nanosupport' ),      // Title*
            'ns_enable_notice',                         // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'submit_ticket_notice',                     // ID*
            __( 'Submit Ticket Notice', 'nanosupport' ),// Title*
            'ns_submit_ticket_notice',                  // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'support_desk_notice',                      // ID*
            __( 'Support Desk Notice', 'nanosupport' ), // Title*
            'ns_support_desk_notice',                   // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'knowledgebase_notice',                     // ID*
            __( 'Knowledgebase Notice', 'nanosupport' ), // Title*
            'ns_knowledgebase_notice',                  // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'highligh_ticket',                          // ID*
            __( 'Highlight Ticket with', 'nanosupport' ),    // Title*
            'ns_highlight_ticket_field',                // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'is_department_visible',                    // ID*
            __( 'Is Departments Visible?', 'nanosupport' ),    // Title*
            'ns_is_department_visible_field',           // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );
        add_settings_field(
            'embedded_login',                           // ID*
            __( 'Enable Embedded Login?', 'nanosupport' ),    // Title*
            'ns_embedded_login_field',                  // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_general'                                // Section
        );

    /**
     * Tab: Account Settings
     *  - Enable Login/Registration?
     *  - Account Creation
     *      - Generate Username?
     *      - Generate Password?
     * ----------------------------------
     */
    add_settings_section(
        'ns_account',                                   // ID/Slug*
        __( 'Account Settings', 'nanosupport' ),        // Name*
        'ns_account_settings_section_callback',         // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );
        add_settings_field(
            'account_creation',                         // ID*
            __( 'Account Creation', 'nanosupport' ),    // Title*
            'ns_account_creation_field',                // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_account'                                // Section
        );

    /**
     * Tab: Other Settings
     *  - Delete Data?
     * ----------------------------------
     */
    add_settings_section(
        'ns_others',                                    // ID/Slug*
        __( 'Other Settings', 'nanosupport' ),          // Name*
        'ns_other_settings_section_callback',           // Callback*
        'nanosupport_settings'                          // Page/Tab where to add the section of options*
    );
        add_settings_field(
            'delete_data',                              // ID*
            __( 'Delete Data?', 'nanosupport' ),        // Title*
            'ns_delete_data_field',                     // Callback Function*
            'nanosupport_settings',                     // Page (Plugin)*
            'ns_others'                                 // Section
        );

    
    /**
     * Tab: Knowledgebase Settings
     * Section: Knowledgebase
     *  - Enable email notifications
     * ----------------------------------
     */
    add_settings_section(
        'ns_knowledgebase',                            // ID/Slug*
        __( 'Knowledgebase Settings', 'nanosupport' ),          // Name*
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
            __( 'Activate Knowledgebase?', 'nanosupport' ),     // Title*
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
            __( 'Knowledgebase Page', 'nanosupport' ),          // Title*
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
            __( 'Featured Categories', 'nanosupport' ),         // Title*
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
            __( 'No. of items per category', 'nanosupport' ),   // Title*
            'ns_doc_ppc_field',                                 // Callback Function*
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
        __( 'Email Settings', 'nanosupport' ),          // Name*
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
            __( 'Notification Email', 'nanosupport' ),  // Title*
            'ns_notification_email_field',              // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email'                                  // Section
        );
        add_settings_field(
            'email_choice',                             // ID*
            __( 'Email Choices', 'nanosupport' ),       // Title*
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
        __( 'Email Template', 'nanosupport' ),          // Name*
        'ns_email_template_section_callback',           // Callback*
        'nanosupport_email_settings'                    // Page/Tab where to add the section of options*
    );
        add_settings_field(
            'header_bg_color',                          // ID*
            __( 'Header Background Color', 'nanosupport' ),        // Title*
            'ns_email_header_bg_color_field',           // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
        add_settings_field(
            'header_text_color',                        // ID*
            __( 'Header Text Color', 'nanosupport' ),   // Title*
            'ns_email_header_text_color_field',         // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
        add_settings_field(
            'header_image',                             // ID*
            __( 'Header Image', 'nanosupport' ),        // Title*
            'ns_email_header_image_field',              // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
        add_settings_field(
            'header_text',                              // ID*
            __( 'Header Text', 'nanosupport' ),         // Title*
            'ns_email_header_text_field',               // Callback Function*
            'nanosupport_email_settings',               // Page (Plugin)*
            'ns_email_template'                         // Section
        );
        add_settings_field(
            'footer_text',                              // ID*
            __( 'Footer Text', 'nanosupport' ),         // Title*
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
        <h1><span class="ns-icon-nanosupport"></span> <?php _e( 'NanoSupport Settings', 'nanosupport' ); ?></h1>

        <?php
        //tabs
        $tabs = array(
            'general_settings'          => __( 'General', 'nanosupport' ),
            'knowledgebase_settings'    => __( 'Knowledgebase', 'nanosupport' ),
            'email_settings'            => __( 'Emails', 'nanosupport' ),
        );
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_settings';

        foreach( $tabs as $tab => $name ) :
            $active_class = $active_tab == $tab ? 'nav-tab-active' : '';
            $tab_links[] = '<a class="nav-tab '. esc_attr($active_class) .'" href="edit.php?post_type=nanosupport&page='. $plugin_page .'&tab='. $tab .'">'. $name .'</a>';
        endforeach;
        ?>
        
        <h2 class="nav-tab-wrapper">
            <?php
            foreach ( $tab_links as $tab_link )
                echo $tab_link;
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
