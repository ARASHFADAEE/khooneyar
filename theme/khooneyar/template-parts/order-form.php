<?php
/**
 * Order form (single step). Posts to admin-post.php (works without JS);
 * main.js upgrades it to a REST submit with an on-page confirmation.
 *
 * Args: service_id (int), area_id (int), heading (string), id (string), compact (bool)
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

global $khy_form_n;
$khy_form_n = isset( $khy_form_n ) ? $khy_form_n + 1 : 1;
$args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'service_id' => 0,
		'area_id'    => 0,
		'heading'    => 'سفارش آنلاین',
		'sub'        => 'فقط نام، موبایل و نوع خدمت را بنویسید؛ همکاران ما تماس می‌گیرند.',
		'compact'    => false,
	)
);
$fid     = 'order' . ( 1 === $khy_form_n ? '' : '-' . $khy_form_n );
$choices = function_exists( 'khy_order_choices' ) ? khy_order_choices() : array( 'services' => array(), 'areas' => array() );
$result  = function_exists( 'khy_result_from_query' ) ? khy_result_from_query() : null;
$old     = ( $result && ! $result['ok'] && ! empty( $result['old'] ) ) ? $result['old'] : array();
$errs    = ( $result && ! $result['ok'] && ! empty( $result['errors'] ) ) ? $result['errors'] : array();
$val     = function ( $k, $def = '' ) use ( $old ) {
	return isset( $old[ $k ] ) ? $old[ $k ] : $def;
};
$sel_service = (int) $val( 'khy_service', $args['service_id'] );
$sel_area    = (int) $val( 'khy_area', $args['area_id'] );
$source      = ( is_singular() ? get_permalink() : home_url( '/' ) );
?>
<section class="order-box<?php echo $args['compact'] ? ' is-compact' : ''; ?>" id="<?php echo esc_attr( $fid ); ?>" aria-labelledby="<?php echo esc_attr( $fid ); ?>-h">
	<h2 id="<?php echo esc_attr( $fid ); ?>-h" class="order-title"><?php echo esc_html( $args['heading'] ); ?></h2>
	<p class="order-sub"><?php echo esc_html( $args['sub'] ); ?></p>

	<?php if ( $result && $result['ok'] ) : ?>
		<div class="order-success" id="order-result" role="status" tabindex="-1">
			<h3><?php echo khy_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> درخواست شما ثبت شد</h3>
			<p><?php echo esc_html( $result['name'] ); ?> عزیز، کد پیگیری شما <strong dir="ltr"><?php echo esc_html( khy_fa( $result['code'] ) ); ?></strong> است. همکاران ما به‌زودی تماس می‌گیرند.</p>
			<a class="btn btn-wa" href="<?php echo esc_url( $result['wa_url'] ); ?>" target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> ارسال پیام در واتساپ برای تأیید سریع‌تر</a>
		</div>
	<?php else : ?>
		<?php if ( $result && ! $result['ok'] && ! empty( $result['message'] ) ) : ?>
			<div class="form-alert" id="order-result" role="alert"><?php echo esc_html( $result['message'] ); ?></div>
		<?php endif; ?>

		<form class="order-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-khy-form novalidate>
			<input type="hidden" name="action" value="khy_order">
			<input type="hidden" name="khy_nonce" value="<?php echo esc_attr( wp_create_nonce( 'khy_order' ) ); ?>">
			<input type="hidden" name="khy_source" value="<?php echo esc_url( $source ); ?>">
			<div class="hp" aria-hidden="true"><label>وب‌سایت<input type="text" name="khy_website" tabindex="-1" autocomplete="off" value=""></label></div>

			<div class="field-row">
				<div class="field<?php echo isset( $errs['khy_name'] ) ? ' has-error' : ''; ?>">
					<label for="<?php echo esc_attr( $fid ); ?>-name">نام و نام خانوادگی <abbr title="الزامی">*</abbr></label>
					<input id="<?php echo esc_attr( $fid ); ?>-name" name="khy_name" type="text" autocomplete="name" required maxlength="60" value="<?php echo esc_attr( $val( 'khy_name' ) ); ?>" aria-describedby="<?php echo esc_attr( $fid ); ?>-name-e">
					<p class="field-error" id="<?php echo esc_attr( $fid ); ?>-name-e"><?php echo isset( $errs['khy_name'] ) ? esc_html( $errs['khy_name'] ) : ''; ?></p>
				</div>
				<div class="field<?php echo isset( $errs['khy_phone'] ) ? ' has-error' : ''; ?>">
					<label for="<?php echo esc_attr( $fid ); ?>-phone">شمارهٔ موبایل <abbr title="الزامی">*</abbr></label>
					<input id="<?php echo esc_attr( $fid ); ?>-phone" name="khy_phone" type="tel" inputmode="numeric" autocomplete="tel" dir="ltr" required placeholder="09121234567" value="<?php echo esc_attr( $val( 'khy_phone' ) ); ?>" aria-describedby="<?php echo esc_attr( $fid ); ?>-phone-e">
					<p class="field-error" id="<?php echo esc_attr( $fid ); ?>-phone-e"><?php echo isset( $errs['khy_phone'] ) ? esc_html( $errs['khy_phone'] ) : ''; ?></p>
				</div>
			</div>

			<div class="field-row">
				<div class="field<?php echo isset( $errs['khy_service'] ) ? ' has-error' : ''; ?>">
					<label for="<?php echo esc_attr( $fid ); ?>-service">نوع خدمت <abbr title="الزامی">*</abbr></label>
					<select id="<?php echo esc_attr( $fid ); ?>-service" name="khy_service" required aria-describedby="<?php echo esc_attr( $fid ); ?>-service-e">
						<option value="">انتخاب کنید…</option>
						<?php foreach ( $choices['services'] as $id => $title ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $sel_service, $id ); ?>><?php echo esc_html( $title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="field-error" id="<?php echo esc_attr( $fid ); ?>-service-e"><?php echo isset( $errs['khy_service'] ) ? esc_html( $errs['khy_service'] ) : ''; ?></p>
				</div>
				<div class="field">
					<label for="<?php echo esc_attr( $fid ); ?>-area">محله / منطقه</label>
					<select id="<?php echo esc_attr( $fid ); ?>-area" name="khy_area">
						<option value="0">سایر مناطق تهران</option>
						<?php foreach ( $choices['areas'] as $id => $title ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $sel_area, $id ); ?>><?php echo esc_html( $title ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="field-row">
				<div class="field">
					<label for="<?php echo esc_attr( $fid ); ?>-time">زمان دلخواه</label>
					<select id="<?php echo esc_attr( $fid ); ?>-time" name="khy_time">
						<?php foreach ( ( function_exists( 'khy_time_choices' ) ? khy_time_choices() : array() ) as $k => $l ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $val( 'khy_time', 'asap' ), $k ); ?>><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="field">
					<label for="<?php echo esc_attr( $fid ); ?>-note">توضیحات (اختیاری)</label>
					<textarea id="<?php echo esc_attr( $fid ); ?>-note" name="khy_note" rows="2" maxlength="500" placeholder="مثلاً متراژ، طبقه یا نوع مشکل"><?php echo esc_textarea( $val( 'khy_note' ) ); ?></textarea>
				</div>
			</div>

			<div class="form-status" role="status" aria-live="polite" data-khy-status></div>
			<button class="btn btn-primary btn-block" type="submit"><span>ثبت سفارش و دریافت تماس</span></button>
			<p class="form-note">پرداخت اینترنتی ندارد. مبلغ نهایی قبل از اعزام تأیید می‌شود.</p>
		</form>

		<div class="order-success" data-khy-success role="status" tabindex="-1" hidden>
			<h3><?php echo khy_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> درخواست شما ثبت شد</h3>
			<p><span data-khy-name></span> عزیز، کد پیگیری شما <strong dir="ltr" data-khy-code></strong> است. همکاران ما به‌زودی تماس می‌گیرند.</p>
			<a class="btn btn-wa" href="#" data-khy-wa target="_blank" rel="noopener"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> ارسال پیام در واتساپ برای تأیید سریع‌تر</a>
			<a class="btn btn-ghost" href="<?php echo esc_url( khy_tel_href() ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> تماس فوری</a>
		</div>
	<?php endif; ?>
</section>
