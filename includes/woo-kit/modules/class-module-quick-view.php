<?php
/**
 * Smart Quick View — a "Quick view" button on product lists that opens the
 * product summary in an accessible popup, with add to cart.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quick View module.
 */
final class WBD_PBB_Module_Quick_View extends WBD_PBB_Kit_Module {

	const HANDLE = 'wbd-pbb-quick-view';

	/**
	 * Whether assets were enqueued.
	 *
	 * @var bool
	 */
	private $enqueued = false;

	public function slug() {
		return 'quick_view';
	}

	public function title() {
		return __( 'Quick View', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Add a “Quick view” button to product lists. Shoppers see images, price, description and add to cart in a popup without leaving the page.', 'wbd-product-benefits-blocks' );
	}

	public function default_enabled() {
		return false;
	}

	public function fields() {
		return array(
			'position'      => array(
				'type'    => 'select',
				'label'   => __( 'Button position', 'wbd-product-benefits-blocks' ),
				'default' => 'after',
				'options' => array(
					'after'    => __( 'After the Add to cart button', 'wbd-product-benefits-blocks' ),
					'before'   => __( 'Before the Add to cart button', 'wbd-product-benefits-blocks' ),
					'on_image' => __( 'On the product image (icon)', 'wbd-product-benefits-blocks' ),
					'none'     => __( 'Only where I add the shortcode', 'wbd-product-benefits-blocks' ),
				),
			),
			'button_type'   => array(
				'type'    => 'select',
				'label'   => __( 'Button type', 'wbd-product-benefits-blocks' ),
				'default' => 'button',
				'options' => array(
					'button' => __( 'Button', 'wbd-product-benefits-blocks' ),
					'link'   => __( 'Text link', 'wbd-product-benefits-blocks' ),
					'icon'   => __( 'Eye icon only', 'wbd-product-benefits-blocks' ),
				),
			),
			'button_text'   => array(
				'type'    => 'text',
				'label'   => __( 'Button text', 'wbd-product-benefits-blocks' ),
				'default' => __( 'Quick view', 'wbd-product-benefits-blocks' ),
			),
			'effect'        => array(
				'type'    => 'select',
				'label'   => __( 'Popup effect', 'wbd-product-benefits-blocks' ),
				'default' => 'zoom',
				'options' => array(
					'zoom'  => __( 'Zoom in', 'wbd-product-benefits-blocks' ),
					'fade'  => __( 'Fade', 'wbd-product-benefits-blocks' ),
					'slide' => __( 'Slide up', 'wbd-product-benefits-blocks' ),
					'none'  => __( 'None', 'wbd-product-benefits-blocks' ),
				),
			),
			'show_gallery'  => array(
				'type'    => 'checkbox',
				'section' => __( 'Show in the popup', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Image gallery', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_rating'   => array(
				'type'    => 'checkbox',
				'label'   => __( 'Star rating', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_excerpt'  => array(
				'type'    => 'checkbox',
				'label'   => __( 'Short description', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_cart'     => array(
				'type'    => 'checkbox',
				'label'   => __( 'Add to cart', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_meta'     => array(
				'type'    => 'checkbox',
				'label'   => __( 'SKU and categories', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_details'  => array(
				'type'    => 'checkbox',
				'label'   => __( '“View full details” link', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'show_nav'      => array(
				'type'    => 'checkbox',
				'label'   => __( 'Previous / next product buttons', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'exclude_cats'  => array(
				'type'    => 'text',
				'label'   => __( 'Hide for categories', 'wbd-product-benefits-blocks' ),
				'default' => '',
				'help'    => __( 'Category slugs, separated by commas.', 'wbd-product-benefits-blocks' ),
			),
		);
	}

	public function help() {
		return array(
			__( 'Switch the feature on in the <strong>Features</strong> tab. A <strong>Quick view</strong> button appears on shop pages, category pages, related products and product blocks.', 'wbd-product-benefits-blocks' ),
			__( 'Choose where the button goes and how it looks (button, link or eye icon on the image).', 'wbd-product-benefits-blocks' ),
			__( 'Pick what the popup shows. Shoppers can choose variations and add to cart right in the popup, and move to the previous/next product.', 'wbd-product-benefits-blocks' ),
			__( 'Place a button anywhere with <code>[wbd_quick_view id="123"]</code>, or link to <code>?quick-view=123</code> to open a product’s popup directly.', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wc_ajax_wbd_quick_view', array( $this, 'ajax' ) );
		add_shortcode( 'wbd_quick_view', array( $this, 'shortcode' ) );

		$position = $this->get( 'position' );
		if ( 'after' === $position ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'print_loop_button' ), 15 );
		} elseif ( 'before' === $position ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'print_loop_button' ), 9 );
		} elseif ( 'on_image' === $position ) {
			add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'print_loop_button' ), 11 );
		}
		if ( 'none' !== $position ) {
			add_filter( 'render_block_woocommerce/product-button', array( $this, 'block_button' ), 10, 3 );
			add_filter( 'render_block_woocommerce/product-image', array( $this, 'block_image' ), 10, 3 );
			add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'grid_item' ), 10, 3 );
		}

		// Open from ?quick-view=123.
		add_action( 'wp_footer', array( $this, 'maybe_auto_open' ) );
	}

	/* Assets ------------------------------------------------------------- */

	/**
	 * Registers the script and style.
	 */
	public function register_assets() {
		$css = WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'assets/frontend/quick-view.css';
		wp_register_style( self::HANDLE, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/quick-view.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION . '.' . ( file_exists( $css ) ? filemtime( $css ) : '' ) );
		wp_register_script( self::HANDLE, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/quick-view.js', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, array( 'in_footer' => true ) );
		wp_localize_script(
			self::HANDLE,
			'wbdPbbQuickView',
			array(
				'url'     => WC_AJAX::get_endpoint( 'wbd_quick_view' ),
				'cartUrl' => WC_AJAX::get_endpoint( 'add_to_cart' ),
				'effect'  => $this->get( 'effect' ),
				'nav'     => (bool) $this->get( 'show_nav' ),
				'i18n'    => array(
					'close'   => __( 'Close', 'wbd-product-benefits-blocks' ),
					'prev'    => __( 'Previous product', 'wbd-product-benefits-blocks' ),
					'next'    => __( 'Next product', 'wbd-product-benefits-blocks' ),
					'loading' => __( 'Loading…', 'wbd-product-benefits-blocks' ),
					'error'   => __( 'Sorry, this product could not be loaded.', 'wbd-product-benefits-blocks' ),
					'added'   => __( 'Added to your cart.', 'wbd-product-benefits-blocks' ),
					'dialog'  => __( 'Product quick view', 'wbd-product-benefits-blocks' ),
				),
			)
		);
	}

	/**
	 * Enqueues assets once a button is printed.
	 */
	private function enqueue() {
		if ( $this->enqueued ) {
			return;
		}
		$this->enqueued = true;
		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			$this->register_assets();
		}
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
		// Variable products inside the popup use WooCommerce's variation form.
		if ( $this->get( 'show_cart' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			// Bundles/composites in the popup need their live total.
			if ( WBD_PBB_Bundle_Engine::has_types() ) {
				wp_enqueue_style( 'wbd-pbb-bundles' );
				wp_enqueue_script( 'wbd-pbb-bundles' );
			}
		}
	}

	/* Buttons ------------------------------------------------------------ */

	/**
	 * Whether a product may get a button.
	 *
	 * @param WC_Product|null $product Product.
	 * @return bool
	 */
	private function allowed( $product ) {
		if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
			return false;
		}
		$excluded = array_filter( array_map( 'sanitize_title', explode( ',', (string) $this->get( 'exclude_cats' ) ) ) );
		if ( $excluded && has_term( $excluded, 'product_cat', $product->get_id() ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Button markup.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $context loop|image|shortcode.
	 * @param string     $text    Override text.
	 * @return string
	 */
	public function button( $product, $context = 'loop', $text = '' ) {
		if ( ! $this->allowed( $product ) ) {
			return '';
		}
		$this->enqueue();
		$type  = 'image' === $context ? 'icon' : $this->get( 'button_type' );
		$label = '' !== $text ? $text : ( $this->get( 'button_text' ) ? $this->get( 'button_text' ) : __( 'Quick view', 'wbd-product-benefits-blocks' ) );
		$class = 'wbd-qv-button wbd-qv-button--' . $type . ' wbd-qv-button--' . $context;
		if ( 'button' === $type ) {
			$class .= ' button' . ( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' );
		}
		$icon = '<svg class="wbd-qv-button__icon" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>';
		return sprintf(
			'<a href="%1$s" class="%2$s" data-wbd-qv="%3$d" role="button" aria-haspopup="dialog"%4$s>%5$s%6$s</a>',
			esc_url( add_query_arg( 'quick-view', $product->get_id(), $product->get_permalink() ) ),
			esc_attr( $class ),
			(int) $product->get_id(),
			/* translators: %s: product name. */
			'icon' === $type ? ' aria-label="' . esc_attr( sprintf( __( 'Quick view: %s', 'wbd-product-benefits-blocks' ), $product->get_name() ) ) . '" title="' . esc_attr( $label ) . '"' : '',
			'icon' === $type || 'button' === $type ? $icon : '',
			'icon' === $type ? '' : '<span class="wbd-qv-button__text">' . esc_html( $label ) . '</span>'
		);
	}

	/**
	 * Classic product loops.
	 */
	public function print_loop_button() {
		global $product;
		echo $this->button( $product, 'on_image' === $this->get( 'position' ) ? 'image' : 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in button().
	}

	/**
	 * Product Collection / product template: next to the Add to cart block.
	 *
	 * @param string   $content Block HTML.
	 * @param array    $parsed  Parsed block.
	 * @param WP_Block $block   Block.
	 * @return string
	 */
	public function block_button( $content, $parsed, $block = null ) {
		if ( 'on_image' === $this->get( 'position' ) ) {
			return $content;
		}
		$id = ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) ? (int) $block->context['postId'] : 0;
		$html = $id ? $this->button( wc_get_product( $id ), 'loop' ) : '';
		if ( '' === $html ) {
			return $content;
		}
		return 'before' === $this->get( 'position' ) ? $html . $content : $content . $html;
	}

	/**
	 * Product image block: icon over the image.
	 *
	 * @param string   $content Block HTML.
	 * @param array    $parsed  Parsed block.
	 * @param WP_Block $block   Block.
	 * @return string
	 */
	public function block_image( $content, $parsed, $block = null ) {
		if ( 'on_image' !== $this->get( 'position' ) || ! $block instanceof WP_Block || empty( $block->context['postId'] ) || ( function_exists( 'is_product' ) && is_product() && (int) $block->context['postId'] === (int) get_queried_object_id() ) ) {
			return $content;
		}
		$html = $this->button( wc_get_product( (int) $block->context['postId'] ), 'image' );
		return '' === $html ? $content : '<div class="wbd-qv-image-wrap">' . $content . $html . '</div>';
	}

	/**
	 * Older product grid blocks (Handpicked, Best sellers…).
	 *
	 * @param string     $html    Item HTML.
	 * @param object     $data    Item data.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public function grid_item( $html, $data, $product ) {
		$button = $this->button( $product, 'on_image' === $this->get( 'position' ) ? 'image' : 'loop' );
		return '' === $button ? $html : str_replace( '</li>', $button . '</li>', $html );
	}

	/**
	 * Shortcode [wbd_quick_view id="123" text="Peek"].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'text' => '',
			),
			$atts,
			'wbd_quick_view'
		);
		$id   = absint( $atts['id'] );
		if ( ! $id ) {
			global $product;
			$id = $product instanceof WC_Product ? $product->get_id() : 0;
		}
		return $id ? $this->button( wc_get_product( $id ), 'shortcode', sanitize_text_field( $atts['text'] ) ) : '';
	}

	/**
	 * Opens the popup on load for ?quick-view=123.
	 */
	public function maybe_auto_open() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display switch.
		$id = isset( $_GET['quick-view'] ) ? absint( $_GET['quick-view'] ) : 0;
		if ( ! $id || ! $this->allowed( wc_get_product( $id ) ) ) {
			return;
		}
		$this->enqueue();
		wp_add_inline_script( self::HANDLE, 'window.wbdPbbQuickViewOpen = ' . (int) $id . ';', 'before' );
		// Footer scripts may already be queued; make sure ours prints.
		if ( did_action( 'wp_print_footer_scripts' ) ) {
			wp_print_scripts( self::HANDLE );
		}
	}

	/* Popup content ------------------------------------------------------ */

	/**
	 * Returns the popup HTML. GET ?wc-ajax=wbd_quick_view&id=123
	 */
	public function ajax() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only product data.
		$id      = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$product = $id ? wc_get_product( $id ) : null;
		if ( ! $product || 'publish' !== $product->get_status() || ! $product->is_visible() || post_password_required( $id ) || $product->is_type( 'variation' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, this product could not be loaded.', 'wbd-product-benefits-blocks' ) ), 404 );
		}

		$backup_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$backup_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WooCommerce templates read the globals; restored below.
		$GLOBALS['post'] = get_post( $id );
		setup_postdata( $GLOBALS['post'] );
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Set after setup_postdata(), which resets it.
		$GLOBALS['product'] = $product;

		ob_start();
		$this->render_popup( $product );
		$html = ob_get_clean();

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = $backup_post;
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['product'] = $backup_product;

		wp_send_json_success(
			array(
				'html'  => $html,
				'title' => $product->get_name(),
				'type'  => $product->get_type(),
			)
		);
	}

	/**
	 * Popup markup.
	 *
	 * @param WC_Product $product Product.
	 */
	private function render_popup( $product ) {
		$image_ids = array_values( array_filter( array_merge( array( $product->get_image_id() ), $this->get( 'show_gallery' ) ? $product->get_gallery_image_ids() : array() ) ) );
		?>
		<div class="wbd-qv-product product type-<?php echo esc_attr( $product->get_type() ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
			<div class="wbd-qv-product__media">
				<div class="wbd-qv-product__main">
					<?php
					if ( $image_ids ) {
						echo wp_get_attachment_image( $image_ids[0], 'woocommerce_single', false, array( 'class' => 'wbd-qv-product__image' ) );
					} else {
						echo wp_kses_post( wc_placeholder_img( 'woocommerce_single', array( 'class' => 'wbd-qv-product__image' ) ) );
					}
					?>
				</div>
				<?php if ( count( $image_ids ) > 1 ) : ?>
					<div class="wbd-qv-product__thumbs">
						<?php foreach ( $image_ids as $i => $image_id ) : ?>
							<?php $full = wp_get_attachment_image_src( $image_id, 'woocommerce_single' ); ?>
							<button type="button" class="wbd-qv-product__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-full="<?php echo esc_url( $full ? $full[0] : '' ); ?>" aria-label="<?php /* translators: %d: image number. */ echo esc_attr( sprintf( __( 'Show image %d', 'wbd-product-benefits-blocks' ), $i + 1 ) ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>">
								<?php echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail' ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="wbd-qv-product__summary summary entry-summary">
				<h2 class="wbd-qv-product__title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
				<?php if ( $this->get( 'show_rating' ) && $product->get_rating_count() ) : ?>
					<div class="wbd-qv-product__rating"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) ); ?></div>
				<?php endif; ?>
				<p class="price wbd-qv-product__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				<?php if ( $this->get( 'show_excerpt' ) && $product->get_short_description() ) : ?>
					<div class="wbd-qv-product__excerpt"><?php echo wp_kses_post( wpautop( do_shortcode( $product->get_short_description() ) ) ); ?></div>
				<?php endif; ?>
				<?php if ( $this->get( 'show_cart' ) ) : ?>
					<div class="wbd-qv-product__cart">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>
					<p class="wbd-qv-product__status" role="status" aria-live="polite"></p>
				<?php endif; ?>
				<?php if ( $this->get( 'show_meta' ) ) : ?>
					<div class="wbd-qv-product__meta">
						<?php if ( $product->get_sku() ) : ?>
							<span><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <?php echo esc_html( $product->get_sku() ); ?></span>
						<?php endif; ?>
						<?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ', '<span>' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ) ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $this->get( 'show_details' ) ) : ?>
					<a class="wbd-qv-product__details" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'View full details', 'wbd-product-benefits-blocks' ); ?> &rarr;</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
