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

	echo '<div id="nanosupport-knowledgebase" class="ns-no-js">';

		//Get the NS Knowledgebase Settings from Database
		$ns_knowledgebase_settings 	= get_option( 'nanosupport_knowledgebase_settings' );

		/**
		 * Show a Redirection Message
		 * while redirected.
		 */
		if( isset($_GET['from']) && 'sd' === $_GET['from'] ) {
			echo '<div class="ns-alert ns-alert-info" role="alert">';
			esc_html_e( 'You are redirected from the Support Desk, because you are not logged in, and have no permission to view any ticket.', 'nanosupport' );
			echo '</div>';
		}

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
		$featured_terms = isset($ns_knowledgebase_settings['terms']) && ! empty($ns_knowledgebase_settings['terms'][0]) ? $ns_knowledgebase_settings['terms'] : false;

		if( $featured_terms ) {

			echo '<div class="ns-row">';
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

						//Get term icons
						$saved_meta    = get_term_meta( $term_id, '_ns_kb_cat_icon', true );
						$ns_icon_class = $saved_meta ? $saved_meta : 'ns-icon-docs';

						echo '<div class="ns-col-sm-4 ns-col-xs-6 nanodoc-term-box'. esc_attr($column_class) .'">';
							echo '<div class="nanodoc-term-box-inner ns-text-center">';
								echo '<a class="icon-link" href="'. $term_link .'" tabindex="-1">';
									echo '<i class="nanosupport-kb-icon '. esc_attr($ns_icon_class) .'" aria-hidden="true"></i> <span class="screen-reader-only">'. $term_name .'</span>';
								echo '</a>';
								echo '<h4 class="nanodoc-term-title">';
									printf(	'<a href="%1s" class="nanodoc-term-link">%2s</a>', $term_link, $term_name );
								echo '</h4>';
								if( $term_desc ) {
									echo '<div class="nanodoc-term-desc ns-small">';
										echo esc_html( $term_desc );
									echo '</div>';
								}
							echo '</div> <!-- /.nanodoc-term-box-inner -->';
						echo '</div> <!-- /.nanodoc-term-box -->';

					endif;

					$_counter++;
				}
			echo '</div> <!-- /.ns-row -->';

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
				 * @param string  $text Header text. Default 'Documentation'.
				 * -----------------------------------------------------------------------
				 */
				// Not necessary, if there is no Featured category/ies
				if( $featured_terms ) {
					echo '<h3 class="ticket-separator ticket-separator-center ns-text-uppercase">';
					echo esc_html( apply_filters( 'nanosupport_kb_header_title', __( 'Documentation', 'nanosupport' ) ) );
					echo '</h3>';
				}

				echo '<div class="ns-row">';

				$_entry_counter = 1;
				foreach( $kb_terms as $kb_term ) :

					// Dynamic classes for global responsiveness
					if( $_entry_counter % 3 === 1 )
						$col_class = ' first-on-three';
					elseif( $_entry_counter % 2 === 1 )
						$col_class = ' first-on-two';
					else
						$col_class = '';

					//Get term icons
					$saved_meta    = get_term_meta( $kb_term->term_id, '_ns_kb_cat_icon', true );
					$ns_icon_class = $saved_meta ? $saved_meta : 'ns-icon-docs';

					echo '<div class="ns-kb-cat-box ns-col-sm-4 ns-col-xs-6'. esc_attr($col_class) .'">';

						echo '<a href="'. get_term_link( $kb_term, 'nanodoc_category' ) .'" class="nanosupport-kb-icon kb-cat-icon-inner '. esc_attr($ns_icon_class) .'" tabindex="-1">';
							echo '<span class="screen-reader-only">'. $kb_term->name .'</span>';
						echo '</a>';

						echo '<h4 class="ns-kb-category-title">';
							echo '<a href="'. get_term_link( $kb_term, 'nanodoc_category' ) .'">';
								echo $kb_term->name;
							echo '</a>';
						echo '</h4>';

						// Get knowledgebase settings. Fallback 'posts_per_page'.
						$kb_posts_per_category = isset($ns_knowledgebase_settings['ppc']) ? $ns_knowledgebase_settings['ppc'] : get_option( 'posts_per_page' );

						$args = array(
							'post_type'		=> 'nanodoc',
							'post_status'	=> 'publish',
							'posts_per_page' => $kb_posts_per_category,
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

							echo '<ul class="ns-kb-catbox-list">';
								$per_category_counter = 1;
								while( $kb_entries->have_posts() ) : $kb_entries->the_post();
									echo '<li class="ns-small"><a href="'. esc_url(get_the_permalink()) .'">'. get_the_title() .'</a></li>';

										//Display the maximum numbers set
									if( $kb_posts_per_category === $per_category_counter )
										break;

									$per_category_counter++;
								endwhile;
							echo '</ul>';

							// If the found entries exceeds the preset maximum entries, display the 'See all' button
							if( $kb_found_entries > $kb_posts_per_category ) :
								echo '<a class="ns-btn ns-btn-xs ns-btn-primary" href="'. get_term_link( $kb_term, 'nanodoc_category' ) .'"><strong>';
								echo __( 'All entries', 'nanosupport' ) .' &raquo;';
								echo '</strong></a>';
							endif;

						else :

							echo '<p>'. _e( 'No Knowledgebase entries till now to display in this category.', 'nanosupport' ) .'</p>';

						endif;
						wp_reset_postdata();
					echo '</div> <!-- /.ns-kb-cat-box ns-col-sm-4 ns-col-xs-6 -->';

					$_entry_counter++;

				endforeach;
				echo '</div> <!-- /.ns-row -->';

			else :

				echo '<div class="ns-alert ns-alert-info" role="alert">';
				if( ns_is_user('manager') )
					/* translators: URL to add new knowledgebase doc */
				printf( wp_kses( __( 'Nothing to display on Knowledgebase. Please <a href="%s">Add some documentation</a> first, and categorize them accordingly.', 'nanosupport' ), array('a'=>array( 'href'=>true )) ), admin_url('post-new.php?post_type=nanodoc') );
				else
					_e( 'Nothing to display on Knowledgebase.', 'nanosupport' );
				echo '</div>';

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

// Get Knowledgebase settings from db.
$ns_knowledgebase_settings = get_option( 'nanosupport_knowledgebase_settings' );

if( isset($ns_knowledgebase_settings['isactive_kb']) && $ns_knowledgebase_settings['isactive_kb'] === 1 ) {
	add_shortcode( 'nanosupport_knowledgebase', 'ns_knowledgebase_page' );
}
