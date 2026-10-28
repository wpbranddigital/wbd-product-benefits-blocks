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
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Loads block module files.
	 */
	private function load_dependencies() {
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/blocks/class-product-benefits.php';
	}

	/**
	 * Block module classes. Each must implement a static register() method.
	 *
	 * @return string[]
	 */
	private function get_block_classes() {
		return array(
			'WBD_Product_Benefits_Blocks_Product_Benefits',
		);
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

