<?php
/**
 * Uninstall NanoSupport
 *
 * Uninstalling/Deleteing the plugin with pages, tickets, departments,
 * knowledgebase docs, categories, and settings with no traces left.
 *
 * @author      nanodesigns
 * @category    Core
 * @package     NanoSupport/Uninstaller
 * @version     1.0.0
 */

if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();


/**
 * Get the plugins' information first.
 */
$ns_general_settings 		= get_option( 'nanosupport_settings' );
$ns_knowledgebase_settings 	= get_option( 'nanosupport_knowledgebase_settings' );

$support_desk_page_id 		= $ns_general_settings['support_desk'];
$submit_ticket_page_id 		= $ns_general_settings['submit_page'];
$knowledgebase_page_id 		= $ns_knowledgebase_settings['page'];

/** ---------------- DELETE EVERYTHING ---------------- **/
/** ------------------ (if permitted) ----------------- **/

$delete_data = isset($ns_general_settings['delete_data']) ? $ns_general_settings['delete_data'] : '';
if( ! empty($delete_data) ) :

	/**
	 * Delete pages
	 * Bypass trash and delete forcefully.
	 */
	wp_delete_post( $support_desk_page_id, true );
	wp_delete_post( $submit_ticket_page_id, true );
	wp_delete_post( $knowledgebase_page_id, true );

	/**
	 * Delete all the data
	 */
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'nanosupport', 'nanodoc' );" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

	/**
	 * Delete all the ticket responses
	 */
	$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type = ( 'nanosupport_response' );" );

	/**
	 * Delete all the Taxonomies and their terms
	 * @link https://wpsmith.net/2014/plugin-uninstall-delete-terms-taxonomies-wordpress-database/
	 */
	foreach ( array( 'nanosupport_department', 'nanodoc_category' ) as $taxonomy ) :
		// Prepare & excecute SQL, Delete Terms
		$wpdb->get_results( $wpdb->prepare( "DELETE t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s')", $taxonomy ) );

		// Delete Taxonomy
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	endforeach;

	/**
	 * Flush the rewrite rules once again
	 */
	flush_rewrite_rules();

	/**
	 * Remove custom capabilities
	 */
	include_once 'includes/ns-install.php';
	ns_remove_caps();

	/**
	 * Delete user meta fields
	 */
	$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'ns_make_agent' ), array( '%s' ) );

	/**
	 * Delete all the options
	 */
	delete_option( 'nanosupport_version' );
	delete_option( 'nanosupport_settings' );
	delete_option( 'nanosupport_knowledgebase_settings' );
	delete_option( 'nanosupport_email_settings' );

	/* ...? */
	delete_option( 'nanosupport_department_children' );
	delete_option( 'nanodoc_category_children' );

endif;
