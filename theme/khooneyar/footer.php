<?php
/**
 * Site footer + sticky mobile bar.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<div class="container footer-grid">
		<div>
			<?php get_template_part( 'template-parts/logo' ); ?>
			<p class="footer-note">اعزام نیروی تأییدشده برای نظافت منزل، نظافت دفتر و لوله‌کشی در تهران، با تمرکز بر انقلاب و فلسطین.</p>
		</div>
		<div>
			<h2 class="footer-h">خدمت‌ها</h2>
			<ul class="footer-list">
				<?php foreach ( khy_services( 8 ) as $s ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h2 class="footer-h">محله‌ها</h2>
			<ul class="footer-list">
				<?php foreach ( khy_neighborhoods( 8 ) as $n ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $n ) ); ?>"><?php echo esc_html( $n->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h2 class="footer-h">تماس</h2>
			<ul class="footer-list">
				<li><a href="<?php echo esc_url( khy_tel_href() ); ?>" dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></a></li>
				<li><a href="<?php echo esc_url( khy_wa_href() ); ?>" target="_blank" rel="noopener">واتساپ</a></li>
				<li><?php echo esc_html( khy_setting( 'hours_label' ) ); ?></li>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</ul>
		</div>
	</div>
	<div class="container footer-bottom">
		<small>© <?php echo esc_html( khy_fa( khy_jalali_year() ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. تمامی حقوق محفوظ است.</small>
	</div>
</footer>

<div class="sticky-bar" role="navigation" aria-label="تماس سریع">
	<a class="sticky-call" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>تماس</span></a>
	<a class="sticky-wa" href="<?php echo esc_url( khy_wa_href() ); ?>" target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>واتساپ</span></a>
</div>

<?php wp_footer(); ?>
</body>
</html>
