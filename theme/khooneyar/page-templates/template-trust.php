<?php
/**
 * Template Name: نظافتچی خانم و اعتماد
 * Template Post Type: page
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$steps = get_post_meta( get_the_ID(), '_khy_steps', true );
	$steps = is_array( $steps ) ? $steps : array();
	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'badges' => array(
				array( 'shield', 'احراز هویت با کارت ملی' ),
				array( 'check', 'بررسی سوءپیشینه' ),
				array( 'user', 'امکان درخواست تعویض نیرو' ),
			),
		)
	);
	?>
	<?php if ( $steps ) : ?>
		<section class="section" aria-labelledby="steps-h">
			<div class="container narrow">
				<h2 id="steps-h" class="section-title">مراحل احراز هویت نیروها</h2>
				<ol class="steps steps-vertical">
					<?php foreach ( $steps as $i => $st ) : ?>
						<li><span class="step-n" aria-hidden="true"><?php echo esc_html( khy_fa( $i + 1 ) ); ?></span><div><h3><?php echo esc_html( $st['title'] ); ?></h3><p><?php echo esc_html( $st['text'] ); ?></p></div></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>
	<section class="section section-alt">
		<div class="container narrow prose">
			<?php the_content(); ?>
		</div>
	</section>
	<section class="section">
		<div class="container narrow">
			<?php get_template_part( 'template-parts/order-form', null, array( 'heading' => 'درخواست نظافتچی خانم', 'sub' => 'در یادداشت بنویسید که نیروی خانم می‌خواهید؛ هماهنگی را انجام می‌دهیم.' ) ); ?>
		</div>
	</section>
	<?php
	get_template_part( 'template-parts/testimonials' );
	get_template_part( 'template-parts/faq' );
	get_template_part( 'template-parts/cta' );
endwhile;
get_footer();
