<?php
/**
 * Content helpers: responsive tables in post content, safe card excerpts, entry meta.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrap every <table> that comes out of the editor in a horizontally scrollable region,
 * so wide price tables never break the mobile layout. Display-only: the stored content is untouched.
 *
 * @param string $content Post content (after wpautop).
 * @return string
 */
function khy_wrap_content_tables( $content ) {
	if ( is_feed() || false === stripos( $content, '<table' ) ) {
		return $content;
	}
	static $n = 0;
	return preg_replace_callback(
		'#<table\b.*?</table>#is',
		static function ( $m ) use ( &$n ) {
			++$n;
			return '<div class="khy-table-wrap" role="region" tabindex="0" aria-label="' . esc_attr( 'جدول ' . khy_fa( $n ) ) . '">' . $m[0] . '</div>';
		},
		$content
	);
}
add_filter( 'the_content', 'khy_wrap_content_tables', 20 );

/**
 * Short, clean excerpt for cards. Uses the manual excerpt, otherwise the first real paragraph
 * (never headings, tables or list text glued together).
 *
 * @param WP_Post|int|null $post  Post.
 * @param int              $words Max words.
 * @return string Plain text, may be empty.
 */
function khy_card_excerpt( $post = null, $words = 26 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	if ( has_excerpt( $post ) ) {
		return wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), $words, '…' );
	}
	$html = strip_shortcodes( $post->post_content );
	if ( preg_match_all( '#<p\b[^>]*>(.*?)</p>#is', $html, $m ) ) {
		foreach ( $m[1] as $p ) {
			$t = trim( wp_strip_all_tags( $p ) );
			if ( mb_strlen( $t ) > 25 ) {
				return wp_trim_words( $t, $words, '…' );
			}
		}
	}
	// Classic editor text without <p> tags: first non-heading block of plain text.
	$html = preg_replace( '#<(h[1-6]|table|ul|ol|figure|details)\b.*?</\1>#is', "\n", $html );
	foreach ( preg_split( '/\n\s*\n/', trim( wp_strip_all_tags( $html ) ) ) as $chunk ) {
		$chunk = trim( preg_replace( '/\s+/u', ' ', $chunk ) );
		if ( mb_strlen( $chunk ) > 25 ) {
			return wp_trim_words( $chunk, $words, '…' );
		}
	}
	return '';
}

/**
 * Does the post have visible body content (ignores empty blocks, &nbsp; and whitespace)?
 *
 * @param WP_Post|int|null $post Post.
 * @return bool
 */
function khy_has_content( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	$html = preg_replace( '/<!--.*?-->/s', '', (string) $post->post_content );
	$text = trim( str_replace( "\xc2\xa0", ' ', wp_strip_all_tags( html_entity_decode( $html ) ) ) );
	return '' !== $text || (bool) preg_match( '/<(img|table|iframe|video|figure)\b/i', $html );
}

/**
 * Author name safe to show publicly: skipped when it equals the login name (would leak the login).
 *
 * @param WP_Post|int|null $post Post.
 * @return string
 */
function khy_public_author( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$user = get_userdata( (int) $post->post_author );
	if ( ! $user ) {
		return '';
	}
	$name = trim( $user->display_name );
	if ( '' === $name || 0 === strcasecmp( $name, $user->user_login ) || 0 === strcasecmp( $name, $user->user_nicename ) ) {
		return '';
	}
	return $name;
}

/**
 * Whether the optional Rank Math breadcrumb output should replace the theme's own breadcrumbs.
 * Off by default: the theme's breadcrumbs keep working and Rank Math's JSON-LD is unaffected.
 * Enable with: add_filter( 'khy_use_rank_math_breadcrumbs', '__return_true' );
 *
 * @return bool
 */
function khy_use_rank_math_breadcrumbs() {
	return (bool) apply_filters( 'khy_use_rank_math_breadcrumbs', false ) && function_exists( 'rank_math_the_breadcrumbs' );
}
