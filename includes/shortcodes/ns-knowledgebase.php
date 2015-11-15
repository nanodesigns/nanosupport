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
	 * Show a Redirection Message
	 * while redirected.
	 */
	if( isset($_GET['from']) && 'sd' === $_GET['from'] ) {
		echo '<div class="alert alert-info" role="alert">';
			_e( 'You are redirected from the Support Desk, because you are not logged in, and have no permission to view any ticket.', 'nanosupport' );
			echo '</div>';
	}

	/**
	 * Knowledgebase Searchform
	 * 
	 * Display a searchform capable of searching specific
	 * only to the knowledgebase.
	 */
	ns_get_template_part( 'ns', 'knowledgebase-searchform' );

	//dynamic values
	$terms = array(227,230,231,232,233);
	$show_all_doc = true; //default: false (only featured)
	$kb_posts_per_page = 20;

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

	echo '<section id="knowledgebase-entries">';

		//Arguments for Featured Knowledgebase doc
		$featured_args = array(
				'meta_key'			=> 'ns_nanodoc_featured',
				'meta_value'		=> 1,
				'meta_compare'		=> '='
			);

		//Arguments for Default Knowledgebase doc
		$args = array(
				'post_type'			=> 'nanodoc',
				'posts_per_page'	=> $kb_posts_per_page,
				'post_status'		=> 'publish',
			);

		if( ! $show_all_doc )
			$kb_args = $args + $featured_args; 	//featured docs only
		else
			$kb_args = $args; 					//all docs

		//with dynamic arguments
		$knowledgebase = new WP_Query( $kb_args );

		if( $knowledgebase->found_posts > 5 && $kb_posts_per_page > 5 )
			$column_count = $kb_posts_per_page / 2; //half, if posts more than 5
		else
			$column_count = false;

		if( $column_count )
			$col_class = 'col-sm-6';
		else
			$col_class = 'col-sm-12';

		if( $knowledgebase->have_posts() ) :

			echo '<h3 class="ns-section-title">'. __( 'Documentation', 'nanosupport' ) .'</h3>';
			echo '<div class="row">';
				echo '<div class="'. esc_attr($col_class) .'">';
					echo '<ul class="ns-doc-list">';
						while( $knowledgebase->have_posts() ) : $knowledgebase->the_post();
							echo '<li><a href="'. get_the_permalink() .'">'. get_the_title() .'</a></li>';
						endwhile;
					echo '</ul> <!-- /.ns-doc-list -->';
				echo '</div> <!-- /.'. esc_attr($col_class) .' -->';
			echo '</div> <!-- /row -->';
			wp_reset_postdata();

		else :
			
			echo '<p>'. _e( '', 'nanosupport' ) .'</p>';

		endif;

	echo '</section> <!-- /#knowledgebase-entries -->';
	
	return ob_get_clean();
}
add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );