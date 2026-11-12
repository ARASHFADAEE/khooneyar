<?php
/**
 * Template Name: سؤالات متداول
 * Template Post Type: page
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	$groups = array();
	foreach ( khy_faq() as $row ) {
		$groups[ ! empty( $row['g'] ) ? $row['g'] : 'سؤالات عمومی' ][] = $row;
	}
	foreach ( $groups as $gname => $rows ) {
		get_template_part( 'template-parts/faq', null, array( 'faq' => $rows, 'heading' => $gname ) );
	}
	if ( trim( get_the_content() ) ) :
		?>
		<section class="section"><div class="container narrow prose"><?php the_content(); ?></div></section>
		<?php
	endif;
	get_template_part( 'template-parts/cta', null, array( 'title' => 'پاسخ سؤالتان را پیدا نکردید؟', 'text' => 'مستقیم بپرسید؛ در واتساپ یا تلفن پاسخ می‌دهیم.' ) );
endwhile;
get_footer();
