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
 * NanoSupport Locate Template
 * Locate a template and return its path for requiring.
 *
 * Load order:
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param  string $template_name Name of the Template.
 * @param  string $template_path Path to the Template (default: '')
 * @param  string $default_path  Default Path to the Template (default: '')
 * @return string                Template path.
 * -----------------------------------------------------------------------
 */
function ns_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if( ! $template_path ) {
		$template_path = NS()->template_path();
	}

	if( ! $default_path ) {
		$default_path = NS()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default templates/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	/**
	 * -----------------------------------------------------------------------
	 * HOOK : FILTER HOOK
	 * nanosupport_locate_template
	 *
	 * Allow other plugins to filter template file from their domain.
	 *
	 * @since  1.0.0
	 *
	 * @param string  $template       The template that is being loaded.
	 * @param string  $template_name  The name of the template.
	 * @param string  $template_path  The path to the template.
	 * -----------------------------------------------------------------------
	 */
	return apply_filters( 'nanosupport_locate_template', $template, $template_name, $template_path );
}


/**
 * Get template part (for templates like the ns-loop).
 *
 * @access 	public
 *
 * @param 	string $template_name   Name of the Template.
 * @param 	string $template_path   Path to the template (default: '').
 * @param 	string $default_path    Default Path to the template (default: '').
 * -----------------------------------------------------------------------
 */
function ns_get_template_part( $template_name, $template_path = '', $default_path = '' ) {
	$located_template = '';

	$located_template = ns_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located_template ) ) {
		return __('Template not found', 'nanosupport');
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
	 * @param string  $located_template The template that is being located.
	 * @param string  $template_name 	The name of the template.
	 * @param string  $template_path 	The path to the template.
	 * -----------------------------------------------------------------------
	 */
	$located_template = apply_filters( 'nanosupport_get_template_part', $located_template, $template_name, $template_path );

	if ( $located_template ) {
		load_template( $located_template, false );
	}
}
