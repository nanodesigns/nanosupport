<?php
function pw_sample_shortcode() {
	ob_start();
		ns_get_template_part( 'ns', 'header' );
	return ob_get_clean();
}
add_shortcode( 'pw_sample', 'pw_sample_shortcode' );