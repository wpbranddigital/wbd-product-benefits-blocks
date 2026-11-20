<?php
/**
 * Product Videos — YouTube, Vimeo or uploaded videos on product pages.
 *
 * Players are only loaded after a click (no third-party requests on page
 * load), shown in a lightbox from the product gallery, a Video tab, a block
 * or a shortcode.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Videos module.
 */
final class WBD_PBB_Module_Product_Videos extends WBD_PBB_Kit_Module {

	const META       = '_wbd_product_videos';
	const MAX_VIDEOS = 10;
	const HANDLE     = 'wbd-pbb-product-videos';
	const BLOCK      = 'wbd/product-video';

	public function slug() {
		return 'product_videos';
	}

	public function title() {
		return __( 'Product Videos', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Add YouTube, Vimeo or uploaded videos to products. Shoppers watch them in a lightbox from the product image or in a Video tab.', 'wbd-product-benefits-blocks' );
	}

	public function fields() {
		return array(
			'display'      => array(
				'type'    => 'select',
				'label'   => __( 'Where to show', 'wbd-product-benefits-blocks' ),
				'default' => 'gallery',
				'options' => array(
					'gallery' => __( '“Watch video” button on the product image', 'wbd-product-benefits-blocks' ),
					'tab'     => __( 'A Video tab', 'wbd-product-benefits-blocks' ),
					'both'    => __( 'Both', 'wbd-product-benefits-blocks' ),
					'none'    => __( 'Only where I add the block or shortcode', 'wbd-product-benefits-blocks' ),
				),
			),
			'button_text'  => array(
				'type'    => 'text',
				'label'   => __( 'Button text', 'wbd-product-benefits-blocks' ),
				'default' => __( 'Watch video', 'wbd-product-benefits-blocks' ),
			),
			'button_style' => array(
				'type'    => 'select',
				'label'   => __( 'Button style', 'wbd-product-benefits-blocks' ),
				'default' => 'pill',
				'options' => array(
					'pill'   => __( 'Pill with text (bottom corner)', 'wbd-product-benefits-blocks' ),
					'center' => __( 'Large round play button (center)', 'wbd-product-benefits-blocks' ),
				),
			),
			'accent'       => array(
				'type'    => 'color',
				'label'   => __( 'Button color', 'wbd-product-benefits-blocks' ),
				'default' => '',
			),
			'tab_title'    => array(
				'type'    => 'text',
				'label'   => __( 'Tab title', 'wbd-product-benefits-blocks' ),
				'default' => __( 'Video', 'wbd-product-benefits-blocks' ),
			),
			'tab_priority' => array(
				'type'    => 'number',
				'label'   => __( 'Tab order', 'wbd-product-benefits-blocks' ),
				'default' => 15,
				'min'     => 1,
				'max'     => 200,
				'help'    => __( 'Description is 10, Additional information 20, Reviews 30.', 'wbd-product-benefits-blocks' ),
			),
			'autoplay'     => array(
				'type'    => 'checkbox',
				'section' => __( 'Player', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Start playing as soon as the video opens', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'privacy'      => array(
				'type'    => 'checkbox',
				'label'   => __( 'Privacy-enhanced mode (youtube-nocookie.com, Vimeo “do not track”)', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
		);
	}

	public function help() {
		return array(
			__( 'Edit a product and open <strong>Product data → Videos</strong>.', 'wbd-product-benefits-blocks' ),
			__( 'Click <strong>Add video</strong> and paste a YouTube or Vimeo link — or click <strong>Upload</strong> to use an MP4 from your Media Library. Add a title and, optionally, a cover image.', 'wbd-product-benefits-blocks' ),
			__( '<strong>Update</strong> the product. A <strong>Watch video</strong> button appears on the product image; it opens the video in a lightbox.', 'wbd-product-benefits-blocks' ),
			__( 'Videos load only when a shopper clicks, so they do not slow the page down. Use the <strong>Product Video</strong> block or <code>[wbd_product_video]</code> to place videos anywhere (add <code>product_id="123"</code> outside product pages).', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		add_action( 'init', array( $this, 'register' ), 20 );
		add_shortcode( 'wbd_product_video', array( $this, 'shortcode' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_filter( 'woocommerce_product_tabs', array( $this, 'product_tab' ), 97 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
	}

	/* Data -------------------------------------------------------------- */

	/**
	 * Parses a video URL into a playable source.
	 *
	 * @param string $url URL.
	 * @return array|null { type: youtube|vimeo|file, id?, src }
	 */
	public static function parse_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return null;
		}
		if ( preg_match( '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m ) ) {
			return array(
				'type' => 'youtube',
				'id'   => $m[1],
			);
		}
		if ( preg_match( '~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{6,12})~', $url, $m ) ) {
			return array(
				'type' => 'vimeo',
				'id'   => $m[1],
			);
		}
		$clean = esc_url_raw( $url, array( 'http', 'https' ) );
		if ( $clean && preg_match( '~\.(mp4|webm|ogv|ogg|mov)$~i', (string) wp_parse_url( $clean, PHP_URL_PATH ) ) ) {
			return array(
				'type' => 'file',
				'src'  => $clean,
			);
		}
		return null;
	}

	/**
	 * Player URL (iframe) or file URL for a parsed source.
	 *
	 * @param array $source Parsed source.
	 * @return string
	 */
	public function player_url( $source ) {
		$autoplay = $this->get( 'autoplay' ) ? 1 : 0;
		if ( 'youtube' === $source['type'] ) {
			$host = $this->get( 'privacy' ) ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';
			return add_query_arg(
				array(
					'autoplay'       => $autoplay,
					'rel'            => 0,
					'playsinline'    => 1,
					'modestbranding' => 1,
				),
				$host . '/embed/' . rawurlencode( $source['id'] )
			);
		}
		if ( 'vimeo' === $source['type'] ) {
			$args = array( 'autoplay' => $autoplay );
			if ( $this->get( 'privacy' ) ) {
				$args['dnt'] = 1;
			}
			return add_query_arg( $args, 'https://player.vimeo.com/video/' . rawurlencode( $source['id'] ) );
		}
		return $source['src'];
	}

	/**
	 * Sanitizes video rows.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array
	 */
	public static function sanitize_rows( $rows ) {
		$clean = array();
		foreach ( is_array( $rows ) ? $rows : array() as $row ) {
			if ( ! is_array( $row ) || empty( $row['url'] ) ) {
				continue;
			}
			$url = esc_url_raw( trim( (string) $row['url'] ), array( 'http', 'https' ) );
			if ( ! $url || ! self::parse_url( $url ) ) {
				continue;
			}
			$poster  = isset( $row['poster'] ) ? absint( $row['poster'] ) : 0;
			$clean[] = array(
				'url'    => $url,
				'title'  => isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '',
				'poster' => ( $poster && wp_attachment_is_image( $poster ) ) ? $poster : 0,
			);
			if ( count( $clean ) >= self::MAX_VIDEOS ) {
				break;
			}
		}
		return $clean;
	}

	/**
	 * Playable videos of a product.
	 *
	 * @param int $product_id Product ID.
	 * @return array List of { title, type, player, poster_id, poster_url }.
	 */
	public function get_videos( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return array();
		}
		$videos = array();
		foreach ( self::sanitize_rows( get_post_meta( $product_id, self::META, true ) ) as $row ) {
			$source = self::parse_url( $row['url'] );
			if ( ! $source ) {
				continue;
			}
			$poster   = $row['poster'] ? $row['poster'] : (int) get_post_thumbnail_id( $product_id );
			$videos[] = array(
				'title'      => $row['title'] ? $row['title'] : get_the_title( $product_id ),
				'type'       => $source['type'],
				'player'     => $this->player_url( $source ),
				'poster_id'  => $poster,
				'poster_url' => $poster ? (string) wp_get_attachment_image_url( $poster, 'woocommerce_single' ) : '',
			);
		}
		return $videos;
	}

	/* Admin ------------------------------------------------------------- */

	/**
	 * Adds Product data → Videos.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function product_data_tab( $tabs ) {
		$tabs['wbd_videos'] = array(
			'label'    => __( 'Videos', 'wbd-product-benefits-blocks' ),
			'target'   => 'wbd_videos_data',
			'class'    => array(),
			'priority' => 76,
		);
		return $tabs;
	}

	/**
	 * One admin row.
	 *
	 * @param string|int $i   Index.
	 * @param array      $row Row.
	 */
	private function admin_row( $i, $row ) {
		$base   = 'wbd_videos[' . $i . ']';
		$poster = ! empty( $row['poster'] ) ? (int) $row['poster'] : 0;
		?>
		<div class="wbd-videos-admin__item">
			<div class="wbd-videos-admin__fields">
				<div class="wbd-videos-admin__url-row">
					<input type="url" class="wbd-videos-admin__url" name="<?php echo esc_attr( $base . '[url]' ); ?>" value="<?php echo esc_attr( isset( $row['url'] ) ? $row['url'] : '' ); ?>" placeholder="https://www.youtube.com/watch?v=…" aria-label="<?php esc_attr_e( 'Video URL', 'wbd-product-benefits-blocks' ); ?>" />
					<button type="button" class="button wbd-videos-admin__upload"><?php esc_html_e( 'Upload', 'wbd-product-benefits-blocks' ); ?></button>
				</div>
				<input type="text" class="wbd-videos-admin__title" name="<?php echo esc_attr( $base . '[title]' ); ?>" value="<?php echo esc_attr( isset( $row['title'] ) ? $row['title'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Title (optional)', 'wbd-product-benefits-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Video title', 'wbd-product-benefits-blocks' ); ?>" />
				<div class="wbd-videos-admin__poster">
					<span class="wbd-videos-admin__poster-preview"><?php echo $poster ? wp_get_attachment_image( $poster, 'thumbnail' ) : ''; ?></span>
					<input type="hidden" class="wbd-videos-admin__poster-id" name="<?php echo esc_attr( $base . '[poster]' ); ?>" value="<?php echo esc_attr( $poster ); ?>" />
					<button type="button" class="button-link wbd-videos-admin__poster-pick"><?php esc_html_e( 'Choose cover image', 'wbd-product-benefits-blocks' ); ?></button>
					<button type="button" class="button-link button-link-delete wbd-videos-admin__poster-clear" <?php echo $poster ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove cover', 'wbd-product-benefits-blocks' ); ?></button>
				</div>
			</div>
			<button type="button" class="button-link button-link-delete wbd-videos-admin__remove" aria-label="<?php esc_attr_e( 'Remove video', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
		</div>
		<?php
	}

	/**
	 * Videos panel.
	 */
	public function product_data_panel() {
		global $post;
		$rows = self::sanitize_rows( get_post_meta( $post ? $post->ID : 0, self::META, true ) );
		?>
		<div id="wbd_videos_data" class="panel woocommerce_options_panel hidden">
			<div class="wbd-videos-admin">
				<?php wp_nonce_field( 'wbd_videos_save', 'wbd_videos_nonce' ); ?>
				<p class="wbd-videos-admin__intro">
					<?php esc_html_e( 'Paste a YouTube or Vimeo link, or upload an MP4. Without a cover image, the product image is used.', 'wbd-product-benefits-blocks' ); ?>
					<a href="<?php echo esc_url( WBD_PBB_Kit::url( $this->slug() ) ); ?>"><?php esc_html_e( 'Display settings', 'wbd-product-benefits-blocks' ); ?></a>
				</p>
				<div class="wbd-videos-admin__list" data-next-index="<?php echo esc_attr( count( $rows ) ); ?>">
					<?php
					foreach ( $rows as $i => $row ) {
						$this->admin_row( $i, $row );
					}
					?>
				</div>
				<p><button type="button" class="button wbd-videos-admin__add"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span> <?php esc_html_e( 'Add video', 'wbd-product-benefits-blocks' ); ?></button></p>
				<template class="wbd-videos-admin__template"><?php $this->admin_row( '__i__', array() ); ?></template>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves videos.
	 *
	 * @param int $post_id Product ID.
	 */
	public function save_product( $post_id ) {
		if ( ! isset( $_POST['wbd_videos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wbd_videos_nonce'] ) ), 'wbd_videos_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize_rows().
		$rows = isset( $_POST['wbd_videos'] ) ? self::sanitize_rows( wp_unslash( $_POST['wbd_videos'] ) ) : array();
		if ( $rows ) {
			update_post_meta( $post_id, self::META, $rows );
		} else {
			delete_post_meta( $post_id, self::META );
		}
	}

	/**
	 * Admin assets.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wbd-pbb-videos-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/product-videos.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_enqueue_script( 'wbd-pbb-videos-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/product-videos.js', array( 'jquery' ), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script(
			'wbd-pbb-videos-admin',
			'wbdPbbVideos',
			array(
				'videoTitle'  => __( 'Choose a video', 'wbd-product-benefits-blocks' ),
				'posterTitle' => __( 'Choose a cover image', 'wbd-product-benefits-blocks' ),
				'select'      => __( 'Use this', 'wbd-product-benefits-blocks' ),
			)
		);
	}

	/* Frontend ---------------------------------------------------------- */

	/**
	 * Registers frontend assets and the block.
	 */
	public function register() {
		$css = WBD_Product_Benefits_Blocks::get_block_build_path( 'product-video' ) . '/style-index.css';
		wp_register_style( self::HANDLE, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'build/product-video/style-index.css', array(), file_exists( $css ) ? WBD_PRODUCT_BENEFITS_BLOCKS_VERSION . '.' . filemtime( $css ) : WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_style_add_data( self::HANDLE, 'rtl', 'replace' );
		wp_register_script( self::HANDLE, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/frontend/product-videos.js', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script(
			self::HANDLE,
			'wbdPbbVideosL10n',
			array(
				'close' => __( 'Close video', 'wbd-product-benefits-blocks' ),
				'title' => __( 'Product video', 'wbd-product-benefits-blocks' ),
			)
		);

		$path = WBD_Product_Benefits_Blocks::get_block_build_path( 'product-video' );
		if ( file_exists( $path . '/block.json' ) ) {
			$type = register_block_type( $path, array( 'render_callback' => array( $this, 'render_block' ) ) );
			if ( $type && ! empty( $type->editor_script_handles ) ) {
				foreach ( $type->editor_script_handles as $handle ) {
					wp_set_script_translations( $handle, 'wbd-product-benefits-blocks', WBD_PRODUCT_BENEFITS_BLOCKS_PATH . 'languages' );
				}
			}
		}
	}

	/**
	 * Current product page ID.
	 *
	 * @return int
	 */
	private function current_product_id() {
		return ( function_exists( 'is_product' ) && is_product() ) ? (int) get_queried_object_id() : 0;
	}

	/**
	 * Enqueues assets; passes gallery videos to the script.
	 */
	public function frontend_assets() {
		$id = $this->current_product_id();
		if ( ! $id || ! in_array( $this->get( 'display' ), array( 'gallery', 'tab', 'both' ), true ) ) {
			return;
		}
		$videos = $this->get_videos( $id );
		if ( ! $videos ) {
			return;
		}
		$this->enqueue();
		if ( in_array( $this->get( 'display' ), array( 'gallery', 'both' ), true ) ) {
			wp_add_inline_script(
				self::HANDLE,
				'window.wbdPbbGalleryVideos = ' . wp_json_encode(
					array(
						'videos' => array_map(
							function ( $v ) {
								return array(
									'title'  => $v['title'],
									'type'   => $v['type'],
									'player' => $v['player'],
								);
							},
							$videos
						),
						'text'   => $this->get( 'button_text' ) ? $this->get( 'button_text' ) : __( 'Watch video', 'wbd-product-benefits-blocks' ),
						'style'  => 'center' === $this->get( 'button_style' ) ? 'center' : 'pill',
						'accent' => $this->get( 'accent' ),
					)
				) . ';',
				'before'
			);
		}
	}

	/**
	 * Enqueues the style and script.
	 */
	private function enqueue() {
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}

	/**
	 * Adds the Video tab.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function product_tab( $tabs ) {
		global $product;
		if ( ! in_array( $this->get( 'display' ), array( 'tab', 'both' ), true ) || ! $product instanceof WC_Product || ! $this->get_videos( $product->get_id() ) ) {
			return $tabs;
		}
		$tabs['wbd_video'] = array(
			'title'    => $this->get( 'tab_title' ) ? $this->get( 'tab_title' ) : __( 'Video', 'wbd-product-benefits-blocks' ),
			'priority' => (int) $this->get( 'tab_priority' ),
			'callback' => array( $this, 'print_tab' ),
		);
		return $tabs;
	}

	/**
	 * Tab content.
	 */
	public function print_tab() {
		global $product;
		if ( $product instanceof WC_Product ) {
			echo $this->render( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render().
		}
	}

	/**
	 * Shortcode [wbd_product_video product_id="" index=""].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id' => 0,
				'index'      => 0,
			),
			$atts,
			'wbd_product_video'
		);
		$id   = absint( $atts['product_id'] ) ? absint( $atts['product_id'] ) : $this->current_product_id();
		return $this->render( $id, array( 'index' => absint( $atts['index'] ) ) );
	}

	/**
	 * Block render callback.
	 *
	 * @param array         $attributes Attributes.
	 * @param string        $content    Unused.
	 * @param WP_Block|null $block      Block.
	 * @return string
	 */
	public function render_block( $attributes, $content = '', $block = null ) {
		$id = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;
		if ( ! $id && $block instanceof WP_Block && ! empty( $block->context['postId'] ) && 'product' === get_post_type( $block->context['postId'] ) ) {
			$id = (int) $block->context['postId'];
		}
		if ( ! $id ) {
			$id = $this->current_product_id();
		}
		$html = $this->render( $id, array( 'index' => isset( $attributes['index'] ) ? absint( $attributes['index'] ) : 0 ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only editor preview flag.
		if ( '' === $html && defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['wbdVideoPreview'] ) && current_user_can( 'edit_posts' ) ) {
			return '<div class="wbd-pvideo-empty">' . esc_html__( 'Choose a product that has a video (Product data → Videos).', 'wbd-product-benefits-blocks' ) . '</div>';
		}
		return '' === $html ? '' : '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
	}

	/**
	 * Inline video cards (click to play).
	 *
	 * @param int   $product_id Product ID.
	 * @param array $args       index: 0 = all videos, n = only the n-th.
	 * @return string
	 */
	public function render( $product_id, $args = array() ) {
		$post = $product_id ? get_post( $product_id ) : null;
		if ( ! $post || 'product' !== $post->post_type || ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) || post_password_required( $post ) ) {
			return '';
		}
		$videos = $this->get_videos( $product_id );
		$index  = isset( $args['index'] ) ? absint( $args['index'] ) : 0;
		if ( $index ) {
			$videos = isset( $videos[ $index - 1 ] ) ? array( $videos[ $index - 1 ] ) : array();
		}
		if ( ! $videos ) {
			return '';
		}
		$this->enqueue();

		$accent = $this->get( 'accent' );
		$html   = '<div class="wbd-pvideos"' . ( $accent ? ' style="--wbd-pvideo-accent:' . esc_attr( $accent ) . '"' : '' ) . '>';
		foreach ( $videos as $video ) {
			$html .= '<figure class="wbd-pvideo">';
			$html .= sprintf(
				'<button type="button" class="wbd-pvideo__facade" data-wbd-video="%1$s" data-wbd-video-type="%2$s" aria-label="%3$s">',
				esc_attr( $video['player'] ),
				esc_attr( $video['type'] ),
				/* translators: %s: video title. */
				esc_attr( sprintf( __( 'Play video: %s', 'wbd-product-benefits-blocks' ), $video['title'] ) )
			);
			if ( $video['poster_id'] ) {
				$html .= wp_get_attachment_image(
					$video['poster_id'],
					'woocommerce_single',
					false,
					array(
						'class'   => 'wbd-pvideo__poster',
						'loading' => 'lazy',
						'alt'     => '',
					)
				);
			}
			$html .= '<span class="wbd-pvideo__play" aria-hidden="true"></span></button>';
			$html .= '<figcaption class="wbd-pvideo__title">' . esc_html( $video['title'] ) . '</figcaption>';
			$html .= '</figure>';
		}
		$html .= '</div>';
		return $html;
	}
}
