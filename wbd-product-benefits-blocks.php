<?php
/**
 * Plugin Name:       WBD Product Benefits Blocks
 * Description:       Lightweight WooCommerce conversion blocks for Gutenberg. Build product benefits, trust badges and conversion-focused product sections without a page builder.
 * Version:           1.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            WPBrand Digital
 * Author URI:        https://wpbranddigital.org
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wbd-product-benefits-blocks
 * Domain Path:       /languages
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WBD_PRODUCT_BENEFITS_BLOCKS_VERSION', '1.0.1' );
define( 'WBD_PRODUCT_BENEFITS_BLOCKS_FILE', __FILE__ );
define( 'WBD_PRODUCT_BENEFITS_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WBD_PRODUCT_BENEFITS_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/class-wbd-product-benefits-blocks.php';

WBD_Product_Benefits_Blocks::instance();
