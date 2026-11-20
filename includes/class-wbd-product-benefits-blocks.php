<?php
/**
 * Core plugin class.
 *
 * Registers the block category and boots every block module listed in
 * self::get_block_classes(). To add a new block, create a class in
 * includes/blocks/ that exposes a static register() method, require it in
 * load_dependencies() and add its class name to get_block_classes().
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin controller.
 */
final class WBD_Product_Benefits_Blocks {

	/**
	 * Block category slug shared by every WBD block.
	 *
	 * @var string
	 */
	const CATEGORY = 'wbd-product-benefits';

	/**
	 * Singleton instance.
	 *
	 * @var WBD_Product_Benefits_Blocks|null
	 */
	private static $instance = null;

	/**
	 * Returns the single plugin instance.
	 *
	 * @return WBD_Product_Benefits_Blocks
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wires hooks.
	 */
	private function __construct() {
		$this->load_dependencies();

		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 1 );
		add_action( 'plugins_loaded', array( $this, 'init_woocommerce_features' ), 20 );
		add_action( 'init', array( $this, 'register_blocks' ) );

		if ( is_admin() ) {
			WBD_Product_Benefits_Blocks_Admin_Dashboard::init();
		}
	}

	/**
	 * Loads block module files.
	 */
	private function load_dependencies() {
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/blocks/class-product-benefits.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/sticky-add-to-cart/class-sticky-settings.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/sticky-add-to-cart/class-sticky-renderer.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/blocks/class-sticky-add-to-cart.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/class-kit-module.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/class-kit.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/class-kit-bundle-engine.php';
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/admin/class-admin-dashboard.php';

		$modules = glob( WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/modules/class-module-*.php' );
		if ( $modules ) {
			sort( $modules );
			foreach ( $modules as $module_file ) {
				require_once $module_file;
			}
		}
	}

	/**
	 * Whether WooCommerce is active. Only reliable from plugins_loaded on.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_active() {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_product' );
	}

	/**
	 * Boots the WooCommerce-only features (Sticky Add to Cart).
	 */
	public function init_woocommerce_features() {
		if ( ! self::is_woocommerce_active() ) {
			return;
		}

		WBD_Product_Benefits_Blocks_Sticky_Settings::init();
		WBD_Product_Benefits_Blocks_Sticky_Renderer::init();

		// Bundle/Composite classes are always loaded so existing products of
		// these types are never treated as simple products (and sold without
		// their contents) while the feature is switched off.
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/class-kit-product-types.php';
		// Modules use translated labels, so boot them on init (WordPress 6.7+
		// warns when a text domain loads earlier). Priority 0 keeps them ahead
		// of their own init callbacks.
		add_action( 'init', array( 'WBD_PBB_Kit', 'init' ), 0 );
	}

	/**
	 * Block module classes. Each must implement a static register() method.
	 *
	 * @return string[]
	 */
	private function get_block_classes() {
		$classes = array(
			'WBD_Product_Benefits_Blocks_Product_Benefits',
		);

		if ( self::is_woocommerce_active() ) {
			$classes[] = 'WBD_Product_Benefits_Blocks_Sticky_Add_To_Cart';
		}

		return $classes;
	}

	/**
	 * Registers every block module.
	 */
	public function register_blocks() {
		foreach ( $this->get_block_classes() as $block_class ) {
			if ( class_exists( $block_class ) && is_callable( array( $block_class, 'register' ) ) ) {
				call_user_func( array( $block_class, 'register' ) );
			}
		}
	}

	/**
	 * Adds the "WBD Product Benefits" category to the block inserter.
	 *
	 * @param array $categories Registered block categories.
	 * @return array
	 */
	public function register_block_category( $categories ) {
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && self::CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}

		$categories[] = array(
			'slug'  => self::CATEGORY,
			'title' => esc_html__( 'WBD Product Benefits', 'wbd-product-benefits-blocks' ),
			'icon'  => null,
		);

		return $categories;
	}

	/**
	 * Absolute path to a built block directory.
	 *
	 * @param string $block_dir Block folder name inside build/.
	 * @return string
	 */
	public static function get_block_build_path( $block_dir ) {
		return WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'build/' . $block_dir;
	}
}

