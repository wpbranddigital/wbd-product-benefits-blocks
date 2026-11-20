<?php
/**
 * Product Bundles — sell several products together, with optional items and a
 * bundle discount.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bundles module.
 */
final class WBD_PBB_Module_Bundles extends WBD_PBB_Kit_Module {

	public function slug() {
		return 'bundles';
	}

	public function title() {
		return __( 'Product Bundles', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Sell products together as one bundle — fixed price or sum with a discount, optional add-ons, stock kept per product.', 'wbd-product-benefits-blocks' );
	}

	public function fields() {
		return self::display_fields();
	}

	/**
	 * Display fields shared with Composite.
	 *
	 * @return array
	 */
	public static function display_fields() {
		return array(
			'show_thumbnails' => array(
				'type'    => 'checkbox',
				'section' => __( 'Show', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Product images', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_prices'     => array(
				'type'    => 'checkbox',
				'label'   => __( 'Price of each product', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_stock'      => array(
				'type'    => 'checkbox',
				'label'   => __( 'Stock status of each product', 'wbd-product-benefits-blocks' ),
				'default' => 0,
			),
			'show_savings'    => array(
				'type'    => 'checkbox',
				'label'   => __( '“You save” amount', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'link_items'      => array(
				'type'    => 'checkbox',
				'label'   => __( 'Link product names to their pages', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'total_label'     => array(
				'type'    => 'text',
				'label'   => __( 'Total label', 'wbd-product-benefits-blocks' ),
				'default' => __( 'Total', 'wbd-product-benefits-blocks' ),
			),
		);
	}

	public function help() {
		return array(
			__( 'Go to <strong>Products → Add New</strong>. In <strong>Product data</strong>, choose <strong>Bundle (WBD)</strong>.', 'wbd-product-benefits-blocks' ),
			__( 'Open the <strong>Bundled products</strong> tab, click <strong>Add product</strong>, search for a product and set the quantity. Tick <em>Optional</em> to let shoppers remove it.', 'wbd-product-benefits-blocks' ),
			__( 'Choose the price: the sum of the products, the sum with a discount (e.g. 10%), or a fixed price you enter on the General tab.', 'wbd-product-benefits-blocks' ),
			__( 'Publish. The bundle page lists the products with a live total. In the cart each product appears under the bundle, and stock is reduced per product.', 'wbd-product-benefits-blocks' ),
			__( 'Simple products and specific variations (e.g. “Shirt – Red”) can be bundled.', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		WBD_PBB_Bundle_Engine::register_type( WBD_PBB_Bundle_Engine::BUNDLE, __( 'Bundle (WBD)', 'wbd-product-benefits-blocks' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'data_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'after_save' ), 99 );
		add_action( 'woocommerce_wbd_bundle_add_to_cart', array( $this, 'render_form' ) );
	}

	/**
	 * Product data tab.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function data_tab( $tabs ) {
		$tabs['wbd_bundle'] = array(
			'label'    => __( 'Bundled products', 'wbd-product-benefits-blocks' ),
			'target'   => 'wbd_bundle_data',
			'class'    => array( 'show_if_wbd_bundle' ),
			'priority' => 15,
		);
		return $tabs;
	}

	/**
	 * Admin row.
	 *
	 * @param string|int $i   Index.
	 * @param array      $row Row.
	 */
	private function row( $i, $row ) {
		$base = 'wbd_bundle_items[' . $i . ']';
		?>
		<tr class="wbd-items-admin__row">
			<td class="wbd-items-admin__handle"><span class="dashicons dashicons-menu" aria-hidden="true"></span></td>
			<td class="wbd-items-admin__product"><?php WBD_PBB_Bundle_Engine::product_select( $base . '[id]', ! empty( $row['id'] ) ? array( $row['id'] ) : array(), false, __( 'Product', 'wbd-product-benefits-blocks' ) ); ?></td>
			<td class="wbd-items-admin__qty"><input type="number" min="1" step="1" name="<?php echo esc_attr( $base . '[qty]' ); ?>" value="<?php echo esc_attr( ! empty( $row['qty'] ) ? $row['qty'] : 1 ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'wbd-product-benefits-blocks' ); ?>" /></td>
			<td class="wbd-items-admin__optional"><label><input type="checkbox" name="<?php echo esc_attr( $base . '[optional]' ); ?>" value="1" <?php checked( ! empty( $row['optional'] ) ); ?> /> <?php esc_html_e( 'Optional', 'wbd-product-benefits-blocks' ); ?></label></td>
			<td><button type="button" class="button-link button-link-delete wbd-items-admin__remove" aria-label="<?php esc_attr_e( 'Remove', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button></td>
		</tr>
		<?php
	}

	/**
	 * Product data panel.
	 */
	public function data_panel() {
		global $post;
		$product = wc_get_product( $post ? $post->ID : 0 );
		$meta    = $product ? $product->get_meta( '_wbd_bundle_items', true, 'edit' ) : array();
		$rows    = is_array( $meta ) ? array_filter( $meta, 'is_array' ) : array();
		?>
		<div id="wbd_bundle_data" class="panel woocommerce_options_panel hidden">
			<div class="wbd-items-admin" data-wbd-repeater>
				<p class="wbd-items-admin__intro"><?php esc_html_e( 'Add the products in this bundle. Drag rows to change their order.', 'wbd-product-benefits-blocks' ); ?> <a href="<?php echo esc_url( WBD_PBB_Kit::url( $this->slug() ) ); ?>"><?php esc_html_e( 'Display settings', 'wbd-product-benefits-blocks' ); ?></a></p>
				<table class="widefat wbd-items-admin__table">
					<thead><tr><th></th><th><?php esc_html_e( 'Product', 'wbd-product-benefits-blocks' ); ?></th><th><?php esc_html_e( 'Qty', 'wbd-product-benefits-blocks' ); ?></th><th></th><th></th></tr></thead>
					<tbody class="wbd-items-admin__list" data-next-index="<?php echo esc_attr( count( $rows ) ); ?>">
						<?php
						foreach ( array_values( $rows ) as $i => $row ) {
							$this->row( $i, $row );
						}
						?>
					</tbody>
				</table>
				<p><button type="button" class="button wbd-items-admin__add"><?php esc_html_e( 'Add product', 'wbd-product-benefits-blocks' ); ?></button></p>
				<template class="wbd-items-admin__template"><?php $this->row( '__i__', array() ); ?></template>
				<div class="options_group">
					<?php
					if ( $product ) {
						WBD_PBB_Bundle_Engine::pricing_fields( $product, 'wbd_bundle', false );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves bundle data (WooCommerce verified nonce and capability).
	 *
	 * @param WC_Product $product Product.
	 */
	public function save( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verified the product meta box nonce.
		if ( ! isset( $_POST['product-type'] ) || WBD_PBB_Bundle_Engine::BUNDLE !== sanitize_key( wp_unslash( $_POST['product-type'] ) ) ) {
			return;
		}
		$raw   = isset( $_POST['wbd_bundle_items'] ) && is_array( $_POST['wbd_bundle_items'] ) ? wp_unslash( $_POST['wbd_bundle_items'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated below.
		$items = array();
		foreach ( $raw as $row ) {
			$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			if ( ! $id || $id === $product->get_id() || ! WBD_PBB_Bundle_Engine::is_includable( wc_get_product( $id ) ) ) {
				continue;
			}
			$items[] = array(
				'id'       => $id,
				'qty'      => max( 1, min( 999, absint( isset( $row['qty'] ) ? $row['qty'] : 1 ) ) ),
				'optional' => ! empty( $row['optional'] ) ? 1 : 0,
			);
			if ( count( $items ) >= WBD_PBB_Bundle_Engine::MAX_ITEMS ) {
				break;
			}
		}
		$product->update_meta_data( '_wbd_bundle_items', $items );
		WBD_PBB_Bundle_Engine::save_pricing( $product, 'wbd_bundle' );
		// phpcs:enable
	}

	/**
	 * Updates the stored lowest price after saving.
	 *
	 * @param int $post_id Product ID.
	 */
	public function after_save( $post_id ) {
		$product = wc_get_product( $post_id );
		if ( $product && $product->is_type( WBD_PBB_Bundle_Engine::BUNDLE ) ) {
			WBD_PBB_Bundle_Engine::sync_lookup_price( $product );
		}
	}

	/**
	 * Formatted price of an included product for display.
	 *
	 * @param WC_Product $child Child.
	 * @param int        $qty   Quantity.
	 * @return string
	 */
	public static function child_price_html( $child, $qty ) {
		$html = wc_price( wc_get_price_to_display( $child, array( 'qty' => $qty ) ) );
		return $qty > 1 ? $html . ' <span class="wbd-bundle__each">(' . esc_html( $qty ) . ' &times; ' . wp_kses_post( wc_price( wc_get_price_to_display( $child ) ) ) . ')</span>' : $html;
	}

	/**
	 * Shared summary: total, savings, quantity and button.
	 *
	 * @param WC_Product_Wbd_Bundle $product  Product.
	 * @param array                 $settings Display settings.
	 */
	public static function render_footer( $product, $settings ) {
		$range = WBD_PBB_Bundle_Engine::price_range( $product );
		?>
		<div class="wbd-bundle__total" aria-live="polite">
			<span class="wbd-bundle__total-label"><?php echo esc_html( $settings['total_label'] ? $settings['total_label'] : __( 'Total', 'wbd-product-benefits-blocks' ) ); ?>:</span>
			<strong class="wbd-bundle__total-price"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></strong>
			<?php if ( $settings['show_savings'] ) : ?>
				<span class="wbd-bundle__save" <?php echo ( '' !== $range['regular'] && $range['price'] < $range['regular'] ) ? '' : 'hidden'; ?>><?php esc_html_e( 'You save', 'wbd-product-benefits-blocks' ); ?> <span class="wbd-bundle__save-amount"><?php echo wp_kses_post( wc_price( max( 0, (float) $range['regular'] - (float) $range['price'] ) ) ); ?></span></span>
			<?php endif; ?>
		</div>
		<p class="wbd-bundle__error" role="alert" hidden></p>
		<div class="wbd-bundle__actions">
			<?php
			do_action( 'woocommerce_before_add_to_cart_button' );
			woocommerce_quantity_input(
				array(
					'min_value'   => 1,
					'max_value'   => $product->get_max_purchase_quantity(),
					'input_value' => 1,
				)
			);
			?>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
			<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
		</div>
		<?php
	}

	/**
	 * Data attributes the total calculator needs.
	 *
	 * @param WC_Product_Wbd_Bundle $product Product.
	 * @return string
	 */
	public static function data_attrs( $product ) {
		$mode = $product->get_pricing_mode();
		return sprintf(
			'data-wbd-bundle data-mode="%1$s" data-discount="%2$s" data-base="%3$s" data-fixed="%4$s"',
			esc_attr( $mode ),
			esc_attr( $product->get_discount_percent() ),
			esc_attr( $product->is_type( WBD_PBB_Bundle_Engine::COMPOSITE ) && 'fixed' !== $mode ? wc_get_price_to_display( $product, array( 'price' => (float) $product->get_regular_price( 'edit' ) ) ) : 0 ),
			esc_attr( 'fixed' === $mode ? wc_get_price_to_display( $product ) : '' )
		);
	}

	/**
	 * Product page: bundle contents and add to cart.
	 */
	public function render_form() {
		global $product;
		if ( ! $product instanceof WC_Product || ! $product->is_type( WBD_PBB_Bundle_Engine::BUNDLE ) ) {
			return;
		}
		$items    = WBD_PBB_Bundle_Engine::bundle_items( $product );
		$settings = $this->get_settings();
		if ( ! $items ) {
			return;
		}
		$available = $product->is_purchasable() && $product->is_in_stock();
		foreach ( $items as $item ) {
			if ( ! $item['optional'] && ( ! $item['product']->is_purchasable() || ! $item['product']->is_in_stock() ) ) {
				$available = false;
			}
		}

		do_action( 'woocommerce_before_add_to_cart_form' );
		?>
		<form class="cart wbd-bundle" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" <?php echo self::data_attrs( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in data_attrs(). ?>>
			<ul class="wbd-bundle__items">
				<?php foreach ( $items as $i => $item ) : ?>
					<?php
					$child = $item['product'];
					$in    = $child->is_purchasable() && $child->is_in_stock();
					?>
					<li class="wbd-bundle__item<?php echo $item['optional'] ? ' is-optional' : ''; ?><?php echo $in ? '' : ' is-unavailable'; ?>" data-price="<?php echo esc_attr( wc_get_price_to_display( $child ) ); ?>" data-qty="<?php echo esc_attr( $item['qty'] ); ?>">
						<?php if ( $item['optional'] ) : ?>
							<input type="checkbox" class="wbd-bundle__toggle" id="wbd-item-<?php echo esc_attr( $i ); ?>" name="wbd_items[<?php echo esc_attr( $i ); ?>]" value="1" <?php disabled( ! $in ); ?> />
						<?php endif; ?>
						<?php if ( $settings['show_thumbnails'] ) : ?>
							<span class="wbd-bundle__thumb"><?php echo wp_kses_post( $child->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></span>
						<?php endif; ?>
						<span class="wbd-bundle__info">
							<?php if ( $item['optional'] ) : ?>
								<label class="wbd-bundle__name" for="wbd-item-<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $child->get_name() ); ?></label>
							<?php elseif ( $settings['link_items'] && $child->is_visible() ) : ?>
								<a class="wbd-bundle__name" href="<?php echo esc_url( $child->get_permalink() ); ?>"><?php echo esc_html( $child->get_name() ); ?></a>
							<?php else : ?>
								<span class="wbd-bundle__name"><?php echo esc_html( $child->get_name() ); ?></span>
							<?php endif; ?>
							<span class="wbd-bundle__meta">
								<span class="wbd-bundle__qty">&times; <?php echo esc_html( $item['qty'] ); ?></span>
								<?php if ( $item['optional'] ) : ?>
									<span class="wbd-bundle__badge"><?php esc_html_e( 'Optional', 'wbd-product-benefits-blocks' ); ?></span>
								<?php endif; ?>
								<?php if ( $settings['show_stock'] || ! $in ) : ?>
									<span class="wbd-bundle__stock"><?php echo wp_kses_post( $in ? wc_get_stock_html( $child ) : __( 'Out of stock', 'woocommerce' ) ); ?></span>
								<?php endif; ?>
							</span>
						</span>
						<?php if ( $settings['show_prices'] ) : ?>
							<span class="wbd-bundle__price"><?php echo wp_kses_post( self::child_price_html( $child, $item['qty'] ) ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $available ) : ?>
				<?php self::render_footer( $product, $settings ); ?>
			<?php else : ?>
				<p class="stock out-of-stock"><?php esc_html_e( 'This bundle is currently unavailable.', 'wbd-product-benefits-blocks' ); ?></p>
			<?php endif; ?>
		</form>
		<?php
		do_action( 'woocommerce_after_add_to_cart_form' );
	}
}
