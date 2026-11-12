<?php
/**
 * Testimonials. Real ones come from settings; otherwise a clearly-marked SAMPLE block.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$t = khy_testimonials();
if ( empty( $t['items'] ) ) {
	return;
}
?>
<section class="section" aria-labelledby="tst-h">
	<div class="container">
		<h2 id="tst-h" class="section-title">نظر مشتریان</h2>
		<?php if ( $t['sample'] ) : ?>
			<p class="sample-flag"><strong>نمونه:</strong> متن‌های زیر فقط برای نمایش طراحی هستند و نظر واقعی مشتری نیستند. پیش از انتشار با نظرهای واقعی جایگزین یا این بخش را خاموش کنید.</p>
		<?php endif; ?>
		<div class="grid grid-2">
			<?php foreach ( $t['items'] as $item ) : ?>
				<figure class="quote<?php echo $t['sample'] ? ' is-sample' : ''; ?>">
					<?php if ( $t['sample'] ) : ?><span class="badge badge-sample">نمونه</span><?php endif; ?>
					<blockquote><p><?php echo esc_html( $item['text'] ); ?></p></blockquote>
					<figcaption><?php echo esc_html( $item['name'] ); ?><?php echo ! empty( $item['area'] ) ? ' — ' . esc_html( $item['area'] ) : ''; ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
