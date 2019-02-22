/**!
 * NanoSupport Copy to Knowledgebase Scripts
 * Scripts to copy NanoSupport ticket content to Knowledgebase.
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
 jQuery(document).ready(function($) {
    // display the progress spinner
    NProgress.configure({ showSpinner: true });

    $('.ns-copy-post').on( 'click', function(e) {
    	e.preventDefault();

        // display the progress bar
        NProgress.start();

        var this_elem = $(this),
        data = {
                action:     'ns_copy_ticket', //passed to ajax handler
                ticket_id:  this_elem.data('ticket'),
                nonce:      this_elem.data('nonce')
            };

            // grab attention with the progress bar
            NProgress.inc(0.3);

            $.post( ajaxurl, data, function( response ) {
            // clear out the progress bar
            NProgress.done();
        });

    });

});
