<?php
/**
 * Neighborhood hub (/tehran/).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-hero">
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1>خدمات منزل در محله‌های تهران</h1>
		<p class="lead">برای هر محله زمان رسیدن، توضیحات محلی و لینک خدمت‌ها را جدا نوشته‌ایم. محله شما را ببینید.</p>
	</div>
</section>
<section class="section">
	<div class="container">
		<div class="grid grid-3">
			<?php
			while ( have_posts() ) :
				the_post();
				$arrival = get_post_meta( get_the_ID(), '_khy_arrival', true );
				?>
				<article class="card">
					<span class="card-icon"><?php echo khy_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2 class="card-h2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php if ( $arrival ) : ?><p class="card-price"><?php echo khy_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $arrival ); ?></p><?php endif; ?>
					<a class="card-link" href="<?php the_permalink(); ?>">خدمات این محله <?php echo khy_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				</article>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta' );
get_footer();
