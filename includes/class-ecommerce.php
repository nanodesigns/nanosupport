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
	 * E-Commerce plugins active or not.
	 *
	 * @return boolean True if active, false otherwise.
	 * -----------------------------------------------------------------------
	 */
	public function is_plugins_active() {
		return ( $this->wc_active || $this->edd_active ) ? true : false;
	}

	/**
	 * E-Commerce enabled?
	 *
	 * @return boolean
	 * -----------------------------------------------------------------------
	 */
	public function ecommerce_enabled() {
		// Get the NanoSupport Settings from Database
		$ns_general_settings = get_option( 'nanosupport_settings' );

		if( isset($ns_general_settings['enable_ecommerce']) && $ns_general_settings['enable_ecommerce'] === 1 && $this->is_plugins_active() ) {
			return true;
		}

		return false;
	}


	/**
	 * Get All the Products.
	 *
	 * Get all the products eligible for providing support.
	 * Products are considered the items published from
	 * Easy Digital Downloads and/or WooCommerce.
	 *
	 * @return array Array of products.
	 * -----------------------------------------------------------------------
	 */
	public function get_all_products() {
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

			$result = wp_cache_get( 'nanosupport_get_products' );

			if( false === $result ) {

				$query = "SELECT ID, post_title FROM $wpdb->posts
				WHERE post_type IN ('$post_types')
				AND post_status = 'publish'";

				$result = $wpdb->get_results($query);

				wp_cache_set( 'nanosupport_get_products', $result );

			}

			foreach( $result as $post ) {
				$products[$post->ID] = $post->post_title;
			}
		}

		return $products;
	}


	/**
	 * Get Products.
	 *
	 * If there's any product to exclude, exclude 'em here.
	 *
	 * @return array Array of products.
	 * -----------------------------------------------------------------------
	 */
	public function get_products() {

		// All the products.
		$products = $this->get_all_products();

		// Get the NanoSupport Settings from Database.
		$ns_general_settings = get_option( 'nanosupport_settings' );

		if(isset($ns_general_settings['excluded_products']) && !empty($ns_general_settings['excluded_products'])) {
			$excluded_ids = (array) $ns_general_settings['excluded_products'];
			$products     = array_diff_key( $products, array_flip($excluded_ids) );
		}

		return $products;
	}


	/**
	 * Get Product Information.
	 *
	 * @param  integer $product_id Product ID.
	 * @param  integer $receipt    Purchase Receipt.
	 * @return object              Product information.
	 * -----------------------------------------------------------------------
	 */
	public function get_product_info( $product_id, $receipt = null ) {

		if( empty($product_id) ) return false;

		$product          = new stdClass();

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
				// Needs EDD prior to v2.5
				$payment = new EDD_Payment( $receipt_id );
				if( $payment->ID != 0 ) {
					$purchase_date = $payment->completed_date;
					$purchase_by   = $payment->first_name .' '. $payment->last_name;
				}
			}

			if( 'product' === $post_type && $this->wc_active ) {
				// Needs WC prior to v2.2
				$order = wc_get_order( $receipt_id );
				if( $order != false ) {
					// Documentation: https://docs.woocommerce.com/wc-apidocs/class-WC_Data.html
					$order_info = $order->get_data();
					if( isset($order_info['date_completed']) ) {
						$purchase_date = get_object_vars($order_info['date_completed'])['date']; //transforming object as an array
					} else {
						$purchase_date = '';
					}
					$purchase_by   = $order_info['billing']['first_name'] .' '. $order_info['billing']['last_name'];
				}
			}

			$product->{'purchase_date'} = !empty($purchase_date) ? ns_date_time( $purchase_date ) : '';
			$product->{'purchase_by'}   = $purchase_by;
			$product->{'payment_url'}   = get_edit_post_link($receipt_id);
		}

		return $product;
	}


	/**
	 * WooCommerce: Allow Agents to view Dashboard.
	 *
	 * @link   https://stackoverflow.com/a/48332889/1743124
	 *
	 * @param  boolean $prevent_access The default state.
	 * @return boolean
	 * -----------------------------------------------------------------------
	 */
	public static function wc_agent_admin_access( $prevent_access ) {
		$prevent_access = ns_is_user('agent') ? false : $prevent_access;
		return $prevent_access;
	}


	/**
	 * WooCommerce: Show Admin Bar to Agents.
	 *
	 * @link   https://stackoverflow.com/a/48332889/1743124
	 *
	 * @param  boolean $show The default state.
	 * @return boolean
	 * -----------------------------------------------------------------------
	 */
	public static function wc_agent_show_admin_bar( $show ) {
		$show = ns_is_user('agent') ? true : $show;
		return $show;
	}
}
