<?php
/**
 * Blog post card.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'card post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'khy-card', array( 'loading' => 'lazy' ) ); ?></a>
	<?php endif; ?>
	<time class="meta" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( khy_jalali( get_the_date( 'Y-m-d' ) ) ); ?></time>
	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<?php $ex = khy_card_excerpt( get_post(), 28 ); ?>
	<?php if ( $ex ) : ?><p><?php echo esc_html( $ex ); ?></p><?php endif; ?>
	<a class="card-link" href="<?php the_permalink(); ?>">ادامهٔ مطلب <?php echo khy_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
</article>
