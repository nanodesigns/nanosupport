<?php
/**
 * Ticket Changelog Class.
 *
 * Class responsible for storing the ticket changes overtime.
 *
 * @author      nanodesigns
 * @package     NanoSupport/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NS_Ticket_Changelog Class.
 */
class NS_Ticket_Changelog {

	public static function init()
	{
		if( self::is_active() ) {
			// Earlier hooking is important, prior to the default 10.
			add_action( 'save_post',        array( __CLASS__, 'log_changes' ), 9 );
			add_action( 'new_to_publish',   array( __CLASS__, 'log_changes' ), 9 );
		}
	}

	/**
	 * Is Active?
	 *
	 * @return boolean True if active, false otherwise.
 	 * ------------------------------------------------------------------------------
	 */
	public static function is_active()
	{
		/**
		 * -----------------------------------------------------------------------
		 * HOOK : FILTER HOOK
		 * nanosupport_log_ticket_changes
		 *
		 * @since  1.0.0
		 *
		 * @param boolean  True to log ticket changes.
		 * -----------------------------------------------------------------------
		 */
		$is_active = apply_filters( 'nanosupport_log_ticket_changes', true );

		return $is_active;
	}

	/**
	 * Detect Ticket Changes.
	 *
	 * @param  integer         $post_id   Ticket Post ID.
	 * @return array|boolean              Ticket changes in array, otherwise false.
 	 * ------------------------------------------------------------------------------
	 */
	public static function detect_changes( $post_id )
	{
		$ticket_info = get_post($post_id);
		$ticket_meta = ns_get_ticket_meta($post_id);

		// New data
		$ns_ticket_status   = filter_input(INPUT_POST, 'ns_ticket_status', FILTER_SANITIZE_STRING);
		$ns_ticket_priority = filter_input(INPUT_POST, 'ns_ticket_priority', FILTER_SANITIZE_STRING);
		$ns_ticket_agent    = filter_input(INPUT_POST, 'ns_ticket_agent', FILTER_SANITIZE_NUMBER_INT);
		$ns_ticket_product  = filter_input(INPUT_POST, 'ns_ticket_product', FILTER_SANITIZE_STRING);
		$ns_ticket_receipt  = filter_input(INPUT_POST, 'ns_ticket_product_receipt', FILTER_SANITIZE_STRING);
		$ns_ticket_author   = filter_input(INPUT_POST, 'post_author', FILTER_SANITIZE_NUMBER_INT);
		$ns_ticket_close    = isset($_POST['close_ticket']) ? true : false;
		$ns_ticket_reopen   = (isset($_GET['reopen']) && wp_verify_nonce( $_GET['_wpnonce'], 'reopen-ticket' )) ? true : false;

		$_changes = array();

		// Status is changed (except 'pending')
		if( is_admin() && !empty($ns_ticket_status) && $ticket_meta['status']['value'] !== $ns_ticket_status ) {
			// translators: 1. User who made the change 2. New ticket status
			$_changes['status_changed'] = sprintf( __('%s changed the ticket status to %s', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%STATUS_{$ns_ticket_status}%%" );
		}

		// Front end ticket close.
		if( $ns_ticket_close ) {
			// translators: 1. User who made the change 2. Ticket status 'solved'
			$_changes['status_closed'] = sprintf( __('%s changed the ticket status to %s', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%STATUS_solved%%" );
		}

		// Front end ticket reopen.
		if( $ns_ticket_reopen ) {
			// translators: 1. User who made the change 2. Ticket status 'open'
			$_changes['status_reopened'] = sprintf( __('%s changed the ticket status to %s', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%STATUS_open%%" );
		}

		// Priority is changed.
		if( is_admin() && !empty($ns_ticket_priority) && $ticket_meta['priority']['value'] !== $ns_ticket_priority ) {
			// translators: 1. User who made the change 2. New ticket priority
			$_changes['priority_changed'] = sprintf( __('%s changed the ticket priority to <strong>%s</strong>', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%PRIORITY_{$ns_ticket_priority}%%" );
		}

		// Agent is changed.
		if( (!empty($ticket_meta['agent']) && $ticket_meta['agent']['ID'] == $ns_ticket_agent)
			|| (empty($ticket_meta['agent']) && $ticket_meta['agent'] == $ns_ticket_agent)) {

			// no change made. do nothing

		} else {

			if( is_admin() && empty($ticket_meta['agent']) && !empty($ns_ticket_agent) ) {
				// translators: 1. User who made the change 2. New ticket-agent
				$_changes['agent_set'] = sprintf( __('%s set <strong>%s</strong> as an agent to the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%AGENT_{$ns_ticket_agent}%%" );
			} elseif( is_admin() && !empty($ticket_meta['agent']) && empty($ns_ticket_agent) ) {
				// translators: 1. User who made the change 2. Agent who was removed
				$_changes['agent_removed'] = sprintf( __('%s removed the agent <strong>%s</strong> from the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%AGENT_{$ticket_meta['agent']['ID']}%%" );
			} elseif( is_admin() && !empty($ticket_meta['agent']) && !empty($ns_ticket_agent) && $ticket_meta['agent']['ID'] != $ns_ticket_agent ) {
				// translators: 1. User who made the change 2. User who is newly set as an agent
				$_changes['agent_changed'] = sprintf( __('%s set <strong>%s</strong> as the new agent to the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%AGENT_{$ns_ticket_agent}%%" );
			}

		}

		// Author is changed.
		if( is_admin() && !empty($ns_ticket_author) && $ticket_info->post_author != $ns_ticket_author ) {
			// translators: 1. User who made the change 2. New author of the ticket
			$_changes['author_changed'] = sprintf( __('%s set <strong>%s</strong> as the author of the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%AUTHOR%%" );
		}

		$NSECommerce = new NSECommerce();
		if( $NSECommerce->ecommerce_enabled() ) {

			// Ticket product. Extra care needs to be taken for optional field.
			if( (!empty($ticket_meta['product']) && $ticket_meta['product'] == $ns_ticket_product)
				|| (empty($ticket_meta['product']) && $ticket_meta['product'] == $ns_ticket_product)) {

				// no change made. do nothing

			} else {

				if( is_admin() && empty($ticket_meta['product']) && !empty($ns_ticket_product) ) {
					// translators: 1. User who made the change 2. New product
					$_changes['product_set'] = sprintf( __('%s assigned the product <strong>%s</strong> to the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%PRODUCT_{$ns_ticket_product}%%" );
				} elseif( is_admin() && !empty($ticket_meta['product']) && empty($ns_ticket_product) ) {
					// translators: 1. User who made the change 2. Product that was removed
					$_changes['product_removed'] = sprintf( __('%s removed the product <strong>%s</strong> from the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%PRODUCT_{$ticket_meta['product']}%%" );
				} elseif( is_admin() && !empty($ticket_meta['product']) && !empty($ns_ticket_product) && $ticket_meta['product'] != $ns_ticket_product ) {
					// translators: 1. User who made the change 2. Product that was newly assigned to the ticket
					$_changes['product_changed'] = sprintf( __('%s changed the product to the ticket with <strong>%s</strong>', 'nanosupport'), '%%CHANGEAUTHOR%%', "%%PRODUCT_{$ns_ticket_product}%%" );
				}

			}

			// Ticket product receipt. Extra care needs to be taken for optional field.
			if( (!empty($ticket_meta['receipt']) && $ticket_meta['receipt'] == $ns_ticket_receipt)
				|| (empty($ticket_meta['receipt']) && $ticket_meta['receipt'] == $ns_ticket_receipt)) {

				// no change made. do nothing

			} else {

				if( is_admin() && empty($ticket_meta['receipt']) && !empty($ns_ticket_receipt) ) {
					// translators: 1. User who made the change
					$_changes['receipt_set'] = sprintf( __('%s added a product receipt to the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%' );
				} elseif( is_admin() && !empty($ticket_meta['receipt']) && empty($ns_ticket_receipt) ) {
					// translators: 1. User who made the change
					$_changes['receipt_removed'] = sprintf( __('%s removed the product receipt from the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%' );
				} elseif( is_admin() && !empty($ticket_meta['receipt']) && !empty($ns_ticket_receipt) && $ticket_meta['receipt'] != $ns_ticket_receipt ) {
					// translators: 1. User who made the change
					$_changes['receipt_changed'] = sprintf( __('%s modified the product receipt of the ticket', 'nanosupport'), '%%CHANGEAUTHOR%%' );
				}

			}

		}

		if( empty($_changes) ) return false;

		return $_changes;
	}


	/**
	 * Log changes to the database.
	 *
	 * @param  array         $changes    Changes to the ticket.
	 * @param  integer|null  $ticket_id  Ticket ID.
	 *
	 * @return boolean                   True, if everything goes successfully, otherwise false.
 	 * ------------------------------------------------------------------------------
	 */
	public static function log_changes($ticket_id = null)
	{
		$post_id = ( null === $ticket_id ) ? get_the_ID() : $ticket_id;

		$changes = self::detect_changes($post_id);

		if( !$changes ) return false;

		if( is_array($changes) ) {
			global $current_user;

			foreach($changes as $key => $change) {
	    		// Using wp_insert_comment() to bypass the duplicate check.
				wp_insert_comment(array(
					'comment_post_ID' => absint( $post_id ),
					'comment_author'  => wp_strip_all_tags( $current_user->display_name ),
					'comment_content' => wp_kses( $change, array('strong' => array()) ),
					'comment_type'    => 'nanosupport_change',
					'comment_parent'  => 0,
					'user_id'         => absint( $current_user->ID ),
				));
			}

			return true;
		}

		return false;
	}


	/**
	 * Translate Changelog.
	 *
	 * Translate and transform the encoded changelog to
	 * a human-readable text to display.
	 *
	 * @param  object  $response Response object.
	 * @param  boolean $style    Whether to push style attributes or not.
	 *
	 * @return string            Human readable string.
 	 * ------------------------------------------------------------------------------
	 */
	public static function translate_changes($response, $style = true)
	{
		if( self::is_active() ) {

			if( !is_object($response) ) return;

			$_content = $response->comment_content;
			$_user    = ns_user_nice_name($response->user_id);
			$_date    = ns_date_time( $response->comment_date );

			// Default changelog class.
			$_icon_class = 'ns-icon-clipboard';

			// Show the username.
			$_content = str_replace('%%CHANGEAUTHOR%%', $_user, $_content);

			// Show the status.
			if( strpos($_content, '%%STATUS_') !== false ) {
				$_icon_class = 'ns-icon-tag';
				preg_match('/%%STATUS_(.*?)%%/', $_content, $_status);
				$status_info = ns_get_ticket_status_info( $_status[1] );
				$_content    = preg_replace('/%%STATUS_(.*?)%%/', $status_info['label'], $_content);
			}

			// Show the priority.
			if( strpos($_content, '%%PRIORITY_') !== false ) {
				$_icon_class   = 'ns-icon-buffer';
				preg_match('/%%PRIORITY_(.*?)%%/', $_content, $_priority);
				$priority_info = ns_get_ticket_priority_info( $_priority[1] );
				$_content      = preg_replace('/%%PRIORITY_(.*?)%%/', $priority_info['name'], $_content);
			}

			// Show the agent.
			if( strpos($_content, '%%AGENT_') !== false ) {
				$_icon_class = 'ns-icon-users';
				preg_match('/%%AGENT_(.*?)%%/', $_content, $_agent);
				$_content    = preg_replace('/%%AGENT_(.*?)%%/', ns_user_nice_name( $_agent[1] ), $_content);
			}

			// Show the product.
			if( strpos($_content, '%%PRODUCT_') !== false ) {
				$_icon_class   = 'ns-icon-cart';
				preg_match('/%%PRODUCT_(.*?)%%/', $_content, $_product);
				$NSECommerce   = new NSECommerce();
				$_product_info = $NSECommerce->get_product_info( $_product[1] );
				$_content      = preg_replace('/%%PRODUCT_(.*?)%%/', $_product_info->name, $_content);
			}

			// Show the ticket author.
			if( strpos($_content, '%%AUTHOR%%') !== false ) {
				$_icon_class = 'ns-icon-user';
				$_content    = str_replace('%%AUTHOR%%', $_user, $_content);
			}

			// Append date, finally.
			$_content = $_content .' &mdash; <time datetime="'. date('Y-m-d H:i:s', strtotime($response->comment_date)) .'">'. $_date .'</time>';

			if( $style ) {
				$_content = '<span class="ns-ticket-log-notation"><i class="'. esc_attr($_icon_class) .'" aria-hidden="true"></i></span>'. $_content;
			}

			return $_content;

		}
	}

}

NS_Ticket_Changelog::init();
