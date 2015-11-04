<?php
/**
 * @link              http://nanodesignsbd.com
 * @since             1.0.0
 * @package           Nanodesigns Support Ticket
 *
 * @wordpress-plugin
 * Plugin Name:       Nanodesigns Support Ticket
 * Plugin URI:        http://nanodesignsbd.com/
 * Description:       Create a COMPLETE Support Center within your WordPress site
 * Version:           1.0.0
 * Author:            Mayeenul Islam
 * Author URI:        http://nishachor.com/
 * Requires at least: 4.0
 * Tested up to:      4.3
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nanodesigns-nst
 * Domain Path:       /i18n/languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Variables.
 *
 * Necessary variables for the uses 'cross the plugin.
 * 
 * @package nano_support_ticket
 */
$_nst_version 		= '1.0.0';
$_nst_prefix		= 'nst_';
$_nst_plugin_url	= plugins_url() .'/nano-support-ticket/';


/**
 * Require additional files.
 * 
 * @package nano_support_ticket
 */
require_once 'includes/setup/nst-setup.php';
require_once 'includes/setup/nst-cpt-and-taxonomy.php';
require_once 'includes/setup/nst-metaboxes-control.php';
require_once 'includes/setup/nst-metaboxes-responses.php';
require_once 'includes/setup/nst-custom-columns.php';
require_once 'includes/setup/nst-helper-functions.php';