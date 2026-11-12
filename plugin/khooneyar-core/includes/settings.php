<?php
/**
 * Brand / contact settings page (Settings → خونه‌یار).
 *
 * @package Khooneyar_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default settings. Phone / WhatsApp are PLACEHOLDERS.
 *
 * @return array
 */
function khy_default_settings() {
	return array(
		'phone'               => '021-00000000',
		'whatsapp'            => '989000000000',
		'order_email'         => '',
		'hours_label'         => 'همه روزه، ۸ صبح تا ۱۰ شب',
		'opening_hours'       => 'Mo-Su 08:00-22:00',
		'address_text'        => '',
		'schema_mode'         => 'auto',
		'prices_updated'      => gmdate( 'Y-m-d' ),
		'prices_sample'       => 1,
		'sample_testimonials' => 1,
		'testimonials'        => array(),
		'rate_limit_max'      => 3,
		'rate_limit_window'   => 600,
	);
}

/**
 * Get one setting (with default fallback).
 *
 * @param string $key     Setting key.
 * @param mixed  $default Optional default.
 * @return mixed
 */
function khy_get_option( $key, $default = '' ) {
	$saved = get_option( 'khy_settings', array() );
	$all   = wp_parse_args( is_array( $saved ) ? $saved : array(), khy_default_settings() );
	if ( 'order_email' === $key && empty( $all['order_email'] ) ) {
		return get_option( 'admin_email' );
	}
	if ( isset( $all[ $key ] ) && '' !== $all[ $key ] ) {
		return $all[ $key ];
	}
	return $default;
}

add_action( 'admin_menu', 'khy_settings_menu' );
function khy_settings_menu() {
	add_options_page( 'تنظیمات خونه‌یار', 'خونه‌یار', 'manage_options', 'khooneyar', 'khy_settings_page' );
}

add_action( 'admin_init', 'khy_settings_register' );
function khy_settings_register() {
	register_setting(
		'khy_settings_group',
		'khy_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'khy_sanitize_settings',
			'default'           => khy_default_settings(),
		)
	);
}

/**
 * Normalise phone-ish values: convert Persian/Arabic digits to ASCII.
 *
 * @param string $value Raw string.
 * @return string
 */
function khy_to_ascii_digits( $value ) {
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	return str_replace( $fa, $en, (string) $value );
}

function khy_sanitize_settings( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$def = khy_default_settings();
	$out = array();

	$out['phone']         = preg_replace( '/[^0-9+\-\s]/', '', khy_to_ascii_digits( $in['phone'] ?? $def['phone'] ) );
	$out['whatsapp']      = preg_replace( '/\D/', '', khy_to_ascii_digits( $in['whatsapp'] ?? $def['whatsapp'] ) );
	$out['order_email']   = sanitize_email( $in['order_email'] ?? '' );
	$out['hours_label']   = sanitize_text_field( $in['hours_label'] ?? $def['hours_label'] );
	$out['opening_hours'] = sanitize_text_field( $in['opening_hours'] ?? $def['opening_hours'] );
	$out['address_text']  = sanitize_text_field( $in['address_text'] ?? '' );

	$mode                = $in['schema_mode'] ?? 'auto';
	$out['schema_mode']  = in_array( $mode, array( 'auto', 'all', 'off' ), true ) ? $mode : 'auto';

	$date = sanitize_text_field( $in['prices_updated'] ?? '' );
	$out['prices_updated'] = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : gmdate( 'Y-m-d' );

	$out['prices_sample']       = empty( $in['prices_sample'] ) ? 0 : 1;
	$out['sample_testimonials'] = empty( $in['sample_testimonials'] ) ? 0 : 1;
	$out['rate_limit_max']      = max( 1, min( 20, absint( $in['rate_limit_max'] ?? 3 ) ) );
	$out['rate_limit_window']   = max( 60, min( 86400, absint( $in['rate_limit_window'] ?? 600 ) ) );

	$out['testimonials'] = khy_sanitize_repeater( $in['testimonials'] ?? array(), array( 'name', 'area', 'text' ) );
	return $out;
}

