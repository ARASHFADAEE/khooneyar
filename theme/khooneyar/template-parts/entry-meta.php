<?php
/**
 * Entry meta line: published date, updated date, author. Each item is optional and hidden when empty.
 * Args: published (bool), updated (bool), author (bool). The updated date is skipped when it equals the published date
 * (unless 'published' is off, in which case it is always shown).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$show_pub = ! empty( $args['published'] );
$show_upd = ! empty( $args['updated'] );
$show_aut = ! empty( $args['author'] );
$post_id  = get_the_ID();
$items    = array();

if ( $show_pub ) {
	$items[] = array( 'calendar', 'منتشر شده', get_the_date( 'c' ), khy_jalali( get_the_date( 'Y-m-d' ) ) );
}
if ( $show_upd && ( ! $show_pub || get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' ) ) ) {
	$items[] = array( 'clock', 'آخرین به‌روزرسانی', get_the_modified_date( 'c' ), khy_jalali( get_the_modified_date( 'Y-m-d' ) ) );
}
if ( $show_aut ) {
	$author = khy_public_author( $post_id );
	if ( $author ) {
		$items[] = array( 'user', 'نویسنده', '', $author );
	}
}
$items = array_filter(
	$items,
	static function ( $it ) {
		return '' !== trim( (string) $it[3] );
	}
);
if ( ! $items ) {
	return;
}
?>
<ul class="entry-meta">
	<?php foreach ( $items as $it ) : ?>
		<li>
			<?php echo khy_icon( $it[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="meta-label"><?php echo esc_html( $it[1] ); ?>:</span>
			<?php if ( $it[2] ) : ?>
				<time datetime="<?php echo esc_attr( $it[2] ); ?>"><?php echo esc_html( $it[3] ); ?></time>
			<?php else : ?>
				<span class="meta-value"><?php echo esc_html( $it[3] ); ?></span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
