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

	private $wc_active;
	private $edd_active;

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

		$post_types = implode("','", $post_types);

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

	public function get_product_info( $product_id, $receipt = null ) {
		$product          = new stdClass();
		
		$date_time_format = get_option('date_format') .' - '. get_option('time_format');
		$product_id       = absint($product_id);
		$post_type        = get_post_type($product_id);
		
		// Setting default to avoid undefined index warning.
		$purchase_date    = '';
		$purchase_by      = '';

		// Set basic information.
		$product->{'name'}   = get_the_title($product_id);
		$product->{'link'}   = get_the_permalink($product_id);
		$product->{'status'} = get_post_status($product_id);


		if( !empty($receipt) ) {
			
			$receipt_id = intval($receipt);

			if( 'download' === $post_type && $this->edd_active ) {
				$payment = new EDD_Payment( $receipt_id );
				if( $payment->ID != 0 ) {
					$purchase_date = $payment->completed_date;
					$purchase_by   = $payment->first_name .' '. $payment->last_name;
				}
			}

			if( 'product' === $post_type && $this->wc_active ) {
				$order = wc_get_order( $receipt_id );
				if( $order != false ) {
					$order_info    = $order->data;
					$purchase_date = get_object_vars($order_info['date_paid'])['date']; //transforming object as an array
					$purchase_by   = $order_info['billing']['first_name'] .' '. $order_info['billing']['last_name'];
				}
			}

			$product->{'purchase_date'} = !empty($purchase_date) ? date( $date_time_format, strtotime($purchase_date) ) : '';
			$product->{'purchase_by'}   = $purchase_by;
			$product->{'payment_url'}   = get_edit_post_link($receipt_id);
		}

		return $product;
	}
}
