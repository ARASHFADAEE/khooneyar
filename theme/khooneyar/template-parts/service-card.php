<?php
/**
 * Service card. Args: post (WP_Post).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$p     = isset( $args['post'] ) ? $args['post'] : get_post();
$group = get_post_meta( $p->ID, '_khy_group', true );
$icon  = ( 'plumbing' === $group ) ? 'wrench' : 'sparkle';
if ( false !== strpos( $p->post_name, 'daftar' ) ) {
	$icon = 'building';
}
$price = get_post_meta( $p->ID, '_khy_price_summary', true );
?>
<article class="card service-card">
	<span class="card-icon card-icon-<?php echo esc_attr( $group ? $group : 'cleaning' ); ?>"><?php echo khy_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<h3><a href="<?php echo esc_url( get_permalink( $p ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></h3>
	<?php $ex = khy_card_excerpt( $p ); ?>
	<?php if ( $ex ) : ?><p><?php echo esc_html( $ex ); ?></p><?php endif; ?>
	<?php if ( $price ) : ?>
		<p class="card-price"><?php echo khy_icon( 'tag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $price ); ?></p>
	<?php endif; ?>
	<a class="card-link" href="<?php echo esc_url( get_permalink( $p ) ); ?>">جزئیات و سفارش <?php echo khy_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
</article>
