<?php
/**
 * Plugin Name: BannerMonster
 * Plugin URI: https://incod.it/bannermonster
 * Description: Create accessible banners and popups with advanced targeting, WCAG 2.2 compliance, and native HTML dialog.
 * Version: 1.5.0
 * Author: Marco De Sangro (inCod)
 * Author URI: https://incod.it/bannermonster/
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bannermonster
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BANNERMONSTER_VERSION', '1.5.0' );
define( 'BANNERMONSTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'BANNERMONSTER_URL', plugin_dir_url( __FILE__ ) );
define( 'BANNERMONSTER_BASE', plugin_basename( __FILE__ ) );

require_once BANNERMONSTER_PATH . 'includes/class-bannermonster-cpt.php';
require_once BANNERMONSTER_PATH . 'includes/class-bannermonster-admin.php';
require_once BANNERMONSTER_PATH . 'includes/class-bannermonster-frontend.php';

add_action( 'init', array( 'BannerMonster_CPT', 'register_post_type' ) );

register_activation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

new BannerMonster_Admin();
new BannerMonster_Frontend();
