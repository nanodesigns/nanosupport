<?php
/**
 * E-commerce Class
 *
 * Make the way to enable/disable e-commerce feature.
 *
 * @author      nanodesigns
 * @category    Class/ECommerce
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class NSECommerce {

	public $wc_active;
	public $edd_active;

	public function __construct() {
		$this->wc_active  = class_exists('WooCommerce');
		$this->edd_active = class_exists('Easy_Digital_Downloads');
	}

	/**
	 * E-Commerce enabled?
	 * @return boolean
	 * -----------------------------------------------------------------------
	 */
	public function ecommerce_enabled() {
		// Get the NanoSupport Settings from Database
	    $ns_general_settings = get_option( 'nanosupport_settings' );

	    $enabled = false;

	    if( isset($ns_general_settings['enable_ecommerce']) && $ns_general_settings['enable_ecommerce'] === 1 && ( $this->wc_active || $this->edd_active ) ) {
	    	$enabled = true;
	    }

	    return $enabled;
	}

	/**
	 * Get Products.
	 * @return array Array of products.
	 * -----------------------------------------------------------------------
	 */
	public function get_products() {
		$products   = array();
		$post_types = array();

		if( $this->wc_active ) {
			$post_types[] = 'product';
		}

		if( $this->edd_active ) {
			$post_types[] = 'download';
		}

		$post_types = implode(",", $post_types);

		if( !empty($post_types) ) {
			global $wpdb;

			$query = "SELECT ID, post_title FROM $wpdb->posts
						WHERE post_type IN ('$post_types')
						AND post_status = 'publish'";

			$result = $wpdb->get_results($query);

			foreach( $result as $post ) {
				$products[$post->ID] = $post->post_title;
			}
		}

		return $products;
	}
}

$NSEcommerce = new NSECommerce();
