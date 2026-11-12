<?php
/**
 * Template helpers.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a plugin setting; fall back to safe placeholders if the plugin is inactive.
 *
 * @param string $key Setting key.
 * @return string
 */
function khy_setting( $key ) {
	if ( function_exists( 'khy_get_option' ) ) {
		return khy_get_option( $key );
	}
	$fallback = array(
		'phone'               => '021-00000000',
		'whatsapp'            => '989000000000',
		'hours_label'         => 'همه روزه، ۸ صبح تا ۱۰ شب',
		'opening_hours'       => 'Mo-Su 08:00-22:00',
		'schema_mode'         => 'auto',
		'prices_sample'       => 1,
		'prices_updated'      => gmdate( 'Y-m-d' ),
		'sample_testimonials' => 1,
	);
	return isset( $fallback[ $key ] ) ? $fallback[ $key ] : '';
}

/**
 * Western digits → Persian digits (display only).
 *
 * @param string|int $s Input.
 * @return string
 */
function khy_fa( $s ) {
	return strtr( (string) $s, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

/**
 * Gregorian date → Jalali string, e.g. «۲۱ آبان ۱۴۰۵».
 *
 * @param string|int $date Y-m-d string or timestamp.
 * @return string
 */
function khy_jalali( $date ) {
	$ts = is_numeric( $date ) ? (int) $date : strtotime( $date . ' 12:00:00 UTC' );
	if ( ! $ts ) {
		return '';
	}
	$gy = (int) gmdate( 'Y', $ts );
	$gm = (int) gmdate( 'n', $ts );
	$gd = (int) gmdate( 'j', $ts );

	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + (int) floor( ( $gy2 + 3 ) / 4 ) - (int) floor( ( $gy2 + 99 ) / 100 ) + (int) floor( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * (int) floor( $days / 12053 ) );
	$days %= 12053;
	$jy   += 4 * (int) floor( $days / 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy  += (int) floor( ( $days - 1 ) / 365 );
		$days = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + (int) floor( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) floor( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	$months = array( '', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	return khy_fa( $jd ) . ' ' . $months[ $jm ] . ' ' . khy_fa( $jy );
}

/**
 * Jalali year for «قیمت ۱۴۰۵» style labels.
 *
 * @return string
 */
function khy_jalali_year() {
	$parts = explode( ' ', khy_jalali( time() ) );
	return end( $parts );
}

function khy_phone() {
	return (string) khy_setting( 'phone' );
}

/**
 * tel: link (international form).
 *
 * @return string
 */
function khy_tel_href() {
	$digits = preg_replace( '/\D/', '', khy_phone() );
	$digits = ltrim( $digits, '0' );
	return 'tel:+98' . $digits;
}

function khy_wa_href( $text = '' ) {
	$url = 'https://wa.me/' . rawurlencode( preg_replace( '/\D/', '', (string) khy_setting( 'whatsapp' ) ) );
	if ( '' !== $text ) {
		$url .= '?text=' . rawurlencode( $text );
	}
	return $url;
}

/**
 * Inline SVG icon via the sprite emitted in footer/body.
 *
 * @param string $name  Icon id.
 * @param string $class Extra class.
 * @return string
 */
function khy_icon( $name, $class = '' ) {
	return '<svg class="i ' . esc_attr( $class ) . '" aria-hidden="true" focusable="false" width="24" height="24"><use href="#i-' . esc_attr( $name ) . '"></use></svg>';
}

/**
 * Is a third-party SEO plugin active?
 *
 * @return bool
 */
function khy_has_seo_plugin() {
	return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

function khy_has_rank_math() {
	return defined( 'RANK_MATH_VERSION' );
}

/**
 * H1 for a singular view (meta override or title).
 *
 * @param int|null $id Post ID.
 * @return string
 */
function khy_h1( $id = null ) {
	$id = $id ? $id : get_the_ID();
	$h1 = get_post_meta( $id, '_khy_h1', true );
	return $h1 ? $h1 : get_the_title( $id );
}

function khy_lead( $id = null ) {
	$id = $id ? $id : get_the_ID();
	return (string) get_post_meta( $id, '_khy_lead', true );
}

/**
 * FAQ rows for a post.
 *
 * @param int|null $id Post ID.
 * @return array
 */
function khy_faq( $id = null ) {
	$id  = $id ? $id : get_the_ID();
	$faq = get_post_meta( $id, '_khy_faq', true );
	return is_array( $faq ) ? $faq : array();
}

/**
 * Breadcrumb trail: array of [label, url] (last item url = '').
 *
 * @return array
 */
function khy_breadcrumb_items() {
	$items = array( array( 'خانه', home_url( '/' ) ) );
	if ( is_front_page() ) {
		return array();
	}
	if ( is_singular( 'neighborhood' ) ) {
		$items[] = array( 'محله‌های تهران', get_post_type_archive_link( 'neighborhood' ) );
		$items[] = array( get_the_title(), '' );
	} elseif ( is_post_type_archive( 'neighborhood' ) ) {
		$items[] = array( 'محله‌های تهران', '' );
	} elseif ( is_singular( 'post' ) ) {
		$blog = (int) get_option( 'page_for_posts' );
		$items[] = array( $blog ? get_the_title( $blog ) : 'بلاگ', $blog ? get_permalink( $blog ) : home_url( '/blog/' ) );
		$items[] = array( get_the_title(), '' );
	} elseif ( is_home() ) {
		$blog    = (int) get_option( 'page_for_posts' );
		$items[] = array( $blog ? get_the_title( $blog ) : 'بلاگ', '' );
	} elseif ( is_singular() ) {
		$items[] = array( get_the_title(), '' );
	} elseif ( is_archive() ) {
		$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	} elseif ( is_search() ) {
		$items[] = array( 'جستجو', '' );
	} elseif ( is_404() ) {
		$items[] = array( 'صفحه پیدا نشد', '' );
	}
	return $items;
}

/**
 * Queried main services (cleaning / plumbing) for cards.
 *
 * @param int $limit Max.
 * @return WP_Post[]
 */
function khy_services( $limit = 20 ) {
	return get_posts(
		array(
			'post_type'      => 'service',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
}

function khy_neighborhoods( $limit = 50 ) {
	return get_posts(
		array(
			'post_type'      => 'neighborhood',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
}

/**
 * Page ID by slug (cached per request).
 *
 * @param string $slug Page slug.
 * @return int
 */
function khy_page_id( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? (int) $p->ID : 0;
}

/**
 * Permalink of a page by slug (with fallback).
 *
 * @param string $slug Page slug.
 * @return string
 */
function khy_page_url( $slug ) {
	$id = khy_page_id( $slug );
	return $id ? get_permalink( $id ) : home_url( '/' . $slug . '/' );
}

/**
 * Sanitised, minimal HTML for FAQ answers etc.
 *
 * @param string $html Text.
 * @return string
 */
function khy_kses_min( $html ) {
	return wp_kses( $html, array( 'a' => array( 'href' => true ), 'strong' => array(), 'em' => array(), 'br' => array() ) );
}

/**
 * H1 / <title> text of the neighborhood hub (/tehran/). Same string as the plugin's archive label.
 *
 * @return string
 */
function khy_neighborhood_archive_title() {
	return 'خدمات منزل در محله‌های تهران';
}
