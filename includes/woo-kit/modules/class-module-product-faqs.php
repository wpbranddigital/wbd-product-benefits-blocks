<?php
/**
 * Product FAQs — per-product and global FAQs shown as a product tab, block or shortcode.
 *
 * @package WBD_Product_Benefits_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product FAQs module.
 */
final class WBD_PBB_Module_Product_Faqs extends WBD_PBB_Kit_Module {

	const META        = '_wbd_faqs';
	const META_GLOBAL = '_wbd_faqs_global';
	const META_HIDE   = '_wbd_faqs_hide_global';
	const CPT         = 'wbd_product_faq';
	const RULE_META   = '_wbd_faq_rule';
	const CATS_META   = '_wbd_faq_cats';
	const BLOCK       = 'wbd/product-faqs';
	const STYLE       = 'wbd-product-faqs';
	const MAX_ITEMS   = 100;

	/**
	 * FAQ items collected for FAQPage schema.
	 *
	 * @var array
	 */
	private $schema_items = array();

	/**
	 * Global FAQ cache.
	 *
	 * @var array|null
	 */
	private $globals = null;

	/**
	 * Instance counter for unique accordion names.
	 *
	 * @var int
	 */
	private $instance = 0;

	public function slug() {
		return 'product_faqs';
	}

	public function title() {
		return __( 'Product FAQs', 'wbd-product-benefits-blocks' );
	}

	public function description() {
		return __( 'Add questions and answers on each product (or once for many products) and show them as an accordion tab, block or shortcode.', 'wbd-product-benefits-blocks' );
	}

	public function fields() {
		return array(
			'display'         => array(
				'type'    => 'select',
				'label'   => __( 'Where to show', 'wbd-product-benefits-blocks' ),
				'default' => 'tab',
				'options' => array(
					'tab'           => __( 'As a product tab (next to Description)', 'wbd-product-benefits-blocks' ),
					'after_summary' => __( 'Under the Add to cart area', 'wbd-product-benefits-blocks' ),
					'after_tabs'    => __( 'Below the product tabs', 'wbd-product-benefits-blocks' ),
					'none'          => __( 'Only where I add the block or shortcode', 'wbd-product-benefits-blocks' ),
				),
			),
			'tab_title'       => array(
				'type'    => 'text',
				'label'   => __( 'Tab title', 'wbd-product-benefits-blocks' ),
				'default' => __( 'FAQs', 'wbd-product-benefits-blocks' ),
			),
			'tab_priority'    => array(
				'type'    => 'number',
				'label'   => __( 'Tab order', 'wbd-product-benefits-blocks' ),
				'default' => 25,
				'min'     => 1,
				'max'     => 200,
				'help'    => __( 'Description is 10, Additional information 20, Reviews 30.', 'wbd-product-benefits-blocks' ),
			),
			'heading'         => array(
				'type'    => 'text',
				'label'   => __( 'Heading', 'wbd-product-benefits-blocks' ),
				'default' => __( 'Frequently asked questions', 'wbd-product-benefits-blocks' ),
				'help'    => __( 'Leave empty for no heading.', 'wbd-product-benefits-blocks' ),
			),
			'style'           => array(
				'type'    => 'select',
				'label'   => __( 'Style', 'wbd-product-benefits-blocks' ),
				'default' => 'accordion',
				'options' => self::styles(),
			),
			'icon'            => array(
				'type'    => 'select',
				'label'   => __( 'Icon', 'wbd-product-benefits-blocks' ),
				'default' => 'plus',
				'options' => array(
					'plus'    => __( 'Plus / minus', 'wbd-product-benefits-blocks' ),
					'chevron' => __( 'Arrow', 'wbd-product-benefits-blocks' ),
				),
			),
			'accent'          => array(
				'type'    => 'color',
				'label'   => __( 'Accent color', 'wbd-product-benefits-blocks' ),
				'default' => '',
			),
			'first_open'      => array(
				'type'    => 'checkbox',
				'section' => __( 'Behaviour', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Open the first question', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
			'one_open'        => array(
				'type'    => 'checkbox',
				'label'   => __( 'Close other questions when one opens', 'wbd-product-benefits-blocks' ),
				'default' => 0,
			),
			'global_position' => array(
				'type'    => 'select',
				'label'   => __( 'Global FAQs', 'wbd-product-benefits-blocks' ),
				'default' => 'after',
				'options' => array(
					'after'  => __( 'After the product’s own FAQs', 'wbd-product-benefits-blocks' ),
					'before' => __( 'Before the product’s own FAQs', 'wbd-product-benefits-blocks' ),
				),
			),
			'schema'          => array(
				'type'    => 'checkbox',
				'section' => __( 'SEO', 'wbd-product-benefits-blocks' ),
				'label'   => __( 'Add FAQ structured data (FAQPage schema)', 'wbd-product-benefits-blocks' ),
				'default' => 1,
			),
		);
	}

	public function help() {
		return array(
			__( 'Edit a product, open <strong>Product data → FAQs</strong>, click <strong>Add question</strong>, type the question and answer, then <strong>Update</strong> the product.', 'wbd-product-benefits-blocks' ),
			__( 'Questions that apply to many products? Go to <strong>Products → FAQs → Add New</strong>. The title is the question, the text is the answer. Choose <em>All products</em>, some categories, or pick it on individual products.', 'wbd-product-benefits-blocks' ),
			__( 'Choose here where FAQs appear. By default they are a <strong>FAQs</strong> tab on the product page.', 'wbd-product-benefits-blocks' ),
			__( 'To place FAQs yourself, use the <strong>Product FAQs</strong> block or the shortcode <code>[wbd_product_faqs]</code> (add <code>product_id="123"</code> outside product pages, <code>style="boxed"</code> to change the look).', 'wbd-product-benefits-blocks' ),
		);
	}

	/**
	 * Style choices.
	 *
	 * @return array
	 */
	public static function styles() {
		return array(
			'accordion' => __( 'Accordion', 'wbd-product-benefits-blocks' ),
			'boxed'     => __( 'Boxed cards', 'wbd-product-benefits-blocks' ),
			'minimal'   => __( 'Minimal lines', 'wbd-product-benefits-blocks' ),
		);
	}

	public function init() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'init', array( $this, 'register_block' ), 20 );
		add_shortcode( 'wbd_product_faqs', array( $this, 'shortcode' ) );

		// Admin.
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'add_meta_boxes_' . self::CPT, array( $this, 'add_rule_box' ) );
		add_action( 'save_post_' . self::CPT, array( $this, 'save_rule' ), 10, 2 );

