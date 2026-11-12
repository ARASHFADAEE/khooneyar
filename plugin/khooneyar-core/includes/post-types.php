<?php
/**
 * Custom post types: service (root-level URLs) and neighborhood (/tehran/<slug>/).
 *
 * @package Khooneyar_Core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'khy_register_post_types' );
function khy_register_post_types() {
	register_post_type(
		'service',
		array(
			'labels'              => array(
				'name'               => 'خدمت‌ها',
				'singular_name'      => 'خدمت',
				'add_new'            => 'افزودن خدمت',
				'add_new_item'       => 'افزودن خدمت جدید',
				'edit_item'          => 'ویرایش خدمت',
				'view_item'          => 'مشاهدهٔ خدمت',
				'search_items'       => 'جستجوی خدمت',
				'not_found'          => 'خدمتی پیدا نشد',
				'all_items'          => 'همهٔ خدمت‌ها',
				'menu_name'          => 'خدمت‌ها',
			),
			'public'              => true,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-hammer',
			'menu_position'       => 21,
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'has_archive'         => false,
			// URLs are root-level (/nazafat-manzel-tehran/); see khy_service_permalink() and khy_resolve_root_services().
			'rewrite'             => false,
			'query_var'           => 'service',
			'hierarchical'        => false,
		)
	);

	register_post_type(
		'neighborhood',
		array(
			'labels'       => array(
				'name'          => 'محله‌ها',
				'singular_name' => 'محله',
				'add_new'       => 'افزودن محله',
				'add_new_item'  => 'افزودن محلهٔ جدید',
				'edit_item'     => 'ویرایش محله',
				'view_item'     => 'مشاهدهٔ محله',
				'search_items'  => 'جستجوی محله',
				'not_found'     => 'محله‌ای پیدا نشد',
				'all_items'     => 'همهٔ محله‌ها',
				'menu_name'     => 'محله‌ها',
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-location-alt',
			'menu_position' => 22,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'has_archive'  => 'tehran',
			'rewrite'      => array(
				'slug'       => 'tehran',
				'with_front' => false,
			),
		)
	);
}

/**
 * Service permalinks live at the site root: /<slug>/
 */
add_filter( 'post_type_link', 'khy_service_permalink', 10, 2 );
function khy_service_permalink( $link, $post ) {
	if ( 'service' !== $post->post_type ) {
		return $link;
	}
	if ( 'publish' !== $post->post_status ) {
		return add_query_arg( array( 'post_type' => 'service', 'p' => $post->ID ), home_url( '/' ) );
	}
	return user_trailingslashit( home_url( '/' . $post->post_name ) );
}

/**
 * Resolve a root-level slug to a service when no page with that path exists.
 */
add_filter( 'request', 'khy_resolve_root_services' );
function khy_resolve_root_services( $qv ) {
	if ( is_admin() || ! empty( $qv['post_type'] ) ) {
		return $qv;
	}
	// Normal case: the generic page rule matched. With "verbose" page rules (permalink structures
	// that start with a tag after a prefix) WordPress instead reports error=404 for unknown pages.
	if ( ! empty( $qv['pagename'] ) ) {
		$path = $qv['pagename'];
	} elseif ( isset( $qv['error'] ) && '404' === (string) $qv['error'] && ! empty( $GLOBALS['wp']->request ) ) {
		$path = trim( rawurldecode( $GLOBALS['wp']->request ), '/' );
	} else {
		return $qv;
	}
	if ( '' === $path || false !== strpos( $path, '/' ) || get_page_by_path( $path ) ) {
		return $qv;
	}
	$service = get_page_by_path( $path, OBJECT, 'service' );
	if ( $service && 'publish' === $service->post_status ) {
		return array(
			'service'   => $service->post_name,
			'post_type' => 'service',
			'name'      => $service->post_name,
		);
	}
	return $qv;
}

/**
 * /tehran/ and /tehran/<slug>/ belong to the neighborhood CPT rewrite. A page at /tehran/
 * replaces the CPT archive. When no published
 * neighborhood has that slug but a child page tehran/<slug> exists, serve the page
 * instead of a 404, so pages and CPT neighborhoods can share the same URL scheme.
 */
