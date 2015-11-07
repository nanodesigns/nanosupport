<?php
/**
 * Nano Support Ticket Core Functions
 *
 * General core functions available on both fornt end and admin.
 *
 * @author  	nanodesigns
 * @category 	Core
 * @package 	Nano Support Ticket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the main instance of NST to prevent the need to use globals.
 * @return NST
 */
function NST() {
	return NST::instance();
}


/**
 * Get template part (for templates like the nst-loop).
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 */
function nst_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/NST/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", NST()->template_path() ."{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( NST()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = NST()->plugin_path() ."/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/NST/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", NST()->template_path() ."{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	if ( $template ) {
		$template = apply_filters( 'nst_get_template_part', $template, $slug, $name );
	}

	if ( $template ) {
		load_template( $template, false );
	}
}