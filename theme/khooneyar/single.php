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
			<p class="meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">منتشر شده: <?php echo esc_html( khy_jalali( get_the_date( 'Y-m-d' ) ) ); ?></time>
				<?php if ( get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' ) ) : ?>
					· <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">به‌روزرسانی: <?php echo esc_html( khy_jalali( get_the_modified_date( 'Y-m-d' ) ) ); ?></time>
				<?php endif; ?>
			</p>
		</div>
	</section>
	<article class="section">
		<div class="container narrow prose">
			<?php the_content(); ?>
		</div>
	</article>
	<section class="section section-alt">
		<div class="container narrow">
			<h2 class="section-title">خدمت‌های مرتبط</h2>
			<ul class="pill-list">
				<?php foreach ( khy_services( 6 ) as $s ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
