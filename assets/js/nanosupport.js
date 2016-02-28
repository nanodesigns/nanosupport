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

});