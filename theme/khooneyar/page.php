<?php
/**
 * Generic page.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero', null, array( 'meta' => array( 'updated' => true ) ) );
	?>
	<?php if ( khy_has_content() ) : ?>
		<section class="section">
			<div class="container narrow prose">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
