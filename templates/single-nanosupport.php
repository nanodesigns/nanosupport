<?php
/**
 * Single Ticket View.
 *
 * Template for displaying the content of a single ticket.
 *
 * This template can be overridden by copying it to:
 * your-theme/nanosupport/single-nanosupport.php
 *
 * Template Update Notice:
 * However on occasion NanoSupport may need to update template files, and
 * the theme developers will need to copy the new files to their theme to
 * maintain compatibility.
 *
 * Though we try to do this not very often, but it does happen. And the
 * version below will reflect any changes made to the template file. And
 * for any major changes the Upgrade Notice will inform you pointing this.
 *
 * @author  	nanodesigns
 * @category 	Content
 * @package 	NanoSupport/Templates/
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php ns_get_template_part( 'content-single-ticket.php' ); ?>

	<?php endwhile; // end of the loop. ?>

<?php
get_footer();
