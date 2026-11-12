<?php
/**
 * 404.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-hero">
	<div class="container narrow">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1>صفحه‌ای که دنبالش بودید پیدا نشد</h1>
		<p class="lead">ممکن است نشانی تغییر کرده باشد. از لینک‌های زیر ادامه دهید یا مستقیم تماس بگیرید.</p>
	</div>
</section>
<section class="section">
	<div class="container narrow">
		<h2 class="section-title">خدمت‌های ما</h2>
		<ul class="pill-list">
			<?php foreach ( khy_services( 8 ) as $s ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
			<?php endforeach; ?>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">صفحهٔ اصلی</a></li>
			<li><a href="<?php echo esc_url( khy_page_url( 'gheymat-nazafat-manzel' ) ); ?>">قیمت‌ها</a></li>
		</ul>
		<p class="mt"><a class="btn btn-primary" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> تماس: <span dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></span></a></p>
	</div>
</section>
<?php
get_footer();
