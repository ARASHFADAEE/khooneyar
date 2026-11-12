<?php
/**
 * Blog index (/blog/).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-hero">
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1><?php echo esc_html( get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : 'راهنما و مقاله‌ها' ); ?></h1>
		<?php $blog_lead = get_option( 'page_for_posts' ) ? khy_lead( get_option( 'page_for_posts' ) ) : ''; ?>
		<?php if ( $blog_lead ) : ?><p class="lead"><?php echo esc_html( $blog_lead ); ?></p><?php endif; ?>
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
