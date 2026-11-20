<?php
/**
 * WBD Woo Kit — module registry and the WooCommerce → WBD Woo Kit dashboard.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kit hub.
 */
final class WBD_PBB_Kit {

	/**
	 * Admin page slug.
	 *
	 * @var string
	 */
	const PAGE = 'wbd-woo-kit';

	/**
	 * Module on/off option.
	 *
	 * @var string
	 */
	const MODULES_OPTION = 'wbd_pbb_modules';

	/**
	 * Registered modules, keyed by slug.
	 *
	 * @var WBD_PBB_Kit_Module[]
	 */
	private static $modules = array();

	/**
	 * Registers modules and boots the enabled ones.
	 */
	public static function init() {
		$classes = apply_filters(
			'wbd_pbb_kit_modules',
			array(
				'WBD_PBB_Module_Product_Faqs',
				'WBD_PBB_Module_Variation_Images',
				'WBD_PBB_Module_Product_Videos',
				'WBD_PBB_Module_Bundles',
				'WBD_PBB_Module_Composite',
				'WBD_PBB_Module_Quick_View',
			)
		);

		foreach ( $classes as $class ) {
			if ( class_exists( $class ) ) {
				$module                            = new $class();
				self::$modules[ $module->slug() ] = $module;
			}
		}

		foreach ( self::$modules as $slug => $module ) {
			if ( self::is_enabled( $slug ) ) {
				$module->init();
			}
		}

		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 59 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
	}

	/**
	 * All modules.
	 *
	 * @return WBD_PBB_Kit_Module[]
	 */
	public static function modules() {
		return self::$modules;
	}

	/**
	 * One module.
	 *
	 * @param string $slug Slug.
	 * @return WBD_PBB_Kit_Module|null
	 */
	public static function module( $slug ) {
		return isset( self::$modules[ $slug ] ) ? self::$modules[ $slug ] : null;
	}

	/**
	 * Whether a module is switched on.
	 *
	 * @param string $slug Slug.
	 * @return bool
	 */
	public static function is_enabled( $slug ) {
		if ( ! isset( self::$modules[ $slug ] ) ) {
			return false;
		}
		$saved = get_option( self::MODULES_OPTION, null );
		if ( is_array( $saved ) && isset( $saved[ $slug ] ) ) {
			return (bool) $saved[ $slug ];
		}
		return self::$modules[ $slug ]->default_enabled();
	}

	/**
	 * Capability to manage the kit.
	 *
	 * @return string
	 */
	public static function capability() {
		return 'manage_woocommerce';
	}

	/**
	 * Settings page URL.
	 *
	 * @param string $tab Tab.
	 * @return string
	 */
	public static function url( $tab = '' ) {
		return admin_url( 'admin.php?page=' . self::PAGE . ( $tab ? '&tab=' . rawurlencode( $tab ) : '' ) );
	}

	/**
	 * Registers the module toggle option and every module's option.
	 */
	public static function register_settings() {
		register_setting(
			'wbd_pbb_modules_group',
			self::MODULES_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_modules' ),
				'show_in_rest'      => false,
			)
		);
		add_filter( 'option_page_capability_wbd_pbb_modules_group', array( __CLASS__, 'capability' ) );