function khy_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = wp_parse_args( get_option( 'khy_settings', array() ), khy_default_settings() );
	?>
	<div class="wrap khy-wrap">
		<h1>تنظیمات خونه‌یار</h1>
		<p>شماره تماس، واتساپ و اطلاعات برند اینجا تنظیم می‌شوند. نام و شعار سایت از «تنظیمات ← عمومی» و لوگو از «نمایش ← سفارشی‌سازی ← هویت سایت» عوض می‌شود.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'khy_settings_group' ); ?>
			<h2 class="title">تماس</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="khy_phone">شماره تلفن</label></th>
					<td><input id="khy_phone" class="regular-text" dir="ltr" type="text" name="khy_settings[phone]" value="<?php echo esc_attr( $o['phone'] ); ?>"><p class="description">مثال: 021-00000000 (نمونه، قبل از انتشار عوض شود)</p></td></tr>
				<tr><th><label for="khy_wa">شماره واتساپ</label></th>
					<td><input id="khy_wa" class="regular-text" dir="ltr" type="text" name="khy_settings[whatsapp]" value="<?php echo esc_attr( $o['whatsapp'] ); ?>"><p class="description">با کد کشور و بدون + یا صفر؛ مثال: 989121234567</p></td></tr>
				<tr><th><label for="khy_email">ایمیل دریافت سفارش</label></th>
					<td><input id="khy_email" class="regular-text" dir="ltr" type="email" name="khy_settings[order_email]" value="<?php echo esc_attr( $o['order_email'] ); ?>"><p class="description">خالی = ایمیل مدیر سایت.</p></td></tr>
				<tr><th><label for="khy_hours">ساعت پاسخگویی (نمایشی)</label></th>
					<td><input id="khy_hours" class="regular-text" type="text" name="khy_settings[hours_label]" value="<?php echo esc_attr( $o['hours_label'] ); ?>"></td></tr>
				<tr><th><label for="khy_oh">ساعت کاری برای گوگل</label></th>
					<td><input id="khy_oh" class="regular-text" dir="ltr" type="text" name="khy_settings[opening_hours]" value="<?php echo esc_attr( $o['opening_hours'] ); ?>"><p class="description">فرمت schema.org؛ مثال: Mo-Su 08:00-22:00</p></td></tr>
				<tr><th><label for="khy_addr">نشانی (اختیاری)</label></th>
					<td><input id="khy_addr" class="large-text" type="text" name="khy_settings[address_text]" value="<?php echo esc_attr( $o['address_text'] ); ?>"><p class="description">فقط اگر نشانی واقعی و ثابت دارید. در اسکیما استفاده نمی‌شود (فقط areaServed).</p></td></tr>
			</table>

			<h2 class="title">قیمت‌ها و نظرات</h2>
			<table class="form-table" role="presentation">
				<tr><th>قیمت‌ها نمونه هستند؟</th>
					<td><label><input type="checkbox" name="khy_settings[prices_sample]" value="1" <?php checked( $o['prices_sample'], 1 ); ?>> بله، برچسب «قیمت نمونه» نمایش داده شود و قیمت در اسکیما نیاید</label><p class="description">بعد از جایگزینی قیمت‌های واقعی این گزینه را بردارید.</p></td></tr>
				<tr><th><label for="khy_pu">تاریخ به‌روزرسانی قیمت</label></th>
					<td><input id="khy_pu" type="date" dir="ltr" name="khy_settings[prices_updated]" value="<?php echo esc_attr( $o['prices_updated'] ); ?>"><p class="description">میلادی ذخیره می‌شود و در سایت به شمسی نشان داده می‌شود.</p></td></tr>
				<tr><th>نظر نمونه</th>
					<td><label><input type="checkbox" name="khy_settings[sample_testimonials]" value="1" <?php checked( $o['sample_testimonials'], 1 ); ?>> اگر نظر واقعی ثبت نشده، بخش «نمونهٔ متن نظر» (با برچسب نمونه) نمایش داده شود</label></td></tr>
				<tr><th>نظرات واقعی مشتریان</th>
					<td><?php khy_repeater_field( 'khy_settings[testimonials]', $o['testimonials'], array( 'name' => 'نام', 'area' => 'محله', 'text' => 'متن نظر' ), array( 'text' => 'textarea' ) ); ?><p class="description">فقط نظر واقعی و با اجازهٔ مشتری وارد کنید.</p></td></tr>
			</table>

			<h2 class="title">سئو و اسکیما</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="khy_sm">حالت اسکیمای قالب</label></th>
					<td><select id="khy_sm" name="khy_settings[schema_mode]">
						<option value="auto" <?php selected( $o['schema_mode'], 'auto' ); ?>>خودکار (پیشنهادی): اگر Rank Math فعال بود، نودها داخل گراف خودش اضافه می‌شود</option>
						<option value="all" <?php selected( $o['schema_mode'], 'all' ); ?>>همیشه خروجی قالب (فقط اگر افزونهٔ سئو اسکیما نمی‌دهد)</option>
						<option value="off" <?php selected( $o['schema_mode'], 'off' ); ?>>خاموش</option>
					</select></td></tr>
			</table>

			<h2 class="title">محدودیت ارسال فرم</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="khy_rl">حداکثر سفارش از یک IP</label></th>
					<td><input id="khy_rl" type="number" min="1" max="20" name="khy_settings[rate_limit_max]" value="<?php echo esc_attr( $o['rate_limit_max'] ); ?>"> در هر
					<input type="number" min="60" max="86400" step="60" name="khy_settings[rate_limit_window]" value="<?php echo esc_attr( $o['rate_limit_window'] ); ?>"> ثانیه</td></tr>
			</table>
			<?php submit_button( 'ذخیرهٔ تنظیمات' ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', 'khy_admin_assets' );
function khy_admin_assets( $hook ) {
	$screen = get_current_screen();
	$ok     = $screen && ( 'settings_page_khooneyar' === $hook || in_array( $screen->post_type, array( 'page', 'service', 'neighborhood', 'order_request' ), true ) );
	if ( ! $ok ) {
		return;
	}
	wp_enqueue_style( 'khy-admin', KHY_CORE_URL . 'assets/admin.css', array(), KHY_CORE_VERSION );
	wp_enqueue_script( 'khy-admin', KHY_CORE_URL . 'assets/admin.js', array(), KHY_CORE_VERSION, true );
}

/* ------------------------------------------------------------------
 * Generic repeater (FAQ, price rows, steps, testimonials)
 * ---------------------------------------------------------------- */

/**
 * Sanitise a repeater value.
 *
 * @param mixed $rows   Raw rows.
 * @param array $fields Field keys.
 * @return array
 */
function khy_sanitize_repeater( $rows, $fields ) {
	$out = array();
	if ( ! is_array( $rows ) ) {
		return $out;
	}
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$clean = array();
		$any   = false;
		foreach ( $fields as $f ) {
			$v = isset( $row[ $f ] ) ? ( is_string( $row[ $f ] ) ? $row[ $f ] : '' ) : '';
			// Long text fields keep basic inline markup.
			$clean[ $f ] = wp_kses( $v, array( 'a' => array( 'href' => true ), 'strong' => array(), 'em' => array(), 'br' => array() ) );
			if ( '' !== trim( $clean[ $f ] ) ) {
				$any = true;
			}
		}
		if ( $any ) {
			$out[] = $clean;
		}
	}
	return $out;
}

