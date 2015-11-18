<?php
function ns_sample_shortcode() {
	ob_start();
		echo "THIS";
	return ob_get_clean();
}
add_shortcode( 'ns_sample', 'ns_sample_shortcode' );