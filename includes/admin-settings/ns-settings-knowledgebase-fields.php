<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_knowledgebase_settings_section_callback() {
	echo "Knowledgebase section";
}

function ns_doc_terms_field() {
    $options = get_option('nanosupport_knowledgebase_settings');
    echo "<input name='nanosupport_knowledgebase_settings[terms]' id='doc_terms' type='checkbox' value='1' ".checked( 1, $options['terms'], false ) . " /> <label for='doc_terms'>". __( 'Load jQuery from plugin', 'nanosupport' ) ."</label>";
}