add_filter( 'request', 'khy_neighborhood_page_fallback', 20 );
function khy_neighborhood_page_fallback( $qv ) {
	if ( is_admin() ) {
		return $qv;
	}
	// /tehran/ itself: a published page with path "tehran" wins over the CPT archive (page 1 only).
	if ( empty( $qv['neighborhood'] ) && isset( $qv['post_type'] ) && 'neighborhood' === $qv['post_type'] && empty( $qv['paged'] ) && empty( $qv['feed'] ) ) {
		$hub = get_page_by_path( 'tehran', OBJECT, 'page' );
		if ( $hub && 'publish' === $hub->post_status ) {
			return array( 'pagename' => 'tehran' );
		}
		return $qv;
	}
	if ( empty( $qv['neighborhood'] ) || ( ! empty( $qv['post_type'] ) && 'neighborhood' !== $qv['post_type'] ) ) {
		return $qv;
	}
	$slug = trim( (string) $qv['neighborhood'], '/' );
	if ( '' === $slug || false !== strpos( $slug, '/' ) ) {
		return $qv;
	}
	$hood = get_page_by_path( $slug, OBJECT, 'neighborhood' );
	if ( $hood && 'publish' === $hood->post_status ) {
		return $qv;
	}
	$page = get_page_by_path( 'tehran/' . $slug, OBJECT, 'page' );
	if ( ! $page ) {
		return $qv;
	}
	$new = array( 'pagename' => 'tehran/' . $slug );
	foreach ( array( 'page', 'preview', 'cpage', 'embed' ) as $keep ) {
		if ( isset( $qv[ $keep ] ) && '' !== $qv[ $keep ] ) {
			$new[ $keep ] = $qv[ $keep ];
		}
	}
	return $new;
}

/* ------------------------------------------------------------------
 * 301 redirects from Persian (old / alternate) slugs to the Latin URL.
 * Aliases are stored in the repeatable meta _khy_alias (one path per row).
 * ---------------------------------------------------------------- */

add_action( 'template_redirect', 'khy_alias_redirect', 1 );
function khy_alias_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$path = wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! is_string( $path ) ) {
		return;
	}
	$path = trim( rawurldecode( $path ), '/' );
	if ( '' === $path ) {
		return;
	}
	$path = str_replace( ' ', '-', $path );
	$ids  = get_posts(
		array(
			'post_type'        => array( 'page', 'service', 'neighborhood', 'post' ),
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_key'         => '_khy_alias',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value'       => $path,
		)
	);
	if ( $ids ) {
		wp_safe_redirect( get_permalink( $ids[0] ), 301 );
		exit;
	}
}

/* ------------------------------------------------------------------
 * Two-way service <-> neighborhood relation.
 * Source of truth: multi-row meta _khy_service on the neighborhood.
 * ---------------------------------------------------------------- */

/**
 * Services linked to a neighborhood.
 *
 * @param int $neighborhood_id Neighborhood post ID.
 * @return WP_Post[]
 */
function khy_get_neighborhood_services( $neighborhood_id ) {
	$ids = array_map( 'absint', get_post_meta( $neighborhood_id, '_khy_service', false ) );
	if ( ! $ids ) {
		return array();
	}
	return get_posts(
		array(
			'post_type'      => 'service',
			'post__in'       => $ids,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'posts_per_page' => 20,
			'no_found_rows'  => true,
		)
	);
}

/**
 * Neighborhoods linked to a service.
 *
 * @param int $service_id Service post ID.
 * @return WP_Post[]
 */
function khy_get_service_neighborhoods( $service_id ) {
	return get_posts(
		array(
			'post_type'      => 'neighborhood',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'posts_per_page' => 50,
			'no_found_rows'  => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => array(
				array(
					'key'   => '_khy_service',
					'value' => (int) $service_id,
				),
			),
		)
	);
}

/**
 * Replace the service set of a neighborhood.
 *
 * @param int   $neighborhood_id Neighborhood ID.
 * @param int[] $service_ids     Service IDs.
 */
function khy_set_neighborhood_services( $neighborhood_id, $service_ids ) {
	delete_post_meta( $neighborhood_id, '_khy_service' );
	foreach ( array_unique( array_map( 'absint', (array) $service_ids ) ) as $sid ) {
		if ( $sid && 'service' === get_post_type( $sid ) ) {
			add_post_meta( $neighborhood_id, '_khy_service', $sid );
		}
	}
}

/**
 * Services and neighborhoods are structured landing pages (long HTML + meta boxes):
 * use the classic editor for them. Pages and blog posts keep the block editor.
 */
add_filter( 'use_block_editor_for_post_type', 'khy_classic_for_cpts', 10, 2 );
function khy_classic_for_cpts( $use, $post_type ) {
	return in_array( $post_type, array( 'service', 'neighborhood' ), true ) ? false : $use;
}