/**
 * Render a repeater editor.
 *
 * @param string $name   Input base name.
 * @param array  $rows   Saved rows.
 * @param array  $fields key => label.
 * @param array  $types  key => 'textarea' (optional).
 */
function khy_repeater_field( $name, $rows, $fields, $types = array() ) {
	$rows = is_array( $rows ) ? array_values( $rows ) : array();
	echo '<div class="khy-repeater" data-name="' . esc_attr( $name ) . '">';
	echo '<div class="khy-rep-rows">';
	foreach ( $rows as $i => $row ) {
		khy_repeater_row( $name, $i, $row, $fields, $types );
	}
	echo '</div>';
	echo '<template class="khy-rep-tpl">';
	khy_repeater_row( $name, '__i__', array(), $fields, $types );
	echo '</template>';
	echo '<p><button type="button" class="button khy-rep-add">+ افزودن ردیف</button></p>';
	echo '</div>';
}

function khy_repeater_row( $name, $i, $row, $fields, $types ) {
	echo '<div class="khy-rep-row">';
	foreach ( $fields as $key => $label ) {
		$val   = isset( $row[ $key ] ) ? $row[ $key ] : '';
		$field = $name . '[' . $i . '][' . $key . ']';
		echo '<label><span>' . esc_html( $label ) . '</span>';
		if ( isset( $types[ $key ] ) && 'textarea' === $types[ $key ] ) {
			echo '<textarea rows="3" name="' . esc_attr( $field ) . '">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="text" name="' . esc_attr( $field ) . '" value="' . esc_attr( $val ) . '">';
		}
		echo '</label>';
	}
	echo '<button type="button" class="button-link-delete khy-rep-del">حذف</button></div>';
}
