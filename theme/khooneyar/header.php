<?php
/**
 * Site header.
 *
 * @package Khooneyar
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/sprite' ); ?>
<a class="skip-link" href="#main">پرش به محتوا</a>

<header class="site-header">
	<div class="container header-row">
		<?php get_template_part( 'template-parts/logo' ); ?>

		<nav class="main-nav" id="primary-nav" aria-label="منوی اصلی">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
					'fallback_cb'    => 'khy_fallback_menu',
				)
			);
			?>
		</nav>

		<div class="header-actions">
			<a class="btn btn-wa btn-sm hide-sm" href="<?php echo esc_url( khy_wa_href() ); ?>" rel="noopener" target="_blank"><?php echo khy_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>واتساپ</span></a>
			<a class="btn btn-primary btn-sm call-btn" href="<?php echo esc_url( khy_tel_href() ); ?>" aria-label="تماس با <?php echo esc_attr( khy_fa( khy_phone() ) ); ?>"><?php echo khy_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="hide-sm" dir="ltr"><?php echo esc_html( khy_fa( khy_phone() ) ); ?></span></a>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="باز و بسته کردن منو"><?php echo khy_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
