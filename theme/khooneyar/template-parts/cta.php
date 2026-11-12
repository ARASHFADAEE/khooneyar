<?php
/**
 * Closing call-to-action band. Args: title, text.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$title = isset( $args['title'] ) ? $args['title'] : 'همین حالا سفارش بدهید';
$text  = isset( $args['text'] ) ? $args['text'] : 'تماس بگیرید یا در واتساپ پیام بدهید؛ معمولاً در همان ساعت‌های کاری پاسخ می‌دهیم.';
?>
<section class="cta-band">
	<div class="container cta-inner">
		<div>
			<h2><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $text ); ?></p>
		</div>
		<div class="cta-actions">
			<a class="btn btn-accent" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <span dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></span></a>
			<a class="btn btn-wa" href="<?php echo esc_url( khy_wa_href() ); ?>" target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> واتساپ</a>
		</div>
	</div>
</section>
