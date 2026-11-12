<?php
/**
 * Front page: hub for the three services.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$services = khy_services( 20 );
$main     = array();
foreach ( $services as $s ) {
	if ( in_array( $s->post_name, array( 'nazafat-manzel-tehran', 'nazafat-daftar-sherkat-tehran', 'loolekesh-fori-tehran' ), true ) ) {
		$main[] = $s;
	}
}
$more = array_filter(
	$services,
	function ( $s ) use ( $main ) {
		return ! in_array( $s, $main, true );
	}
);
?>
<section class="hero">
	<div class="container hero-grid">
		<div class="hero-copy">
			<h1><?php echo esc_html( khy_h1() ); ?></h1>
			<p class="lead"><?php echo esc_html( khy_lead() ); ?></p>
			<ul class="chips chips-light">
				<li><?php echo khy_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> نیروی احراز هویت‌شده</li>
				<li><?php echo khy_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> قیمت شفاف قبل از اعزام</li>
				<li><?php echo khy_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> اعزام سریع در مرکز تهران</li>
			</ul>
			<div class="hero-actions">
				<a class="btn btn-accent" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> تماس: <span dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></span></a>
				<a class="btn btn-wa" href="<?php echo esc_url( khy_wa_href() ); ?>" target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> واتساپ</a>
			</div>
		</div>
		<div class="hero-form">
			<?php get_template_part( 'template-parts/order-form', null, array( 'heading' => 'سفارش سریع', 'compact' => true ) ); ?>
		</div>
	</div>
</section>

<section class="section" aria-labelledby="svc-h">
	<div class="container">
		<h2 id="svc-h" class="section-title">خدمت‌های ما</h2>
		<div class="grid grid-3">
			<?php foreach ( $main as $s ) : ?>
				<?php get_template_part( 'template-parts/service-card', null, array( 'post' => $s ) ); ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $more ) : ?>
			<h3 class="sub-title">خدمت‌های تخصصی و فوری</h3>
			<ul class="pill-list">
				<?php foreach ( $more as $s ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $s ) ); ?>"><?php echo esc_html( $s->post_title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<section class="section section-alt" aria-labelledby="why-h">
	<div class="container">
		<h2 id="why-h" class="section-title">چرا خونه‌یار؟</h2>
		<div class="grid grid-3">
			<div class="feature"><span class="card-icon"><?php echo khy_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>احراز هویت نیروها</h3><p>مدارک هویتی نیروها بررسی می‌شود تا با خیال راحت به خانه یا دفتر شما بیایند.</p></div>
			<div class="feature"><span class="card-icon"><?php echo khy_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>قیمت شفاف</h3><p>بازهٔ قیمت هر خدمت را در <a href="<?php echo esc_url( khy_page_url( 'gheymat-nazafat-manzel' ) ); ?>">صفحهٔ قیمت‌ها</a> می‌بینید و مبلغ نهایی قبل از اعزام تأیید می‌شود.</p></div>
			<div class="feature"><span class="card-icon"><?php echo khy_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3>سرعت در مرکز تهران</h3><p>تمرکز ما روی انقلاب، فلسطین و محله‌های اطراف است؛ پس زمان رسیدن کوتاه‌تر می‌شود.</p></div>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/how-to-order' ); ?>

<section class="section" aria-labelledby="area-h">
	<div class="container">
		<h2 id="area-h" class="section-title">محدودهٔ پوشش</h2>
		<p class="section-lead">برای هر محله زمان رسیدن و توضیحات اختصاصی نوشته‌ایم:</p>
		<ul class="pill-list">
			<?php foreach ( khy_neighborhoods() as $n ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $n ) ); ?>"><?php echo esc_html( $n->post_title ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php if ( trim( get_the_content() ) ) : ?>
<section class="section section-alt">
	<div class="container narrow prose">
		<?php the_content(); ?>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/testimonials' ); ?>
<?php get_template_part( 'template-parts/faq' ); ?>
<?php get_template_part( 'template-parts/cta' ); ?>
<?php
get_footer();
