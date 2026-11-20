<?php
/**
 * Shared engine for Bundle and Composite products: pricing, cart, order.
 *
 * A bundle/composite is added to the cart as a parent line plus one child
 * line per included product. Children are real cart items, so stock, tax and
 * shipping work as usual. Their quantities follow the parent, removing either
 * removes the whole set, and prices are adjusted by the pricing mode:
 * - auto:          children keep their prices (composite: plus the base price)
 * - auto_discount: children are discounted by a percentage
 * - fixed:         the parent carries the price, children are free
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bundle engine.
 */
final class WBD_PBB_Bundle_Engine {

	const BUNDLE    = 'wbd_bundle';
	const COMPOSITE = 'wbd_composite';
	const MAX_ITEMS = 30;

	/**
	 * Registered types (type => label).
	 *
	 * @var array
	 */
	private static $types = array();

	/**
	 * Children resolved during validation, by product ID.
	 *
	 * @var array
	 */
	private static $pending = array();

	/**
	 * Price cache.
	 *
	 * @var array
	 */
	private static $prices = array();

	/**
	 * Guards cascading cart changes.
	 *
	 * @var bool
	 */
	private static $busy = false;

	/**
	 * Boots once and registers a product type.
	 *
	 * @param string $type  Type.
	 * @param string $label Label.
	 */
	public static function register_type( $type, $label ) {
		if ( ! self::$types ) {
			self::boot();
		}
		self::$types[ $type ] = $label;
	}

	/**
	 * Whether a product (or type string) is one of ours.
	 *
	 * @param mixed $product Product or type.
	 * @return bool
	 */
	public static function is_ours( $product ) {
		$type = $product instanceof WC_Product ? $product->get_type() : (string) $product;
		return isset( self::$types[ $type ] );
	}

