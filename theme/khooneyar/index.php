<?php
/**
 * Fallback loop (archives, categories, search).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-hero">
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1><?php echo is_search() ? 'نتایج جستجو' : esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	</div>
</section>
<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid-3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => 'قبلی', 'next_text' => 'بعدی' ) ); ?>
		<?php else : ?>
			<p>مطلبی پیدا نشد.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta' );
get_footer();
