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
     *
     * @since  1.0.0
     * ...
     */
    $('body.post-new-php.post-type-nanosupport input#title, body.post-php.post-type-nanosupport input#title, body.post-new-php.post-type-nanodoc input#title, body.post-php.post-type-nanodoc input#title').prop('required',true);

    /**
     * Delete/Remove Responses
     * Delete/remove individual old, recorded responses from the DOM.
     *
     * @since  1.0.0
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
     *
     * @since  1.0.0
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
     *
     * @scope  includes/admin/ns-settings-email-fields.php
     * @since  1.0.0
     * ...
     */
    var color_holder = $('.ns-colorbox');
    if( color_holder.length > 0 ) {
        color_holder.wpColorPicker();
    }


    /**
     * Knowledgebase Categories Icon Selection
     * Selecting knowledgebase categories icon from lightbox.
     * 
     * @scope  includes/ns-cpt-knowledgebase.php
     * @since  1.0.0
     * ...
     */
    var ns_icon_selector = $('.nanosupport-icon-button');

    if( ns_icon_selector.length > 0 ) {
        ns_icon_selector.on('click', function(){
            var this_btn = $(this),
                icon_class = this_btn.val();

            ns_icon_selector.removeClass('active');
            this_btn.addClass('active');

            $('#kb-cat-icon').val(icon_class).attr('value', icon_class);
            $('#nanosupport-icon-preview i').attr('class', icon_class);

            //close the ThickBox window
            tb_remove();
        });
    }


    /**
     * Optional Notices
     * Top navigation and notices are made optional. Showing/hiding
     * message boxes based on checkbox choice.
     * 
     * @scope  includes/admin/ns-settings-knowledgebase-fields.php
     * @since  1.0.0
     * ...
     */
    var ns_notice_activator         = $('#enable_notice'),
        ns_notice_toggle_selector   = $('#submit_ticket_notice, #support_desk_notice, #knowledgebase_notice').closest('tr');

    if( ns_notice_toggle_selector.length > 0 ) {

        //hide initially
        ns_notice_toggle_selector.hide();

        //display, if checkbox is checked
        if( ns_notice_activator.is(":checked") ) {
            ns_notice_toggle_selector.show();
        }

        //toggle on checkbox check
        ns_notice_activator.on( 'click', function() {
            if( $(this).is(':checked') ) {
                ns_notice_toggle_selector.show();
            } else {
                ns_notice_toggle_selector.hide();
            }
        });
    }


    /**
     * Optional Knowledgebase
     * Knowledgebase Settings making optional, showing/hiding
     * KB settings as per checkbox selection.
     * 
     * @scope  includes/admin/ns-settings-knowledgebase-fields.php
     * @since  1.0.0
     * ...
     */
    var ns_kb_activator         = $('#isactive_kb'),
        ns_kb_toggle_selector   = $('#ns_knowledgebase, #ns_doc_terms, #ns_doc_ppc, #rewrite_url, .ns-hide').closest('tr');

    if( ns_kb_toggle_selector.length > 0 ) {

        //hide initially
        ns_kb_toggle_selector.hide();

        //display, if checkbox is checked
        if( ns_kb_activator.is(":checked") ) {
            ns_kb_toggle_selector.show();
        }

        //toggle on checkbox check
        ns_kb_activator.on( 'click', function() {
            if( $(this).is(':checked') ) {
                ns_kb_toggle_selector.show();
            } else {
                ns_kb_toggle_selector.hide();
            }
        });
    }

    /**
     * Copy system status.
     * Copy system status on click.
     * ...
     */
    var export_status_div = $('#ns-export-status');

    if( export_status_div.length > 0 ) {
        $('#ns-export-status').on( 'click', function(e){
            e.preventDefault();

            copyToClipboard($('#ns-system-status-text').text());
            
            // Make sure the user understands the consequence.
            $(this).find('i.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
        });
    }

    $('#ns-btn-edit-product').on('click', function() {
        $('#ns-product-display-panel').hide();
        $('#ns-product-edit-panel').show();
    });

});

/**
 * Copy to Clipboard.
 * @{@link  https://gist.github.com/lgarron/d1dee380f4ed9d825ca7}
 * @param  {string} ) {               var _dataString Content to copy.
 * @return {void}
 * -------------------------------------------------------------------
 */
var copyToClipboard = (function() {
    var _dataString = null;
    document.addEventListener("copy", function(e){
        if (_dataString !== null) {
            try {
                e.clipboardData.setData("text/plain", _dataString);
                e.preventDefault();
            } finally {
                _dataString = null;
            }
        }
    });

    return function(data) {
        _dataString = data;
        document.execCommand("copy");
    };
})();
