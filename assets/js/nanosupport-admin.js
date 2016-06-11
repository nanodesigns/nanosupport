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
    var select_field        = $('select.ns-select'),
        select_search       = $('select.ns-select-search'),
        auto_select_field   = $('select.ns-auto-select'),
        auto_select_search  = $('select.ns-auto-select-search');

    if( select_field.length > 0 ) {
        select_field.select2({
            minimumResultsForSearch: -1
        });
    }
    if( auto_select_field.length > 0 ) {
        auto_select_field.select2({
            minimumResultsForSearch: -1
        });
    }

    if( select_search.length > 0 ) {
        select_search.select2();
    }
    if( auto_select_search.length > 0 ) {
        auto_select_search.select2();
    }


    /**
     * Enable wpColorPicker
     * Enable Iris ColorPicker on specific color fields using WP3.5 colorPicker API.
     * @scope includes/admin/ns-settings-email-fields.php
     * ...
     */
    var color_holder = $('.ns-colorbox');
    if( color_holder.length > 0 ) {
        color_holder.wpColorPicker();
    }

});