	/**
	 * Hooks.
	 */
	private static function boot() {
		require_once WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'includes/woo-kit/class-kit-product-types.php';

		add_filter( 'product_type_selector', array( __CLASS__, 'type_selector' ) );
		add_filter( 'woocommerce_product_class', array( __CLASS__, 'product_class' ), 10, 2 );
		add_filter( 'woocommerce_data_stores', array( __CLASS__, 'data_stores' ) );

		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate' ), 20, 3 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 20, 2 );
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'add_children' ), 20, 6 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'set_prices' ), 99 );
		add_action( 'woocommerce_after_cart_item_quantity_update', array( __CLASS__, 'sync_quantity' ), 10, 2 );
		add_action( 'woocommerce_cart_item_removed', array( __CLASS__, 'cascade_remove' ), 10, 2 );
		add_action( 'woocommerce_cart_item_restored', array( __CLASS__, 'cascade_restore' ), 10, 2 );
		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'remove_orphans' ) );

		add_filter( 'woocommerce_cart_item_class', array( __CLASS__, 'item_class' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'item_name' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( __CLASS__, 'item_quantity' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_remove_link', array( __CLASS__, 'item_remove_link' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'item_price_html' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_subtotal', array( __CLASS__, 'item_price_html' ), 10, 2 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_data' ), 10, 2 );
		add_filter( 'woocommerce_store_api_product_quantity_editable', array( __CLASS__, 'store_api_editable' ), 10, 3 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_item_meta' ), 10, 3 );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	/* Types -------------------------------------------------------------- */

	/**
	 * Adds our types to the Product type dropdown.
	 *
	 * @param array $types Types.
	 * @return array
	 */
	public static function type_selector( $types ) {
		return array_merge( $types, self::$types );
	}

	/**
	 * Maps our types to classes.
	 *
	 * @param string $classname Class.
	 * @param string $type      Type.
	 * @return string
	 */
	public static function product_class( $classname, $type ) {
		if ( self::BUNDLE === $type && isset( self::$types[ self::BUNDLE ] ) ) {
			return 'WC_Product_Wbd_Bundle';
		}
		if ( self::COMPOSITE === $type && isset( self::$types[ self::COMPOSITE ] ) ) {
			return 'WC_Product_Wbd_Composite';
		}
		return $classname;
	}

	/**
	 * Our types use the standard product data store.
	 *
	 * @param array $stores Stores.
	 * @return array
	 */
	public static function data_stores( $stores ) {
		foreach ( array_keys( self::$types ) as $type ) {
			$stores[ 'product-' . $type ] = 'WC_Product_Data_Store_CPT';
		}
		return $stores;
	}

	/* Definitions -------------------------------------------------------- */

	/**
	 * Whether a product may be included (simple, or a variation with all attributes set).
	 *
	 * @param WC_Product|false|null $child Product.
	 * @return bool
	 */
	public static function is_includable( $child ) {
		if ( ! $child instanceof WC_Product || 'publish' !== get_post_status( $child->is_type( 'variation' ) ? $child->get_parent_id() : $child->get_id() ) ) {
			return false;
		}
		if ( $child->is_type( 'variation' ) ) {
			foreach ( $child->get_variation_attributes() as $value ) {
				if ( '' === $value ) {
					return false;
				}
			}
			return true;
		}
		return $child->is_type( 'simple' );
	}

	/**
	 * Bundle items: list of { id, qty, optional, product }.
	 *
	 * @param WC_Product $product Bundle.
	 * @return array
	 */
	public static function bundle_items( $product ) {
		$items = array();
		foreach ( (array) $product->get_meta( '_wbd_bundle_items', true, 'edit' ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$child = isset( $row['id'] ) ? wc_get_product( absint( $row['id'] ) ) : null;
			if ( ! self::is_includable( $child ) ) {
				continue;
			}
			$items[] = array(
				'id'       => $child->get_id(),
				'qty'      => max( 1, absint( isset( $row['qty'] ) ? $row['qty'] : 1 ) ),
				'optional' => ! empty( $row['optional'] ),
				'product'  => $child,
			);
		}
		return $items;
	}

	/**
	 * Composite components: list of { title, qty, required, options: WC_Product[] }.
	 *
	 * @param WC_Product $product Composite.
	 * @return array
	 */
	public static function components( $product ) {
		$components = array();
		foreach ( (array) $product->get_meta( '_wbd_components', true, 'edit' ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$options = array();
			foreach ( isset( $row['options'] ) ? (array) $row['options'] : array() as $id ) {
				$child = wc_get_product( absint( $id ) );
				if ( self::is_includable( $child ) ) {
					$options[ $child->get_id() ] = $child;
				}
			}
			if ( ! $options ) {
				continue;
			}
			$components[] = array(
				'title'    => isset( $row['title'] ) && '' !== $row['title'] ? $row['title'] : __( 'Choose an option', 'wbd-product-benefits-blocks' ),
				'qty'      => max( 1, absint( isset( $row['qty'] ) ? $row['qty'] : 1 ) ),
				'required' => ! empty( $row['required'] ),
				'options'  => $options,
			);
		}
		return $components;
	}

	/**
	 * Whether a product has anything to sell.
	 *
	 * @param WC_Product $product Product.
	 * @return bool
	 */
	public static function has_items( $product ) {
		return $product->is_type( self::COMPOSITE ) ? (bool) self::components( $product ) : (bool) self::bundle_items( $product );
	}

	/**
	 * Raw price of an included product.
	 *
	 * @param WC_Product $child Product.
	 * @return float
	 */
	public static function child_price( $child ) {
		return (float) $child->get_price();
	}

	/**
	 * Lowest total price (and regular total) for display.
	 *
	 * @param WC_Product_Wbd_Bundle $product Product.
	 * @return array { price, regular, variable }
	 */
	public static function price_range( $product ) {
		$key = $product->get_id() . ':' . $product->get_pricing_mode() . ':' . $product->get_discount_percent();
		if ( isset( self::$prices[ $key ] ) ) {
			return self::$prices[ $key ];
		}
		$factor   = 1 - $product->get_discount_percent() / 100;
		$regular  = 0.0;
		$variable = false;
		$found    = false;

		if ( $product->is_type( self::COMPOSITE ) ) {
			$base    = (float) $product->get_regular_price( 'edit' );
			$regular = $base;
			$items   = 0.0;
			foreach ( self::components( $product ) as $component ) {
				$found    = true;
				$variable = $variable || count( $component['options'] ) > 1 || ! $component['required'];
				if ( ! $component['required'] ) {
					continue;
				}
				$min = null;
				foreach ( $component['options'] as $option ) {
					$p   = self::child_price( $option ) * $component['qty'];
					$min = null === $min ? $p : min( $min, $p );
				}
				$items += (float) $min;
			}
			$regular += $items;
			$price    = $base + $items * $factor;
		} else {
			foreach ( self::bundle_items( $product ) as $item ) {
				$found    = true;
				$variable = $variable || $item['optional'];
				if ( ! $item['optional'] ) {
					$regular += self::child_price( $item['product'] ) * $item['qty'];
				}
			}
			$price = $regular * $factor;
		}

		$decimals              = wc_get_price_decimals();
		self::$prices[ $key ] = array(
			'price'    => $found ? round( $price, $decimals ) : '',
			'regular'  => $found ? round( $regular, $decimals ) : '',
			'variable' => $variable,
		);
		return self::$prices[ $key ];
	}

	/**
	 * Children chosen in a request: list of { id, qty }, or WP_Error.
	 *
	 * @param WC_Product $product Product.
	 * @param array      $data    Request data (unslashed).
	 * @return array|WP_Error
	 */
	public static function resolve( $product, $data ) {
		$children = array();
		if ( $product->is_type( self::COMPOSITE ) ) {
			$picked = isset( $data['wbd_component'] ) && is_array( $data['wbd_component'] ) ? $data['wbd_component'] : array();
			foreach ( self::components( $product ) as $i => $component ) {
				$id = isset( $picked[ $i ] ) ? absint( $picked[ $i ] ) : 0;
				if ( ! $id ) {
					if ( $component['required'] ) {
						/* translators: %s: component title. */
						return new WP_Error( 'wbd_required', sprintf( __( 'Please choose an option for “%s”.', 'wbd-product-benefits-blocks' ), $component['title'] ) );
					}
					continue;
				}
				if ( ! isset( $component['options'][ $id ] ) ) {
					return new WP_Error( 'wbd_invalid', __( 'One of the chosen options is not available. Please choose again.', 'wbd-product-benefits-blocks' ) );
				}
				$children[] = array(
					'id'  => $id,
					'qty' => $component['qty'],
				);
			}
		} else {
			$selected = isset( $data['wbd_items'] ) && is_array( $data['wbd_items'] ) ? $data['wbd_items'] : array();
			foreach ( self::bundle_items( $product ) as $i => $item ) {
				if ( $item['optional'] && empty( $selected[ $i ] ) ) {
					continue;
				}
				$children[] = array(
					'id'  => $item['id'],
					'qty' => $item['qty'],
				);
			}
		}
		if ( ! $children ) {
			return new WP_Error( 'wbd_empty', __( 'Please choose at least one product.', 'wbd-product-benefits-blocks' ) );
		}
		return $children;
	}

	/* Cart --------------------------------------------------------------- */

	/**
	 * Validates choices and stock before the parent is added.
	 *
	 * @param bool $passed     Passed so far.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity   Quantity.
	 * @return bool
	 */
	public static function validate( $passed, $product_id, $quantity ) {
		$product = wc_get_product( $product_id );
		if ( ! $passed || ! $product || ! self::is_ours( $product ) ) {
			return $passed;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- WooCommerce add-to-cart forms have no nonce; values are validated against the product definition.
		$data     = wp_unslash( $_REQUEST );
		$children = self::resolve( $product, is_array( $data ) ? $data : array() );
		if ( is_wp_error( $children ) ) {
			wc_add_notice( $children->get_error_message(), 'error' );
			return false;
		}
		foreach ( $children as $child ) {
			$c     = wc_get_product( $child['id'] );
			$total = $child['qty'] * max( 1, (int) $quantity );
			if ( ! $c || ! $c->is_purchasable() || ! $c->is_in_stock() || ! $c->has_enough_stock( $total ) ) {
				/* translators: %s: product name. */
				wc_add_notice( sprintf( __( '“%s” is not available in the quantity needed for this bundle.', 'wbd-product-benefits-blocks' ), $c ? $c->get_name() : '#' . $child['id'] ), 'error' );
				return false;
			}
		}
		self::$pending[ $product->get_id() ] = $children;
		return true;
	}

	/**
	 * Stores the resolved children on the parent cart item.
	 *
	 * @param array $data       Cart item data.
	 * @param int   $product_id Product ID.
	 * @return array
	 */
	public static function cart_item_data( $data, $product_id ) {
		if ( isset( self::$pending[ $product_id ] ) && empty( $data['wbd_bundled_by'] ) ) {
			$data['wbd_bundle'] = array( 'children' => self::$pending[ $product_id ] );
			unset( self::$pending[ $product_id ] );
		}
		return $data;
	}

	/**
	 * Keys of the child lines of a parent line. Children are found by their
	 * wbd_bundled_by link, so nothing extra has to survive the cart session
	 * (WooCommerce may store the session before a callback can add data).
	 *
	 * @param array  $contents   Cart contents (or removed contents).
	 * @param string $parent_key Parent key.
	 * @return string[]
	 */
	public static function child_keys( $contents, $parent_key ) {
		$keys = array();
		foreach ( (array) $contents as $key => $item ) {
			if ( isset( $item['wbd_bundled_by'] ) && $item['wbd_bundled_by'] === $parent_key ) {
				$keys[] = $key;
			}
		}
		return $keys;
	}

	/**
	 * Adds the child lines after the parent.
	 *
	 * @param string $key          Parent cart key.
	 * @param int    $product_id   Product ID.
	 * @param int    $quantity     Quantity added.
	 * @param int    $variation_id Variation ID.
	 * @param array  $variation    Attributes.
	 * @param array  $data         Cart item data.
	 */
	public static function add_children( $key, $product_id, $quantity, $variation_id, $variation, $data ) {
		if ( empty( $data['wbd_bundle']['children'] ) || self::$busy ) {
			return;
		}
		$cart = WC()->cart;
		// The same bundle was already in the cart: WooCommerce raised the parent
		// quantity and sync_quantity() already updated the children.
		if ( self::child_keys( $cart->cart_contents, $key ) ) {
			return;
		}
		self::$busy = true;
		$keys       = array();
		foreach ( $data['wbd_bundle']['children'] as $child ) {
			$c = wc_get_product( $child['id'] );
			if ( ! $c ) {
				continue;
			}
			$child_key = $cart->add_to_cart(
				$c->is_type( 'variation' ) ? $c->get_parent_id() : $c->get_id(),
				$child['qty'] * $quantity,
				$c->is_type( 'variation' ) ? $c->get_id() : 0,
				$c->is_type( 'variation' ) ? $c->get_variation_attributes() : array(),
				array(
					'wbd_bundled_by' => $key,
					'wbd_unit_qty'   => $child['qty'],
				)
			);
			if ( ! $child_key ) {
				// Roll back the whole set.
				foreach ( $keys as $k ) {
					$cart->remove_cart_item( $k );
				}
				$cart->remove_cart_item( $key );
				self::$busy = false;
				wc_add_notice( __( 'Sorry, this bundle could not be added to your cart.', 'wbd-product-benefits-blocks' ), 'error' );
				return;
			}
			$keys[] = $child_key;
		}
		self::$busy = false;
	}

	/**
	 * Applies the pricing mode to parent and children.
	 *
	 * @param WC_Cart $cart Cart.
	 */
	public static function set_prices( $cart ) {
		foreach ( $cart->get_cart() as $key => $item ) {
			$product = $item['data'];
			if ( ! empty( $item['wbd_bundle'] ) && self::is_ours( $product ) ) {
				$mode = $product->get_pricing_mode();
				if ( 'fixed' === $mode ) {
					$fresh = wc_get_product( $product->get_id() );
					$product->set_wbd_cart_price( $fresh ? (float) $fresh->get_price() : 0 );
				} elseif ( $product->is_type( self::COMPOSITE ) ) {
					$product->set_wbd_cart_price( (float) $product->get_regular_price( 'edit' ) );
				} else {
					$product->set_wbd_cart_price( 0 );
				}
			} elseif ( ! empty( $item['wbd_bundled_by'] ) && isset( $cart->cart_contents[ $item['wbd_bundled_by'] ] ) ) {
				$parent = $cart->cart_contents[ $item['wbd_bundled_by'] ]['data'];
				if ( ! self::is_ours( $parent ) ) {
					continue;
				}
				$fresh = wc_get_product( $product->get_id() );
				$price = $fresh ? self::child_price( $fresh ) : 0.0;
				if ( 'fixed' === $parent->get_pricing_mode() ) {
					$price = 0.0;
				} else {
					$price = $price * ( 1 - $parent->get_discount_percent() / 100 );
				}
				$product->set_price( round( $price, wc_get_price_decimals() ) );
			}
		}
	}

	/**
	 * Keeps child quantities in step with the parent.
	 *
	 * @param string $key      Updated item key.
	 * @param int    $quantity New quantity.
	 */
	public static function sync_quantity( $key, $quantity ) {
		if ( self::$busy ) {
			return;
		}
		$cart = WC()->cart;
		$item = $cart->get_cart_item( $key );
		if ( ! $item ) {
			return;
		}
		self::$busy = true;
		if ( ! empty( $item['wbd_bundle'] ) ) {
			foreach ( self::child_keys( $cart->cart_contents, $key ) as $child_key ) {
				$child = $cart->get_cart_item( $child_key );
				$cart->set_quantity( $child_key, $quantity * max( 1, (int) $child['wbd_unit_qty'] ), false );
			}
		} elseif ( ! empty( $item['wbd_bundled_by'] ) ) {
			// A child changed directly (e.g. Store API): put it back.
			$parent = $cart->get_cart_item( $item['wbd_bundled_by'] );
			if ( $parent ) {
				$cart->set_quantity( $key, $parent['quantity'] * max( 1, (int) $item['wbd_unit_qty'] ), false );
			}
		}
		self::$busy = false;
	}

	/**
	 * Removing the parent removes its children; removing a child removes the set.
	 *
	 * @param string  $key  Removed key.
	 * @param WC_Cart $cart Cart.
	 */
	public static function cascade_remove( $key, $cart ) {
		if ( self::$busy || empty( $cart->removed_cart_contents[ $key ] ) ) {
			return;
		}
		$item       = $cart->removed_cart_contents[ $key ];
		self::$busy = true;
		if ( ! empty( $item['wbd_bundle'] ) ) {
			foreach ( self::child_keys( $cart->cart_contents, $key ) as $child_key ) {
				$cart->remove_cart_item( $child_key );
			}
		} elseif ( ! empty( $item['wbd_bundled_by'] ) && isset( $cart->cart_contents[ $item['wbd_bundled_by'] ] ) ) {
			$parent_key = $item['wbd_bundled_by'];
			$siblings   = self::child_keys( $cart->cart_contents, $parent_key );
			$cart->remove_cart_item( $parent_key );
			foreach ( $siblings as $child_key ) {
				$cart->remove_cart_item( $child_key );
			}
		}
		self::$busy = false;
	}

	/**
	 * Undo of a removed parent restores its children.
	 *
	 * @param string  $key  Restored key.
	 * @param WC_Cart $cart Cart.
	 */
	public static function cascade_restore( $key, $cart ) {
		if ( self::$busy || empty( $cart->cart_contents[ $key ]['wbd_bundle'] ) ) {
			return;
		}
		self::$busy = true;
		foreach ( self::child_keys( $cart->removed_cart_contents, $key ) as $child_key ) {
			$cart->restore_cart_item( $child_key );
		}
		self::$busy = false;
	}

	/**
	 * Drops children whose parent is gone (and parents whose children are gone).
	 *
	 * @param WC_Cart $cart Cart.
	 */
	public static function remove_orphans( $cart ) {
		foreach ( $cart->cart_contents as $key => $item ) {
			if ( ! empty( $item['wbd_bundled_by'] ) && ! isset( $cart->cart_contents[ $item['wbd_bundled_by'] ] ) ) {
				unset( $cart->cart_contents[ $key ] );
			}
		}
		foreach ( $cart->cart_contents as $key => $item ) {
			if ( ! empty( $item['wbd_bundle'] ) && ! self::child_keys( $cart->cart_contents, $key ) ) {
				unset( $cart->cart_contents[ $key ] );
			}
		}
	}

	/**
	 * Parent of a child cart item.
	 *
	 * @param array $item Cart item.
	 * @return array|null
	 */
	private static function parent_of( $item ) {
		if ( empty( $item['wbd_bundled_by'] ) || ! WC()->cart ) {
			return null;
		}
		$parent = WC()->cart->get_cart_item( $item['wbd_bundled_by'] );
		return $parent ? $parent : null;
	}

	/**
	 * Row classes.
	 *
	 * @param string $class Class.
	 * @param array  $item  Item.
	 * @return string
	 */
	public static function item_class( $class, $item ) {
		if ( ! empty( $item['wbd_bundled_by'] ) ) {
			$class .= ' wbd-bundled-item';
		} elseif ( ! empty( $item['wbd_bundle'] ) ) {
			$class .= ' wbd-bundle-parent';
		}
		return $class;
	}

	/**
	 * Indents child names.
	 *
	 * @param string $name Name HTML.
	 * @param array  $item Item.
	 * @return string
	 */
	public static function item_name( $name, $item ) {
		return ! empty( $item['wbd_bundled_by'] ) ? '<span class="wbd-bundled-item__mark" aria-hidden="true">&#8627;</span> ' . $name : $name;
	}

	/**
	 * Children quantity is not editable.
	 *
	 * @param string $html Quantity HTML.
	 * @param string $key  Key.
	 * @param array  $item Item.
	 * @return string
	 */
	public static function item_quantity( $html, $key, $item = array() ) {
		if ( empty( $item ) && WC()->cart ) {
			$item = WC()->cart->get_cart_item( $key );
		}
		return ! empty( $item['wbd_bundled_by'] ) ? '<span class="wbd-bundled-qty">' . esc_html( (int) $item['quantity'] ) . '</span>' : $html;
	}

	/**
	 * Children cannot be removed on their own.
	 *
	 * @param string $link Link.
	 * @param string $key  Key.
	 * @return string
	 */
	public static function item_remove_link( $link, $key ) {
		$item = WC()->cart ? WC()->cart->get_cart_item( $key ) : null;
		return ! empty( $item['wbd_bundled_by'] ) ? '' : $link;
	}

	/**
	 * "Included" instead of a zero price for children of fixed-price sets.
	 *
	 * @param string $html Price HTML.
	 * @param array  $item Item.
	 * @return string
	 */
	public static function item_price_html( $html, $item ) {
		$parent = self::parent_of( $item );
		if ( $parent && self::is_ours( $parent['data'] ) && 'fixed' === $parent['data']->get_pricing_mode() ) {
			return '<span class="wbd-included">' . esc_html__( 'Included', 'wbd-product-benefits-blocks' ) . '</span>';
		}
		return $html;
	}

	/**
	 * "Part of: Bundle name" on child lines (also shown by the Cart block).
	 *
	 * @param array $data Item data.
	 * @param array $item Cart item.
	 * @return array
	 */
	public static function item_data( $data, $item ) {
		$parent = self::parent_of( $item );
		if ( $parent ) {
			$data[] = array(
				'key'   => __( 'Part of', 'wbd-product-benefits-blocks' ),
				'value' => $parent['data']->get_name(),
			);
		}
		return $data;
	}

	/**
	 * Cart block: children quantity is fixed.
	 *
	 * @param bool       $editable Editable.
	 * @param WC_Product $product  Product.
	 * @param array      $item     Cart item.
	 * @return bool
	 */
	public static function store_api_editable( $editable, $product, $item = array() ) {
		return ! empty( $item['wbd_bundled_by'] ) ? false : $editable;
	}

	/**
	 * Order line meta.
	 *
	 * @param WC_Order_Item_Product $order_item Item.
	 * @param string                $key        Cart key.
	 * @param array                 $values     Cart item.
	 */
	public static function order_item_meta( $order_item, $key, $values ) {
		if ( ! empty( $values['wbd_bundled_by'] ) ) {
			$parent = self::parent_of( $values );
			$order_item->add_meta_data( '_wbd_bundled_by', $values['wbd_bundled_by'], true );
			if ( $parent ) {
				$order_item->add_meta_data( __( 'Part of', 'wbd-product-benefits-blocks' ), $parent['data']->get_name(), true );
			}
		} elseif ( ! empty( $values['wbd_bundle'] ) ) {
			$order_item->add_meta_data( '_wbd_bundle_key', $key, true );
		}
	}

	/* Assets ------------------------------------------------------------- */

	/**
	 * Frontend style + total calculator on our product pages and the cart.
	 */
	public static function frontend_assets() {
		$css = WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'assets/frontend/bundles.css';
		wp_register_style( 'wbd-pbb-bundles', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/bundles.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION . '.' . ( file_exists( $css ) ? filemtime( $css ) : '' ) );
		// Registered everywhere (Quick View may need it), enqueued only where used.
		wp_register_script( 'wbd-pbb-bundles', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/bundles.js', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		self::localize_frontend();
		if ( ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) ) {
			wp_enqueue_style( 'wbd-pbb-bundles' );
		}
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product || ! self::is_ours( $product ) ) {
			return;
		}
		wp_enqueue_style( 'wbd-pbb-bundles' );
		wp_enqueue_script( 'wbd-pbb-bundles' );
	}

	/**
	 * Whether any bundle-type product type is enabled.
	 *
	 * @return bool
	 */
	public static function has_types() {
		return (bool) self::$types;
	}

	/**
	 * Price format data for the total calculator.
	 */
	private static function localize_frontend() {
		wp_localize_script(
			'wbd-pbb-bundles',
			'wbdPbbBundles',
			array(
				'decimals'  => wc_get_price_decimals(),
				'decimal'   => wc_get_price_decimal_separator(),
				'thousand'  => wc_get_price_thousand_separator(),
				'format'    => html_entity_decode( get_woocommerce_price_format(), ENT_QUOTES, 'UTF-8' ),
				'symbol'    => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
				'required'  => __( 'Please choose an option for every required step.', 'wbd-product-benefits-blocks' ),
				'nothing'   => __( 'Please choose at least one product.', 'wbd-product-benefits-blocks' ),
			)
		);
	}

	/**
	 * Shared admin script/style on the product screen.
	 *
	 * @param string $hook Hook.
	 */
	public static function admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_style( 'wbd-pbb-bundles-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/bundles.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_enqueue_script( 'wbd-pbb-bundles-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/bundles.js', array( 'jquery', 'jquery-ui-sortable', 'wc-enhanced-select' ), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script( 'wbd-pbb-bundles-admin', 'wbdPbbBundlesAdmin', array( 'types' => array_keys( self::$types ) ) );
	}

	/* Shared admin field helpers ------------------------------------------ */

	/**
	 * Product search select (WooCommerce enhanced select).
	 *
	 * @param string $name     Field name.
	 * @param int[]  $selected Selected IDs.
	 * @param bool   $multiple Multiple.
	 * @param string $label    Accessible label.
	 */
	public static function product_select( $name, $selected, $multiple, $label ) {
		printf(
			'<select class="wc-product-search wbd-product-search" %1$s name="%2$s" data-placeholder="%3$s" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="variable,grouped,external,wbd_bundle,wbd_composite" aria-label="%4$s" style="width:100%%">',
			$multiple ? 'multiple="multiple"' : '',
			esc_attr( $name ),
			esc_attr__( 'Search for a product…', 'wbd-product-benefits-blocks' ),
			esc_attr( $label )
		);
		foreach ( (array) $selected as $id ) {
			$p = wc_get_product( $id );
			if ( $p ) {
				printf( '<option value="%d" selected="selected">%s</option>', (int) $id, esc_html( wp_strip_all_tags( $p->get_formatted_name() ) ) );
			}
		}
		echo '</select>';
	}

	/**
	 * Pricing fields shared by both types.
	 *
	 * @param WC_Product $product Product (may be any type while editing).
	 * @param string     $prefix  Field prefix.
	 * @param bool       $composite Composite wording.
	 */
	public static function pricing_fields( $product, $prefix, $composite ) {
		$mode     = $product->get_meta( '_wbd_pricing', true, 'edit' );
		$mode     = $mode ? $mode : 'auto';
		$discount = $product->get_meta( '_wbd_discount', true, 'edit' );
		woocommerce_wp_select(
			array(
				'id'          => $prefix . '_pricing',
				'name'        => $prefix . '_pricing',
				'label'       => __( 'Price', 'wbd-product-benefits-blocks' ),
				'value'       => $mode,
				'options'     => array(
					'auto'          => $composite ? __( 'Base price + chosen products', 'wbd-product-benefits-blocks' ) : __( 'Sum of the products', 'wbd-product-benefits-blocks' ),
					'auto_discount' => $composite ? __( 'Base price + chosen products with a discount', 'wbd-product-benefits-blocks' ) : __( 'Sum of the products with a discount', 'wbd-product-benefits-blocks' ),
					'fixed'         => __( 'Fixed price (set on the General tab)', 'wbd-product-benefits-blocks' ),
				),
				'desc_tip'    => true,
				'description' => $composite ? __( 'The “Regular price” on the General tab is the base price (can be 0).', 'wbd-product-benefits-blocks' ) : __( 'With a fixed price, the products are included for free.', 'wbd-product-benefits-blocks' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => $prefix . '_discount',
				'name'              => $prefix . '_discount',
				'label'             => __( 'Discount (%)', 'wbd-product-benefits-blocks' ),
				'value'             => $discount,
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '0.01',
				),
				'wrapper_class'     => 'wbd-discount-field',
			)
		);
	}

	/**
	 * Saves pricing fields.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $prefix  Prefix.
	 */
	public static function save_pricing( $product, $prefix ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verified the product meta box nonce.
		$mode = isset( $_POST[ $prefix . '_pricing' ] ) ? sanitize_key( wp_unslash( $_POST[ $prefix . '_pricing' ] ) ) : 'auto';
		$product->update_meta_data( '_wbd_pricing', in_array( $mode, array( 'auto', 'auto_discount', 'fixed' ), true ) ? $mode : 'auto' );
		$discount = isset( $_POST[ $prefix . '_discount' ] ) ? (float) wc_format_decimal( wp_unslash( $_POST[ $prefix . '_discount' ] ) ) : 0;
		$product->update_meta_data( '_wbd_discount', min( 100, max( 0, $discount ) ) );
		// phpcs:enable
	}

	/**
	 * Stores the computed lowest price in _price so sorting and price filters work.
	 *
	 * @param WC_Product $product Product.
	 */
	public static function sync_lookup_price( $product ) {
		if ( ! self::is_ours( $product ) || 'fixed' === $product->get_pricing_mode() ) {
			return;
		}
		self::$prices = array();
		$range        = self::price_range( $product );
		if ( '' !== $range['price'] ) {
			update_post_meta( $product->get_id(), '_price', wc_format_decimal( $range['price'] ) );
		}
	}
}
