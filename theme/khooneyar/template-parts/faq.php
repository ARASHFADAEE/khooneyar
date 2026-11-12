<?php
/**
 * FAQ accordion (native <details>, no JS). Args: faq (array), heading (string).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

$faq = isset( $args['faq'] ) ? $args['faq'] : khy_faq();
if ( ! $faq ) {
	return;
}
$heading = isset( $args['heading'] ) ? $args['heading'] : 'سؤالات متداول';
?>
<section class="section faq" aria-labelledby="faq-h">
	<div class="container narrow">
		<h2 id="faq-h" class="section-title"><?php echo esc_html( $heading ); ?></h2>
		<?php foreach ( $faq as $row ) : ?>
			<?php
			if ( empty( $row['q'] ) ) {
				continue;
			}
			?>
			<details class="faq-item">
				<summary><?php echo esc_html( $row['q'] ); ?></summary>
				<div class="faq-a"><?php echo wp_kses_post( wpautop( khy_kses_min( $row['a'] ) ) ); ?></div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
