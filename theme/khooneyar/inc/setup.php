<?php
/**
 * Theme setup, assets, menus.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'khy_theme_setup' );
function khy_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 48, 'width' => 160, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'khy-card', 640, 400, true );
	register_nav_menus(
		array(
			'primary' => 'منوی اصلی',
			'footer'  => 'منوی پایین سایت',
		)
	);
}

/** Force Persian / RTL even if the site language is not yet fa_IR. */
add_filter( 'language_attributes', 'khy_language_attributes' );
function khy_language_attributes( $out ) {
	if ( is_admin() ) {
		return $out;
	}
	$out = preg_replace( '/\s*(dir|lang)="[^"]*"/', '', $out );
	return trim( $out ) . ' lang="fa-IR" dir="rtl"';
}

add_action( 'wp_enqueue_scripts', 'khy_enqueue' );
function khy_enqueue() {
	$ver = KHY_THEME_VERSION;
	wp_enqueue_style( 'khy-main', get_template_directory_uri() . '/assets/css/main.css', array(), $ver );
	wp_enqueue_script( 'khy-main', get_template_directory_uri() . '/assets/js/main.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script(
		'khy-main',
		'window.KHY=' . wp_json_encode(
			array(
				'rest' => esc_url_raw( rest_url( 'khooneyar/v1/' ) ),
			)
		) . ';',
		'before'
	);
}

/** Preload the self-hosted font (single request, no layout shift). */
add_action( 'wp_head', 'khy_preload_font', 1 );
function khy_preload_font() {
	echo '<link rel="preload" href="' . esc_url( get_template_directory_uri() . '/assets/fonts/vazirmatn-subset.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#0F766E">' . "\n";
}

/** Default nav (used until a menu is assigned). */
function khy_fallback_menu() {
	$links = array(
		array( 'نظافت منزل', home_url( '/nazafat-manzel-tehran/' ) ),
		array( 'نظافت دفتر', home_url( '/nazafat-daftar-sherkat-tehran/' ) ),
		array( 'لوله‌کشی فوری', home_url( '/loolekesh-fori-tehran/' ) ),
		array( 'قیمت‌ها', khy_page_url( 'gheymat-nazafat-manzel' ) ),
		array( 'محله‌ها', get_post_type_archive_link( 'neighborhood' ) ),
		array( 'تماس', khy_page_url( 'tamas' ) ),
	);
	echo '<ul class="menu">';
	foreach ( $links as $l ) {
		echo '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
	}
	echo '</ul>';
}

/** Admin notice when the companion plugin is missing. */
add_action( 'admin_notices', 'khy_plugin_notice' );
function khy_plugin_notice() {
	if ( function_exists( 'khy_get_option' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-warning"><p>قالب خونه‌یار برای نوع نوشته‌ها (خدمت و محله)، فرم سفارش و تنظیمات به افزونهٔ <strong>Khooneyar Core</strong> نیاز دارد. لطفاً آن را فعال کنید.</p></div>';
}

/** Sample / placeholder testimonials (clearly marked in the template). */
function khy_testimonials() {
	$real = function_exists( 'khy_get_option' ) ? khy_get_option( 'testimonials', array() ) : array();
	if ( is_array( $real ) && $real ) {
		return array( 'sample' => false, 'items' => $real );
	}
	if ( ! khy_setting( 'sample_testimonials' ) ) {
		return array( 'sample' => false, 'items' => array() );
	}
	return array(
		'sample' => true,
		'items'  => array(
			array( 'name' => 'نام مشتری', 'area' => 'محله', 'text' => 'اینجا متن نظر واقعی مشتری قرار می‌گیرد. این یک متن نمونه برای طراحی است و باید با نظر واقعی جایگزین شود.' ),
			array( 'name' => 'نام مشتری', 'area' => 'محله', 'text' => 'نمونهٔ دوم: پس از ثبت نظر واقعی مشتریان از «تنظیمات ← خونه‌یار»، این بخش خودکار با نظرهای واقعی عوض می‌شود.' ),
		),
	);
}
