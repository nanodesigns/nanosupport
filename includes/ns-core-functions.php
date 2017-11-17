<?php
/**
 * NanoSupport Core Functions
 *
 * General core functions available on both front end and admin.
 *
 * @author  	nanodesigns
 * @category 	Core
 * @package 	NanoSupport
 * @version   	1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template part (for templates like the ns-loop).
 *
 * @since  	1.0.0
 *
 * @access 	public
 * 
 * @param 	mixed $slug
 * @param 	string $name (default: '')
 * -----------------------------------------------------------------------
 */
function ns_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/nanosupport/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", NS()->template_path() ."{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( NS()->plugin_path() ."/templates/{$slug}-{$name}.php" ) ) {
		$template = NS()->plugin_path() ."/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/nanosupport/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", NS()->template_path() ."{$slug}.php" ) );
	}

	/**
	 * -----------------------------------------------------------------------
	 * HOOK : FILTER HOOK
	 * nanosupport_get_template_part
	 *
	 * Allow 3rd party plugin filter template file from their plugin.
	 * 
	 * @since  1.0.0
	 *
	 * @param string  $template The template that is being loaded.
	 * @param string  $slug 	The page slug.
	 * @param string  $name 	The page name.
	 * -----------------------------------------------------------------------
	 */
	$template = apply_filters( 'nanosupport_get_template_part', $template, $slug, $name );

	if ( $template ) {
		load_template( $template, false );
	}
}
