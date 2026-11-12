<?php
/**
 * Khooneyar seed script (idempotent).
 *
 * Usage (from the WordPress root):
 *   wp eval-file /path/to/seed/seed.php
 *   wp eval-file /path/to/seed/seed.php -- --reset-settings     (also resets phone/WhatsApp to placeholders)
 *
 * Creates: pages (home, price, female-cleaner trust, about, contact, FAQ, blog), 8 services,
 * 7 neighborhoods with unique copy, service↔neighborhood relations, 3 sample blog posts, menus,
 * and basic site options. Re-running updates content in place (matched by slug).
 *
 * NOTE: all prices are EXAMPLE ranges for the site owner to replace. No reviews are invented.
 *
 * @package Khooneyar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with: wp eval-file seed/seed.php\n" );
}
if ( ! function_exists( 'khy_register_post_types' ) ) {
	WP_CLI::error( 'افزونهٔ khooneyar-core فعال نیست. ابتدا آن را فعال کنید: wp plugin activate khooneyar-core' );
}

$dir = __DIR__ . '/data/';
$say = function ( $m ) {
	WP_CLI::log( $m );
};

// ---------------------------------------------------------------- helpers
$upsert = function ( $type, $slug, $args ) {
	$existing = get_posts(
		array(
			'name'           => $slug,
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	$defaults = array(
		'post_type'   => $type,
		'post_name'   => $slug,
		'post_status' => 'publish',
		'post_author' => 1,
	);
	$args = wp_parse_args( $args, $defaults );
	if ( $existing ) {
		$args['ID'] = $existing[0];
		$id         = wp_update_post( wp_slash( $args ), true );
	} else {
		$id = wp_insert_post( wp_slash( $args ), true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "{$type}/{$slug}: " . $id->get_error_message() );
	}
	return (int) $id;
};

$set_meta = function ( $id, $d ) {
	$map = array(
		'h1'            => '_khy_h1',
		'lead'          => '_khy_lead',
		'seo_title'     => '_khy_seo_title',
		'seo_desc'      => '_khy_seo_desc',
		'arrival'       => '_khy_arrival',
		'price_summary' => '_khy_price_summary',
		'group'         => '_khy_group',
	);
	foreach ( $map as $k => $meta ) {
		if ( isset( $d[ $k ] ) && '' !== $d[ $k ] ) {
			update_post_meta( $id, $meta, wp_slash( $d[ $k ] ) );
		}
	}
	foreach ( array( 'price_from' => '_khy_price_from', 'price_to' => '_khy_price_to' ) as $k => $meta ) {
		if ( ! empty( $d[ $k ] ) ) {
			update_post_meta( $id, $meta, (int) $d[ $k ] );
		}
	}
	foreach ( array( 'faq' => '_khy_faq', 'price_rows' => '_khy_price_rows', 'steps' => '_khy_steps' ) as $k => $meta ) {
		if ( ! empty( $d[ $k ] ) ) {
			update_post_meta( $id, $meta, wp_slash( $d[ $k ] ) );
		} else {
			delete_post_meta( $id, $meta );
		}
	}
	delete_post_meta( $id, '_khy_alias' );
	foreach ( (array) ( $d['aliases'] ?? array() ) as $alias ) {
		add_post_meta( $id, '_khy_alias', wp_slash( str_replace( ' ', '-', $alias ) ) );
	}
	// Mirror into Rank Math so content is right if it is activated later.
	if ( ! empty( $d['seo_title'] ) ) {
		update_post_meta( $id, 'rank_math_title', wp_slash( $d['seo_title'] ) );
	}
	if ( ! empty( $d['seo_desc'] ) ) {
		update_post_meta( $id, 'rank_math_description', wp_slash( $d['seo_desc'] ) );
	}
	if ( ! empty( $d['h1'] ) ) {
		update_post_meta( $id, 'rank_math_focus_keyword', wp_slash( $d['h1'] ) );
	}
};

// ---------------------------------------------------------------- site options
update_option( 'blogname', 'خونه‌یار' );
update_option( 'blogdescription', 'خدمات منزل مطمئن در تهران' );
update_option( 'timezone_string', 'Asia/Tehran' );
update_option( 'date_format', 'j F Y' );
update_option( 'start_of_week', 6 );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'permalink_structure', '/blog/%postname%/' );
update_option( 'category_base', 'blog/category' );
update_option( 'show_on_front', 'page' );
update_option( 'posts_per_page', 9 );

// Settings (placeholders; only reset when asked).
$settings = get_option( 'khy_settings', array() );
$defaults = khy_default_settings();
if ( empty( $settings ) || in_array( '--reset-settings', (array) ( $GLOBALS['argv'] ?? array() ), true ) ) {
	$settings = $defaults;
} else {
	$settings = wp_parse_args( $settings, $defaults );
}
$settings['prices_updated'] = gmdate( 'Y-m-d' );
update_option( 'khy_settings', $settings );
$say( 'تنظیمات اولیه ثبت شد.' );

// Make sure post types exist before inserting.
khy_register_post_types();

// ---------------------------------------------------------------- services
$service_ids = array();
foreach ( require $dir . 'services.php' as $d ) {
	$id = $upsert(
		'service',
		$d['slug'],
		array(
			'post_title'   => $d['title'],
			'post_content' => $d['content'],
			'post_excerpt' => $d['excerpt'],
			'menu_order'   => $d['menu_order'],
		)
	);
	$set_meta( $id, $d );
	$service_ids[ $d['slug'] ] = $id;
	$say( "خدمت: {$d['slug']} (#{$id})" );
}

// ---------------------------------------------------------------- neighborhoods
$hood_ids  = array();
$hood_data = require $dir . 'neighborhoods.php';
foreach ( $hood_data as $d ) {
	$id = $upsert(
		'neighborhood',
		$d['slug'],
		array(
			'post_title'   => $d['title'],
			'post_content' => $d['content'],
			'post_excerpt' => $d['excerpt'],
			'menu_order'   => $d['menu_order'],
		)
	);
	$set_meta( $id, $d );
	$hood_ids[ $d['slug'] ] = $id;
}
foreach ( $hood_data as $d ) {
	$id = $hood_ids[ $d['slug'] ];
	khy_set_neighborhood_services( $id, array_map( fn( $s ) => $service_ids[ $s ], $d['services'] ) );
	delete_post_meta( $id, '_khy_nearby' );
	foreach ( $d['nearby'] as $n ) {
		add_post_meta( $id, '_khy_nearby', $hood_ids[ $n ] );
	}
	$say( "محله: {$d['slug']} (#{$id})" );
}

// ---------------------------------------------------------------- pages
$page_ids = array();
foreach ( require $dir . 'pages.php' as $d ) {
	$id = $upsert(
		'page',
		$d['slug'],
		array(
			'post_title'   => $d['title'],
			'post_content' => $d['content'],
		)
	);
	if ( ! empty( $d['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', $d['template'] );
	} else {
		delete_post_meta( $id, '_wp_page_template' );
	}
	$set_meta( $id, $d );
	$page_ids[ $d['slug'] ] = $id;
	$say( "صفحه: {$d['slug']} (#{$id})" );
}
update_option( 'page_on_front', $page_ids['home'] );
update_option( 'page_for_posts', $page_ids['blog'] );

// ---------------------------------------------------------------- posts
$post_dates = array( '-9 days', '-5 days', '-2 days' );
foreach ( require $dir . 'posts.php' as $i => $d ) {
	$id = $upsert(
		'post',
		$d['slug'],
		array(
			'post_title'   => $d['title'],
			'post_content' => $d['content'],
			'post_excerpt' => $d['excerpt'],
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( $post_dates[ $i ] ?? 'now' ) ),
		)
	);
	$set_meta( $id, $d );
	$say( "مقاله: {$d['slug']} (#{$id})" );
}

// ---------------------------------------------------------------- menus
$build_menu = function ( $name, $location, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	$mid  = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $mid ) as $old ) {
		wp_delete_post( $old->ID, true );
	}
	foreach ( $items as $pos => $it ) {
		wp_update_nav_menu_item(
			$mid,
			0,
			array(
				'menu-item-title'    => $it[0],
				'menu-item-url'      => $it[1],
				'menu-item-status'   => 'publish',
				'menu-item-type'     => 'custom',
				'menu-item-position' => $pos + 1,
			)
		);
	}
	$locs              = get_theme_mod( 'nav_menu_locations', array() );
	$locs[ $location ] = $mid;
	set_theme_mod( 'nav_menu_locations', $locs );
};
$u = function ( $path ) {
	return home_url( $path );
};
$build_menu(
	'منوی اصلی',
	'primary',
	array(
		array( 'نظافت منزل', $u( '/nazafat-manzel-tehran/' ) ),
		array( 'نظافت دفتر', $u( '/nazafat-daftar-sherkat-tehran/' ) ),
		array( 'لوله‌کشی فوری', $u( '/loolekesh-fori-tehran/' ) ),
		array( 'قیمت‌ها', $u( '/gheymat-nazafat-manzel/' ) ),
		array( 'محله‌ها', $u( '/tehran/' ) ),
		array( 'تماس', $u( '/tamas/' ) ),
	)
);
$build_menu(
	'منوی پایین سایت',
	'footer',
	array(
		array( 'قیمت‌ها', $u( '/gheymat-nazafat-manzel/' ) ),
		array( 'نظافتچی خانم', $u( '/nazafatchi-khanom-tehran/' ) ),
		array( 'سؤالات متداول', $u( '/faq/' ) ),
		array( 'راهنما و مقاله‌ها', $u( '/blog/' ) ),
		array( 'دربارهٔ ما', $u( '/darbare-ma/' ) ),
		array( 'تماس با ما', $u( '/tamas/' ) ),
	)
);

// ---------------------------------------------------------------- theme + permalinks
if ( 'khooneyar' !== get_stylesheet() && wp_get_theme( 'khooneyar' )->exists() ) {
	switch_theme( 'khooneyar' );
	$say( 'قالب khooneyar فعال شد.' );
}
flush_rewrite_rules( true );

WP_CLI::success( 'سید کامل شد: ' . count( $service_ids ) . ' خدمت، ' . count( $hood_ids ) . ' محله، ' . count( $page_ids ) . ' صفحه، ۳ مقاله.' );
