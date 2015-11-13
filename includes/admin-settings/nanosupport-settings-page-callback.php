<?php
/**
 * NanoSupport Settings SubMenu Page Callback function
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nanosupport_settings_options_init(){

	/**
     * Tab: Basic Settings
     * 	- Bootstrap?
     * 	- Support Desk Page
     * 	- Submit Ticket Page
     * ----------------------------------
     */
    add_settings_section(
        'ns_general',									// ID/Slug*
        __( 'General Settings', 'nanosupport' ),		// Name*
        'ns_general_settings_section_callback',			// Callback*
        'nanosupport_settings'							// Page/Tab where to add the section of options*
    );
    register_setting(
        'nanosupport_settings',							// Option group*
        'nanosupport_settings',							// Option Name (db)*
        'ns_general_settings_validate'					// Sanitize Callback Function
    );
        add_settings_field(
            'support_desk_page',						// ID*
            __( 'Support Desk Page', 'nanosupport' ),	// Title*
            'ns_support_desk_field',					// Callback Function*
            'nanosupport_settings',						// Page (Plugin)*
            'ns_general'								// Section
        );
        add_settings_field(
            'submit_ticket_page',						// ID*
            __( 'Ticket Submission Page', 'nanosupport' ),  // Title*
            'ns_submit_ticket_field',					// Callback Function*
            'nanosupport_settings',						// Page (Plugin)*
            'ns_general'								// Section
        );
        add_settings_field(
            'bootstrap',								// ID*
            __( 'Load Bootstrap CSS?', 'nanosupport' ),	// Title*
            'ns_bootstrap_field',						// Callback Function*
            'nanosupport_settings',						// Page (Plugin)*
            'ns_general'								// Section
        );

    /**
     * Tab: Email Settings
     * 	- Enable email notifications
     * ----------------------------------
     */
    add_settings_section(
        'nanosupport_email',							// ID/Slug*
        __( 'Email Settings', 'nanosupport' ),			// Name*
        'ns_email_settings_section_callback',			// Callback*
        'nanosupport_email_settings'					// Page/Tab where to add the section of options*
    );
    register_setting(
        'nanosupport_email_settings',					// Option group*
        'nanosupport_email_settings',					// Option Name*
        'ns_email_settings_validate'                    // Sanitize Callback Function
    );
	    add_settings_field(
	        'email',									// ID*
	        __( 'Email', 'nanosupport' ),				// Title*
	        'ns_email_field',							// Callback Function*
	        'nanosupport_email_settings',				// Page (Plugin)*
	        'nanosupport_email'							// Section
	    );
}
add_action( 'admin_init', 'nanosupport_settings_options_init' );

function ns_general_settings_section_callback() {
	//echo "Basic Section Here";
}

function ns_support_desk_field() {
	$options = get_option( 'nanosupport_settings' );

    $args = array(
        'hierarchical'	=> 0,
        'post_type'		=> 'page',
        'post_status'	=> 'publish'
    );
    $pages = get_pages( $args );

    if( $pages ) {
        echo '<select name="nanosupport_settings[support_desk]" id="ns_support_desk" class="ns-select">';
            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) {
                if( has_shortcode( $page->post_content, 'nanosupport_desk' ) ) {
                    echo '<option value="'. $page->ID .'" '. selected( $page->ID, $options['support_desk'], false ) .'>'. $page->post_title .'</option>';
                }
            }
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want to display the Support Desk. If no page is in the list, create one with the shortcode [nanosupport_desk] in it.', 'nanosupport' ) .'"></span>';
    }
}

