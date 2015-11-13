<?php
/**
 * Functions related to Responses
 *
 * Responses are actually comments with the `comment_type` = 'nanosupport_response'.
 *
 * @package NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hide all the Responses in Comments page (Admin Panel)
 *
 * @link http://wordpress.stackexchange.com/a/56657/22728
 * 
 * @param  array $comments All the comments from comments table.
 * @return array           Filtering 'nanosupport_response' hiding them.
 * -----------------------------------------------------------------------
 */
function ns_filter_comments_for_responses( $comments ) {
    global $pagenow;
    if( 'edit-comments.php' === $pagenow  ) {
        foreach( $comments as $i => $comment ) {
        	$the_post = get_post( $comment->comment_post_ID );
            if( 'nanosupport_response' === $comment->comment_type || 'nanosupport' === $the_post->post_type )
            	unset( $comments[$i] );
        }
    }
    return $comments;
}
add_filter( 'the_comments', 'ns_filter_comments_for_responses' );