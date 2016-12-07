<?php
/**
 * Tools Page
 *
 * Showing a tools page for the Plugin helping tools.
 *
 * @author  	nanodesigns
 * @category 	Debug
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tools submenu page.
 *
 * @since  1.0.0
 * -----------------------------------------------------------------------
 */
function ns_tools_page() {
    add_submenu_page(
        'edit.php?post_type=nanosupport',   //$parent_slug
        __( 'Tools', 'nanosupport' ),       //$page_title
        __( 'Tools', 'nanosupport' ),       //$menu_title
        'manage_nanosupport',               //$capability
        'nanosupport-tools',                //$menu_slug
        'nanosupport_tools_page_callback'   //callback function
    );
}
add_action( 'admin_menu', 'ns_tools_page' );


/**
 * Callback: Tools Page
 * 
 * Showing the complete Tools page.
 * ...
 */
function nanosupport_tools_page_callback() {
    global $plugin_page; ?>

    <div class="wrap">
        <h1><span class="ns-icon-nanosupport"></span> <?php esc_html_e( 'NanoSupport Tools', 'nanosupport' ); ?></h1>

        <?php settings_errors(); ?>

        <div class="nanosupport-left-column ns-tools">

            <table class="wp-list-table widefat fixed striped">
                <tbody>
                    <?php
                    $theme_data = wp_get_theme();
                    $parent     = $theme_data->parent();
                    if ( ! empty($parent) ) {
                        $parent_name = $theme_data->parent()->Name;
                    }
                    ?>
                    <tr>
                        <th><?php esc_html_e( 'Active Theme', 'nanosupport' ); ?></th>
                        <td><?php echo '<strong>'. $theme_data->get('Name') .'</strong> &ndash; '. $theme_data->get('Version'); ?></td>
                    </tr>
                    <?php if ( ! empty($parent) ) { ?>
                    <tr>
                        <th><?php esc_html_e( 'Parent Theme', 'nanosupport' ); ?></th>
                        <td><?php echo $parent_name, ' &ndash; ', $theme_data->parent()->Version; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div> <!-- /.nanosupport-left-column ns-tools -->
        <div class="nanosupport-right-column">

            <?php require_once '__nanodesigns.php'; ?>

        </div> <!-- /.nanosupport-right-column -->
        <div class="ns-clearfix"></div>

    </div> <!-- /.wrap -->
<?php
}
