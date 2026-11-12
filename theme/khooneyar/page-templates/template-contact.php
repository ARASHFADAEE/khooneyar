<?php
/**
 * Template Name: تماس با ما
 * Template Post Type: page
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	$address = function_exists( 'khy_get_option' ) ? khy_get_option( 'address_text' ) : '';
	?>
	<section class="section">
		<div class="container contact-grid">
			<div class="contact-info">
				<ul class="contact-list">
					<li><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <div><strong>تلفن</strong><a href="<?php echo esc_url( khy_tel_href() ); ?>" dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></a></div></li>
					<li><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <div><strong>واتساپ</strong><a href="<?php echo esc_url( khy_wa_href() ); ?>" target="_blank" rel="noopener">ارسال پیام</a></div></li>
					<li><?php echo khy_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <div><strong>ساعت پاسخگویی</strong><span><?php echo esc_html( khy_setting( 'hours_label' ) ); ?></span></div></li>
					<li><?php echo khy_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <div><strong>محدودهٔ خدمت</strong><span><?php echo $address ? esc_html( $address ) : 'اعزام به محل شما در تهران؛ تمرکز بر انقلاب، فلسطین و اطراف'; ?></span></div></li>
				</ul>
				<div class="prose"><?php the_content(); ?></div>
			</div>
			<div>
				<?php get_template_part( 'template-parts/order-form', null, array( 'heading' => 'فرم سفارش و تماس' ) ); ?>
			</div>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/faq' );
endwhile;
get_footer();
