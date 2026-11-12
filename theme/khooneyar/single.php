<?php
/**
 * Blog post.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero">
		<div class="container narrow">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<h1><?php echo esc_html( khy_h1() ); ?></h1>
			<?php get_template_part( 'template-parts/entry-meta', null, array( 'published' => true, 'updated' => true, 'author' => true ) ); ?>
		</div>
	</section>
	<article class="section">
		<div class="container narrow prose entry-content">
			<?php the_content(); ?>
		</div>
	</article>
	<?php $related = khy_services( 6 ); ?>
	<?php if ( $related ) : ?>
		<section class="section section-alt">
			<div class="container narrow">
				<h2 class="section-title">خدمت‌های مرتبط</h2>
				<ul class="pill-list">
					<?php foreach ( $related as $s ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>
	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
