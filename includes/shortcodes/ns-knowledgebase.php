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

	echo '<div id="nanosupport-knowledgebase">';

		//Get the NanoSupport Settings from Database
    	$ns_general_settings = get_option( 'nanosupport_settings' );

		/**
		 * Show a Redirection Message
		 * while redirected.
		 */
		if( isset($_GET['from']) && 'sd' === $_GET['from'] ) {
			echo '<div class="alert alert-info" role="alert">';
				_e( 'You are redirected from the Support Desk, because you are not logged in, and have no permission to view any ticket.', 'nanosupport' );
			echo '</div>';
		}

		?>

		<?php
		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_before_knowledgebase
		 * 
		 * Display a searchform capable of searching specific
		 * only to the knowledgebase.
		 *
		 * 10	ns_knowledgebase_navigation()
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_before_knowledgebase' );

		/**
		 * -----------------------------------------------------------------------
		 * HOOK : FILTER HOOK
		 * nanosupport_kb_posts_per_page
		 *
		 * Modify Knowledgebase posts_per_page.
		 * -----------------------------------------------------------------------
		 */
		$kb_posts_per_page = apply_filters( 'nanosupport_kb_posts_per_page', get_option('posts_per_page') );


		//Dynamic values
		$terms = array(227,230,231,232,233);

		if( $terms ) {

			echo '<div class="row">';
			$total_terms = count( $terms );

			$_counter = 1;
			foreach( $terms as $term_id ) {
				$term 		= get_term_by( 'id', $term_id, 'nanodoc_category' );
				$term_link 	= get_term_link( $term_id, 'nanodoc_category' );
				$term_link 	= !is_wp_error( $term_link ) ? $term_link : '#';

				// Dynamic classes
				if( $_counter % 3 === 1 )
					$column_class = ' first-on-three';
				elseif( $_counter % 2 === 1 )
					$column_class = ' first-on-two';
				else
					$column_class = '';

				echo '<div class="col-sm-4 col-xs-6 nanodoc-term-box'. esc_attr($column_class) .'">';
					echo '<div class="nanodoc-term-box-inner text-center">';
						printf(	'<a class="icon-link" href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term->name), '<span class="ns-icon-docs"></span>' );
						echo '<h4 class="nanodoc-term-title">';
							printf(	'<a href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term->name), $term->name );
						echo '</h4>';
						if( $term->description ) {
							echo '<div class="nanodoc-term-desc"><small>';
								echo $term->description;
							echo '</small></div>';
						}
					echo '</div> <!-- /.nanodoc-term-box-inner -->';
				echo '</div> <!-- /.nanodoc-term-box -->';

				$_counter++;
			}
			echo '</div> <!-- /.row -->';

		} //endif( $terms )


		echo '<hr>';
		

		echo '<section id="knowledgebase-entries">';

			//Arguments for Default Knowledgebase doc
			//Show all the docs
			$args = array(
					'post_type'			=> 'nanodoc',
					'posts_per_page'	=> $kb_posts_per_page,
					'post_status'		=> 'publish',
				);

			/**
			 * -----------------------------------------------------------------------
			 * HOOK : FILTER HOOK
			 * nanosupport_knowledgebase_query
			 *
			 * Hook to modify the Knowledgebase query.
			 * -----------------------------------------------------------------------
			 */
			$knowledgebase = new WP_Query( apply_filters( 'nanosupport_knowledgebase_query', $args ) );

			if( $knowledgebase->have_posts() ) :

				/**
				 * -----------------------------------------------------------------------
				 * HOOK : FILTER HOOK
				 * nanosupport_kb_header_title
				 *
				 * @param string  $text Header text. Default 'Documentaion'.
				 * -----------------------------------------------------------------------
				 */
				$knowledgebase_title = apply_filters( 'nanosupport_kb_header_title', __( 'Documentation', 'nanosupport' ) );

				echo '<h3 class="ns-section-title">'. esc_html($knowledgebase_title) .'</h3>';
				echo '<div class="row">';
					echo '<div class="col-sm-12">';
						echo '<ul class="ns-doc-list">';
							while( $knowledgebase->have_posts() ) : $knowledgebase->the_post();
								echo '<li><a href="'. get_the_permalink() .'">'. get_the_title() .'</a></li>';
							endwhile;
						echo '</ul> <!-- /.ns-doc-list -->';
					echo '</div> <!-- /.col-sm-12 -->';
				echo '</div> <!-- /row -->';
				wp_reset_postdata();

			else :
				
				echo '<p>'. _e( 'No Knowledgebase entries till now to display.', 'nanosupport' ) .'</p>';

			endif;

		echo '</section> <!-- /#knowledgebase-entries -->';

	echo '</div> <!-- /#nanosupport-knowledgebase -->';
	
	return ob_get_clean();
}

add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );