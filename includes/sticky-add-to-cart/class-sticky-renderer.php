<?php
/**
 * Sticky Add to Cart — markup, assets, automatic display and shortcode.
 *
 * One renderer is shared by the automatic product-page display, the
 * wbd/sticky-add-to-cart block, the [wbd_sticky_add_to_cart] shortcode and
 * the dashboard live preview, so all four always output the same markup.
 * Only one bar is printed per request.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sticky Add to Cart renderer.
 */
final class WBD_Product_Benefits_Blocks_Sticky_Renderer {

	/**
	 * Block name.
	 *
	 * @var string
	 */
	const BLOCK = 'wbd/sticky-add-to-cart';

	/**
	 * Build folder name.
	 *
	 * @var string
	 */
	const DIR = 'sticky-add-to-cart';

	/**
	 * Frontend stylesheet handle.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'wbd-sticky-add-to-cart';

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	const SHORTCODE = 'wbd_sticky_add_to_cart';

	/**
	 * Whether a bar has already been printed in this request.
	 *
	 * @var bool
	 */
	private static $rendered = false;

	/**
	 * Product ID queued for automatic display in wp_footer.
	 *
	 * @var int
	 */
	private static $auto_product_id = 0;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'prepare_auto_display' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'print_auto_display' ), 5 );
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Registers the frontend stylesheet (shared by frontend, editor and dashboard).
	 */
	public static function register_assets() {
		if ( wp_style_is( self::STYLE_HANDLE, 'registered' ) ) {
			return;
		}

		$file = WBD_Product_Benefits_Blocks::get_block_build_path( self::DIR ) . '/style-index.css';
		$ver  = file_exists( $file ) ? WBD_PRODUCT_BENEFITS_BLOCKS_VERSION . '.' . filemtime( $file ) : WBD_PRODUCT_BENEFITS_BLOCKS_VERSION;

		wp_register_style(
			self::STYLE_HANDLE,
			WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'build/' . self::DIR . '/style-index.css',
			array(),
			$ver
		);
		wp_style_add_data( self::STYLE_HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Enqueues the frontend stylesheet and view script.
	 */
	public static function enqueue_assets() {
		self::register_assets();
		wp_enqueue_style( self::STYLE_HANDLE );

		$script = generate_block_asset_handle( self::BLOCK, 'viewScript' );
		if ( wp_script_is( $script, 'registered' ) ) {
			wp_enqueue_script( $script );
		}
	}

	/**
	 * Decides early (so assets load in <head>) whether the automatic bar shows.
	 */
	public static function prepare_auto_display() {
		$settings = WBD_Product_Benefits_Blocks_Sticky_Settings::get();

		if ( empty( $settings['enabled'] ) || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$product = wc_get_product( get_queried_object_id() );

		if ( ! self::should_display( $product, $settings, true ) ) {
			return;
		}

		self::$auto_product_id = $product->get_id();
		self::enqueue_assets();
	}

	/**
	 * Prints the automatic bar, unless a block or shortcode already printed one.
	 */
	public static function print_auto_display() {
		if ( ! self::$auto_product_id || self::$rendered ) {
			return;
		}

		// Markup is escaped inside get_markup().
		echo self::render( wc_get_product( self::$auto_product_id ), array(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id'  => 0,
				'template'    => '',
				'position'    => '',
				'button_text' => '',
			),
			$atts,
			self::SHORTCODE
		);

		$product = self::resolve_product( absint( $atts['product_id'] ) );

		return self::render(
			$product,
			array(
				'template'    => $atts['template'],
				'position'    => $atts['position'],
				'button_text' => $atts['button_text'],
			)
		);
	}

	/**
	 * Finds the product to show.
	 *
	 * @param int $product_id      Explicit product ID (0 = current product).
	 * @param int $context_post_id Post ID from block context.
	 * @return WC_Product|null
	 */
	public static function resolve_product( $product_id = 0, $context_post_id = 0 ) {
		if ( $product_id ) {
			$product = wc_get_product( $product_id );
			return $product ? $product : null;
		}

		if ( $context_post_id && 'product' === get_post_type( $context_post_id ) ) {
			$product = wc_get_product( $context_post_id );
			if ( $product ) {
				return $product;
			}
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			$product = wc_get_product( get_queried_object_id() );
			return $product ? $product : null;
		}

		return null;
	}

	/**
	 * Sanitizes per-instance overrides (block attributes or shortcode attributes).
	 * Empty values mean "use the dashboard setting".
	 *
	 * @param array $overrides Raw overrides.
	 * @return array
	 */
	public static function sanitize_overrides( $overrides ) {
		$clean     = array();
		$overrides = is_array( $overrides ) ? $overrides : array();

		if ( isset( $overrides['template'] ) && array_key_exists( (string) $overrides['template'], WBD_Product_Benefits_Blocks_Sticky_Settings::get_templates() ) ) {
			$clean['template'] = (string) $overrides['template'];
		}

		if ( isset( $overrides['position'] ) && in_array( $overrides['position'], array( 'bottom', 'top' ), true ) ) {
			$clean['position'] = $overrides['position'];
		}

		if ( ! empty( $overrides['button_text'] ) ) {
			$text = sanitize_text_field( (string) $overrides['button_text'] );
			if ( '' !== $text ) {
				$clean['button_text'] = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 60 ) : substr( $text, 0, 60 );
			}
		}

		foreach ( array( 'bg_color', 'text_color', 'button_bg', 'button_color' ) as $key ) {
			if ( ! empty( $overrides[ $key ] ) ) {
				$color = sanitize_hex_color( $overrides[ $key ] );
				if ( $color ) {
					$clean[ $key ] = $color;
				}
			}
		}

		return $clean;
	}

	/**
	 * Renders the bar once per request.
	 *
	 * @param WC_Product|null $product   Product.
	 * @param array           $overrides Per-instance overrides.
	 * @param bool            $is_auto   Automatic display (applies exclusions).
	 * @return string
	 */
	public static function render( $product, $overrides = array(), $is_auto = false ) {
		if ( self::$rendered ) {
			return '';
		}

		$settings = array_merge( WBD_Product_Benefits_Blocks_Sticky_Settings::get(), self::sanitize_overrides( $overrides ) );

		if ( ! self::should_display( $product, $settings, $is_auto ) ) {
			return '';
		}

		self::$rendered = true;
		self::enqueue_assets();

		$is_native = function_exists( 'is_product' ) && is_product() && (int) get_queried_object_id() === (int) $product->get_id();

		return self::get_markup( self::get_product_data( $product ), $settings, array( 'native' => $is_native ) );
	}

	/**
	 * Whether the bar may be shown for a product.
	 *
	 * @param mixed $product  Product.
	 * @param array $settings Settings.
	 * @param bool  $is_auto  Automatic display.
	 * @return bool
	 */
	public static function should_display( $product, $settings, $is_auto = false ) {
		if ( ! $product instanceof WC_Product ) {
			return false;
		}

		$id   = $product->get_id();
		$show = true;

		// Never expose unpublished or password-protected products to visitors.
		if ( 'publish' !== $product->get_status() && ! current_user_can( 'edit_post', $id ) ) {
			$show = false;
		} elseif ( post_password_required( $id ) ) {
			$show = false;
		} elseif ( empty( $settings['show_desktop'] ) && empty( $settings['show_tablet'] ) && empty( $settings['show_mobile'] ) ) {
			$show = false;
		} elseif ( ! empty( $settings['hide_out_of_stock'] ) && 'unavailable' === self::get_mode( self::get_product_data( $product ) ) ) {
			$show = false;
		} elseif ( $is_auto ) {
			$excluded_ids = WBD_Product_Benefits_Blocks_Sticky_Settings::parse_ids( $settings['exclude_products'] );
			if ( in_array( $id, $excluded_ids, true ) ) {
				$show = false;
			} elseif ( ! empty( $settings['exclude_categories'] ) && has_term( array_map( 'intval', $settings['exclude_categories'] ), 'product_cat', $id ) ) {
				$show = false;
			}
		}

		/**
		 * Filters whether the sticky add to cart bar is displayed.
		 *
		 * @param bool       $show     Whether to show the bar.
		 * @param WC_Product $product  Product.
		 * @param array      $settings Effective settings.
		 * @param bool       $is_auto  True for the automatic product-page display.
		 */
		return (bool) apply_filters( 'wbd_pbb_sticky_add_to_cart_display', $show, $product, $settings, $is_auto );
	}

	/**
	 * Collects the product fields used by the bar.
	 *
	 * @param WC_Product $product Product.
	 * @return array
	 */
	public static function get_product_data( $product ) {
		$type = $product->get_type();
		$max  = (int) $product->get_max_purchase_quantity();

		return array(
			'id'                => $product->get_id(),
			'type'              => $type,
			'name'              => $product->get_name(),
			'url'               => get_permalink( $product->get_id() ),
			'image'             => $product->get_image(
				'woocommerce_gallery_thumbnail',
				array(
					'class'    => 'wbd-satc__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			),
			'price_html'        => $product->get_price_html(),
			'rating'            => (float) $product->get_average_rating(),
			'review_count'      => (int) $product->get_review_count(),
			'purchasable'       => $product->is_purchasable(),
			'in_stock'          => $product->is_in_stock(),
			'min_qty'           => max( 1, (int) $product->get_min_purchase_quantity() ),
			'max_qty'           => $max > 0 ? $max : 0,
			'sold_individually' => $product->is_sold_individually(),
			'single_text'       => $product->single_add_to_cart_text(),
			'loop_text'         => $product->add_to_cart_text(),
			'external_url'      => $product instanceof WC_Product_External ? $product->get_product_url() : '',
		);
	}

	/**
	 * Sample data for previews.
	 *
	 * @return array
	 */
	public static function get_sample_data() {
		return array(
			'id'                => 0,
			'type'              => 'simple',
			'name'              => __( 'Organic Cotton T-Shirt', 'wbd-product-benefits-blocks' ),
			'url'               => '#',
			'image'             => function_exists( 'wc_placeholder_img' ) ? wc_placeholder_img( 'woocommerce_gallery_thumbnail', array( 'class' => 'wbd-satc__img' ) ) : '',
			'price_html'        => function_exists( 'wc_format_sale_price' ) ? wc_format_sale_price( 39, 29 ) : '',
			'rating'            => 4.5,
			'review_count'      => 12,
			'purchasable'       => true,
			'in_stock'          => true,
			'min_qty'           => 1,
			'max_qty'           => 0,
			'sold_individually' => false,
			'single_text'       => __( 'Add to cart', 'wbd-product-benefits-blocks' ),
			'loop_text'         => __( 'Add to cart', 'wbd-product-benefits-blocks' ),
			'external_url'      => '',
		);
	}

	/**
	 * How the bar's button behaves for a product.
	 *
	 * @param array $data Product data.
	 * @return string simple|select|link|unavailable
	 */
	public static function get_mode( $data ) {
		if ( 'external' === $data['type'] ) {
			return 'link';
		}
		if ( 'grouped' === $data['type'] ) {
			return 'select';
		}
		if ( ! $data['purchasable'] || ! $data['in_stock'] ) {
			return 'unavailable';
		}
		return 'simple' === $data['type'] ? 'simple' : 'select';
	}

	/**
	 * Builds the bar markup. Every dynamic value is escaped here.
	 *
	 * @param array $data     Product data (get_product_data() or get_sample_data()).
	 * @param array $settings Effective settings.
	 * @param array $context  { preview: bool, native: bool }.
	 * @return string
	 */
	public static function get_markup( $data, $settings, $context = array() ) {
		$preview = ! empty( $context['preview'] );
		$native  = ! $preview && ! empty( $context['native'] );
		$mode    = self::get_mode( $data );

		$templates = WBD_Product_Benefits_Blocks_Sticky_Settings::get_templates();
		$template  = isset( $templates[ $settings['template'] ] ) ? $settings['template'] : 'classic';
		$position  = 'top' === $settings['position'] ? 'top' : 'bottom';

		$classes = array( 'wbd-satc', 'wbd-satc--' . $template, 'wbd-satc--' . $position, 'wbd-satc--mode-' . $mode );
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			if ( empty( $settings[ 'show_' . $device ] ) && ! $preview ) {
				$classes[] = 'wbd-satc-hide-' . $device;
			}
		}
		if ( $preview ) {
			$classes[] = 'is-preview';
			$classes[] = 'is-visible';
		}

		$vars = array(
			'bg_color'     => '--wbd-satc-bg',
			'text_color'   => '--wbd-satc-color',
			'button_bg'    => '--wbd-satc-btn-bg',
			'button_color' => '--wbd-satc-btn-color',
		);
		$style = '';
		foreach ( $vars as $key => $var ) {
			$color = empty( $settings[ $key ] ) ? '' : sanitize_hex_color( $settings[ $key ] );
			if ( $color ) {
				$style .= $var . ':' . $color . ';';
			}
		}
		if ( isset( $settings['button_radius'] ) && '' !== $settings['button_radius'] && is_numeric( $settings['button_radius'] ) ) {
			$style .= '--wbd-satc-radius:' . min( 50, absint( $settings['button_radius'] ) ) . 'px;';
		}

		if ( '' !== $settings['button_text'] && 'unavailable' !== $mode ) {
			$button_text = $settings['button_text'];
		} elseif ( 'unavailable' === $mode ) {
			$button_text = __( 'Out of stock', 'wbd-product-benefits-blocks' );
		} elseif ( ( 'select' === $mode && ! $native ) || 'link' === $mode ) {
			$button_text = $data['loop_text'];
		} else {
			$button_text = $data['single_text'];
		}

		$show_qty = ( 'simple' === $mode || ( 'select' === $mode && $native && 'variable' === $data['type'] ) ) && ! $data['sold_individually'];

		$attrs = array(
			'class'      => implode( ' ', $classes ),
			'role'       => 'region',
			'aria-label' => __( 'Quick add to cart', 'wbd-product-benefits-blocks' ),
		);
		if ( '' !== $style ) {
			$attrs['style'] = $style;
		}
		if ( ! $preview ) {
			$attrs['aria-hidden']   = 'true';
			$attrs['inert']         = '';
			$attrs['data-wbd-satc'] = wp_json_encode(
				array(
					'productId'   => (int) $data['id'],
					'mode'        => $mode,
					'type'        => $data['type'],
					'native'      => $native,
					'trigger'     => 'always' === $settings['trigger'] ? 'always' : 'scroll_past',
					'ajax'        => ! empty( $settings['ajax'] ),
					'afterAdd'    => $settings['after_add'],
					'ajaxUrl'     => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '',
					'cartUrl'     => wc_get_cart_url(),
					'checkoutUrl' => wc_get_checkout_url(),
					'productUrl'  => $data['url'],
					'i18n'        => array(
						'adding' => __( 'Adding…', 'wbd-product-benefits-blocks' ),
						'added'  => __( 'Added to your cart.', 'wbd-product-benefits-blocks' ),
						'error'  => __( 'Could not add the product. Please try again.', 'wbd-product-benefits-blocks' ),
					),
				)
			);
		}

		/**
		 * Filters the wrapper attributes of the sticky bar.
		 *
		 * @param array $attrs    Attributes.
		 * @param array $data     Product data.
		 * @param array $settings Effective settings.
		 */
		$attrs = apply_filters( 'wbd_pbb_sticky_add_to_cart_attributes', $attrs, $data, $settings );

		$html = '<div';
		foreach ( $attrs as $name => $value ) {
			$html .= '' === $value && 'inert' === $name ? ' inert' : ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		$html .= '><div class="wbd-satc__inner">';

		// Product summary.
		$html .= '<div class="wbd-satc__product">';
		if ( ( $preview || ! empty( $settings['show_image'] ) ) && '' !== $data['image'] ) {
			$html .= '<span class="wbd-satc__image" data-wbd-el="image"' . self::hidden( $settings, 'show_image', $preview ) . '>' . wp_kses_post( $data['image'] ) . '</span>';
		}
		$html .= '<div class="wbd-satc__info">';
		if ( $preview || ! empty( $settings['show_title'] ) ) {
			$html .= '<p class="wbd-satc__title" data-wbd-el="title"' . self::hidden( $settings, 'show_title', $preview ) . '>' . esc_html( $data['name'] ) . '</p>';
		}
		$html .= '<div class="wbd-satc__meta">';
		if ( ( $preview || ! empty( $settings['show_rating'] ) ) && $data['review_count'] > 0 ) {
			$rating = min( 5, max( 0, (float) $data['rating'] ) );
			$html  .= sprintf(
				'<span class="wbd-satc__rating" data-wbd-el="rating" role="img" aria-label="%1$s"%2$s><span class="wbd-satc__stars" style="--wbd-satc-rating:%3$s%%"></span></span>',
				/* translators: %s: average rating. */
				esc_attr( sprintf( __( 'Rated %s out of 5', 'wbd-product-benefits-blocks' ), number_format_i18n( $rating, 2 ) ) ),
				self::hidden( $settings, 'show_rating', $preview ),
				esc_attr( round( $rating / 5 * 100, 2 ) )
			);
		}
		if ( ( $preview || ! empty( $settings['show_price'] ) ) && '' !== $data['price_html'] ) {
			$html .= '<span class="wbd-satc__price" data-wbd-el="price"' . self::hidden( $settings, 'show_price', $preview ) . '>' . wp_kses_post( $data['price_html'] ) . '</span>';
		}
		$html .= '</div></div></div>';

		// Actions.
		$is_form = 'simple' === $mode && ! $preview;
		if ( $is_form ) {
			$html .= '<form class="wbd-satc__actions" method="post" enctype="multipart/form-data" action="' . esc_url( $data['url'] ) . '">';
		} else {
			$html .= '<div class="wbd-satc__actions">';
		}

		if ( $show_qty && ( $preview || ! empty( $settings['show_quantity'] ) ) ) {
			$html .= '<div class="wbd-satc__qty" data-wbd-el="quantity"' . self::hidden( $settings, 'show_quantity', $preview ) . '>';
			$html .= '<button type="button" class="wbd-satc__qty-btn" data-step="-1" aria-label="' . esc_attr__( 'Decrease quantity', 'wbd-product-benefits-blocks' ) . '">&minus;</button>';
			$html .= sprintf(
				'<input type="number" class="wbd-satc__qty-input" name="quantity" value="%1$d" min="%1$d"%2$s step="1" inputmode="numeric" aria-label="%3$s" />',
				(int) $data['min_qty'],
				$data['max_qty'] ? ' max="' . (int) $data['max_qty'] . '"' : '',
				esc_attr__( 'Product quantity', 'wbd-product-benefits-blocks' )
			);
			$html .= '<button type="button" class="wbd-satc__qty-btn" data-step="1" aria-label="' . esc_attr__( 'Increase quantity', 'wbd-product-benefits-blocks' ) . '">+</button>';
			$html .= '</div>';
		}

		if ( $preview ) {
			$html .= '<button type="button" class="wbd-satc__button">' . esc_html( $button_text ) . '</button>';
		} elseif ( 'simple' === $mode ) {
			$html .= '<input type="hidden" name="add-to-cart" value="' . esc_attr( $data['id'] ) . '" />';
			$html .= '<button type="submit" class="wbd-satc__button" name="wbd-satc-submit" value="1">' . esc_html( $button_text ) . '</button>';
		} elseif ( 'select' === $mode && $native ) {
			$html .= '<button type="button" class="wbd-satc__button" data-wbd-action="native">' . esc_html( $button_text ) . '</button>';
		} elseif ( 'link' === $mode ) {
			$href  = $data['external_url'] ? $data['external_url'] : $data['url'];
			$html .= '<a class="wbd-satc__button" href="' . esc_url( $href ) . '" rel="nofollow">' . esc_html( $button_text ) . '</a>';
		} elseif ( 'select' === $mode ) {
			$html .= '<a class="wbd-satc__button" href="' . esc_url( $data['url'] ) . '">' . esc_html( $button_text ) . '</a>';
		} else {
			$html .= '<span class="wbd-satc__button is-disabled" aria-disabled="true">' . esc_html( $button_text ) . '</span>';
		}

		if ( ! $preview ) {
			$html .= '<a class="wbd-satc__view-cart" href="' . esc_url( wc_get_cart_url() ) . '" hidden>' . esc_html__( 'View cart', 'wbd-product-benefits-blocks' ) . '</a>';
			$html .= '<span class="wbd-satc__status" role="status" aria-live="polite"></span>';
		}

		$html .= $is_form ? '</form>' : '</div>';
		$html .= '</div></div>';

		return $html;
	}

	/**
	 * Returns a `hidden` attribute for disabled elements in previews.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Setting key.
	 * @param bool   $preview  Preview mode.
	 * @return string
	 */
	private static function hidden( $settings, $key, $preview ) {
		return ( $preview && empty( $settings[ $key ] ) ) ? ' hidden' : '';
	}
}
