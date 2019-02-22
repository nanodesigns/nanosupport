<?php
/**
 * System Status Class
 *
 * Prepare the system status data in an organized way.
 *
 * @author      nanodesigns
 * @category    Class/Helpers
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSSystemStatus {

	/**
	 * WordPress Version
	 * @return integer
	 */
	public function wp_version() {
		global $wp_version;
		return $wp_version;
	}

	/**
	 * WP_DEBUG status
	 * @return string
	 */
	public function debug_status() {
		return (WP_DEBUG === true) ? esc_html__( 'On', 'nanosupport' ) : esc_html__( 'Off', 'nanosupport' );
	}

	/**
	 * E-Commerce status
	 * @return string
	 */
	public function ecommerce_status() {
		$NSECommerce = new NSECommerce();
		return $NSECommerce->ecommerce_enabled() ? esc_html__( 'Enabled', 'nanosupport' ) : esc_html__( 'Disabled', 'nanosupport' );
	}

	/**
	 * Multisite status
	 * @return string
	 */
	public function is_multisite() {
		return is_multisite() ? esc_html__( 'Yes', 'nanosupport' ) : esc_html__( 'No', 'nanosupport' );
	}

	/**
	 * Get Theme information for reuse
	 * @return object Active theme information.
	 */
	private function theme_data() {
		return wp_get_theme();
	}

	/**
	 * Prepare active theme info
	 * @return array
	 */
	public function active_theme() {
		$theme_data = $this->theme_data();
		return array(
			'theme'   => $theme_data->get('Name'),
			'version' => $theme_data->get('Version')
		);
	}

	/**
	 * Prepare active theme's parent
	 * Parent theme's data, if it's a child theme.
	 * @return array|boolean Parent theme's data or false.
	 */
	public function active_theme_parent() {
		$theme_data = $this->theme_data();
		$parent     = $theme_data->parent();
		if ( ! empty($parent) ) {
			return array(
				'theme'   => $theme_data->parent()->Name,
				'version' => $theme_data->parent()->Version
			);
		}

		return false;
	}

	/**
	 * Active plugin info for reuse
	 * @return object
	 */
	private function get_active_plugins_info() {
		$active_plugins_paths = wp_get_active_and_valid_plugins();
		return $active_plugins_paths;
	}

	/**
	 * Get active plugins' count
	 * @return integer
	 */
	public function get_active_plugins_count() {
		$active_plugins_paths = $this->get_active_plugins_info();
		return count($active_plugins_paths);
	}

	/**
	 * Get active plugins' details
	 * @return array
	 */
	public function get_active_plugins() {
		$active_plugins_paths = $this->get_active_plugins_info();

		$plugin_info = array();
		foreach( $active_plugins_paths as $plugin_file ) {
			$plugin_info[] = get_plugin_data($plugin_file);
		}

		return $plugin_info;
	}

	/**
	 * Get PHP memory limit
	 * @return integer
	 */
	public function memory_limit() {
		$wp_memory_limit = ns_transform_to_numeric( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, ns_transform_to_numeric( @ini_get( 'memory_limit' ) ) );
		}

		return $wp_memory_limit;
	}

	/**
	 * MySQL version
	 * @return integer
	 */
	public function mysql_version() {
		global $wpdb;
		return (! empty( $wpdb->is_mysql )) ? $wpdb->db_version() : 0;
	}
}
