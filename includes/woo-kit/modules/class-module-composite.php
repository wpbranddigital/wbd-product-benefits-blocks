<?php
/**
 * Composite Products — shoppers build a product by choosing one option in
 * each component (e.g. frame, lens, case).
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composite module.
 */
final class WBD_PBB_Module_Composite extends WBD_PBB_Kit_Module {

	const MAX_COMPONENTS = 10;

	public function slug() {
		return 'composite';
	}

	public function title() {
		return __( 'Composite Products', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Let shoppers build their own product: one choice per component (e.g. PC parts, gift boxes, menus) with a live total.', 'wbd-product-benefits-blocks' );
	}

	public function fields() {
		$fields = WBD_PBB_Module_Bundles::display_fields();
		unset( $fields['link_items'] );
		$fields['none_text'] = array(
			'type'    => 'text',
			'label'   => __( '“No thanks” text for optional components', 'wbd-product-benefits-blocks' ),
			'default' => __( 'No, thanks', 'wbd-product-benefits-blocks' ),
		);
		return $fields;
	}

	public function help() {
		return array(
			__( 'Go to <strong>Products → Add New</strong>. In <strong>Product data</strong>, choose <strong>Composite (WBD)</strong>.', 'wbd-product-benefits-blocks' ),
			__( 'Open <strong>Components</strong> and click <strong>Add component</strong>. Give it a title (e.g. “Choose a case”), add the products shoppers can pick from, and tick <em>Required</em> if a choice is mandatory.', 'wbd-product-benefits-blocks' ),
			__( 'Pick the pricing: base price + chosen products (optionally discounted) or one fixed price. The base price is the Regular price on the General tab and can be 0.', 'wbd-product-benefits-blocks' ),
			__( 'Publish. Shoppers choose one option per component, see the total update live, and each chosen product is added to the cart under the composite.', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		WBD_PBB_Bundle_Engine::register_type( WBD_PBB_Bundle_Engine::COMPOSITE, __( 'Composite (WBD)', 'wbd-product-benefits-blocks' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'data_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'after_save' ), 99 );
		add_action( 'woocommerce_wbd_composite_add_to_cart', array( $this, 'render_form' ) );
	}

	/**
	 * Product data tab.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function data_tab( $tabs ) {
		$tabs['wbd_composite'] = array(
			'label'    => __( 'Components', 'wbd-product-benefits-blocks' ),
			'target'   => 'wbd_composite_data',
			'class'    => array( 'show_if_wbd_composite' ),
			'priority' => 15,
		);
		return $tabs;
	}

	/**
	 * One component card in admin.
	 *
	 * @param string|int $i   Index.
	 * @param array      $row Row.
	 */
	private function component( $i, $row ) {
		$base = 'wbd_components[' . $i . ']';
		?>
		<div class="wbd-component-admin">
			<div class="wbd-component-admin__head">
				<span class="dashicons dashicons-menu wbd-items-admin__handle" aria-hidden="true"></span>
				<input type="text" class="wbd-component-admin__title" name="<?php echo esc_attr( $base . '[title]' ); ?>" value="<?php echo esc_attr( isset( $row['title'] ) ? $row['title'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Component title, e.g. Choose a case', 'wbd-product-benefits-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Component title', 'wbd-product-benefits-blocks' ); ?>" />
				<button type="button" class="button-link button-link-delete wbd-items-admin__remove" aria-label="<?php esc_attr_e( 'Remove component', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
			</div>
			<div class="wbd-component-admin__options">
				<?php WBD_PBB_Bundle_Engine::product_select( $base . '[options][]', isset( $row['options'] ) ? (array) $row['options'] : array(), true, __( 'Products to choose from', 'wbd-product-benefits-blocks' ) ); ?>
			</div>
			<div class="wbd-component-admin__row">
				<label><input type="checkbox" name="<?php echo esc_attr( $base . '[required]' ); ?>" value="1" <?php checked( ! isset( $row['required'] ) || ! empty( $row['required'] ) ); ?> /> <?php esc_html_e( 'Required', 'wbd-product-benefits-blocks' ); ?></label>
				<label><?php esc_html_e( 'Quantity', 'wbd-product-benefits-blocks' ); ?> <input type="number" min="1" step="1" class="small-text" name="<?php echo esc_attr( $base . '[qty]' ); ?>" value="<?php echo esc_attr( ! empty( $row['qty'] ) ? $row['qty'] : 1 ); ?>" /></label>
			</div>
		</div>
		<?php
	}

	/**
	 * Product data panel.
	 */
	public function data_panel() {
		global $post;
		$product = wc_get_product( $post ? $post->ID : 0 );
		$meta    = $product ? $product->get_meta( '_wbd_components', true, 'edit' ) : array();
		$rows    = is_array( $meta ) ? array_values( array_filter( $meta, 'is_array' ) ) : array();
		?>
		<div id="wbd_composite_data" class="panel woocommerce_options_panel hidden">
			<div class="wbd-items-admin" data-wbd-repeater>
				<p class="wbd-items-admin__intro"><?php esc_html_e( 'Each component is one step where the shopper picks a product.', 'wbd-product-benefits-blocks' ); ?> <a href="<?php echo esc_url( WBD_PBB_Kit::url( $this->slug() ) ); ?>"><?php esc_html_e( 'Display settings', 'wbd-product-benefits-blocks' ); ?></a></p>
				<div class="wbd-items-admin__list" data-next-index="<?php echo esc_attr( count( $rows ) ); ?>">
					<?php
					foreach ( $rows as $i => $row ) {
						$this->component( $i, $row );
					}
					?>
				</div>
				<p><button type="button" class="button wbd-items-admin__add"><?php esc_html_e( 'Add component', 'wbd-product-benefits-blocks' ); ?></button></p>
				<template class="wbd-items-admin__template"><?php $this->component( '__i__', array() ); ?></template>
				<div class="options_group">
					<?php
					if ( $product ) {
						WBD_PBB_Bundle_Engine::pricing_fields( $product, 'wbd_composite', true );
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves components (WooCommerce verified nonce and capability).
	 *
	 * @param WC_Product $product Product.
	 */
	public function save( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verified the product meta box nonce.
		if ( ! isset( $_POST['product-type'] ) || WBD_PBB_Bundle_Engine::COMPOSITE !== sanitize_key( wp_unslash( $_POST['product-type'] ) ) ) {
			return;
		}
		$raw        = isset( $_POST['wbd_components'] ) && is_array( $_POST['wbd_components'] ) ? wp_unslash( $_POST['wbd_components'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated below.
		$components = array();
		foreach ( $raw as $row ) {
			$options = array();
			foreach ( isset( $row['options'] ) ? (array) $row['options'] : array() as $id ) {
				$id = absint( $id );
				if ( $id && $id !== $product->get_id() && ! in_array( $id, $options, true ) && WBD_PBB_Bundle_Engine::is_includable( wc_get_product( $id ) ) ) {
					$options[] = $id;
				}
			}
			if ( ! $options ) {
				continue;
			}
			$components[] = array(
				'title'    => isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '',
				'options'  => array_slice( $options, 0, 50 ),
				'required' => ! empty( $row['required'] ) ? 1 : 0,
				'qty'      => max( 1, min( 999, absint( isset( $row['qty'] ) ? $row['qty'] : 1 ) ) ),
			);
			if ( count( $components ) >= self::MAX_COMPONENTS ) {
				break;
			}
		}
		$product->update_meta_data( '_wbd_components', $components );
		WBD_PBB_Bundle_Engine::save_pricing( $product, 'wbd_composite' );
		// phpcs:enable
	}

	/**
	 * Updates the stored lowest price after saving.
	 *
	 * @param int $post_id Product ID.
	 */
	public function after_save( $post_id ) {
		$product = wc_get_product( $post_id );
		if ( $product && $product->is_type( WBD_PBB_Bundle_Engine::COMPOSITE ) ) {
			WBD_PBB_Bundle_Engine::sync_lookup_price( $product );
		}
	}

	/**
	 * Product page: the component steps and add to cart.
	 */
	public function render_form() {
		global $product;
		if ( ! $product instanceof WC_Product || ! $product->is_type( WBD_PBB_Bundle_Engine::COMPOSITE ) ) {
			return;
		}
		$components = WBD_PBB_Bundle_Engine::components( $product );
		$settings   = $this->get_settings();
		if ( ! $components ) {
			return;
		}

		do_action( 'woocommerce_before_add_to_cart_form' );
		?>
		<form class="cart wbd-bundle wbd-composite" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" <?php echo WBD_PBB_Module_Bundles::data_attrs( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in data_attrs(). ?>>
			<?php foreach ( $components as $i => $component ) : ?>
				<fieldset class="wbd-composite__step" data-qty="<?php echo esc_attr( $component['qty'] ); ?>">
					<legend class="wbd-composite__title">
						<span class="wbd-composite__number"><?php echo esc_html( $i + 1 ); ?></span>
						<?php echo esc_html( $component['title'] ); ?>
						<?php if ( $component['qty'] > 1 ) : ?>
							<span class="wbd-composite__qty">&times; <?php echo esc_html( $component['qty'] ); ?></span>
						<?php endif; ?>
						<?php if ( $component['required'] ) : ?>
							<span class="wbd-composite__required"><?php esc_html_e( 'Required', 'wbd-product-benefits-blocks' ); ?></span>
						<?php endif; ?>
					</legend>
					<div class="wbd-composite__options">
						<?php if ( ! $component['required'] ) : ?>
							<label class="wbd-option wbd-option--none">
								<input type="radio" name="wbd_component[<?php echo esc_attr( $i ); ?>]" value="0" data-price="0" checked />
								<span class="wbd-option__name"><?php echo esc_html( $settings['none_text'] ? $settings['none_text'] : __( 'No, thanks', 'wbd-product-benefits-blocks' ) ); ?></span>
							</label>
						<?php endif; ?>
						<?php
						// Required steps start on the cheapest available option, so the
						// first total shown matches the "From" price.
						$preselect = 0;
						if ( $component['required'] ) {
							$cheapest = null;
							foreach ( $component['options'] as $candidate_id => $candidate ) {
								if ( $candidate->is_purchasable() && $candidate->is_in_stock() && ( null === $cheapest || (float) $candidate->get_price() < $cheapest ) ) {
									$cheapest  = (float) $candidate->get_price();
									$preselect = $candidate_id;
								}
							}
						}
						?>
						<?php foreach ( $component['options'] as $id => $option ) : ?>
							<?php
							$in      = $option->is_purchasable() && $option->is_in_stock();
							$checked = $preselect === $id;
							?>
							<label class="wbd-option<?php echo $in ? '' : ' is-unavailable'; ?>">
								<input type="radio" name="wbd_component[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $id ); ?>" data-price="<?php echo esc_attr( wc_get_price_to_display( $option ) ); ?>" <?php checked( $checked ); ?> <?php disabled( ! $in ); ?> <?php echo $component['required'] ? 'required' : ''; ?> />
								<?php if ( $settings['show_thumbnails'] ) : ?>
									<span class="wbd-option__thumb"><?php echo wp_kses_post( $option->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></span>
								<?php endif; ?>
								<span class="wbd-option__name"><?php echo esc_html( $option->get_name() ); ?></span>
								<?php if ( $settings['show_prices'] ) : ?>
									<span class="wbd-option__price"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $option ) ) ); ?></span>
								<?php endif; ?>
								<?php if ( $settings['show_stock'] || ! $in ) : ?>
									<span class="wbd-option__stock"><?php echo wp_kses_post( $in ? wc_get_stock_html( $option ) : __( 'Out of stock', 'woocommerce' ) ); ?></span>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<?php WBD_PBB_Module_Bundles::render_footer( $product, $settings ); ?>
			<?php else : ?>
				<p class="stock out-of-stock"><?php esc_html_e( 'This product is currently unavailable.', 'wbd-product-benefits-blocks' ); ?></p>
			<?php endif; ?>
		</form>
		<?php
		do_action( 'woocommerce_after_add_to_cart_form' );
	}
}
