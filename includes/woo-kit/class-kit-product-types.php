<?php
/**
 * Product types for Bundles and Composite products.
 * Loaded from WBD_PBB_Bundle_Engine::boot(), after WooCommerce.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Product' ) && ! class_exists( 'WC_Product_Wbd_Bundle' ) ) {

	/**
	 * Bundle product: a fixed set of products sold together.
	 */
	class WC_Product_Wbd_Bundle extends WC_Product {

		/**
		 * Type.
		 *
		 * @return string
		 */
		public function get_type() {
			return 'wbd_bundle';
		}

		/**
		 * Price set by the cart engine for this cart line (null outside the cart).
		 *
		 * @var float|null
		 */
		protected $wbd_cart_price = null;

		/**
		 * Sets the price this object charges as a cart line.
		 *
		 * @param float $price Price.
		 */
		public function set_wbd_cart_price( $price ) {
			$this->wbd_cart_price = (float) $price;
			$this->set_price( $this->wbd_cart_price );
		}

		/**
		 * Pricing mode: auto | auto_discount | fixed.
		 *
		 * @return string
		 */
		public function get_pricing_mode() {
			$mode = $this->get_meta( '_wbd_pricing', true, 'edit' );
			return in_array( $mode, array( 'auto', 'auto_discount', 'fixed' ), true ) ? $mode : 'auto';
		}

		/**
		 * Discount percent for auto_discount.
		 *
		 * @return float
		 */
		public function get_discount_percent() {
			return 'auto_discount' === $this->get_pricing_mode() ? min( 100, max( 0, (float) $this->get_meta( '_wbd_discount', true, 'edit' ) ) ) : 0.0;
		}

		/**
		 * Price shown and charged ("view" context is calculated in auto modes).
		 *
		 * @param string $context Context.
		 * @return string
		 */
		public function get_price( $context = 'view' ) {
			if ( 'view' === $context && null !== $this->wbd_cart_price ) {
				return (string) $this->wbd_cart_price;
			}
			if ( 'view' === $context && 'fixed' !== $this->get_pricing_mode() ) {
				$totals = WBD_PBB_Bundle_Engine::price_range( $this );
				return '' === $totals['regular'] ? '' : (string) $totals['price'];
			}
			return parent::get_price( $context );
		}

		/**
		 * Regular price.
		 *
		 * @param string $context Context.
		 * @return string
		 */
		public function get_regular_price( $context = 'view' ) {
			if ( 'view' === $context && 'fixed' !== $this->get_pricing_mode() ) {
				$totals = WBD_PBB_Bundle_Engine::price_range( $this );
				return (string) $totals['regular'];
			}
			return parent::get_regular_price( $context );
		}

		/**
		 * Sale price.
		 *
		 * @param string $context Context.
		 * @return string
		 */
		public function get_sale_price( $context = 'view' ) {
			if ( 'view' === $context && 'fixed' !== $this->get_pricing_mode() ) {
				$totals = WBD_PBB_Bundle_Engine::price_range( $this );
				return ( '' !== $totals['regular'] && $totals['price'] < $totals['regular'] ) ? (string) $totals['price'] : '';
			}
			return parent::get_sale_price( $context );
		}

		/**
		 * Price HTML ("From:" when the total depends on choices).
		 *
		 * @param string $deprecated Unused.
		 * @return string
		 */
		public function get_price_html( $deprecated = '' ) {
			$html = parent::get_price_html( $deprecated );
			if ( '' !== $html && WBD_PBB_Bundle_Engine::price_range( $this )['variable'] ) {
				$html = wc_get_price_html_from_text() . $html;
			}
			return $html;
		}

		/**
		 * Can be bought when it has items and a price.
		 *
		 * @return bool
		 */
		public function is_purchasable() {
			// Not for sale while its feature is switched off: without the engine
			// the included products would not be added to the cart.
			if ( ! WBD_PBB_Bundle_Engine::is_ours( $this ) ) {
				return false;
			}
			return (bool) apply_filters( 'woocommerce_is_purchasable', $this->exists() && ( 'publish' === $this->get_status() || current_user_can( 'edit_post', $this->get_id() ) ) && '' !== $this->get_price() && WBD_PBB_Bundle_Engine::has_items( $this ), $this );
		}

		/**
		 * Children are shipped, not the container.
		 *
		 * @return bool
		 */
		public function needs_shipping() {
			return false;
		}

		/**
		 * Shop buttons link to the product page (items/options are chosen there).
		 *
		 * @return string
		 */
		public function add_to_cart_url() {
			return apply_filters( 'woocommerce_product_add_to_cart_url', $this->get_permalink(), $this );
		}

		/**
		 * Shop button text.
		 *
		 * @return string
		 */
		public function add_to_cart_text() {
			$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'View bundle', 'wbd-product-benefits-blocks' ) : __( 'Read more', 'woocommerce' );
			return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
		}

		/**
		 * Single product button text.
		 *
		 * @return string
		 */
		public function single_add_to_cart_text() {
			return apply_filters( 'woocommerce_product_single_add_to_cart_text', __( 'Add bundle to cart', 'wbd-product-benefits-blocks' ), $this );
		}

		/**
		 * No AJAX add from shop loops.
		 *
		 * @param string $feature Feature.
		 * @return bool
		 */
		public function supports( $feature ) {
			if ( 'ajax_add_to_cart' === $feature ) {
				return false;
			}
			return parent::supports( $feature );
		}
	}

	/**
	 * Composite product: shoppers pick one product per component.
	 */
	class WC_Product_Wbd_Composite extends WC_Product_Wbd_Bundle {

		/**
		 * Type.
		 *
		 * @return string
		 */
		public function get_type() {
			return 'wbd_composite';
		}

		/**
		 * Shop button text.
		 *
		 * @return string
		 */
		public function add_to_cart_text() {
			$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Build yours', 'wbd-product-benefits-blocks' ) : __( 'Read more', 'woocommerce' );
			return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
		}

		/**
		 * Single product button text.
		 *
		 * @return string
		 */
		public function single_add_to_cart_text() {
			return apply_filters( 'woocommerce_product_single_add_to_cart_text', __( 'Add to cart', 'woocommerce' ), $this );
		}
	}
}
