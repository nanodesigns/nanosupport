<?php
/**
 * Shortcode: Knowledgebase
 *
 * Showing the common ticket center of all the support tickets to the respective privileges.
 * Show all the tickets at the front end using shortcode [nanosupport_knowledgebase]
 *
 * @author  	nanodesigns
 * @category 	Shortcode
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ns_knowledgebase_page() {
	ob_start();

		/**
		 * Knowledgebase Searchform
		 * 
		 * Display a searchform capable of searching specific
		 * only to the knowledgebase.
		 */
		ns_get_template_part( 'ns', 'knowledgebase-searchform' );

		//dynamic values
		$terms = array(227,230,231,232,233);
		$show_all_doc = false; //default: false (only featured)

		if( $terms ) {

			echo '<div class="row">';
			$total_terms = count( $terms );

			$_counter = 1;
			foreach( $terms as $term_id ) {
				$term = get_term_by( 'id', $term_id, 'nanodoc_category' );
				$term_link = get_term_link( $term_id, 'nanodoc_category' );
				$term_link = !is_wp_error( $term_link ) ? $term_link : '#';

				// Dynamic classes
				$class_on_four 	= $_counter % 4 === 1 ? ' first-on-four' : '';
				$class_on_two 	= $_counter % 2 === 1 ? ' first-on-two' : '';

				echo '<div class="col-sm-3 col-xs-6 nanodoc-term-box'. esc_attr($class_on_four), esc_attr($class_on_two) .'">';
					echo '<div class="nanodoc-term-box-inner text-center">';
						printf(	'<a class="icon-link" href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term->name), '<span class="ns-icon-docs"></span>' );
						echo '<h4 class="nanodoc-term-title">';
							printf(	'<a href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term->name), $term->name );
						echo '</h4>';
					echo '</div> <!-- /.nanodoc-term-box-inner -->';
				echo '</div> <!-- /.nanodoc-term-box -->';

				$_counter++;
			}
			echo '</div> <!-- /.row -->';

			echo '<hr>';
		} //endif( $terms )

		//Arguments for Featured Knowledgebase doc
		$featured_args = array(
				'meta_key'			=> 'ns_nanodoc_featured',
				'meta_value'		=> 1,
				'meta_compare'		=> '='
			);

		//Arguments for Default Knowledgebase doc
		$args = array(
				'post_type'			=> 'nanodoc',
				'posts_per_page'	=> 20,
				'post_status'		=> 'publish',
			);

		if( ! $show_all_doc )
			$kb_args = $args + $featured_args; 	//featured docs only
		else
			$kb_args = $args; 					//all docs

		$knowledgebase = new WP_Query( $kb_args );

		if( $knowledgebase->have_posts() ) :
			while( $knowledgebase->have_posts() ) : $knowledgebase->the_post();
				the_title( '<h3 class="entry-title">', '<h3>' );
			endwhile;
			wp_reset_postdata();
		else :
			echo '<p>'. _e( '', 'nanosupport' ) .'</p>';
		endif;
	
	return ob_get_clean();
}
add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );