<?php
/**
 * Plugin Name:       Khooneyar Core (خونه‌یار)
 * Description:       نوع نوشته‌های خدمت و محله، فرم سفارش داخلی، مدیریت سفارش‌ها و تنظیمات برند. داده‌ها با تغییر قالب از بین نمی‌روند.
 * Version:           1.0.3
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Arash Fadaee
 * License:           GPL-2.0-or-later
 * Text Domain:       khooneyar-core
 *
 * @package Khooneyar_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'KHY_CORE_VERSION', '1.0.3' );
define( 'KHY_CORE_FILE', __FILE__ );
define( 'KHY_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'KHY_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once KHY_CORE_DIR . 'includes/settings.php';
require_once KHY_CORE_DIR . 'includes/post-types.php';
require_once KHY_CORE_DIR . 'includes/meta-boxes.php';
require_once KHY_CORE_DIR . 'includes/orders.php';

/**
 * Flush rewrite rules once on activation (after CPTs are registered).
 */
function khy_core_activate() {
	khy_register_post_types();
	flush_rewrite_rules();
	if ( false === get_option( 'khy_settings', false ) ) {
		add_option( 'khy_settings', khy_default_settings() );
	}
}
register_activation_hook( __FILE__, 'khy_core_activate' );

function khy_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'khy_core_deactivate' );
