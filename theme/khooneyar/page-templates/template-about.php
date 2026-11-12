<?php
/**
 * Template Name: دربارهٔ ما
 * Template Post Type: page
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	?>
	<section class="section">
		<div class="container narrow prose">
			<?php the_content(); ?>
		</div>
	</section>
	<section class="section section-alt">
		<div class="container">
			<div class="grid grid-3">
				<div class="feature"><span class="card-icon"><?php echo khy_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>احراز و انتخاب نیرو</h3><p>نیروها پیش از شروع همکاری مدارک هویتی ارائه می‌دهند و بررسی می‌شوند.</p></div>
				<div class="feature"><span class="card-icon"><?php echo khy_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>قیمت روشن</h3><p>بازهٔ قیمت‌ها در سایت است و مبلغ نهایی قبل از اعزام اعلام می‌شود.</p></div>
				<div class="feature"><span class="card-icon"><?php echo khy_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>تمرکز محلی</h3><p>فعالیت ما از انقلاب و فلسطین شروع می‌شود تا در همین محدوده سریع و منظم باشیم.</p></div>
			</div>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
