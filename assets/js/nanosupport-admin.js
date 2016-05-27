/**!
 * NanoSupport Admin Scripts
 * Scripts to decorate/manipulate and executed AJAX requests in NanoSupport admin-end.
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
jQuery(document).ready(function($) {

    /**
     * Making Support and Knowledgebase Question mandatory
     * Making post title field in Support Ticket, and Knowledgebase mandatory.
     * ...
     */
    $('body.post-new-php.post-type-nanosupport input#title, body.post-php.post-type-nanosupport input#title, body.post-new-php.post-type-nanodoc input#title, body.post-php.post-type-nanodoc input#title').prop('required',true);

    /**
     * Delete/Remove Responses
     * Delete/remove individual old, recorded responses from the DOM.
     * ...
     */
    $('.delete-response').on( 'click', function(e) {
        var this_item = $(this),
            delete_btn_id = this_item.attr('id');
        
        //prevent PHP deletion, and let use the AJAX.
        e.preventDefault();

        var confirmed = confirm( ns.del_confirmation );
        if( true === confirmed ) {            
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'delete_response',
                    'id': this.id
                },
                success: function (data) {
                    if( data !== false ) {
                        $('#'+ data).closest('.ticket-response-cards').slideUp();
                    }
                }
            });
        }
    });

    /**
     * Enable Select2
     * Enable Select2 to all the .ns-select fields.
     * ...
     */
    $('select.ns-select').select2({
        minimumResultsForSearch: -1
    });

    $('#ns_doc_terms').select2({
        placeholder: ns.doc_placeholder
    });


    /**
     * Enable wpColorPicker
     * Enable Iris ColorPicker on specific color fields using WP3.5 colorPicker API.
     * @scope includes/admin/ns-settings-email-fields.php
     * ...
     */
    $('.ns-colorbox').wpColorPicker();

});