		// Frontend.
		add_filter( 'woocommerce_product_tabs', array( $this, 'product_tab' ), 98 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'print_after_summary' ), 45 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'print_after_tabs' ), 15 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
		add_action( 'wp_footer', array( $this, 'print_schema' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Data                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Registers the global FAQ post type (Products → FAQs).
	 */
	public function register_cpt() {
		register_post_type(
			self::CPT,
			array(
				'labels'          => array(
					'name'          => __( 'Product FAQs', 'wbd-product-benefits-blocks' ),
					'singular_name' => __( 'Product FAQ', 'wbd-product-benefits-blocks' ),
					'menu_name'     => __( 'FAQs', 'wbd-product-benefits-blocks' ),
					'all_items'     => __( 'FAQs', 'wbd-product-benefits-blocks' ),
					'add_new_item'  => __( 'Add new FAQ', 'wbd-product-benefits-blocks' ),
					'edit_item'     => __( 'Edit FAQ', 'wbd-product-benefits-blocks' ),
					'not_found'     => __( 'No FAQs yet.', 'wbd-product-benefits-blocks' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'edit.php?post_type=product',
				'show_in_rest'    => false,
				'supports'        => array( 'title', 'editor', 'page-attributes' ),
				'capability_type' => 'product',
				'map_meta_cap'    => true,
				'rewrite'         => false,
				'query_var'       => false,
			)
		);
	}

	/**
	 * Sanitizes a list of FAQ rows.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array
	 */
	public static function sanitize_rows( $rows ) {
		$clean = array();
		if ( ! is_array( $rows ) ) {
			return $clean;
		}
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$q = isset( $row['q'] ) ? sanitize_text_field( (string) $row['q'] ) : '';
			$a = isset( $row['a'] ) ? wp_kses_post( (string) $row['a'] ) : '';
			if ( '' === trim( $q ) ) {
				continue;
			}
			$clean[] = array(
				'q' => function_exists( 'mb_substr' ) ? mb_substr( $q, 0, 300 ) : substr( $q, 0, 300 ),
				'a' => $a,
			);
			if ( count( $clean ) >= self::MAX_ITEMS ) {
				break;
			}
		}
		return $clean;
	}

	/**
	 * Published global FAQs with their rules.
	 *
	 * @return array
	 */
	private function get_globals() {
		if ( null === $this->globals ) {
			$this->globals = array();
			$posts         = get_posts(
				array(
					'post_type'        => self::CPT,
					'post_status'      => 'publish',
					'numberposts'      => self::MAX_ITEMS,
					'orderby'          => array(
						'menu_order' => 'ASC',
						'date'       => 'ASC',
					),
					'suppress_filters' => false,
				)
			);
			foreach ( $posts as $post ) {
				$rule                       = get_post_meta( $post->ID, self::RULE_META, true );
				$this->globals[ $post->ID ] = array(
					'q'    => $post->post_title,
					'a'    => $post->post_content,
					'rule' => in_array( $rule, array( 'all', 'categories', 'manual' ), true ) ? $rule : 'all',
					'cats' => array_map( 'intval', (array) get_post_meta( $post->ID, self::CATS_META, true ) ),
				);
			}
		}
		return $this->globals;
	}

	/**
	 * FAQs for a product (own + global).
	 *
	 * @param int $product_id Product ID.
	 * @return array List of array( q, a ).
	 */
	public function get_faqs( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return array();
		}

		$own    = self::sanitize_rows( get_post_meta( $product_id, self::META, true ) );
		$global = array();

		if ( ! get_post_meta( $product_id, self::META_HIDE, true ) ) {
			$picked  = array_map( 'intval', (array) get_post_meta( $product_id, self::META_GLOBAL, true ) );
			$cat_ids = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
			$cat_ids = is_wp_error( $cat_ids ) ? array() : array_map( 'intval', $cat_ids );

			foreach ( $this->get_globals() as $id => $faq ) {
				$match = ( 'all' === $faq['rule'] )
					|| ( 'categories' === $faq['rule'] && array_intersect( $faq['cats'], $cat_ids ) )
					|| in_array( (int) $id, $picked, true );
				if ( $match && '' !== trim( $faq['q'] ) ) {
					$global[] = array(
						'q' => $faq['q'],
						'a' => $faq['a'],
					);
				}
			}
		}

		$faqs = 'before' === $this->get( 'global_position' ) ? array_merge( $global, $own ) : array_merge( $own, $global );

		/**
		 * Filters the FAQs shown for a product.
		 *
		 * @param array $faqs       List of array( q, a ).
		 * @param int   $product_id Product ID.
		 */
		return (array) apply_filters( 'wbd_pbb_product_faqs', $faqs, $product_id );
	}

	/* ------------------------------------------------------------------ */
	/* Admin                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Adds Product data → FAQs.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function product_data_tab( $tabs ) {
		$tabs['wbd_faqs'] = array(
			'label'    => __( 'FAQs', 'wbd-product-benefits-blocks' ),
			'target'   => 'wbd_faqs_data',
			'class'    => array(),
			'priority' => 75,
		);
		return $tabs;
	}

	/**
	 * Prints one admin FAQ row.
	 *
	 * @param string|int $index Row index.
	 * @param string     $q     Question.
	 * @param string     $a     Answer.
	 */
	private function admin_row( $index, $q, $a ) {
		$base = 'wbd_faqs[' . $index . ']';
		?>
		<div class="wbd-faqs-admin__item">
			<span class="wbd-faqs-admin__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'wbd-product-benefits-blocks' ); ?>" aria-hidden="true"></span>
			<div class="wbd-faqs-admin__fields">
				<input type="text" class="wbd-faqs-admin__q" name="<?php echo esc_attr( $base . '[q]' ); ?>" value="<?php echo esc_attr( $q ); ?>" placeholder="<?php esc_attr_e( 'Question, e.g. How long does delivery take?', 'wbd-product-benefits-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Question', 'wbd-product-benefits-blocks' ); ?>" maxlength="300" />
				<textarea class="wbd-faqs-admin__a" name="<?php echo esc_attr( $base . '[a]' ); ?>" rows="3" placeholder="<?php esc_attr_e( 'Answer (basic HTML such as links and bold text is allowed)', 'wbd-product-benefits-blocks' ); ?>" aria-label="<?php esc_attr_e( 'Answer', 'wbd-product-benefits-blocks' ); ?>"><?php echo esc_textarea( $a ); ?></textarea>
			</div>
			<div class="wbd-faqs-admin__actions">
				<button type="button" class="button-link wbd-faqs-admin__up" aria-label="<?php esc_attr_e( 'Move up', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
				<button type="button" class="button-link wbd-faqs-admin__down" aria-label="<?php esc_attr_e( 'Move down', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
				<button type="button" class="button-link button-link-delete wbd-faqs-admin__remove" aria-label="<?php esc_attr_e( 'Remove question', 'wbd-product-benefits-blocks' ); ?>"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Prints the FAQs panel on the product edit screen.
	 */
	public function product_data_panel() {
		global $post;
		$product_id = $post ? (int) $post->ID : 0;
		$rows       = self::sanitize_rows( get_post_meta( $product_id, self::META, true ) );
		$picked     = array_map( 'intval', (array) get_post_meta( $product_id, self::META_GLOBAL, true ) );
		$globals    = $this->get_globals();
		?>
		<div id="wbd_faqs_data" class="panel woocommerce_options_panel hidden">
			<div class="wbd-faqs-admin">
				<?php wp_nonce_field( 'wbd_faqs_save', 'wbd_faqs_nonce' ); ?>
				<p class="wbd-faqs-admin__intro">
					<?php esc_html_e( 'Answer the questions shoppers ask before buying. They appear on the product page as an accordion.', 'wbd-product-benefits-blocks' ); ?>
					<a href="<?php echo esc_url( WBD_PBB_Kit::url( $this->slug() ) ); ?>"><?php esc_html_e( 'Display settings', 'wbd-product-benefits-blocks' ); ?></a>
				</p>
				<div class="wbd-faqs-admin__list" data-next-index="<?php echo esc_attr( count( $rows ) ); ?>">
					<?php
					foreach ( $rows as $i => $row ) {
						$this->admin_row( $i, $row['q'], $row['a'] );
					}
					?>
				</div>
				<p class="wbd-faqs-admin__empty" <?php echo $rows ? 'hidden' : ''; ?>><?php esc_html_e( 'No questions yet.', 'wbd-product-benefits-blocks' ); ?></p>
				<p><button type="button" class="button button-secondary wbd-faqs-admin__add"><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> <?php esc_html_e( 'Add question', 'wbd-product-benefits-blocks' ); ?></button></p>
				<template class="wbd-faqs-admin__template"><?php $this->admin_row( '__i__', '', '' ); ?></template>

				<div class="wbd-faqs-admin__global">
					<h4><?php esc_html_e( 'Global FAQs', 'wbd-product-benefits-blocks' ); ?></h4>
					<?php if ( $globals ) : ?>
						<p class="description"><?php esc_html_e( 'FAQs saved under Products → FAQs. Tick the ones to add here; FAQs set to “All products” or a matching category are added automatically.', 'wbd-product-benefits-blocks' ); ?></p>
						<div class="wbd-faqs-admin__global-list">
							<?php foreach ( $globals as $id => $faq ) : ?>
								<label>
									<?php if ( 'manual' === $faq['rule'] ) : ?>
										<input type="checkbox" name="wbd_faqs_global[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( in_array( (int) $id, $picked, true ) ); ?> />
									<?php else : ?>
										<input type="checkbox" checked disabled />
									<?php endif; ?>
									<?php echo esc_html( $faq['q'] ); ?>
									<?php if ( 'manual' !== $faq['rule'] ) : ?>
										<em>(<?php echo esc_html( 'all' === $faq['rule'] ? __( 'all products', 'wbd-product-benefits-blocks' ) : __( 'by category', 'wbd-product-benefits-blocks' ) ); ?>)</em>
									<?php endif; ?>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="description">
							<?php esc_html_e( 'Same question on many products? Create it once under Products → FAQs.', 'wbd-product-benefits-blocks' ); ?>
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>"><?php esc_html_e( 'Add a global FAQ', 'wbd-product-benefits-blocks' ); ?></a>
						</p>
					<?php endif; ?>
					<label class="wbd-faqs-admin__hide">
						<input type="checkbox" name="wbd_faqs_hide_global" value="1" <?php checked( (bool) get_post_meta( $product_id, self::META_HIDE, true ) ); ?> />
						<?php esc_html_e( 'Do not show global FAQs on this product', 'wbd-product-benefits-blocks' ); ?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves product FAQs. WooCommerce has already checked its own nonce and
	 * edit_post; we check ours too.
	 *
	 * @param int $post_id Product ID.
	 */
	public function save_product( $post_id ) {
		if ( ! isset( $_POST['wbd_faqs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wbd_faqs_nonce'] ) ), 'wbd_faqs_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize_rows().
		$rows = isset( $_POST['wbd_faqs'] ) ? self::sanitize_rows( wp_unslash( $_POST['wbd_faqs'] ) ) : array();
		if ( $rows ) {
			update_post_meta( $post_id, self::META, $rows );
		} else {
			delete_post_meta( $post_id, self::META );
		}

		$picked = isset( $_POST['wbd_faqs_global'] ) ? array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['wbd_faqs_global'] ) ) ) ) ) : array();
		if ( $picked ) {
			update_post_meta( $post_id, self::META_GLOBAL, $picked );
		} else {
			delete_post_meta( $post_id, self::META_GLOBAL );
		}

		if ( ! empty( $_POST['wbd_faqs_hide_global'] ) ) {
			update_post_meta( $post_id, self::META_HIDE, 1 );
		} else {
			delete_post_meta( $post_id, self::META_HIDE );
		}
	}

	/**
	 * Admin assets for the product screen.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function admin_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_style( 'wbd-pbb-product-faqs-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/product-faqs.css', array(), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_enqueue_script( 'wbd-pbb-product-faqs-admin', WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'assets/admin/product-faqs.js', array( 'jquery', 'jquery-ui-sortable' ), WBD_PRODUCT_BENEFITS_BLOCKS_VERSION, true );
		wp_localize_script( 'wbd-pbb-product-faqs-admin', 'wbdPbbFaqs', array( 'confirmRemove' => __( 'Remove this question?', 'wbd-product-benefits-blocks' ) ) );
	}

	/**
	 * "Show on" box for global FAQs.
	 */
	public function add_rule_box() {
		add_meta_box( 'wbd_faq_rule', __( 'Show this FAQ on', 'wbd-product-benefits-blocks' ), array( $this, 'render_rule_box' ), self::CPT, 'side', 'high' );
	}

	/**
	 * Renders the "Show on" box.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_rule_box( $post ) {
		$rule = get_post_meta( $post->ID, self::RULE_META, true );
		$rule = $rule ? $rule : 'all';
		$cats = array_map( 'intval', (array) get_post_meta( $post->ID, self::CATS_META, true ) );
		wp_nonce_field( 'wbd_faq_rule_save', 'wbd_faq_rule_nonce' );
		$options = array(
			'all'        => __( 'All products', 'wbd-product-benefits-blocks' ),
			'categories' => __( 'Products in these categories', 'wbd-product-benefits-blocks' ),
			'manual'     => __( 'Only products where I tick it (Product data → FAQs)', 'wbd-product-benefits-blocks' ),
		);
		foreach ( $options as $value => $label ) {
			printf( '<p><label><input type="radio" name="wbd_faq_rule" value="%s" %s /> %s</label></p>', esc_attr( $value ), checked( $rule, $value, false ), esc_html( $label ) );
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 200,
			)
		);
		if ( ! is_wp_error( $terms ) && $terms ) {
			echo '<div style="max-height:180px;overflow:auto;padding-left:22px">';
			foreach ( $terms as $term ) {
				printf( '<label style="display:block"><input type="checkbox" name="wbd_faq_cats[]" value="%d" %s /> %s</label>', (int) $term->term_id, checked( in_array( (int) $term->term_id, $cats, true ), true, false ), esc_html( $term->name ) );
			}
			echo '</div>';
		}
		echo '<p class="description">' . esc_html__( 'The title is the question and the text is the answer.', 'wbd-product-benefits-blocks' ) . '</p>';
	}

	/**
	 * Saves the "Show on" box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_rule( $post_id, $post ) {
		if ( ! isset( $_POST['wbd_faq_rule_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wbd_faq_rule_nonce'] ) ), 'wbd_faq_rule_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$rule = isset( $_POST['wbd_faq_rule'] ) ? sanitize_key( wp_unslash( $_POST['wbd_faq_rule'] ) ) : 'all';
		update_post_meta( $post_id, self::RULE_META, in_array( $rule, array( 'all', 'categories', 'manual' ), true ) ? $rule : 'all' );
		$cats = isset( $_POST['wbd_faq_cats'] ) ? array_values( array_unique( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['wbd_faq_cats'] ) ) ) ) ) : array();
		update_post_meta( $post_id, self::CATS_META, $cats );
	}

	/* ------------------------------------------------------------------ */
	/* Frontend                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Registers the stylesheet and block.
	 */
	public function register_block() {
		$css = WBD_Product_Benefits_Blocks::get_block_build_path( 'product-faqs' ) . '/style-index.css';
		wp_register_style( self::STYLE, WBD_PRODUCT_BENEFITS_BLOCKS_URL . 'build/product-faqs/style-index.css', array(), file_exists( $css ) ? WBD_PRODUCT_BENEFITS_BLOCKS_VERSION . '.' . filemtime( $css ) : WBD_PRODUCT_BENEFITS_BLOCKS_VERSION );
		wp_style_add_data( self::STYLE, 'rtl', 'replace' );

		$path = WBD_Product_Benefits_Blocks::get_block_build_path( 'product-faqs' );
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
	 * Current product ID on single product pages.
	 *
	 * @return int
	 */
	private function current_product_id() {
		return ( function_exists( 'is_product' ) && is_product() ) ? (int) get_queried_object_id() : 0;
	}

	/**
	 * Loads the stylesheet early on product pages that show FAQs automatically.
	 */
	public function maybe_enqueue() {
		$id = $this->current_product_id();
		if ( $id && 'none' !== $this->get( 'display' ) && $this->get_faqs( $id ) ) {
			wp_enqueue_style( self::STYLE );
		}
	}

	/**
	 * Adds the FAQs tab.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public function product_tab( $tabs ) {
		global $product;
		if ( 'tab' !== $this->get( 'display' ) || ! $product instanceof WC_Product || ! $this->get_faqs( $product->get_id() ) ) {
			return $tabs;
		}
		$tabs['wbd_faqs'] = array(
			'title'    => $this->get( 'tab_title' ) ? $this->get( 'tab_title' ) : __( 'FAQs', 'wbd-product-benefits-blocks' ),
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
	 * Under the Add to cart area.
	 */
	public function print_after_summary() {
		global $product;
		if ( 'after_summary' === $this->get( 'display' ) && $product instanceof WC_Product ) {
			echo $this->render( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render().
		}
	}

	/**
	 * Below the product tabs.
	 */
	public function print_after_tabs() {
		global $product;
		if ( 'after_tabs' === $this->get( 'display' ) && $product instanceof WC_Product ) {
			echo $this->render( $product->get_id() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in render().
		}
	}

	/**
	 * Shortcode [wbd_product_faqs product_id="" style="" heading=""].
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product_id' => 0,
				'style'      => '',
				'heading'    => null,
			),
			$atts,
			'wbd_product_faqs'
		);
		$id   = absint( $atts['product_id'] ) ? absint( $atts['product_id'] ) : $this->current_product_id();
		if ( ! $id ) {
			global $product;
			$id = $product instanceof WC_Product ? $product->get_id() : 0;
		}
		return $this->render(
			$id,
			array(
				'style'   => $atts['style'],
				'heading' => $atts['heading'],
			)
		);
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
		$args = array(
			'style'   => isset( $attributes['style'] ) ? $attributes['style'] : '',
			'heading' => ( isset( $attributes['heading'] ) && '' !== $attributes['heading'] ) ? $attributes['heading'] : null,
		);
		if ( isset( $attributes['showHeading'] ) && ! $attributes['showHeading'] ) {
			$args['heading'] = '';
		}
		$id = isset( $attributes['productId'] ) ? absint( $attributes['productId'] ) : 0;
		if ( ! $id && $block instanceof WP_Block && ! empty( $block->context['postId'] ) && 'product' === get_post_type( $block->context['postId'] ) ) {
			$id = (int) $block->context['postId'];
		}
		if ( ! $id ) {
			$id = $this->current_product_id();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only editor preview flag.
		$preview = defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['wbdFaqsPreview'] ) && current_user_can( 'edit_posts' );
		if ( $preview ) {
			$args['preview'] = true;
			$faqs            = $id ? $this->get_faqs( $id ) : array();
			if ( ! $faqs ) {
				$args['faqs'] = array(
					array(
						'q' => __( 'How long does delivery take?', 'wbd-product-benefits-blocks' ),
						'a' => __( 'Most orders arrive in 2–4 working days.', 'wbd-product-benefits-blocks' ),
					),
					array(
						'q' => __( 'Can I return this product?', 'wbd-product-benefits-blocks' ),
						'a' => __( 'Yes, within 30 days in its original condition.', 'wbd-product-benefits-blocks' ),
					),
				);
			}
		}

		$html = $this->render( $id, $args );
		if ( '' === $html ) {
			return '';
		}
		return '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>';
	}

	/**
	 * Builds the FAQ markup. Uses <details>, so it works without JavaScript.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $args       style, heading (null = setting), faqs (override), preview.
	 * @return string
	 */
	public function render( $product_id, $args = array() ) {
		if ( isset( $args['faqs'] ) ) {
			$faqs = self::sanitize_rows( $args['faqs'] );
		} else {
			$post = $product_id ? get_post( $product_id ) : null;
			if ( ! $post || 'product' !== $post->post_type || ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) || post_password_required( $post ) ) {
				return '';
			}
			$faqs = $this->get_faqs( $product_id );
		}
		if ( ! $faqs ) {
			return '';
		}

		wp_enqueue_style( self::STYLE );
		++$this->instance;

		$styles  = self::styles();
		$style   = ( ! empty( $args['style'] ) && isset( $styles[ $args['style'] ] ) ) ? $args['style'] : $this->get( 'style' );
		$heading = ( isset( $args['heading'] ) && null !== $args['heading'] ) ? sanitize_text_field( $args['heading'] ) : $this->get( 'heading' );
		$accent  = $this->get( 'accent' );
		$name    = $this->get( 'one_open' ) ? ' name="wbd-faqs-' . (int) $this->instance . '"' : '';

		$html = sprintf(
			'<section class="wbd-faqs wbd-faqs--%1$s wbd-faqs--icon-%2$s"%3$s>',
			esc_attr( $style ),
			esc_attr( 'chevron' === $this->get( 'icon' ) ? 'chevron' : 'plus' ),
			$accent ? ' style="--wbd-faqs-accent:' . esc_attr( $accent ) . '"' : ''
		);
		if ( '' !== $heading ) {
			$html .= '<h2 class="wbd-faqs__heading">' . esc_html( $heading ) . '</h2>';
		}
		$html .= '<div class="wbd-faqs__list">';
		foreach ( $faqs as $i => $faq ) {
			$open  = ( 0 === $i && $this->get( 'first_open' ) ) ? ' open' : '';
			$html .= '<details class="wbd-faq"' . $name . $open . '>';
			$html .= '<summary class="wbd-faq__q"><span class="wbd-faq__q-text">' . esc_html( $faq['q'] ) . '</span><span class="wbd-faq__icon" aria-hidden="true"></span></summary>';
			$html .= '<div class="wbd-faq__a">' . wpautop( wp_kses_post( $faq['a'] ) ) . '</div>';
			$html .= '</details>';

			if ( empty( $args['preview'] ) && ! isset( $this->schema_items[ $faq['q'] ] ) ) {
				$this->schema_items[ $faq['q'] ] = wp_strip_all_tags( $faq['a'] );
			}
		}
		$html .= '</div></section>';

		return $html;
	}

	/**
	 * Prints FAQPage JSON-LD for the FAQs shown on this page.
	 */
	public function print_schema() {
		if ( ! $this->get( 'schema' ) || ! $this->schema_items || is_admin() ) {
			return;
		}
		$entities = array();
		foreach ( $this->schema_items as $question => $answer ) {
			if ( '' === trim( $answer ) ) {
				continue;
			}
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer,
				),
			);
		}
		if ( ! $entities ) {
			return;
		}
		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
	}
}
