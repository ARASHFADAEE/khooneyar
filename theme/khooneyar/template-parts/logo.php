<?php
/**
 * Brand logo: custom logo if set, else the built-in SVG icon + text.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;

if ( has_custom_logo() ) {
	echo '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'logo-img', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager', 'fetchpriority' => 'high' ) );
	echo '</a>';
	return;
}
?>
<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	<svg class="logo-mark" width="40" height="40" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
		<rect width="48" height="48" rx="12" fill="#0F766E"/>
		<path d="M10 24 24 12l14 12v13a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2z" fill="#fff"/>
		<path d="m17.5 29 4.5 4.5L31 24" fill="none" stroke="#F59E0B" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
	<span class="logo-text">
		<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
		<small><?php echo esc_html( get_bloginfo( 'description' ) ); ?></small>
	</span>
</a>
