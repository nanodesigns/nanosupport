/**!
 * NanoSupport Scripts
 * Scripts to decorate/manipulate NanoSupport front-end.
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
 jQuery(document).ready(function($) {

    /**
     * JavaScripts Loaded
     */
     $('#nanosupport-add-ticket, #nanosupport-desk, #nanosupport-knowledgebase').removeClass('ns-no-js').addClass('ns-js');

    /**
     * Equal Height Knowledgebase Categories
     *
     * Based on MatchHeight JS, it will make Knowledgebase
     * categories' height equal to look better.
     * -----------------------------------------------------------
     */
     var nano_doc_cat_elem = $('.nanodoc-term-box-inner');
     if( nano_doc_cat_elem.length > 0 ) {
     	nano_doc_cat_elem.matchHeight();
     	$('.ns-kb-catbox-list').matchHeight();
     }

    /**
     * Show/Hide additional fields from ticket display
     *
     * Applicable only on viewport smaller than 517px.
     * -----------------------------------------------------------
     */
     var ticket_toggle = $('.toggle-ticket-additional');
     if( ticket_toggle.length > 0 ) {
     	ticket_toggle.on('click', function(){
     		var additional_info_btn = $(this);
            // Add class to hide conditionally on smaller viewport.
            additional_info_btn.next('.ticket-additional').toggleClass('ns-hide-mobile');
            // Toggle the button icon.
            additional_info_btn.find('.ns-toggle-icon').toggleClass('ns-icon-chevron-circle-down ns-icon-chevron-circle-up');
        });
     }


    /**
     * Bootstrap File Select
     *
     * Bootstrap File Selection
     *
     * @link https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
     * -----------------------------------------------------------
     */
     var ns_btn_file = $('.ns-btn-file :file');
     if( ns_btn_file.length > 0 ) {

     	ns_btn_file.on('change', function() {
     		var input       = $(this),
	     		numFiles    = input.get(0).files ? input.get(0).files.length : 1,
	     		label       = input.val().replace(/\\/g, '/').replace(/.*\//, '');

     		input.trigger('fileselect', [numFiles, label]);
     	});

     	ns_btn_file.on('fileselect', function(event, numFiles, label) {
     		$('#ns-file-status').html(label);
     		console.log(label);
     	});

     }


    /**
     * Reply Toggler.
     *
     * Load Replies and/or Changelog based on user choice.
     * -----------------------------------------------------------
     */
     var reply_toggler        = $('.ns-btn-reply-toggler'),
     	 ticket_log_card      = $('.ticket-log'),
     	 ticket_response_card = $('.ticket-response-cards');

     reply_toggler.on('click', function() {
     	var this_btn = $(this);

    	// Make clicked button active.
    	reply_toggler.removeClass('active').attr('aria-selected', 'false');
    	this_btn.addClass('active').attr('aria-selected', 'true');

    	// Load proper element based on the choice.
    	var selection = this_btn.val();
    	if( 'replies' === selection ) {
    		ticket_log_card.slideUp();
    		ticket_response_card.slideDown();
    	} else if( 'changelog' === selection ) {
    		ticket_response_card.slideUp();
    		ticket_log_card.slideDown();
    	} else if( 'all' === selection ) {
    		ticket_log_card.slideDown();
    		ticket_response_card.slideDown();
    	}
    });

 });
