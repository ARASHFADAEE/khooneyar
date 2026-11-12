<?php
/**
 * Template Name: قیمت‌ها
 * Template Post Type: page
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$rows    = get_post_meta( get_the_ID(), '_khy_price_rows', true );
	$rows    = is_array( $rows ) ? $rows : array();
	$groups  = array();
	foreach ( $rows as $r ) {
		$groups[ ! empty( $r['g'] ) ? $r['g'] : 'قیمت خدمت‌ها' ][] = $r;
	}
	$updated = khy_setting( 'prices_updated' );
	$sample  = (bool) khy_setting( 'prices_sample' );
	$by_slug = array();
	foreach ( khy_services( 30 ) as $s ) {
		$by_slug[ $s->post_name ] = $s->ID;
	}
	$after  = '<p class="updated">' . khy_icon( 'calendar' ) . ' آخرین به‌روزرسانی قیمت‌ها: <time datetime="' . esc_attr( $updated ) . '">' . esc_html( khy_jalali( $updated ) ) . '</time></p>';
	get_template_part( 'template-parts/page-hero', null, array( 'after' => $after ) );
	?>
	<section class="section">
		<div class="container">
			<?php if ( $sample ) : ?>
				<p class="sample-flag"><strong>توجه:</strong> قیمت‌های این صفحه <strong>نمونه</strong> هستند و برای طراحی گذاشته شده‌اند. پیش از انتشار با قیمت‌های واقعی جایگزین شوند.</p>
			<?php endif; ?>

			<?php foreach ( $groups as $gname => $items ) : ?>
				<h2 class="section-title"><?php echo esc_html( $gname ); ?></h2>
				<div class="table-wrap">
					<table class="price-table">
						<thead>
							<tr><th scope="col">خدمت</th><th scope="col">مدت / واحد</th><th scope="col">قیمت<?php echo $sample ? ' (نمونه)' : ''; ?></th><th scope="col"><span class="sr-only">سفارش</span></th></tr>
						</thead>
						<tbody>
							<?php foreach ( $items as $r ) : ?>
								<tr>
									<th scope="row" data-label="خدمت"><?php echo esc_html( $r['title'] ); ?><?php echo ! empty( $r['note'] ) ? '<small>' . esc_html( $r['note'] ) . '</small>' : ''; ?></th>
									<td data-label="مدت / واحد"><?php echo esc_html( $r['duration'] ); ?></td>
									<td data-label="قیمت"><strong><?php echo esc_html( $r['price'] ); ?></strong><?php echo $sample ? ' <span class="badge badge-sample">نمونه</span>' : ''; ?></td>
									<td class="td-cta"><a class="btn btn-primary btn-sm js-order-pick" href="#order" data-service="<?php echo esc_attr( isset( $by_slug[ $r['service'] ] ) ? $by_slug[ $r['service'] ] : '' ); ?>">سفارش</a></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section section-alt">
		<div class="container narrow prose">
			<?php the_content(); ?>
		</div>
	</section>

	<section class="section">
		<div class="container narrow">
			<?php get_template_part( 'template-parts/order-form', null, array( 'heading' => 'سفارش یا استعلام قیمت دقیق' ) ); ?>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
