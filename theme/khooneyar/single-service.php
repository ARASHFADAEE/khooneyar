<?php
/**
 * Single service: H1, price summary, details, how to order, FAQ, CTA.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$id      = get_the_ID();
	$price   = get_post_meta( $id, '_khy_price_summary', true );
	$arrival = get_post_meta( $id, '_khy_arrival', true );
	$hoods   = function_exists( 'khy_get_service_neighborhoods' ) ? khy_get_service_neighborhoods( $id ) : array();
	$chips   = array();
	if ( $arrival ) {
		$chips[] = array( 'clock', $arrival );
	}
	$chips[] = array( 'shield', 'نیروی احراز هویت‌شده' );
	$chips[] = array( 'pin', 'تهران؛ انقلاب و فلسطین' );
	?>
	<section class="page-hero has-form">
		<div class="container hero-grid">
			<div>
				<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
				<h1><?php echo esc_html( khy_h1() ); ?></h1>
				<?php if ( khy_lead() ) : ?>
					<p class="lead"><?php echo esc_html( khy_lead() ); ?></p>
				<?php endif; ?>
				<?php get_template_part( 'template-parts/entry-meta', null, array( 'updated' => true ) ); ?>
				<ul class="chips">
					<?php foreach ( $chips as $c ) : ?>
						<li><?php echo khy_icon( $c[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $c[1] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $price ) : ?>
					<div class="price-box">
						<span class="price-label"><?php echo khy_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> قیمت خلاصه</span>
						<strong class="price-value"><?php echo esc_html( $price ); ?></strong>
						<?php if ( khy_setting( 'prices_sample' ) ) : ?><span class="badge badge-sample">قیمت نمونه</span><?php endif; ?>
						<a href="<?php echo esc_url( khy_page_url( 'gheymat-nazafat-manzel' ) ); ?>">جدول کامل قیمت‌ها</a>
					</div>
				<?php endif; ?>
				<div class="hero-actions">
					<a class="btn btn-primary" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> تماس فوری</a>
					<a class="btn btn-wa" href="<?php echo esc_url( khy_wa_href( 'سلام، درباره «' . get_the_title() . '» سؤال دارم.' ) ); ?>" target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> واتساپ</a>
				</div>
			</div>
			<div class="hero-form">
				<?php get_template_part( 'template-parts/order-form', null, array( 'service_id' => $id, 'heading' => 'سفارش ' . get_the_title(), 'compact' => true ) ); ?>
			</div>
		</div>
	</section>

	<?php if ( khy_has_content() ) : ?>
		<section class="section" aria-label="جزئیات خدمت">
			<div class="container narrow prose entry-content">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/how-to-order' ); ?>

	<?php if ( $hoods ) : ?>
		<section class="section" aria-labelledby="hoods-h">
			<div class="container">
				<h2 id="hoods-h" class="section-title">محله‌هایی که پوشش می‌دهیم</h2>
				<ul class="pill-list">
					<?php foreach ( $hoods as $n ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $n ) ); ?>"><?php echo esc_html( $n->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/testimonials' );
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
