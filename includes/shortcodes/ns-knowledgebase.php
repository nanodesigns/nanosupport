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
	 * HOOK
	 * nanosupport_knowledgebase_serachform
	 * 
	 * Knowledgebase Searchform
	 * 
	 * Display a searchform capable of searching specific
	 * only to the knowledgebase.
	 */
	do_action( 'nanosupport_knowledgebase_serachform' );

	/**
	 * Necessary values in vars.
	 */
	$show_all_doc = true; //default: false (only featured)

	/**
	 * HOOK : FILTER HOOK
	 * nanosupport_kb_posts_per_page
	 *
	 * Modify Knowledgebase posts_per_page.
	 */
	$kb_posts_per_page = apply_filters( 'nanosupport_kb_posts_per_page', get_option('posts_per_page') );


	/**
	 * HOOK
	 * nanosupport_knowledgebase_categories
	 *
	 * Hook categories into display.
	 */
	do_action( 'nanosupport_knowledgebase_categories' );

	

	echo '<section id="knowledgebase-entries">';

		//Arguments for Default Knowledgebase doc
		//Show all the docs
		$args = array(
				'post_type'			=> 'nanodoc',
				'posts_per_page'	=> $kb_posts_per_page,
				'post_status'		=> 'publish',
			);

		/**
		 * HOOK : FILTER HOOK
		 * nanosupport_knowledgebase_query
		 *
		 * Hook to modify the Knowledgebase query.
		 */
		$knowledgebase = new WP_Query( apply_filters( 'nanosupport_knowledgebase_query', $args ) );

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
			
			echo '<p>'. _e( 'No Knowledgebase entries till now to display.', 'nanosupport' ) .'</p>';

		endif;

	echo '</section> <!-- /#knowledgebase-entries -->';
	
	return ob_get_clean();
}
add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );