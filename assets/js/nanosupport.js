/**!
 * NanoSupport Scripts
 * Scripts to decorate/manipulate NanoSupport front-end.
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
jQuery(document).ready(function($) {

    /**
     * Equal Height Knowledgebase Categories
     * 
     * Based on MatchHeight JS, it will make Knowledgebase
     * categories' height equal to look better.
     *
     * @since  1.0.0
     */
    var nano_doc_cat_elem = $('.nanodoc-term-box-inner');
    if( nano_doc_cat_elem.length > 0 )
    	nano_doc_cat_elem.matchHeight();

    /**
     * Show/Hide additional fields from ticket display
     *
     * Applicable only on viewport smaller than 517px.
     *
     * @since  1.0.0
     */
    var ticket_toggle = $('.toggle-ticket-additional');
    if( ticket_toggle.length > 0 ) {
        ticket_toggle.on('click', function(){
            $(this).next('.ticket-additional').toggle('slow');
            $(this).find('.ns-toggle-icon').toggleClass('ns-icon-chevron-circle-down ns-icon-chevron-circle-up');
        });
    }

});