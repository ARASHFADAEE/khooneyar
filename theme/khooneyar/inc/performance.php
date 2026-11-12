<?php
/**
 * Performance: trim WordPress front-end output.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'khy_trim_head' );
function khy_trim_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
}

add_action( 'wp_enqueue_scripts', 'khy_dequeue_core_assets', 100 );
function khy_dequeue_core_assets() {
	// The theme styles content itself; skip block-library CSS on the front end.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wc-blocks-style' );
}
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

/** Convert uploaded JPEG/PNG to WebP when the server supports it. */
add_filter( 'image_editor_output_format', 'khy_webp_output' );
function khy_webp_output( $formats ) {
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
	}
	return $formats;
}

/** Comments are not used on this site. */
add_action( 'init', 'khy_disable_comments_support' );
function khy_disable_comments_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );
}
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

/** Search results should not be indexed. */
add_filter( 'wp_robots', 'khy_robots' );
function khy_robots( $robots ) {
	if ( is_search() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}

/** No comments → no comments feed link. */
add_filter( 'feed_links_show_comments_feed', '__return_false' );
