/**!
 * NanoSupport Admin Scripts
 * Scripts to decorate/manipulate NanoSupport admin-end.
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
jQuery(document).ready(function($) {

	/**
	 * Add/Remove responses
	 * @scope includes/ns-metaboxes-responses.php
	 */
	var add_response_btn	= $('#ns-add-response'),
        remove_response_btn = $('#ns-remove-response'),
        save_response_btn	= $('#ns-save-response'),
        count               = 1;

    add_response_btn.on( 'click', function() {
    	$('<div id="ns-new-response-'+ count +'" class="ns-response-group-new"><div class="ns-row"><div class="response-user ns-new-response-color"><input type="hidden" name="ns_date[]" id="ns-date" value="'+ ns.date_time_now +'"><input type="hidden" name="ns_user[]" id="ns-user" value="'+ ns.user_id +'">'+ ns.current_user +' &mdash; <span>'+ ns.date_time_formatted +'</span><span class="go-right">New Response</span></div><div class="ns-box"><div class="ns-field"><textarea class="ns-field-item" name="ns_response[]" id="ns-response" rows="5"></textarea></div></div></div></div>').appendTo($('.ns-holder'));
    	count++;

        save_response_btn.show();
        remove_response_btn.show();

    }); 
    
    remove_response_btn.on( 'click', function() {
        $('.ns-response-group-new:nth-last-of-type(1)').remove();
            
        if( $('.ns-response-group-new').length < 1 ) {
            save_response_btn.hide();
            remove_response_btn.hide();
        }
    });


    //delete/remove individual old, recorded responses from the DOM
    var delete_btn = $('.delete-response');
    delete_btn.each(function(){
        var this_item = $(this);
        var delete_btn_id = this_item.attr('id');
        this_item.replaceWith( '<span id="'+ delete_btn_id +'" class="delete-response dashicons dashicons-dismiss" title="Delete Response"></span>' );
    });

    /**
     * Enable Select2
     * Enable Select2 to all the .ns-select fields.
     */
    $('select.ns-select').select2({
        minimumResultsForSearch: -1
    });

    $('#ns_doc_terms').select2({
        placeholder: 'Select categories'
    });

});