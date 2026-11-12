<?php
/**
 * Title tag, meta description and Open Graph (only when no SEO plugin is active).
 * Rank Math is the recommended SEO plugin; when it is active the theme stays out of the way.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'document_title_separator', 'khy_title_sep' );
function khy_title_sep() {
	return '|';
}

add_filter( 'pre_get_document_title', 'khy_document_title', 20 );
function khy_document_title( $title ) {
	if ( khy_has_seo_plugin() ) {
		return $title;
	}
	$name = get_bloginfo( 'name' );
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_khy_seo_title', true );
		if ( $custom ) {
			return $custom;
		}
	}
	if ( is_post_type_archive( 'neighborhood' ) ) {
		return 'محله‌های تحت پوشش در تهران | ' . $name;
	}
	return $title;
}

/**
 * Meta description for the current view.
 *
 * @return string
 */
function khy_meta_description() {
	if ( is_singular() ) {
		$id = get_queried_object_id();
		$d  = get_post_meta( $id, '_khy_seo_desc', true );
		if ( $d ) {
			return $d;
		}
		if ( has_excerpt( $id ) ) {
			return wp_strip_all_tags( get_the_excerpt( $id ) );
		}
		return wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 30, '…' );
	}
	if ( is_post_type_archive( 'neighborhood' ) ) {
		return 'نظافت منزل، نظافت دفتر و لوله‌کشی فوری در محله‌های انقلاب، فلسطین، یوسف‌آباد، هفت‌تیر، کارگر، کشاورز و جمهوری تهران. زمان رسیدن هر محله را ببینید.';
	}
	if ( is_front_page() || is_home() ) {
		return get_bloginfo( 'description' );
	}
	return '';
}

add_action( 'wp_head', 'khy_head_meta', 2 );
function khy_head_meta() {
	if ( khy_has_seo_plugin() ) {
		return;
	}
	$desc = khy_meta_description();
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : ( is_post_type_archive() ? get_post_type_archive_link( get_query_var( 'post_type' ) ) : home_url( '/' ) );
	echo '<meta property="og:locale" content="fa_IR">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( $url ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	}
	if ( is_singular() && has_post_thumbnail() ) {
		echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( null, 'large' ) ) . '">' . "\n";
	}
}
