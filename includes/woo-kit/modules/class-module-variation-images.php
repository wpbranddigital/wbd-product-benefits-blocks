<?php
/**
 * Additional Variation Images — a full image gallery per variation.
 *
 * The gallery HTML is rendered by WooCommerce's own product-image template,
 * so zoom, lightbox and slider keep working with any theme that uses it.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variation Images module.
 */
final class WBD_PBB_Module_Variation_Images extends WBD_PBB_Kit_Module {

	const META       = '_wbd_variation_gallery';
	const MAX_IMAGES = 30;
	const SCRIPT     = 'wbd-pbb-variation-images';

	public function slug() {
		return 'variation_images';
	}

	public function title() {
		return __( 'Variation Images', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Give every variation its own image gallery. Choosing a color or size swaps the whole product gallery.', 'wbd-product-benefits-blocks' );
	}

	public function fields() {
		return array(
			'keep_parent' => array(
				'type'    => 'checkbox',
				'section' => __( 'Gallery', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Also show the main product’s gallery images after the variation images', 'wbd-product-benefits-blocks' ),
				'default' => 0,
			),
		);
	}

	public function help() {
		return array(
			__( 'Edit a variable product and open <strong>Product data → Variations</strong>.', 'wbd-product-benefits-blocks' ),
			__( 'Expand a variation and click <strong>Add images</strong> under “Variation gallery”. Select several images at once; drag thumbnails to reorder.', 'wbd-product-benefits-blocks' ),
			__( 'Click <strong>Save changes</strong>. On the product page, choosing that variation shows its images; clearing the selection brings back the main gallery.', 'wbd-product-benefits-blocks' ),
			__( 'The variation’s own image (the small image field) is shown first, followed by its gallery images.', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_field' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_filter( 'woocommerce_available_variation', array( $this, 'variation_data' ), 10, 3 );
		add_action( 'wc_ajax_wbd_variation_gallery', array( $this, 'ajax_gallery' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
	}

	/**
	 * Gallery attachment IDs of a variation.
	 *
	 * @param int $variation_id Variation ID.
	 * @return int[]
	 */
	public static function get_ids( $variation_id ) {
		return self::parse_ids( get_post_meta( $variation_id, self::META, true ) );
	}

	/**
	 * Parses and validates image IDs.
	 *
	 * @param mixed $value Comma list or array.
	 * @return int[]
	 */
	public static function parse_ids( $value ) {
		$parts = is_array( $value ) ? $value : explode( ',', (string) $value );
		$ids   = array();
		foreach ( $parts as $part ) {
			$id = absint( trim( (string) $part ) );
			if ( $id && ! in_array( $id, $ids, true ) && wp_attachment_is_image( $id ) ) {
				$ids[] = $id;
			}
			if ( count( $ids ) >= self::MAX_IMAGES ) {
				break;
			}
		}
		return $ids;
	}

	/* Admin ------------------------------------------------------------- */

	/**
	 * Gallery field inside each variation.
	 *
	 * @param int     $loop           Variation index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Variation post.
	 */
	public function variation_field( $loop, $variation_data, $variation ) {
		$ids = self::get_ids( $variation->ID );
		?>
		<div class="form-row form-row-full wbd-vgallery">
			<label><?php esc_html_e( 'Variation gallery', 'wbd-product-benefits-blocks' ); ?></label>
			<ul class="wbd-vgallery__list">
				<?php foreach ( $ids as $id ) : ?>
					<li class="wbd-vgallery__item" data-id="<?php echo esc_attr( $id ); ?>">
						<?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?>
						<button type="button" class="wbd-vgallery__remove" aria-label="<?php esc_attr_e( 'Remove image', 'wbd-product-benefits-blocks' ); ?>">&times;</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<input type="hidden" class="wbd-vgallery__input" name="wbd_variation_gallery[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
			<button type="button" class="button wbd-vgallery__add"><?php esc_html_e( 'Add images', 'wbd-product-benefits-blocks' ); ?></button>
			<span class="description"><?php esc_html_e( 'Shown instead of the product gallery when this variation is chosen. Drag to reorder.', 'wbd-product-benefits-blocks' ); ?></span>
		</div>
		<?php
	}

	/**
	 * Saves the gallery. WooCommerce already verified its nonce and capability.
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $i            Loop index.
	 */
	public function save_variation( $variation_id, $i ) {
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the save-variations nonce before this hook.
		if ( ! isset( $_POST['wbd_variation_gallery'] ) || ! is_array( $_POST['wbd_variation_gallery'] ) || ! array_key_exists( $i, $_POST['wbd_variation_gallery'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce checked by WooCommerce; IDs validated in parse_ids().
		$ids = self::parse_ids( wp_unslash( $_POST['wbd_variation_gallery'][ $i ] ) );
		if ( $ids ) {
			update_post_meta( $variation_id, self::META, implode( ',', $ids ) );
		} else {
			delete_post_meta( $variation_id, self::META );
		}
	}

	/**
	 * Admin assets on the product screen.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wbd-pbb-vgallery-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/variation-images.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_enqueue_script( 'wbd-pbb-vgallery-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/variation-images.js', array( 'jquery', 'jquery-ui-sortable' ), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script(
			'wbd-pbb-vgallery-admin',
			'wbdPbbVGallery',
			array(
				'title'  => __( 'Variation gallery', 'wbd-product-benefits-blocks' ),
				'button' => __( 'Add to gallery', 'wbd-product-benefits-blocks' ),
				'remove' => __( 'Remove image', 'wbd-product-benefits-blocks' ),
			)
		);
	}

	/* Frontend ---------------------------------------------------------- */

	/**
	 * Flags variations that have their own gallery.
	 *
	 * @param array                $data      Variation data.
	 * @param WC_Product           $product   Parent.
	 * @param WC_Product_Variation $variation Variation.
	 * @return array
	 */
	public function variation_data( $data, $product, $variation ) {
		$data['wbd_gallery'] = (bool) self::get_ids( $variation->get_id() );
		return $data;
	}

	/**
	 * Loads the small script on variable products that use variation galleries.
	 */
	public function frontend_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$has = false;
		foreach ( $product->get_children() as $child_id ) {
			if ( self::get_ids( $child_id ) ) {
				$has = true;
				break;
			}
		}
		if ( ! $has ) {
			return;
		}
		wp_enqueue_script( self::SCRIPT, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/variation-images.js', array( 'jquery', 'wc-single-product' ), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script(
			self::SCRIPT,
			'wbdPbbVariationImages',
			array(
				'url'       => WC_AJAX::get_endpoint( 'wbd_variation_gallery' ),
				'productId' => $product->get_id(),
			)
		);
	}

	/**
	 * Returns gallery HTML for a variation (or the parent, to restore it).
	 * GET ?wc-ajax=wbd_variation_gallery&id=123
	 */
	public function ajax_gallery() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only data (same as the product page).
		$id      = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$product = $id ? wc_get_product( $id ) : null;
		if ( ! $product ) {
			wp_send_json_error( null, 404 );
		}

		$parent = $product->is_type( 'variation' ) ? wc_get_product( $product->get_parent_id() ) : $product;
		if ( ! $parent || ! $parent->is_type( 'variable' ) || 'publish' !== $parent->get_status() || post_password_required( $parent->get_id() ) ) {
			wp_send_json_error( null, 404 );
		}
		if ( $product->is_type( 'variation' ) && 'publish' !== $product->get_status() ) {
			wp_send_json_error( null, 404 );
		}

		$main    = $parent->get_image_id();
		$gallery = $parent->get_gallery_image_ids();

		if ( $product->is_type( 'variation' ) ) {
			$ids = self::get_ids( $product->get_id() );
			if ( ! $ids ) {
				wp_send_json_success( array( 'html' => '' ) );
			}
			$main    = $product->get_image_id() ? $product->get_image_id() : array_shift( $ids );
			$gallery = array_values( array_diff( $ids, array( $main ) ) );
			if ( $this->get( 'keep_parent' ) ) {
				$gallery = array_values( array_unique( array_merge( $gallery, array_filter( array_merge( array( $parent->get_image_id() ), $parent->get_gallery_image_ids() ) ) ) ) );
				$gallery = array_values( array_diff( $gallery, array( $main ) ) );
			}
		}

		$html = $this->render_gallery( $parent, (int) $main, $gallery );
		nocache_headers();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Renders WooCommerce's gallery template for a set of images.
	 *
	 * @param WC_Product $parent  Parent product.
	 * @param int        $main    Main image ID.
	 * @param int[]      $gallery Gallery IDs.
	 * @return string
	 */
	private function render_gallery( $parent, $main, $gallery ) {
		$backup_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
		$backup_post    = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		$clone = clone $parent;
		$clone->set_image_id( $main );
		$clone->set_gallery_image_ids( $gallery );

		// setup_postdata() fires the_post, where WooCommerce unsets and replaces
		// $GLOBALS['product']; assign the clone afterwards, through $GLOBALS
		// (a `global` reference would be broken by that unset).
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Temporarily set for the WooCommerce template, restored below.
		$GLOBALS['post'] = get_post( $parent->get_id() );
		setup_postdata( $GLOBALS['post'] );
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['product'] = $clone;

		$html = wc_get_template_html( 'single-product/product-image.php' );

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = $backup_post;
		if ( $backup_post ) {
			setup_postdata( $backup_post );
		}
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['product'] = $backup_product;

		return $html;
	}
}
