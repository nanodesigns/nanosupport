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
    	$ns_general_settings 		= get_option( 'nanosupport_settings' );
    	$ns_knowledgebase_settings 	= get_option( 'nanosupport_knowledgebase_settings' );

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
		 * To display anything before the knowledgebase.
		 *
		 * @since  1.0.0
		 *
		 * 10	- ns_knowledgebase_navigation()
		 * 20	- nspro_autocomplete_search_form()
		 * 20	- nspro_enqueue_knowledgebase_searchform()
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_before_knowledgebase' );

		
		/**
		 * Featured Knowledgebase Terms
		 * Get values from Knowledgebase Settings
		 * -----------------------------------------------------------------------
		 */
		$featured_terms = isset($ns_knowledgebase_settings['terms']) ? $ns_knowledgebase_settings['terms'] : '';

		if( $featured_terms ) {

			echo '<div class="row">';
			$total_terms = count( $featured_terms );

			$_counter = 1;
			foreach( $featured_terms as $term_id ) {
				$term 		= get_term_by( 'id', $term_id, 'nanodoc_category' );

				if( $term ) :
					$term_name  = $term ? $term->name : '';
					$term_desc  = $term ? $term->description : '';
					$term_link 	= get_term_link( (int) $term_id, 'nanodoc_category' );
					$term_link 	= !is_wp_error( $term_link ) ? $term_link : '#';

					// Dynamic classes for global responsiveness
					if( $_counter % 3 === 1 )
						$column_class = ' first-on-three';
					elseif( $_counter % 2 === 1 )
						$column_class = ' first-on-two';
					else
						$column_class = '';

					echo '<div class="col-sm-4 col-xs-6 nanodoc-term-box'. esc_attr($column_class) .'">';
						echo '<div class="nanodoc-term-box-inner text-center">';
							printf(	'<a class="icon-link" href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term_name), '<span class="ns-icon-docs"></span>' );
							echo '<h4 class="nanodoc-term-title">';
								printf(	'<a href="%1s" title="%2s">%3s</a>', $term_link, esc_attr($term_name), $term_name );
							echo '</h4>';
							if( $term_desc ) {
								echo '<div class="nanodoc-term-desc"><small>';
									echo $term_desc;
								echo '</small></div>';
							}
						echo '</div> <!-- /.nanodoc-term-box-inner -->';
					echo '</div> <!-- /.nanodoc-term-box -->';

				endif;

				$_counter++;
			}
			echo '</div> <!-- /.row -->';

		} //endif( $featured_terms )
		

		echo '<section id="knowledgebase-entries">';

			$kb_terms = get_terms( 'nanodoc_category' );

			if( $kb_terms ) :

				/**
				 * -----------------------------------------------------------------------
				 * HOOK : FILTER HOOK
				 * nanosupport_kb_header_title
				 * 
				 * @since  1.0.0
				 *
				 * @param string  $text Header text. Default 'Documentaion'.
				 * -----------------------------------------------------------------------
				 */
				echo '<h3 class="ticket-separator"><span>';
					echo esc_html( apply_filters( 'nanosupport_kb_header_title', __( 'Documentation', 'nanosupport' ) ) );
				echo '</span></h3>';

				echo '<div class="row">';

				$_entry_counter = 1;
				foreach( $kb_terms as $kb_term ) :

					// Dynamic classes for global responsiveness
					if( $_entry_counter % 3 === 1 )
						$col_class = ' first-on-three';
					elseif( $_entry_counter % 2 === 1 )
						$col_class = ' first-on-two';
					else
						$col_class = '';

					echo '<div class="ns-kb-cat-box col-sm-4 col-xs-6'. esc_attr($col_class) .'">';

						echo '<h4 class="ns-kb-category-title">';
							echo '<span class="ns-icon-docs"></span>&nbsp;';
							echo $kb_term->name;
						echo '</h4>';

						// Get knowledgebase settings. Fallback 'posts_per_page'.
						$kb_posts_per_category = isset($ns_knowledgebase_settings['ppc']) ? $ns_knowledgebase_settings['ppc'] : get_option( 'posts_per_page' );

						$args = array(
							'post_type'		=> 'nanodoc',
							'post_status'	=> 'publish',
							'posts_per_page' => -1,	//all to display 'All entries' button with a single db query
							'tax_query'		=> array(
									array(
										'taxonomy'	=> 'nanodoc_category',
										'field'		=> 'term_id',
										'terms'		=> $kb_term->term_id,
									)
								)
						);

						/**
						 * -----------------------------------------------------------------------
						 * HOOK : FILTER HOOK
						 * nanosupport_knowledgebase_query
						 *
						 * Hook to modify the Knowledgebase query.
						 *
						 * @since  1.0.0
						 * -----------------------------------------------------------------------
						 */
						$kb_entries = new WP_Query(apply_filters( 'nanosupport_knowledgebase_query', $args ));

						//Count how many doc items are fetched
						$kb_found_entries = $kb_entries->found_posts;

						if( $kb_entries->have_posts() ) :

							echo '<ul>';
								$per_category_counter = 1;
								while( $kb_entries->have_posts() ) : $kb_entries->the_post();
									echo '<li class="small"><a href="'. esc_url(get_the_permalink()) .'" title="'. the_title_attribute( array('echo' => false) ) .'" >'. get_the_title() .'</a></li>';

									//Display the maximum numbers set
									if( $kb_posts_per_category === $per_category_counter )
										break;

									$per_category_counter++;
								endwhile;
							echo '</ul>';

							// If the found entries exceeds the preset maximum entries, display the 'See all' button
							if( $kb_found_entries > $kb_posts_per_category ) :
								echo '<a class="btn btn-xs btn-primary" href="'. esc_url(get_term_link( $kb_term, 'nanodoc_category' )) .'"><strong>';
									_e( 'All entries &raquo;', 'nanosupport' );
								echo '</strong></a>';
							endif;

						else :

							echo '<p>'. _e( 'No Knowledgebase entries till now to display in this category.', 'nanosupport' ) .'</p>';

						endif;
						wp_reset_postdata();
					echo '</div> <!-- /.ns-kb-cat-box col-sm-4 col-xs-6 -->';

					$_entry_counter++;

				endforeach;
				echo '</div> <!-- /.row -->';

			else :

				_e( 'Nothing to display on Knowledgebase. Please add some documentation first.', 'nanosupport' );

			endif;

		echo '</section> <!-- /#knowledgebase-entries -->';

		/**
		 * -----------------------------------------------------------------------
		 * HOOK : ACTION HOOK
		 * nanosupport_after_knowledgebase
		 * 
		 * To display anything after the knowledgebase.
		 *
		 * @since  1.0.0
		 * -----------------------------------------------------------------------
		 */
		do_action( 'nanosupport_after_knowledgebase' );

	echo '</div> <!-- /#nanosupport-knowledgebase -->';
	
	return ob_get_clean();
}

add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );
