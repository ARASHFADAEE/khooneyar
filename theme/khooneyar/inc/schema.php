<?php
/**
 * JSON-LD schema (switchable).
 *
 * Modes (Settings → خونه‌یار → حالت اسکیمای قالب):
 *  - auto : theme prints its own @graph; if Rank Math is active, the theme's nodes
 *           (business with areaServed, Service, FAQPage) are injected INTO Rank Math's graph
 *           instead, so nothing is printed twice.
 *  - all  : theme always prints its graph (use only if no SEO plugin outputs schema).
 *  - off  : theme prints nothing.
 *
 * Business schema uses areaServed ONLY (no address / geo) — the business is a
 * service-area intermediary without a public office.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

function khy_schema_mode() {
	$m = khy_setting( 'schema_mode' );
	return in_array( $m, array( 'auto', 'all', 'off' ), true ) ? $m : 'auto';
}

function khy_schema_plain( $html ) {
	return trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
}

function khy_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_post_type_archive( 'neighborhood' ) ) {
		return get_post_type_archive_link( 'neighborhood' );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_permalink( get_option( 'page_for_posts' ) );
	}
	return home_url( '/' );
}

/** @return array areaServed list. */
function khy_schema_area_served() {
	$areas = array( array( '@type' => 'City', 'name' => 'تهران' ) );
	foreach ( khy_neighborhoods() as $n ) {
		$areas[] = array( '@type' => 'Place', 'name' => $n->post_title . '، تهران' );
	}
	return $areas;
}

function khy_schema_business( $full = true ) {
	$id   = home_url( '/#business' );
	$node = array(
		'@type' => array( 'HomeAndConstructionBusiness', 'LocalBusiness' ),
		'@id'   => $id,
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);
	if ( ! $full ) {
		return $node;
	}
	$node['description']  = get_bloginfo( 'description' );
	$node['telephone']    = '+98' . ltrim( preg_replace( '/\D/', '', khy_phone() ), '0' );
	$node['areaServed']   = khy_schema_area_served();
	$node['priceRange']   = '$$';
	$hours                = khy_setting( 'opening_hours' );
	if ( $hours ) {
		$node['openingHours'] = array_map( 'trim', explode( ',', $hours ) );
	}
	$logo = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : get_template_directory_uri() . '/assets/img/logo.png';
	$node['image'] = $logo;
	$node['logo']  = $logo;
	return $node;
}

function khy_schema_service( $post ) {
	$url  = get_permalink( $post );
	$node = array(
		'@type'       => 'Service',
		'@id'         => $url . '#service',
		'name'        => khy_h1( $post->ID ),
		'serviceType' => $post->post_title,
		'description' => khy_schema_plain( get_post_meta( $post->ID, '_khy_seo_desc', true ) ? get_post_meta( $post->ID, '_khy_seo_desc', true ) : get_the_excerpt( $post ) ),
		'url'         => $url,
		'provider'    => array( '@id' => home_url( '/#business' ) ),
		'areaServed'  => array( array( '@type' => 'City', 'name' => 'تهران' ) ),
	);
	if ( function_exists( 'khy_get_service_neighborhoods' ) ) {
		foreach ( khy_get_service_neighborhoods( $post->ID ) as $n ) {
			$node['areaServed'][] = array( '@type' => 'Place', 'name' => $n->post_title . '، تهران' );
		}
	}
	// Prices only appear once Arash has switched off "sample prices".
	$from = (int) get_post_meta( $post->ID, '_khy_price_from', true );
	$to   = (int) get_post_meta( $post->ID, '_khy_price_to', true );
	if ( ! khy_setting( 'prices_sample' ) && $from ) {
		if ( $to && $to > $from ) {
			$node['offers'] = array( '@type' => 'AggregateOffer', 'priceCurrency' => 'IRR', 'lowPrice' => (string) ( $from * 10 ), 'highPrice' => (string) ( $to * 10 ) );
		} else {
			$node['offers'] = array( '@type' => 'Offer', 'priceCurrency' => 'IRR', 'price' => (string) ( $from * 10 ) );
		}
	}
	return $node;
}

