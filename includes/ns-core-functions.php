<?php
/**
 * NanoSupport Core Functions
 *
 * General core functions available on both fornt end and admin.
 *
 * @author  	nanodesigns
 * @category 	Core
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get template part (for templates like the ns-loop).
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 */
function ns_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/NS/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", NS()->template_path() ."{$slug}-{$name}.php" ) );
	}

	// Get template from pro version
	if( ! $template && $name && class_exists( 'NSPro' ) && file_exists( NSPro()->plugin_path() ."/templates/{$slug}-{$name}.php" ) ) {
		$template = NSPro()->plugin_path() ."/templates/{$slug}-{$name}.php";
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( NS()->plugin_path() ."/templates/{$slug}-{$name}.php" ) ) {
		$template = NS()->plugin_path() ."/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/NS/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", NS()->template_path() ."{$slug}.php" ) );
	}

	// Allow 3rd party plugin filter template file from their plugin
	if ( $template ) {
		$template = apply_filters( 'ns_get_template_part', $template, $slug, $name );
	}

	if ( $template ) {
		load_template( $template, false );
	}
}