<?php
/**
 * Knowledgebase Search Form
 *
 * A general search form specific to Knowledgebase searching.
 *
 * @author  	nanodesigns
 * @category 	knowledgebase
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<form role="search" method="get" class="ns-search-form form-group" action="<?php echo home_url( '/' ); ?>">
	
	<!-- Triggering the knowledgebase search -->
	<input type="hidden" name="knowledgebase" value="">
	
	<label for="search-field" class="sr-only"><?php echo _x( 'Search Knowledgebase for:', 'label', 'nanosupport' ) ?></label>
	<input type="search" class="search-field form-control" autocomplete="off" placeholder="<?php echo esc_attr_x( 'Search Knowledgebase', 'placeholder', 'nanosupport' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search Knowledgebase:', 'label', 'nanosupport' ) ?>" />	

	<button class="btn btn-link search-btn"><span class="ns-icon-search"></span></button>

</form>