function khy_schema_faq( $post_id ) {
	$entities = array();
	foreach ( khy_faq( $post_id ) as $row ) {
		if ( empty( $row['q'] ) || empty( $row['a'] ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => khy_schema_plain( $row['q'] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => khy_schema_plain( $row['a'] ) ),
		);
	}
	if ( ! $entities ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink( $post_id ) . '#faq',
		'mainEntity' => $entities,
	);
}

function khy_schema_breadcrumb() {
	$items = khy_breadcrumb_items();
	if ( ! $items ) {
		return null;
	}
	$list = array();
	foreach ( $items as $i => $it ) {
		$row = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it[0] );
		$row['item'] = $it[1] ? $it[1] : khy_current_url();
		$list[]      = $row;
	}
	return array( '@type' => 'BreadcrumbList', '@id' => khy_current_url() . '#breadcrumb', 'itemListElement' => $list );
}

/**
 * Nodes that Rank Math does not generate on its own (safe to merge into its graph).
 *
 * @return array keyed nodes
 */
function khy_schema_extra_nodes() {
	$nodes = array();
	$is_business_page = is_front_page() || ( is_page() && 'template-contact.php' === basename( (string) get_page_template_slug() ) );
	if ( $is_business_page ) {
		$nodes['khyBusiness'] = khy_schema_business( true );
	}
	if ( is_singular( 'service' ) ) {
		$nodes['khyService'] = khy_schema_service( get_queried_object() );
	}
	if ( is_singular( array( 'service', 'page', 'neighborhood' ) ) ) {
		$faq = khy_schema_faq( get_queried_object_id() );
		if ( $faq ) {
			$nodes['khyFaq'] = $faq;
		}
	}
	return $nodes;
}

/** Full graph printed by the theme when no SEO plugin handles schema. */
function khy_schema_full_graph() {
	$graph   = array();
	$full_on = is_front_page() || ( is_page() && 'template-contact.php' === basename( (string) get_page_template_slug() ) );
	$graph[] = khy_schema_business( $full_on );
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#website' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'inLanguage' => 'fa-IR',
			'publisher' => array( '@id' => home_url( '/#business' ) ),
		);
	}
	$url  = khy_current_url();
	$page = array(
		'@type'      => 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_get_document_title(),
		'inLanguage' => 'fa-IR',
		'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
		'about'      => array( '@id' => home_url( '/#business' ) ),
	);
	if ( is_singular() ) {
		$page['datePublished'] = get_the_date( 'c' );
		$page['dateModified']  = get_the_modified_date( 'c' );
	}
	$bc = khy_schema_breadcrumb();
	if ( $bc ) {
		$page['breadcrumb'] = array( '@id' => $bc['@id'] );
	}
	$graph[] = $page;
	if ( $bc ) {
		$graph[] = $bc;
	}
	$extra = khy_schema_extra_nodes();
	unset( $extra['khyBusiness'] ); // already added above.
	foreach ( $extra as $n ) {
		$graph[] = $n;
	}
	return $graph;
}

add_action( 'wp_head', 'khy_print_schema', 30 );
function khy_print_schema() {
	$mode = khy_schema_mode();
	if ( 'off' === $mode || is_404() || is_search() ) {
		return;
	}
	// Rank Math (or another handler): inject into its graph instead of printing twice.
	if ( 'auto' === $mode && khy_has_seo_plugin() ) {
		return;
	}
	$data = array( '@context' => 'https://schema.org', '@graph' => khy_schema_full_graph() );
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Rank Math integration: add our nodes to its single graph (no duplicates).
 */
add_filter( 'rank_math/json_ld', 'khy_rank_math_graph', 99, 2 );
function khy_rank_math_graph( $data, $jsonld ) {
	if ( 'auto' !== khy_schema_mode() || ! is_array( $data ) ) {
		return $data;
	}
	foreach ( khy_schema_extra_nodes() as $key => $node ) {
		if ( ! isset( $data[ $key ] ) ) {
			$data[ $key ] = $node;
		}
	}
	return $data;
}
