<?php
/**
 * Single neighborhood: unique local text + links to its services (no templated thin copy).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$id       = get_the_ID();
	$arrival  = get_post_meta( $id, '_khy_arrival', true );
	$services = function_exists( 'khy_get_neighborhood_services' ) ? khy_get_neighborhood_services( $id ) : array();
	$nearby   = array_filter( array_map( 'get_post', array_map( 'absint', get_post_meta( $id, '_khy_nearby', false ) ) ) );
	$chips    = array( array( 'pin', get_the_title() . '، تهران' ) );
	if ( $arrival ) {
		$chips[] = array( 'clock', 'زمان رسیدن: ' . $arrival );
	}
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
				<?php if ( $arrival ) : ?><p class="sample-note">زمان رسیدن تقریبی است و به ترافیک و ساعت سفارش بستگی دارد.</p><?php endif; ?>
			</div>
			<div class="hero-form">
				<?php get_template_part( 'template-parts/order-form', null, array( 'area_id' => $id, 'heading' => 'سفارش در ' . get_the_title(), 'compact' => true ) ); ?>
			</div>
		</div>
	</section>

	<?php if ( $services ) : ?>
		<section class="section" aria-labelledby="hs-h">
			<div class="container">
				<h2 id="hs-h" class="section-title">خدمت‌ها در <?php the_title(); ?></h2>
				<div class="grid grid-3">
					<?php foreach ( $services as $s ) : ?>
						<?php get_template_part( 'template-parts/service-card', null, array( 'post' => $s ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( khy_has_content() ) : ?>
		<section class="section section-alt" aria-label="دربارهٔ محله">
			<div class="container narrow prose entry-content">
				<?php the_content(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $nearby ) : ?>
		<section class="section" aria-labelledby="nb-h">
			<div class="container">
				<h2 id="nb-h" class="section-title">محله‌های نزدیک</h2>
				<ul class="pill-list">
					<?php foreach ( $nearby as $n ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $n ) ); ?>"><?php echo esc_html( $n->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta', null, array( 'title' => 'ساکن ' . get_the_title() . ' هستید؟ همین حالا سفارش بدهید' ) );
endwhile;
get_footer();