		foreach ( self::$modules as $module ) {
			if ( ! $module->fields() ) {
				continue;
			}
			register_setting(
				$module->group(),
				$module->option_name(),
				array(
					'type'              => 'array',
					'sanitize_callback' => array( $module, 'sanitize' ),
					'show_in_rest'      => false,
				)
			);
			add_filter( 'option_page_capability_' . $module->group(), array( __CLASS__, 'capability' ) );
			add_action( 'update_option_' . $module->option_name(), array( $module, 'flush' ) );
		}
	}

	/**
	 * Sanitizes the module toggles.
	 *
	 * @param mixed $input Raw.
	 * @return array
	 */
	public static function sanitize_modules( $input ) {
		$input  = is_array( $input ) ? $input : array();
		$output = array();
		foreach ( array_keys( self::$modules ) as $slug ) {
			$output[ $slug ] = empty( $input[ $slug ] ) ? 0 : 1;
		}
		return $output;
	}

	/**
	 * Adds WooCommerce → WBD Woo Kit.
	 */
	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'WBD Woo Kit', 'wbd-product-benefits-blocks' ),
			__( 'WBD Woo Kit', 'wbd-product-benefits-blocks' ),
			self::capability(),
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Admin styles for the hub.
	 *
	 * @param string $hook Hook suffix.
	 */
	public static function enqueue_admin( $hook ) {
		if ( false === strpos( (string) $hook, self::PAGE ) ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wbd-pbb-kit-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/kit.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".wbd-kit__color").wpColorPicker();});' );
	}

	/**
	 * Renders the hub.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'wbd-product-benefits-blocks' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab switch only.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'modules';
		if ( 'modules' !== $tab && ! isset( self::$modules[ $tab ] ) ) {
			$tab = 'modules';
		}
		?>
		<div class="wrap wbd-kit">
			<h1><?php esc_html_e( 'WBD Woo Kit', 'wbd-product-benefits-blocks' ); ?></h1>
			<p class="wbd-kit__intro"><?php esc_html_e( 'Lightweight WooCommerce features in one place. Switch on what you need — every feature has a short “How to use” guide.', 'wbd-product-benefits-blocks' ); ?></p>
			<?php settings_errors(); ?>
			<nav class="nav-tab-wrapper wbd-kit__tabs" aria-label="<?php esc_attr_e( 'WBD Woo Kit sections', 'wbd-product-benefits-blocks' ); ?>">
				<a href="<?php echo esc_url( self::url() ); ?>" class="nav-tab <?php echo 'modules' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Features', 'wbd-product-benefits-blocks' ); ?></a>
				<?php foreach ( self::$modules as $slug => $module ) : ?>
					<a href="<?php echo esc_url( self::url( $slug ) ); ?>" class="nav-tab <?php echo $slug === $tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $module->title() ); ?>
						<?php if ( ! self::is_enabled( $slug ) ) : ?>
							<span class="wbd-kit__off"><?php esc_html_e( 'off', 'wbd-product-benefits-blocks' ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
				<a href="<?php echo esc_url( WBD_Product_Benefits_Blocks_Sticky_Settings::get_page_url() ); ?>" class="nav-tab"><?php esc_html_e( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ); ?></a>
			</nav>
			<?php
			if ( 'modules' === $tab ) {
				self::render_modules();
			} else {
				self::render_module( self::$modules[ $tab ] );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Modules (on/off) screen.
	 */
	private static function render_modules() {
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wbd_pbb_modules_group' ); ?>
			<div class="wbd-kit__grid">
				<?php foreach ( self::$modules as $slug => $module ) : ?>
					<div class="wbd-kit__card">
						<label class="wbd-kit__switch">
							<input type="checkbox" name="<?php echo esc_attr( self::MODULES_OPTION . '[' . $slug . ']' ); ?>" value="1" <?php checked( self::is_enabled( $slug ) ); ?> />
							<strong><?php echo esc_html( $module->title() ); ?></strong>
						</label>
						<p><?php echo esc_html( $module->description() ); ?></p>
						<a href="<?php echo esc_url( self::url( $slug ) ); ?>"><?php esc_html_e( 'Settings & how to use →', 'wbd-product-benefits-blocks' ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
			<?php submit_button( __( 'Save features', 'wbd-product-benefits-blocks' ) ); ?>
		</form>
		<?php
	}

	/**
	 * One module's settings screen.
	 *
	 * @param WBD_PBB_Kit_Module $module Module.
	 */
	private static function render_module( $module ) {
		$settings = $module->get_settings();
		$name     = $module->option_name();
		?>
		<div class="wbd-kit__layout">
			<div>
				<?php if ( ! self::is_enabled( $module->slug() ) ) : ?>
					<div class="notice notice-warning inline"><p>
						<?php
						printf(
							/* translators: %s: link to the Features tab. */
							esc_html__( 'This feature is switched off. Turn it on in the %s tab.', 'wbd-product-benefits-blocks' ),
							'<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Features', 'wbd-product-benefits-blocks' ) . '</a>'
						);
						?>
					</p></div>
				<?php endif; ?>
				<?php if ( $module->fields() ) : ?>
					<form method="post" action="options.php" class="wbd-kit__card">
						<?php settings_fields( $module->group() ); ?>
						<h2><?php echo esc_html( $module->title() ); ?></h2>
						<table class="form-table" role="presentation">
							<?php
							foreach ( $module->fields() as $key => $field ) {
								self::render_field( $name, $key, $field, $settings[ $key ] );
							}
							?>
						</table>
						<?php submit_button(); ?>
					</form>
				<?php else : ?>
					<div class="wbd-kit__card"><h2><?php echo esc_html( $module->title() ); ?></h2><p><?php esc_html_e( 'No settings needed — this feature works as soon as it is switched on.', 'wbd-product-benefits-blocks' ); ?></p></div>
				<?php endif; ?>
			</div>
			<?php if ( $module->help() ) : ?>
				<aside class="wbd-kit__card wbd-kit__help">
					<h2><?php esc_html_e( 'How to use', 'wbd-product-benefits-blocks' ); ?></h2>
					<ol>
						<?php foreach ( $module->help() as $step ) : ?>
							<li><?php echo wp_kses( $step, array( 'strong' => array(), 'code' => array(), 'em' => array() ) ); ?></li>
						<?php endforeach; ?>
					</ol>
				</aside>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Renders one settings row.
	 *
	 * @param string $name  Option name.
	 * @param string $key   Field key.
	 * @param array  $field Field schema.
	 * @param mixed  $value Current value.
	 */
	private static function render_field( $name, $key, $field, $value ) {
		$id       = 'wbd-kit-' . $key;
		$field_nm = $name . '[' . $key . ']';
		echo '<tr><th scope="row">';
		if ( 'checkbox' !== $field['type'] ) {
			echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
		} else {
			echo esc_html( isset( $field['section'] ) ? $field['section'] : '' );
		}
		echo '</th><td>';

		switch ( $field['type'] ) {
			case 'checkbox':
				printf( '<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>', esc_attr( $id ), esc_attr( $field_nm ), checked( 1, (int) $value, false ), esc_html( $field['label'] ) );
				break;
			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $field_nm ) );
				foreach ( $field['options'] as $opt => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $opt ), selected( (string) $value, (string) $opt, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'number':
				printf(
					'<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s />',
					esc_attr( $id ),
					esc_attr( $field_nm ),
					esc_attr( $value ),
					isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : '',
					isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : ''
				);
				break;
			case 'color':
				printf( '<input type="text" class="wbd-kit__color" id="%1$s" name="%2$s" value="%3$s" />', esc_attr( $id ), esc_attr( $field_nm ), esc_attr( $value ) );
				break;
			case 'textarea':
				printf( '<textarea class="large-text" rows="3" id="%1$s" name="%2$s">%3$s</textarea>', esc_attr( $id ), esc_attr( $field_nm ), esc_textarea( $value ) );
				break;
			default:
				printf( '<input type="text" class="regular-text" id="%1$s" name="%2$s" value="%3$s" />', esc_attr( $id ), esc_attr( $field_nm ), esc_attr( $value ) );
		}

		if ( ! empty( $field['help'] ) ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
}