function ns_submit_ticket_field() {
	$options = get_option( 'nanosupport_settings' );

    $args = array(
        'hierarchical'  => 0,
        'post_type'     => 'page',
        'post_status'   => 'publish'
    ); 
    $pages = get_pages($args);

    if( $pages ) {
        echo '<select name="nanosupport_settings[submit_page]" id="ns_submit_ticket" class="ns-select">';
            echo '<option value="">'. __( 'Select a page', 'nanosupport' ) .'</option>';                
            foreach ( $pages as $page ) {
                if( has_shortcode( $page->post_content, 'nanosupport_submit_ticket' ) ) {
                    echo '<option value="'. $page->ID .'" '. selected( $page->ID, $options['submit_page'], false ) .'>'. $page->post_title .'</option>';
                }
            }
        echo '</select>';
        echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'Choose the page where you want show the Ticket Submission page. If no page is in the list, create one with the shortcode [nanosupport_submit_ticket] in it.', 'nanosupport' ) .'"></span>';
    }
}

function ns_bootstrap_field() {
	$options = get_option( 'nanosupport_settings' );

	echo '<input name="nanosupport_settings[bootstrap]" id="ns_bootstrap" type="checkbox" value="1" '. checked( 1, $options['bootstrap'], false ) . '/> <label for="ns_bootstrap">'. __( 'Load Bootstrap CSS (default)', 'nanosupport' ) .'</label>';
	echo '&nbsp;<span class="dashicons dashicons-editor-help ns-tooltip-icon" data-tooltip="'. __( 'If your theme is designed in Bootstrap, just uncheck here to not to load the file again.', 'nanosupport' ) .'"></span>';
}


function ns_email_settings_section_callback() {
	//echo "Email section";
}

function ns_email_field() {
    $options = get_option('nanosupport_email_settings');
    echo "<input name='nanosupport_email_settings[email_check]' id='email' type='checkbox' value='1' ".checked( 1, $options['email_check'], false ) . " /> <label for='email'>". __( 'Load jQuery from plugin', 'nanosupport' ) ."</label>";
}



/**
 * THE SETTINGS PAGE
 * Showing the complete Settings page.
 */
function nanosupport_settings_page_callback() {
	global $plugin_page; ?>

    <div class="wrap">
        <h1><?php _e( 'NanoSupport Settings', 'nanosupport' ); ?></h1>

        <?php
        //tabs
        $tabs = array(
			'general_settings'	=> __( 'General', 'nanosupport' ),
			'email_settings'	=> __( 'Emails', 'nanosupport' ),
		);
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_settings';

    	foreach( $tabs as $tab => $name ) :
			$active_class = $active_tab == $tab ? 'nav-tab-active' : '';
			$tab_links[] = '<a class="nav-tab '. esc_attr($active_class) .'" href="?page='. $plugin_page .'&tab='. $tab .'">'. $name .'</a>';
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

        		<div class="nanosupport-left-column">

					<?php settings_fields('nanosupport_settings'); ?>
					<?php do_settings_sections('nanosupport_settings'); ?>

				</div> <!-- /.nanosupport-left-column -->
				<div class="nanosupport-right-column">

                    <?php printf( __( '<strong>NanoSupport</strong> is a complete package for a front-end Support Ticketing System in a complete WordPress\' way. It has a rich back end for ticket maintenance and management.<hr><a href="%s"><strong>nano</strong>designs</a>', 'nanosupport' ), 'http://nanodesignsbd.com/' ); ?>

                </div> <!-- /.nanosupport-right-column -->
                <div class="clearfix"></div>

			<?php } else if( 'email_settings' === $active_tab ) { ?>

				<div class="nanosupport-left-column">

					<?php settings_fields('nanosupport_email_settings'); ?>
					<?php do_settings_sections('nanosupport_email_settings'); ?>

				</div> <!-- /.nanosupport-left-column -->
				<div class="nanosupport-right-column">
					
					<?php printf( __( '<strong>NanoSupport</strong> is a complete package for a front-end Support Ticketing System in a complete WordPress\' way. It has a rich back end for ticket maintenance and management.<hr><a href="%s"><strong>nano</strong>designs</a>', 'nanosupport' ), 'http://nanodesignsbd.com/' ); ?>

				</div> <!-- /.nanosupport-right-column -->
                <div class="clearfix"></div>

			<?php } //endif ?>

			<?php submit_button(); ?>
        </form>

    </div> <!-- /.wrap -->
<?php
}