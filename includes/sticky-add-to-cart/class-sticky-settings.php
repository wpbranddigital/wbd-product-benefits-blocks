<?php
/**
 * Sticky Add to Cart — dashboard settings (WooCommerce → Sticky Add to Cart).
 *
 * Stores one option array. Saved through the Settings API, so options.php
 * handles the nonce check; the capability is narrowed to manage_woocommerce.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings page and option accessors.
 */
final class WBD_Product_Benefits_Blocks_Sticky_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION = 'wbd_pbb_sticky_atc';

	/**
	 * Settings API group.
	 *
	 * @var string
	 */
	const GROUP = 'wbd_pbb_sticky_atc_group';

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const PAGE = 'wbd-sticky-add-to-cart';

	/**
	 * Cached, sanitized settings for the current request.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 60 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_filter( 'option_page_capability_' . self::GROUP, array( __CLASS__, 'capability' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WBD_PRODUCT_BENEFITS_BLOCKS_FILE ), array( __CLASS__, 'action_links' ) );
		add_action( 'update_option_' . self::OPTION, array( __CLASS__, 'flush_cache' ) );
		add_action( 'add_option_' . self::OPTION, array( __CLASS__, 'flush_cache' ) );
	}

	/**
	 * Capability required to manage the settings.
	 *
	 * @return string
	 */
	public static function capability() {
		return 'manage_woocommerce';
	}

	/**
	 * Available visual templates.
	 *
	 * @return array<string,string> slug => label.
	 */
	public static function get_templates() {
		return array(
			'classic'  => __( 'Classic', 'wbd-product-benefits-blocks' ),
			'minimal'  => __( 'Minimal', 'wbd-product-benefits-blocks' ),
			'floating' => __( 'Floating', 'wbd-product-benefits-blocks' ),
			'dark'     => __( 'Dark', 'wbd-product-benefits-blocks' ),
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'enabled'            => 0,
			'template'           => 'classic',
			'position'           => 'bottom',
			'trigger'            => 'scroll_past',
			'show_desktop'       => 1,
			'show_tablet'        => 1,
			'show_mobile'        => 1,
			'show_image'         => 1,
			'show_title'         => 1,
			'show_price'         => 1,
			'show_rating'        => 0,
			'show_quantity'      => 1,
			'button_text'        => '',
			'ajax'               => 1,
			'after_add'          => 'stay',
			'hide_out_of_stock'  => 1,
			'exclude_products'   => '',
			'exclude_categories' => array(),
			'bg_color'           => '',
			'text_color'         => '',
			'button_bg'          => '',
			'button_color'       => '',
			'button_radius'      => '',
		);
	}

	/**
	 * Boolean setting keys.
	 *
	 * @return string[]
	 */
	private static function get_bool_keys() {
		return array(
			'enabled',
			'show_desktop',
			'show_tablet',
			'show_mobile',
			'show_image',
			'show_title',
			'show_price',
			'show_rating',
			'show_quantity',
			'ajax',
			'hide_out_of_stock',
		);
	}

	/**
	 * Returns the saved settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		if ( null === self::$cache ) {
			$saved = get_option( self::OPTION, null );

			// Never saved: use the defaults as-is. Saved: fill in keys added by
			// later versions with their defaults, then sanitize. (Sanitizing an
			// empty array would turn every checkbox off.)
			self::$cache = is_array( $saved )
				? self::sanitize( array_merge( self::get_defaults(), $saved ) )
				: self::get_defaults();
		}
		return self::$cache;
	}

	/**
	 * Clears the request cache after the option changes.
	 */
	public static function flush_cache() {
		self::$cache = null;
	}

	/**
	 * Registers the option with the Settings API.
	 */
	public static function register_setting() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::get_defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Sanitizes the settings array. Unknown keys are dropped.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$defaults = self::get_defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();

		foreach ( self::get_bool_keys() as $key ) {
			$output[ $key ] = empty( $input[ $key ] ) ? 0 : 1;
		}

		$choices = array(
			'template'  => array_keys( self::get_templates() ),
			'position'  => array( 'bottom', 'top' ),
			'trigger'   => array( 'scroll_past', 'always' ),
			'after_add' => array( 'stay', 'cart', 'checkout' ),
		);
		foreach ( $choices as $key => $allowed ) {
			$value          = isset( $input[ $key ] ) ? sanitize_key( $input[ $key ] ) : '';
			$output[ $key ] = in_array( $value, $allowed, true ) ? $value : $defaults[ $key ];
		}

		$button_text           = isset( $input['button_text'] ) ? sanitize_text_field( (string) $input['button_text'] ) : '';
		$output['button_text'] = function_exists( 'mb_substr' ) ? mb_substr( $button_text, 0, 60 ) : substr( $button_text, 0, 60 );

		foreach ( array( 'bg_color', 'text_color', 'button_bg', 'button_color' ) as $key ) {
			$color          = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
			$output[ $key ] = $color ? $color : '';
		}

		$radius                  = isset( $input['button_radius'] ) ? trim( (string) $input['button_radius'] ) : '';
		$output['button_radius'] = ( '' === $radius || ! is_numeric( $radius ) ) ? '' : (string) min( 50, absint( $radius ) );

		$output['exclude_products'] = implode( ',', self::parse_ids( isset( $input['exclude_products'] ) ? $input['exclude_products'] : '' ) );

		$categories                   = isset( $input['exclude_categories'] ) ? (array) $input['exclude_categories'] : array();
		$output['exclude_categories'] = array_values( array_unique( array_filter( array_map( 'absint', $categories ) ) ) );

		return $output;
	}

	/**
	 * Parses a comma/space separated list of IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return int[]
	 */
	public static function parse_ids( $value ) {
		if ( is_array( $value ) ) {
			$parts = $value;
		} else {
			$parts = preg_split( '/[\s,]+/', (string) $value );
		}
		$ids = array();
		foreach ( (array) $parts as $part ) {
			$part = trim( (string) $part );
			// Only plain positive integers; "-5" or "5abc" are ignored, not turned into 5.
			if ( ctype_digit( $part ) && (int) $part > 0 ) {
				$ids[] = (int) $part;
			}
		}
		return array_values( array_unique( $ids ) );
	}

	/**
	 * Adds the page under the WooCommerce menu.
	 */
	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ),
			__( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ),
			self::capability(),
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * URL of the settings page.
	 *
	 * @return string
	 */
	public static function get_page_url() {
		return admin_url( 'admin.php?page=' . self::PAGE );
	}

	/**
	 * Adds a "Settings" link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		if ( current_user_can( self::capability() ) ) {
			array_unshift(
				$links,
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( self::get_page_url() ),
					esc_html__( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' )
				)
			);
		}
		return $links;
	}

	/**
	 * Enqueues admin assets on the settings page only.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public static function enqueue_admin_assets( $hook_suffix ) {
		if ( false === strpos( (string) $hook_suffix, self::PAGE ) ) {
			return;
		}

		WBD_Product_Benefits_Blocks_Sticky_Renderer::register_assets();
		wp_enqueue_style( WBD_Product_Benefits_Blocks_Sticky_Renderer::STYLE_HANDLE );
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			'wbd-pbb-sticky-admin',
			WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/sticky-settings.css',
			array(),
			WBD_PRODUCT_BENEFITS_BLOCKS_VERSION
		);
		wp_enqueue_script(
			'wbd-pbb-sticky-admin',
			WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/sticky-settings.js',
			array( 'jquery', 'wp-color-picker' ),
			WBD_PRODUCT_BENEFITS_BLOCKS_VERSION,
			true
		);
		wp_localize_script(
			'wbd-pbb-sticky-admin',
			'wbdPbbStickyAdmin',
			array(
				'defaultButtonText' => __( 'Add to cart', 'wbd-product-benefits-blocks' ),
				'copied'            => __( 'Copied!', 'wbd-product-benefits-blocks' ),
				'copy'              => __( 'Copy', 'wbd-product-benefits-blocks' ),
			)
		);
	}

	/**
	 * Prints a checkbox row.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Setting key.
	 * @param string $label    Label.
	 * @param string $element  Optional preview element this toggles.
	 */
	private static function checkbox( $settings, $key, $label, $element = '' ) {
		printf(
			'<label class="wbd-satc-admin__check"><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s %4$s /> %5$s</label>',
			esc_attr( self::OPTION ),
			esc_attr( $key ),
			checked( 1, (int) $settings[ $key ], false ),
			$element ? 'data-preview-element="' . esc_attr( $element ) . '"' : '',
			esc_html( $label )
		);
	}

	/**
	 * Prints a select.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Setting key.
	 * @param array  $options  value => label.
	 */
	private static function select( $settings, $key, $options ) {
		printf( '<select id="wbd-satc-%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( self::OPTION ) );
		foreach ( $options as $value => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $settings[ $key ], $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	/**
	 * Renders the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'wbd-product-benefits-blocks' ) );
		}

		$s          = self::get();
		$name       = self::OPTION;
		$templates  = self::get_templates();
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 200,
			)
		);
		$categories = is_wp_error( $categories ) ? array() : $categories;
		?>
		<div class="wrap wbd-satc-admin">
			<h1><?php esc_html_e( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ); ?></h1>
			<p class="wbd-satc-admin__intro">
				<?php esc_html_e( 'Show a lightweight bar with the product and an Add to cart button once shoppers scroll past the main button. Use it on every product page automatically, or place it with the Sticky Add to Cart block or the shortcode.', 'wbd-product-benefits-blocks' ); ?>
			</p>

			<?php settings_errors(); ?>

			<div class="wbd-satc-admin__layout">
				<form method="post" action="options.php" class="wbd-satc-admin__form">
					<?php settings_fields( self::GROUP ); ?>

					<div class="wbd-satc-admin__card">
						<h2><?php esc_html_e( 'General', 'wbd-product-benefits-blocks' ); ?></h2>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Product pages', 'wbd-product-benefits-blocks' ); ?></th>
								<td>
									<?php self::checkbox( $s, 'enabled', __( 'Show the sticky bar on every product page automatically', 'wbd-product-benefits-blocks' ) ); ?>
									<p class="description"><?php esc_html_e( 'Works with block and classic themes. The block and shortcode work even when this is off.', 'wbd-product-benefits-blocks' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Template', 'wbd-product-benefits-blocks' ); ?></th>
								<td>
									<fieldset class="wbd-satc-admin__templates">
										<legend class="screen-reader-text"><?php esc_html_e( 'Template', 'wbd-product-benefits-blocks' ); ?></legend>
										<?php foreach ( $templates as $slug => $label ) : ?>
											<label class="wbd-satc-admin__template wbd-satc-admin__template--<?php echo esc_attr( $slug ); ?>">
												<input type="radio" name="<?php echo esc_attr( $name ); ?>[template]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $s['template'], $slug ); ?> />
												<span class="wbd-satc-admin__template-thumb" aria-hidden="true"><span></span></span>
												<span class="wbd-satc-admin__template-label"><?php echo esc_html( $label ); ?></span>
											</label>
										<?php endforeach; ?>
									</fieldset>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wbd-satc-position"><?php esc_html_e( 'Position', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td>
									<?php
									self::select(
										$s,
										'position',
										array(
											'bottom' => __( 'Bottom of the screen', 'wbd-product-benefits-blocks' ),
											'top'    => __( 'Top of the screen', 'wbd-product-benefits-blocks' ),
										)
									);
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wbd-satc-trigger"><?php esc_html_e( 'Show the bar', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td>
									<?php
									self::select(
										$s,
										'trigger',
										array(
											'scroll_past' => __( 'After scrolling past the main Add to cart button', 'wbd-product-benefits-blocks' ),
											'always'      => __( 'Always', 'wbd-product-benefits-blocks' ),
										)
									);
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Devices', 'wbd-product-benefits-blocks' ); ?></th>
								<td>
									<?php
									self::checkbox( $s, 'show_desktop', __( 'Desktop (782px and wider)', 'wbd-product-benefits-blocks' ) );
									self::checkbox( $s, 'show_tablet', __( 'Tablet (600–781px)', 'wbd-product-benefits-blocks' ) );
									self::checkbox( $s, 'show_mobile', __( 'Mobile (below 600px)', 'wbd-product-benefits-blocks' ) );
									?>
								</td>
							</tr>
						</table>
					</div>

					<div class="wbd-satc-admin__card">
						<h2><?php esc_html_e( 'Content', 'wbd-product-benefits-blocks' ); ?></h2>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Show in the bar', 'wbd-product-benefits-blocks' ); ?></th>
								<td>
									<?php
									self::checkbox( $s, 'show_image', __( 'Product image', 'wbd-product-benefits-blocks' ), 'image' );
									self::checkbox( $s, 'show_title', __( 'Product name', 'wbd-product-benefits-blocks' ), 'title' );
									self::checkbox( $s, 'show_price', __( 'Price', 'wbd-product-benefits-blocks' ), 'price' );
									self::checkbox( $s, 'show_rating', __( 'Star rating', 'wbd-product-benefits-blocks' ), 'rating' );
									self::checkbox( $s, 'show_quantity', __( 'Quantity selector', 'wbd-product-benefits-blocks' ), 'quantity' );
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wbd-satc-button_text"><?php esc_html_e( 'Button text', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" id="wbd-satc-button_text" name="<?php echo esc_attr( $name ); ?>[button_text]" value="<?php echo esc_attr( $s['button_text'] ); ?>" maxlength="60" placeholder="<?php esc_attr_e( 'Product default (e.g. Add to cart)', 'wbd-product-benefits-blocks' ); ?>" />
								</td>
							</tr>
						</table>
					</div>

					<div class="wbd-satc-admin__card">
						<h2><?php esc_html_e( 'Behaviour', 'wbd-product-benefits-blocks' ); ?></h2>
						<table class="form-table" role="presentation">
							<tr>
								<th scope="row"><?php esc_html_e( 'Add to cart', 'wbd-product-benefits-blocks' ); ?></th>
								<td>
									<?php self::checkbox( $s, 'ajax', __( 'Add simple products without reloading the page (AJAX)', 'wbd-product-benefits-blocks' ) ); ?>
									<p class="description"><?php esc_html_e( 'Variable and grouped products scroll to the product options first. Products with extra fields (e.g. add-ons) always use the main product form.', 'wbd-product-benefits-blocks' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="wbd-satc-after_add"><?php esc_html_e( 'After adding', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td>
									<?php
									self::select(
										$s,
										'after_add',
										array(
											'stay'     => __( 'Stay on the page and show a “View cart” link', 'wbd-product-benefits-blocks' ),
											'cart'     => __( 'Go to the cart', 'wbd-product-benefits-blocks' ),
											'checkout' => __( 'Go to checkout (buy now)', 'wbd-product-benefits-blocks' ),
										)
									);
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Out of stock', 'wbd-product-benefits-blocks' ); ?></th>
								<td><?php self::checkbox( $s, 'hide_out_of_stock', __( 'Hide the bar when the product cannot be bought', 'wbd-product-benefits-blocks' ) ); ?></td>
							</tr>
							<tr>
								<th scope="row"><label for="wbd-satc-exclude_products"><?php esc_html_e( 'Exclude products', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" id="wbd-satc-exclude_products" name="<?php echo esc_attr( $name ); ?>[exclude_products]" value="<?php echo esc_attr( $s['exclude_products'] ); ?>" placeholder="12, 34, 56" />
									<p class="description"><?php esc_html_e( 'Product IDs, separated by commas. Applies to automatic display only.', 'wbd-product-benefits-blocks' ); ?></p>
								</td>
							</tr>
							<?php if ( $categories ) : ?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Exclude categories', 'wbd-product-benefits-blocks' ); ?></th>
									<td>
										<div class="wbd-satc-admin__terms">
											<?php foreach ( $categories as $term ) : ?>
												<label class="wbd-satc-admin__check">
													<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[exclude_categories][]" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, $s['exclude_categories'], true ) ); ?> />
													<?php echo esc_html( $term->name ); ?>
												</label>
											<?php endforeach; ?>
										</div>
									</td>
								</tr>
							<?php endif; ?>
						</table>
					</div>

					<div class="wbd-satc-admin__card">
						<h2><?php esc_html_e( 'Style', 'wbd-product-benefits-blocks' ); ?></h2>
						<p class="description"><?php esc_html_e( 'Leave a color empty to use the template default.', 'wbd-product-benefits-blocks' ); ?></p>
						<table class="form-table" role="presentation">
							<?php
							$colors = array(
								'bg_color'     => array( __( 'Bar background', 'wbd-product-benefits-blocks' ), '--wbd-satc-bg' ),
								'text_color'   => array( __( 'Bar text', 'wbd-product-benefits-blocks' ), '--wbd-satc-color' ),
								'button_bg'    => array( __( 'Button background', 'wbd-product-benefits-blocks' ), '--wbd-satc-btn-bg' ),
								'button_color' => array( __( 'Button text', 'wbd-product-benefits-blocks' ), '--wbd-satc-btn-color' ),
							);
							foreach ( $colors as $key => $color ) :
								?>
								<tr>
									<th scope="row"><label for="wbd-satc-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $color[0] ); ?></label></th>
									<td><input type="text" class="wbd-satc-admin__color" id="wbd-satc-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $s[ $key ] ); ?>" data-preview-var="<?php echo esc_attr( $color[1] ); ?>" /></td>
								</tr>
							<?php endforeach; ?>
							<tr>
								<th scope="row"><label for="wbd-satc-button_radius"><?php esc_html_e( 'Button radius (px)', 'wbd-product-benefits-blocks' ); ?></label></th>
								<td><input type="number" class="small-text" min="0" max="50" id="wbd-satc-button_radius" name="<?php echo esc_attr( $name ); ?>[button_radius]" value="<?php echo esc_attr( $s['button_radius'] ); ?>" data-preview-var="--wbd-satc-radius" /></td>
							</tr>
						</table>
					</div>

					<?php submit_button(); ?>
				</form>

				<aside class="wbd-satc-admin__side">
					<div class="wbd-satc-admin__card wbd-satc-admin__preview-card">
						<h2><?php esc_html_e( 'Live preview', 'wbd-product-benefits-blocks' ); ?></h2>
						<div class="wbd-satc-admin__preview" id="wbd-satc-preview">
							<?php
							// Markup is escaped inside get_markup().
							echo WBD_Product_Benefits_Blocks_Sticky_Renderer::get_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								WBD_Product_Benefits_Blocks_Sticky_Renderer::get_sample_data(),
								$s,
								array( 'preview' => true )
							);
							?>
						</div>
						<p class="description"><?php esc_html_e( 'Sample product. On your store the bar is fixed to the screen edge you choose.', 'wbd-product-benefits-blocks' ); ?></p>
					</div>

					<div class="wbd-satc-admin__card">
						<h2><?php esc_html_e( 'Block & shortcode', 'wbd-product-benefits-blocks' ); ?></h2>
						<p><?php esc_html_e( 'Add the “Sticky Add to Cart” block to the Single Product template or to any page. On other pages, choose a product in the block settings.', 'wbd-product-benefits-blocks' ); ?></p>
						<p><?php esc_html_e( 'Shortcode for classic themes and page builders:', 'wbd-product-benefits-blocks' ); ?></p>
						<?php
						$shortcodes = array(
							'[wbd_sticky_add_to_cart]',
							'[wbd_sticky_add_to_cart product_id="123" template="floating" position="bottom" button_text="Buy now"]',
						);
						foreach ( $shortcodes as $shortcode ) :
							?>
							<div class="wbd-satc-admin__code">
								<code><?php echo esc_html( $shortcode ); ?></code>
								<button type="button" class="button button-small wbd-satc-admin__copy" data-copy="<?php echo esc_attr( $shortcode ); ?>"><?php esc_html_e( 'Copy', 'wbd-product-benefits-blocks' ); ?></button>
							</div>
						<?php endforeach; ?>
						<p class="description">
							<?php
							/* translators: %s: list of template slugs. */
							printf( esc_html__( 'Templates: %s. Attributes you leave out use the settings on this page.', 'wbd-product-benefits-blocks' ), esc_html( implode( ', ', array_keys( $templates ) ) ) );
							?>
						</p>
					</div>
				</aside>
			</div>
		</div>
		<?php
	}
}
