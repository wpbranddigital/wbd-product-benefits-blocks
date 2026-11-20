<?php
/**
 * Plugin dashboard: welcome screen, feature/block overview, documentation
 * and support pages under a top-level "WBD Woo Kit" admin menu.
 *
 * Read-only screens — nothing is saved here. Feature settings stay on their
 * own pages (WooCommerce → WBD Woo Kit and WooCommerce → Sticky Add to Cart).
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin dashboard.
 */
final class WBD_Product_Benefits_Blocks_Admin_Dashboard {

	const PAGE         = 'wbd-pbb';
	const PAGE_DOCS    = 'wbd-pbb-docs';
	const PAGE_SUPPORT = 'wbd-pbb-support';

	const SUPPORT_URL = 'https://wordpress.org/support/plugin/wbd-product-benefits-blocks/';
	const REVIEW_URL  = 'https://wordpress.org/support/plugin/wbd-product-benefits-blocks/reviews/#new-post';
	const PLUGIN_URL  = 'https://wordpress.org/plugins/wbd-product-benefits-blocks/';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WBD_PRODUCT_BENEFITS_BLOCKS_FILE ), array( __CLASS__, 'action_links' ), 20 );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
		add_filter( 'admin_footer_text', array( __CLASS__, 'footer_text' ) );
	}

	/**
	 * Capability to view the dashboard. Shop managers can see it when
	 * WooCommerce is active; otherwise administrators.
	 *
	 * @return string
	 */
	public static function capability() {
		$cap = WBD_Product_Benefits_Blocks::is_woocommerce_active() ? 'manage_woocommerce' : 'manage_options';
		/**
		 * Filters the capability required to view the WBD Woo Kit dashboard.
		 *
		 * @param string $cap Capability.
		 */
		return (string) apply_filters( 'wbd_pbb_dashboard_capability', $cap );
	}

	/**
	 * Dashboard URL.
	 *
	 * @param string $page Page slug.
	 * @return string
	 */
	public static function url( $page = self::PAGE ) {
		return admin_url( 'admin.php?page=' . $page );
	}

	/**
	 * Registers the menu.
	 */
	public static function add_menu() {
		$cap      = self::capability();
		$callback = array( __CLASS__, 'render_page' );

		add_menu_page(
			__( 'WBD Woo Kit', 'wbd-product-benefits-blocks' ),
			__( 'WBD Woo Kit', 'wbd-product-benefits-blocks' ),
			$cap,
			self::PAGE,
			$callback,
			'dashicons-screenoptions',
			58
		);
		add_submenu_page( self::PAGE, __( 'Dashboard', 'wbd-product-benefits-blocks' ), __( 'Dashboard', 'wbd-product-benefits-blocks' ), $cap, self::PAGE, $callback );
		add_submenu_page( self::PAGE, __( 'Documentation', 'wbd-product-benefits-blocks' ), __( 'Documentation', 'wbd-product-benefits-blocks' ), $cap, self::PAGE_DOCS, $callback );
		add_submenu_page( self::PAGE, __( 'Support', 'wbd-product-benefits-blocks' ), __( 'Support', 'wbd-product-benefits-blocks' ), $cap, self::PAGE_SUPPORT, $callback );
	}

	/**
	 * Whether the current screen is one of ours.
	 *
	 * @return string Page slug or ''.
	 */
	private static function current_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Page routing only.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		return in_array( $page, array( self::PAGE, self::PAGE_DOCS, self::PAGE_SUPPORT ), true ) ? $page : '';
	}

	/**
	 * Styles and the "copy system info" script, on our pages only.
	 */
	public static function enqueue() {
		if ( ! self::current_page() ) {
			return;
		}
		wp_enqueue_style( 'wbd-pbb-dashboard', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/dashboard.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_enqueue_script( 'wbd-pbb-dashboard', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/dashboard.js', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script(
			'wbd-pbb-dashboard',
			'wbdPbbDashboard',
			array(
				'copied' => __( 'Copied!', 'wbd-product-benefits-blocks' ),
				'failed' => __( 'Select the text and copy it manually.', 'wbd-product-benefits-blocks' ),
			)
		);
	}

	/**
	 * "Dashboard" link on the Plugins screen.
	 *
	 * @param string[] $links Links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		if ( current_user_can( self::capability() ) ) {
			array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( self::url() ), esc_html__( 'Dashboard', 'wbd-product-benefits-blocks' ) ) );
		}
		return $links;
	}

	/**
	 * Docs / Support / Rate links under the plugin description.
	 *
	 * @param string[] $meta Meta links.
	 * @param string   $file Plugin file.
	 * @return string[]
	 */
	public static function row_meta( $meta, $file ) {
		if ( plugin_basename( WBD_PRODUCT_BENEFITS_BLOCKS_FILE ) !== $file ) {
			return $meta;
		}
		if ( current_user_can( self::capability() ) ) {
			$meta[] = sprintf( '<a href="%s">%s</a>', esc_url( self::url( self::PAGE_DOCS ) ), esc_html__( 'Documentation', 'wbd-product-benefits-blocks' ) );
		}
		$meta[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::SUPPORT_URL ), esc_html__( 'Support', 'wbd-product-benefits-blocks' ) );
		$meta[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url( self::REVIEW_URL ), esc_html__( 'Rate ★★★★★', 'wbd-product-benefits-blocks' ) );
		return $meta;
	}

	/**
	 * Review request in the admin footer of our pages.
	 *
	 * @param string $text Footer text.
	 * @return string
	 */
	public static function footer_text( $text ) {
		if ( ! self::current_page() ) {
			return $text;
		}
		return sprintf(
			/* translators: %s: review link. */
			esc_html__( 'Enjoying WBD Product Benefits Blocks? Please leave us a %s rating — it really helps.', 'wbd-product-benefits-blocks' ),
			'<a href="' . esc_url( self::REVIEW_URL ) . '" target="_blank" rel="noopener noreferrer">★★★★★</a>'
		);
	}

	/**
	 * Gutenberg blocks shipped by the plugin.
	 *
	 * @return array[]
	 */
	public static function blocks() {
		$woo = WBD_Product_Benefits_Blocks::is_woocommerce_active();
		return array(
			array(
				'icon'  => 'dashicons-awards',
				'title' => __( 'Product Benefits', 'wbd-product-benefits-blocks' ),
				'desc'  => __( 'Free shipping, secure payment, returns and trust badges in four layouts.', 'wbd-product-benefits-blocks' ),
				'ready' => true,
				'docs'  => 'benefits',
			),
			array(
				'icon'  => 'dashicons-cart',
				'title' => __( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ),
				'desc'  => __( 'A bar with the product and an Add to cart button that follows shoppers as they scroll.', 'wbd-product-benefits-blocks' ),
				'ready' => $woo,
				'docs'  => 'sticky',
			),
			array(
				'icon'  => 'dashicons-editor-help',
				'title' => __( 'Product FAQs', 'wbd-product-benefits-blocks' ),
				'desc'  => __( 'Questions and answers for a product as an accessible accordion.', 'wbd-product-benefits-blocks' ),
				'ready' => $woo && self::module_on( 'product_faqs' ),
				'docs'  => 'product_faqs',
			),
			array(
				'icon'  => 'dashicons-video-alt3',
				'title' => __( 'Product Video', 'wbd-product-benefits-blocks' ),
				'desc'  => __( 'Click-to-play YouTube, Vimeo or uploaded product videos.', 'wbd-product-benefits-blocks' ),
				'ready' => $woo && self::module_on( 'product_videos' ),
				'docs'  => 'product_videos',
			),
		);
	}

	/**
	 * Whether a Woo Kit feature is switched on.
	 *
	 * @param string $slug Module slug.
	 * @return bool
	 */
	private static function module_on( $slug ) {
		return class_exists( 'WBD_PBB_Kit' ) && WBD_PBB_Kit::is_enabled( $slug );
	}

	/**
	 * Dashicon per Woo Kit feature.
	 *
	 * @param string $slug Module slug.
	 * @return string
	 */
	private static function module_icon( $slug ) {
		$icons = array(
			'product_faqs'     => 'dashicons-editor-help',
			'variation_images' => 'dashicons-format-gallery',
			'product_videos'   => 'dashicons-video-alt3',
			'bundles'          => 'dashicons-products',
			'composite'        => 'dashicons-screenoptions',
			'quick_view'       => 'dashicons-visibility',
		);
		return isset( $icons[ $slug ] ) ? $icons[ $slug ] : 'dashicons-admin-generic';
	}

	/**
	 * Woo Kit modules, or an empty array without WooCommerce.
	 *
	 * @return WBD_PBB_Kit_Module[]
	 */
	private static function modules() {
		return class_exists( 'WBD_PBB_Kit' ) && WBD_Product_Benefits_Blocks::is_woocommerce_active() ? WBD_PBB_Kit::modules() : array();
	}

	/**
	 * Renders one of the three pages.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'wbd-product-benefits-blocks' ) );
		}
		$page = self::current_page();
		$tabs = array(
			self::PAGE         => __( 'Dashboard', 'wbd-product-benefits-blocks' ),
			self::PAGE_DOCS    => __( 'Documentation', 'wbd-product-benefits-blocks' ),
			self::PAGE_SUPPORT => __( 'Support', 'wbd-product-benefits-blocks' ),
		);
		?>
		<div class="wrap wbd-dash">
			<div class="wbd-dash__hero">
				<div>
					<h1 class="wbd-dash__title">
						<?php esc_html_e( 'WBD Product Benefits Blocks', 'wbd-product-benefits-blocks' ); ?>
						<span class="wbd-dash__version"><?php echo esc_html( 'v' . WBD_PRODUCT_BENEFITS_BLOCKS_VERSION ); ?></span>
					</h1>
					<p class="wbd-dash__lead"><?php esc_html_e( 'Lightweight conversion blocks and WooCommerce features — no page builder needed.', 'wbd-product-benefits-blocks' ); ?></p>
				</div>
				<a class="button wbd-dash__hero-btn" href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer">★ <?php esc_html_e( 'Rate us', 'wbd-product-benefits-blocks' ); ?></a>
			</div>
			<nav class="nav-tab-wrapper wbd-dash__tabs" aria-label="<?php esc_attr_e( 'WBD Woo Kit sections', 'wbd-product-benefits-blocks' ); ?>">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<a href="<?php echo esc_url( self::url( $slug ) ); ?>" class="nav-tab <?php echo $slug === $page ? 'nav-tab-active' : ''; ?>" <?php echo $slug === $page ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php
			if ( self::PAGE_DOCS === $page ) {
				self::render_docs();
			} elseif ( self::PAGE_SUPPORT === $page ) {
				self::render_support();
			} else {
				self::render_dashboard();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Welcome + overview.
	 */
	private static function render_dashboard() {
		$woo     = WBD_Product_Benefits_Blocks::is_woocommerce_active();
		$modules = self::modules();
		$manage  = current_user_can( 'manage_woocommerce' );
		?>
		<section class="wbd-dash__card wbd-dash__welcome">
			<h2><?php esc_html_e( 'Welcome! 👋', 'wbd-product-benefits-blocks' ); ?></h2>
			<p><?php esc_html_e( 'Thanks for using WBD Product Benefits Blocks. Here you can see every block and feature, switch features on, and learn how to use them in a few steps.', 'wbd-product-benefits-blocks' ); ?></p>
			<ol class="wbd-dash__steps">
				<li><strong><?php esc_html_e( 'Add a block', 'wbd-product-benefits-blocks' ); ?></strong> — <?php esc_html_e( 'open any page or the Single Product template, click “+” and search “WBD”.', 'wbd-product-benefits-blocks' ); ?></li>
				<li><strong><?php esc_html_e( 'Switch on features', 'wbd-product-benefits-blocks' ); ?></strong> — <?php esc_html_e( 'turn on only what you need in WooCommerce → WBD Woo Kit. Features that are off load no code.', 'wbd-product-benefits-blocks' ); ?></li>
				<li><strong><?php esc_html_e( 'Read the guide', 'wbd-product-benefits-blocks' ); ?></strong> — <?php esc_html_e( 'every feature has short step-by-step instructions in Documentation.', 'wbd-product-benefits-blocks' ); ?></li>
			</ol>
			<p class="wbd-dash__actions">
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Create a page with blocks', 'wbd-product-benefits-blocks' ); ?></a>
				<?php if ( $woo && $manage ) : ?>
					<a class="button" href="<?php echo esc_url( WBD_PBB_Kit::url() ); ?>"><?php esc_html_e( 'Manage features', 'wbd-product-benefits-blocks' ); ?></a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( self::url( self::PAGE_DOCS ) ); ?>"><?php esc_html_e( 'Documentation', 'wbd-product-benefits-blocks' ); ?></a>
			</p>
		</section>

		<?php if ( ! $woo ) : ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'Install and activate WooCommerce to unlock Sticky Add to Cart and the WBD Woo Kit features (FAQs, Variation Images, Videos, Bundles, Composite Products and Quick View). The Product Benefits block works without it.', 'wbd-product-benefits-blocks' ); ?></p></div>
		<?php endif; ?>

		<div class="wbd-dash__layout">
			<div>
				<h2 class="wbd-dash__heading"><?php esc_html_e( 'Blocks', 'wbd-product-benefits-blocks' ); ?></h2>
				<div class="wbd-dash__grid">
					<?php foreach ( self::blocks() as $block ) : ?>
						<div class="wbd-dash__card wbd-dash__item">
							<span class="dashicons <?php echo esc_attr( $block['icon'] ); ?> wbd-dash__icon" aria-hidden="true"></span>
							<h3><?php echo esc_html( $block['title'] ); ?> <?php self::badge( $block['ready'], __( 'Available', 'wbd-product-benefits-blocks' ), $woo ? __( 'Feature off', 'wbd-product-benefits-blocks' ) : __( 'Needs WooCommerce', 'wbd-product-benefits-blocks' ) ); ?></h3>
							<p><?php echo esc_html( $block['desc'] ); ?></p>
							<a href="<?php echo esc_url( self::url( self::PAGE_DOCS ) . '#doc-' . $block['docs'] ); ?>"><?php esc_html_e( 'How to use →', 'wbd-product-benefits-blocks' ); ?></a>
						</div>
					<?php endforeach; ?>
				</div>

				<h2 class="wbd-dash__heading"><?php esc_html_e( 'WooCommerce features', 'wbd-product-benefits-blocks' ); ?></h2>
				<div class="wbd-dash__grid">
					<?php if ( $woo ) : ?>
						<div class="wbd-dash__card wbd-dash__item">
							<span class="dashicons dashicons-cart wbd-dash__icon" aria-hidden="true"></span>
							<?php $sticky_on = ! empty( WBD_Product_Benefits_Blocks_Sticky_Settings::get()['enabled'] ); ?>
							<h3><?php esc_html_e( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ); ?> <?php self::badge( $sticky_on, __( 'On every product', 'wbd-product-benefits-blocks' ), __( 'Block / shortcode only', 'wbd-product-benefits-blocks' ) ); ?></h3>
							<p><?php esc_html_e( 'Show the bar automatically on every product page, or place it with the block or shortcode.', 'wbd-product-benefits-blocks' ); ?></p>
							<?php self::item_links( WBD_Product_Benefits_Blocks_Sticky_Settings::get_page_url(), 'sticky', $manage ); ?>
						</div>
					<?php endif; ?>
					<?php foreach ( $modules as $slug => $module ) : ?>
						<div class="wbd-dash__card wbd-dash__item">
							<span class="dashicons <?php echo esc_attr( self::module_icon( $slug ) ); ?> wbd-dash__icon" aria-hidden="true"></span>
							<h3><?php echo esc_html( $module->title() ); ?> <?php self::badge( WBD_PBB_Kit::is_enabled( $slug ), __( 'On', 'wbd-product-benefits-blocks' ), __( 'Off', 'wbd-product-benefits-blocks' ) ); ?></h3>
							<p><?php echo esc_html( $module->description() ); ?></p>
							<?php self::item_links( WBD_PBB_Kit::url( $slug ), $slug, $manage ); ?>
						</div>
					<?php endforeach; ?>
					<?php if ( ! $woo ) : ?>
						<p class="wbd-dash__muted"><?php esc_html_e( 'Available once WooCommerce is active.', 'wbd-product-benefits-blocks' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php self::render_sidebar(); ?>
		</div>
		<?php
	}

	/**
	 * Settings + docs links for a feature card.
	 *
	 * @param string $settings Settings URL.
	 * @param string $doc      Doc anchor.
	 * @param bool   $manage   Whether the user can open settings.
	 */
	private static function item_links( $settings, $doc, $manage ) {
		?>
		<p class="wbd-dash__links">
			<?php if ( $manage ) : ?>
				<a href="<?php echo esc_url( $settings ); ?>"><?php esc_html_e( 'Settings', 'wbd-product-benefits-blocks' ); ?></a>
				<span aria-hidden="true">·</span>
			<?php endif; ?>
			<a href="<?php echo esc_url( self::url( self::PAGE_DOCS ) . '#doc-' . $doc ); ?>"><?php esc_html_e( 'How to use', 'wbd-product-benefits-blocks' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Status badge.
	 *
	 * @param bool   $on    State.
	 * @param string $yes   Label when on.
	 * @param string $no    Label when off.
	 */
	private static function badge( $on, $yes, $no ) {
		printf( '<span class="wbd-dash__badge %s">%s</span>', $on ? 'is-on' : 'is-off', esc_html( $on ? $yes : $no ) );
	}

	/**
	 * Support + review sidebar.
	 */
	private static function render_sidebar() {
		?>
		<aside class="wbd-dash__side">
			<div class="wbd-dash__card wbd-dash__review">
				<h2><?php esc_html_e( 'Love the plugin?', 'wbd-product-benefits-blocks' ); ?></h2>
				<p class="wbd-dash__stars" aria-hidden="true">★★★★★</p>
				<p><?php esc_html_e( 'A short review on WordPress.org helps other store owners find the plugin and keeps it free and updated.', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Write a review', 'wbd-product-benefits-blocks' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wbd-product-benefits-blocks' ); ?></span></a>
			</div>
			<div class="wbd-dash__card">
				<h2><?php esc_html_e( 'Need help?', 'wbd-product-benefits-blocks' ); ?></h2>
				<p><?php esc_html_e( 'Ask a question or report a problem in the free support forum. We usually reply within one or two working days.', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button" href="<?php echo esc_url( self::SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support forum', 'wbd-product-benefits-blocks' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wbd-product-benefits-blocks' ); ?></span></a>
				<a class="button-link wbd-dash__side-link" href="<?php echo esc_url( self::url( self::PAGE_SUPPORT ) ); ?>"><?php esc_html_e( 'Support page & system info →', 'wbd-product-benefits-blocks' ); ?></a>
			</div>
		</aside>
		<?php
	}

	/**
	 * Documentation sections: [ id, title, steps[], shortcode? ].
	 *
	 * @return array[]
	 */
	private static function doc_sections() {
		$sections   = array();
		$sections[] = array(
			'id'    => 'start',
			'title' => __( 'Getting started', 'wbd-product-benefits-blocks' ),
			'steps' => array(
				__( 'Open <strong>Pages → Add New</strong> (or <strong>Appearance → Editor → Templates → Single Product</strong> in a block theme).', 'wbd-product-benefits-blocks' ),
				__( 'Click the <strong>+</strong> button and search for <strong>WBD</strong>. All blocks are in the <strong>WBD Product Benefits</strong> category.', 'wbd-product-benefits-blocks' ),
				__( 'Select the block and use the settings panel on the right to change layout, colors and content.', 'wbd-product-benefits-blocks' ),
				__( 'For WooCommerce features go to <strong>WooCommerce → WBD Woo Kit</strong>, tick the features you want and click <strong>Save</strong>. Each feature has its own settings tab.', 'wbd-product-benefits-blocks' ),
				__( 'Using a page builder or classic editor? Every WooCommerce block also has a <strong>shortcode</strong> (see the table at the end).', 'wbd-product-benefits-blocks' ),
			),
		);
		$sections[] = array(
			'id'    => 'benefits',
			'title' => __( 'Product Benefits block', 'wbd-product-benefits-blocks' ),
			'steps' => array(
				__( 'Insert the <strong>Product Benefits</strong> block. Four benefits are added for you: Free Shipping, Secure Payment, Easy Returns and Expert Support.', 'wbd-product-benefits-blocks' ),
				__( 'Click a title or description to edit it. Use the block toolbar to add, duplicate, move or remove benefits.', 'wbd-product-benefits-blocks' ),
				__( 'Choose an icon from 18 built-in icons or upload your own image (remember the alt text).', 'wbd-product-benefits-blocks' ),
				__( 'Pick a layout — <strong>Grid</strong>, <strong>Horizontal</strong>, <strong>Checklist</strong> or <strong>Trust Badges</strong> — and set columns for desktop, tablet and mobile.', 'wbd-product-benefits-blocks' ),
				__( 'Style colors, spacing, borders and shadows, and hide the block on certain devices if you need to.', 'wbd-product-benefits-blocks' ),
			),
		);
		if ( WBD_Product_Benefits_Blocks::is_woocommerce_active() ) {
			$sections[] = array(
				'id'    => 'sticky',
				'title' => __( 'Sticky Add to Cart', 'wbd-product-benefits-blocks' ),
				'steps' => array(
					__( 'Go to <strong>WooCommerce → Sticky Add to Cart</strong>.', 'wbd-product-benefits-blocks' ),
					__( 'Tick <strong>Show the sticky bar on every product page automatically</strong> to turn it on for all products, or leave it off and place the <strong>Sticky Add to Cart</strong> block where you want it.', 'wbd-product-benefits-blocks' ),
					__( 'Choose a template (Classic, Minimal, Floating or Dark), position (bottom or top) and what to show. The live preview updates as you change settings.', 'wbd-product-benefits-blocks' ),
					__( 'The bar appears once shoppers scroll past the main Add to cart button and hides again when it comes back into view.', 'wbd-product-benefits-blocks' ),
				),
			);
			foreach ( self::modules() as $slug => $module ) {
				$sections[] = array(
					'id'    => $slug,
					'title' => $module->title(),
					'intro' => $module->description(),
					'steps' => $module->help(),
					'on'    => WBD_PBB_Kit::is_enabled( $slug ),
					'url'   => WBD_PBB_Kit::url( $slug ),
				);
			}
		}
		return $sections;
	}

	/**
	 * Documentation page.
	 */
	private static function render_docs() {
		$sections = self::doc_sections();
		$woo      = WBD_Product_Benefits_Blocks::is_woocommerce_active();
		$allowed  = array(
			'strong' => array(),
			'code'   => array(),
			'em'     => array(),
		);
		?>
		<div class="wbd-dash__docs">
			<nav class="wbd-dash__card wbd-dash__toc" aria-label="<?php esc_attr_e( 'Documentation contents', 'wbd-product-benefits-blocks' ); ?>">
				<h2><?php esc_html_e( 'Contents', 'wbd-product-benefits-blocks' ); ?></h2>
				<ul>
					<?php foreach ( $sections as $section ) : ?>
						<li><a href="#doc-<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['title'] ); ?></a></li>
					<?php endforeach; ?>
					<?php if ( $woo ) : ?>
						<li><a href="#doc-shortcodes"><?php esc_html_e( 'Shortcodes', 'wbd-product-benefits-blocks' ); ?></a></li>
					<?php endif; ?>
					<li><a href="#doc-faq"><?php esc_html_e( 'FAQ', 'wbd-product-benefits-blocks' ); ?></a></li>
				</ul>
			</nav>
			<div class="wbd-dash__doc-body">
				<?php foreach ( $sections as $section ) : ?>
					<section class="wbd-dash__card wbd-dash__doc" id="doc-<?php echo esc_attr( $section['id'] ); ?>">
						<h2>
							<?php echo esc_html( $section['title'] ); ?>
							<?php
							if ( isset( $section['on'] ) ) {
								self::badge( $section['on'], __( 'On', 'wbd-product-benefits-blocks' ), __( 'Off', 'wbd-product-benefits-blocks' ) );
							}
							?>
						</h2>
						<?php if ( ! empty( $section['intro'] ) ) : ?>
							<p><?php echo esc_html( $section['intro'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $section['steps'] ) ) : ?>
							<ol>
								<?php foreach ( $section['steps'] as $step ) : ?>
									<li><?php echo wp_kses( $step, $allowed ); ?></li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
						<?php if ( ! empty( $section['url'] ) && current_user_can( 'manage_woocommerce' ) ) : ?>
							<p><a href="<?php echo esc_url( $section['url'] ); ?>"><?php echo esc_html( ! empty( $section['on'] ) ? __( 'Open settings →', 'wbd-product-benefits-blocks' ) : __( 'Switch on & open settings →', 'wbd-product-benefits-blocks' ) ); ?></a></p>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>

				<?php if ( $woo ) : ?>
					<section class="wbd-dash__card wbd-dash__doc" id="doc-shortcodes">
						<h2><?php esc_html_e( 'Shortcodes', 'wbd-product-benefits-blocks' ); ?></h2>
						<p><?php esc_html_e( 'For page builders, widgets or the classic editor. On a product page the product ID can be left out.', 'wbd-product-benefits-blocks' ); ?></p>
						<div class="wbd-dash__table-wrap">
							<table class="widefat striped">
								<thead><tr><th><?php esc_html_e( 'Shortcode', 'wbd-product-benefits-blocks' ); ?></th><th><?php esc_html_e( 'What it does', 'wbd-product-benefits-blocks' ); ?></th></tr></thead>
								<tbody>
									<tr><td><code>[wbd_sticky_add_to_cart product_id="123"]</code></td><td><?php esc_html_e( 'Sticky Add to Cart bar for a product.', 'wbd-product-benefits-blocks' ); ?></td></tr>
									<tr><td><code>[wbd_product_faqs product_id="123"]</code></td><td><?php esc_html_e( 'FAQ accordion of a product (Product FAQs must be on).', 'wbd-product-benefits-blocks' ); ?></td></tr>
									<tr><td><code>[wbd_product_video product_id="123" index="1"]</code></td><td><?php esc_html_e( 'A product video; index picks which video (Product Videos must be on).', 'wbd-product-benefits-blocks' ); ?></td></tr>
									<tr><td><code>[wbd_quick_view id="123" text="Quick view"]</code></td><td><?php esc_html_e( 'A Quick View button for any product (Quick View must be on).', 'wbd-product-benefits-blocks' ); ?></td></tr>
								</tbody>
							</table>
						</div>
					</section>
				<?php endif; ?>

				<section class="wbd-dash__card wbd-dash__doc" id="doc-faq">
					<h2><?php esc_html_e( 'Frequently asked questions', 'wbd-product-benefits-blocks' ); ?></h2>
					<?php foreach ( self::faqs() as $faq ) : ?>
						<details class="wbd-dash__faq">
							<summary><?php echo esc_html( $faq[0] ); ?></summary>
							<p><?php echo wp_kses( $faq[1], $allowed ); ?></p>
						</details>
					<?php endforeach; ?>
					<p><?php esc_html_e( 'Still stuck?', 'wbd-product-benefits-blocks' ); ?> <a href="<?php echo esc_url( self::url( self::PAGE_SUPPORT ) ); ?>"><?php esc_html_e( 'Get support →', 'wbd-product-benefits-blocks' ); ?></a></p>
				</section>
			</div>
		</div>
		<?php
	}

	/**
	 * FAQ entries: [ question, answer ].
	 *
	 * @return array[]
	 */
	private static function faqs() {
		return array(
			array( __( 'I can’t find the blocks in the editor.', 'wbd-product-benefits-blocks' ), __( 'Click <strong>+</strong> and search “WBD”. WooCommerce blocks only appear when WooCommerce is active, and FAQ/Video blocks only when that feature is switched on in WBD Woo Kit.', 'wbd-product-benefits-blocks' ) ),
			array( __( 'Will switched-off features slow down my site?', 'wbd-product-benefits-blocks' ), __( 'No. A feature that is off loads no scripts, styles or hooks.', 'wbd-product-benefits-blocks' ) ),
			array( __( 'Does it work with my theme?', 'wbd-product-benefits-blocks' ), __( 'Yes — block themes and classic themes. Features hook into standard WooCommerce templates; blocks and shortcodes can be placed anywhere.', 'wbd-product-benefits-blocks' ) ),
			array( __( 'A feature doesn’t show on the product page.', 'wbd-product-benefits-blocks' ), __( 'Check that it is <strong>On</strong> in WBD Woo Kit, that the product has content for it (FAQs, videos, bundled items) and clear any caching plugin.', 'wbd-product-benefits-blocks' ) ),
			array( __( 'Do bundles and composite products reduce stock?', 'wbd-product-benefits-blocks' ), __( 'Yes. Each product inside is added to the cart and the order as its own line, so stock is reduced per product.', 'wbd-product-benefits-blocks' ) ),
			array( __( 'What happens to my data if I delete the plugin?', 'wbd-product-benefits-blocks' ), __( 'Plugin settings are removed. Product content (FAQs, videos, bundle items) stays on the products.', 'wbd-product-benefits-blocks' ) ),
		);
	}

	/**
	 * Support page.
	 */
	private static function render_support() {
		?>
		<div class="wbd-dash__grid wbd-dash__grid--support">
			<div class="wbd-dash__card wbd-dash__item">
				<span class="dashicons dashicons-sos wbd-dash__icon" aria-hidden="true"></span>
				<h3><?php esc_html_e( 'Support forum', 'wbd-product-benefits-blocks' ); ?></h3>
				<p><?php esc_html_e( 'Questions, bugs or something not working? Open a topic in the free WordPress.org support forum.', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( self::SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get support', 'wbd-product-benefits-blocks' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wbd-product-benefits-blocks' ); ?></span></a>
			</div>
			<div class="wbd-dash__card wbd-dash__item">
				<span class="dashicons dashicons-book wbd-dash__icon" aria-hidden="true"></span>
				<h3><?php esc_html_e( 'Documentation', 'wbd-product-benefits-blocks' ); ?></h3>
				<p><?php esc_html_e( 'Step-by-step guides for every block and feature, shortcodes and answers to common questions.', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button" href="<?php echo esc_url( self::url( self::PAGE_DOCS ) ); ?>"><?php esc_html_e( 'Read the docs', 'wbd-product-benefits-blocks' ); ?></a>
			</div>
			<div class="wbd-dash__card wbd-dash__item">
				<span class="dashicons dashicons-lightbulb wbd-dash__icon" aria-hidden="true"></span>
				<h3><?php esc_html_e( 'Request a feature', 'wbd-product-benefits-blocks' ); ?></h3>
				<p><?php esc_html_e( 'Missing something for your store? Tell us in the forum — the most requested ideas are built first.', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button" href="<?php echo esc_url( self::SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Suggest an idea', 'wbd-product-benefits-blocks' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wbd-product-benefits-blocks' ); ?></span></a>
			</div>
			<div class="wbd-dash__card wbd-dash__item wbd-dash__review">
				<span class="dashicons dashicons-star-filled wbd-dash__icon" aria-hidden="true"></span>
				<h3><?php esc_html_e( 'Leave a review', 'wbd-product-benefits-blocks' ); ?></h3>
				<p><?php esc_html_e( 'Happy with the plugin? A 5-star review takes one minute and helps us a lot. Thank you!', 'wbd-product-benefits-blocks' ); ?></p>
				<a class="button button-primary" href="<?php echo esc_url( self::REVIEW_URL ); ?>" target="_blank" rel="noopener noreferrer">★★★★★ <?php esc_html_e( 'Write a review', 'wbd-product-benefits-blocks' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'wbd-product-benefits-blocks' ); ?></span></a>
			</div>
		</div>

		<section class="wbd-dash__card wbd-dash__sysinfo">
			<h2><?php esc_html_e( 'System information', 'wbd-product-benefits-blocks' ); ?></h2>
			<p><?php esc_html_e( 'When you ask for help, copy this and paste it into your forum topic. It contains no passwords or personal data.', 'wbd-product-benefits-blocks' ); ?></p>
			<label for="wbd-dash-sysinfo" class="screen-reader-text"><?php esc_html_e( 'System information', 'wbd-product-benefits-blocks' ); ?></label>
			<textarea id="wbd-dash-sysinfo" class="large-text code" rows="12" readonly><?php echo esc_textarea( self::system_info() ); ?></textarea>
			<p>
				<button type="button" class="button" data-wbd-copy="wbd-dash-sysinfo"><?php esc_html_e( 'Copy system info', 'wbd-product-benefits-blocks' ); ?></button>
				<span class="wbd-dash__copy-status" role="status" aria-live="polite"></span>
			</p>
		</section>
		<?php
	}

	/**
	 * Plain-text environment report (no URLs, users or secrets).
	 *
	 * @return string
	 */
	private static function system_info() {
		global $wp_version;
		$theme = wp_get_theme();
		$lines = array(
			'Plugin version'  => WBD_PRODUCT_BENEFITS_BLOCKS_VERSION,
			'WordPress'       => $wp_version . ( is_multisite() ? ' (multisite)' : '' ),
			'WooCommerce'     => defined( 'WC_VERSION' ) ? WC_VERSION : 'not active',
			'PHP'             => PHP_VERSION,
			'Theme'           => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ) . ( wp_is_block_theme() ? ' (block theme)' : ' (classic theme)' ),
			'Language'        => get_locale(),
			'Memory limit'    => WP_MEMORY_LIMIT,
			'Debug mode'      => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'on' : 'off',
		);
		if ( WBD_Product_Benefits_Blocks::is_woocommerce_active() ) {
			$lines['Sticky Add to Cart'] = ! empty( WBD_Product_Benefits_Blocks_Sticky_Settings::get()['enabled'] ) ? 'all products' : 'block/shortcode';
			foreach ( self::modules() as $slug => $module ) {
				$lines[ 'Woo Kit: ' . $slug ] = WBD_PBB_Kit::is_enabled( $slug ) ? 'on' : 'off';
			}
		}
		$plugins = array();
		foreach ( (array) get_option( 'active_plugins', array() ) as $file ) {
			$dir       = dirname( (string) $file );
			$plugins[] = '.' === $dir ? basename( (string) $file, '.php' ) : $dir;
		}
		$lines['Active plugins'] = implode( ', ', $plugins );

		$width = max( array_map( 'strlen', array_keys( $lines ) ) ) + 2;
		$out   = '';
		foreach ( $lines as $label => $value ) {
			$out .= str_pad( $label . ':', $width ) . $value . "\n";
		}
		return $out;
	}
}
