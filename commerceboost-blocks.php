<?php
/**
 * Plugin Name:       CommerceBoost Blocks
 * Description:       Lightweight WooCommerce conversion blocks for Gutenberg. Build product benefits, trust badges and conversion-focused product sections without a page builder.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4 
 * Author:            WPBrand Digital
 * Author URI:        https://wpbranddigital.org
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       commerceboost-blocks
 * Domain Path:       /languages
 *
 * @package CommerceBoost_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMMERCEBOOST_BLOCKS_VERSION', '1.0.0' );
define( 'COMMERCEBOOST_BLOCKS_FILE', __FILE__ );
define( 'COMMERCEBOOST_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'COMMERCEBOOST_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once COMMERCEBOOST_BLOCKS_PATH . 'includes/class-commerceboost-blocks.php';

CommerceBoost_Blocks::instance();
