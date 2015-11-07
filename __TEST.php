<?php
function pw_sample_shortcode() {
	ob_start();
		nst_get_template_part( 'nst', 'header' );
	return ob_get_clean();
}
add_shortcode( 'pw_sample', 'pw_sample_shortcode' );