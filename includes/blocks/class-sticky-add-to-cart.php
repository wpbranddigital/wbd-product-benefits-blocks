<?php
/**
 * Sticky Add to Cart block registration (dynamic block).
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sticky Add to Cart block module. Only loaded when WooCommerce is active.
 */
final class WBD_Product_Benefits_Blocks_Sticky_Add_To_Cart {

	/**
	 * Registers the block type.
	 */
	public static function register() {
		$path = WBD_Product_Benefits_Blocks::get_block_build_path( WBD_Product_Benefits_Blocks_Sticky_Renderer::DIR );

		if ( ! file_exists( $path . '/block.json' ) ) {
			return;
		}

		// The frontend stylesheet is used as an editor style for the preview.
		WBD_Product_Benefits_Blocks_Sticky_Renderer::register_assets();

		$block_type = register_block_type(
			$path,
			array(
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);

		if ( ! $block_type ) {
			return;
		}

		if ( ! empty( $block_type->editor_script_handles ) ) {
			foreach ( $block_type->editor_script_handles as $handle ) {
				wp_set_script_translations( $handle, 'wbd-product-benefits-blocks', WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'languages' );
				wp_add_inline_script(
					$handle,
					'window.wbdPbbSticky = ' . wp_json_encode(
						array(
							'settingsUrl' => WBD_Product_Benefits_Blocks_Sticky_Settings::get_page_url(),
							'canManage'   => current_user_can( WBD_Product_Benefits_Blocks_Sticky_Settings::capability() ),
						)
					) . ';',
					'before'
				);
			}
		}
	}

	/**
	 * Whether this is the editor's server-side preview request.
	 *
	 * @return bool
	 */
	private static function is_editor_preview() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview flag; REST cookie auth already verified the nonce.
		return defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['wbdSatcPreview'] ) && current_user_can( 'edit_posts' );
	}

	/**
	 * Converts block attributes to renderer overrides.
	 *
	 * @param array $attributes Block attributes.
	 * @return array
	 */
	private static function get_overrides( $attributes ) {
		return array(
			'template'     => isset( $attributes['template'] ) ? $attributes['template'] : '',
			'position'     => isset( $attributes['position'] ) ? $attributes['position'] : '',
			'button_text'  => isset( $attributes['buttonText'] ) ? $attributes['buttonText'] : '',
			'bg_color'     => isset( $attributes['barBackground'] ) ? $attributes['barBackground'] : '',
			'text_color'   => isset( $attributes['barText'] ) ? $attributes['barText'] : '',
			'button_bg'    => isset( $attributes['buttonBackground'] ) ? $attributes['buttonBackground'] : '',
			'button_color' => isset( $attributes['buttonTextColor'] ) ? $attributes['buttonTextColor'] : '',
		);
	}

	/**
	 * Render callback.
	 *
	 * @param array         $attributes Block attributes.
	 * @param string        $content    Inner content (unused).
	 * @param WP_Block|null $block      Block instance.
	 * @return string
	 */
	public static function render( $attributes, $content = '', $block = null ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$product_id = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;
		$overrides  = self::get_overrides( $attributes );

		if ( self::is_editor_preview() ) {
			$product = $product_id ? wc_get_product( $product_id ) : null;
			if ( $product && 'publish' !== $product->get_status() && ! current_user_can( 'read_post', $product_id ) ) {
				$product = null;
			}
			$data     = $product ? WBD_Product_Benefits_Blocks_Sticky_Renderer::get_product_data( $product ) : WBD_Product_Benefits_Blocks_Sticky_Renderer::get_sample_data();
			$settings = array_merge(
				WBD_Product_Benefits_Blocks_Sticky_Settings::get(),
				WBD_Product_Benefits_Blocks_Sticky_Renderer::sanitize_overrides( $overrides )
			);

			return WBD_Product_Benefits_Blocks_Sticky_Renderer::get_markup( $data, $settings, array( 'preview' => true ) );
		}

		$context_id = ( $block instanceof WP_Block && isset( $block->context['postId'] ) ) ? absint( $block->context['postId'] ) : 0;
		$product    = WBD_Product_Benefits_Blocks_Sticky_Renderer::resolve_product( $product_id, $context_id );

		return WBD_Product_Benefits_Blocks_Sticky_Renderer::render( $product, $overrides );
	}
}
