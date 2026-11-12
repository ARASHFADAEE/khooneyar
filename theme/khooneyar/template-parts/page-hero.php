<?php
/**
 * Page hero: breadcrumbs, H1 and lead. Args: h1, lead, badges (array of [icon,text]).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$h1    = isset( $args['h1'] ) ? $args['h1'] : khy_h1();
$lead  = isset( $args['lead'] ) ? $args['lead'] : khy_lead();
$chips = isset( $args['badges'] ) ? $args['badges'] : array();
?>
<section class="page-hero">
	<div class="container">
		<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
		<h1><?php echo esc_html( $h1 ); ?></h1>
		<?php if ( $lead ) : ?>
			<p class="lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
		<?php if ( $chips ) : ?>
			<ul class="chips">
				<?php foreach ( $chips as $c ) : ?>
					<li><?php echo khy_icon( $c[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $c[1] ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php echo isset( $args['after'] ) ? $args['after'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted markup built with esc_* in the calling template. ?>
	</div>
</section>
