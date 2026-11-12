<?php
/**
 * Breadcrumbs (HTML only; JSON-LD is produced in inc/schema.php or by Rank Math).
 * Optionally outputs Rank Math's own breadcrumb trail (filter khy_use_rank_math_breadcrumbs).
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

if ( khy_use_rank_math_breadcrumbs() ) {
	if ( ! is_front_page() ) {
		echo '<div class="breadcrumbs breadcrumbs-rm">';
		rank_math_the_breadcrumbs();
		echo '</div>';
	}
	return;
}

$items = khy_breadcrumb_items();
if ( ! $items ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="مسیر صفحه">
	<ol>
		<?php foreach ( $items as $it ) : ?>
			<li>
				<?php if ( $it[1] ) : ?>
					<a href="<?php echo esc_url( $it[1] ); ?>"><?php echo esc_html( $it[0] ); ?></a>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( $it[0] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
