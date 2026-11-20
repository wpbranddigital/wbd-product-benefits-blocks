/**
 * Sticky Add to Cart — frontend behaviour.
 *
 * Plain JavaScript with no dependencies. The bar:
 * - shows once the main Add to cart button scrolls out of view (or always);
 * - adds simple products with WooCommerce's own `add_to_cart` AJAX endpoint;
 * - hands variable/grouped products, and forms with extra fields, to the
 *   product's own form so variations, add-ons and validation keep working;
 * - falls back to a normal form submit if anything fails.
 */

const NATIVE_FORM_SELECTOR =
	'form.cart, form.wp-block-woocommerce-add-to-cart-with-options';
const NATIVE_BUTTON_SELECTOR =
	'.single_add_to_cart_button, button[type="submit"]';
const NATIVE_FIELDS = [
	'quantity',
	'add-to-cart',
	'product_id',
	'variation_id',
	'gtm4wp_product_data',
];

const prefersReducedMotion = () =>
	window.matchMedia &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/**
 * The product's main add to cart form on this page, if any.
 *
 * @return {HTMLFormElement|null} Form.
 */
function findNativeForm() {
	const forms = document.querySelectorAll( NATIVE_FORM_SELECTOR );
	for ( const form of forms ) {
		if ( ! form.closest( '.wbd-satc' ) ) {
			return form;
		}
	}
	return null;
}

/**
 * Whether the native form has fields beyond the standard ones (add-ons,
 * gift messages, custom options…) that only the native form can submit.
 *
 * @param {HTMLFormElement} form Form.
 * @return {boolean} Has extra fields.
 */
function hasExtraFields( form ) {
	return Array.from( form.elements ).some(
		( el ) =>
			el.name &&
			! el.disabled &&
			'hidden' !== el.type &&
			'submit' !== el.type &&
			'button' !== el.type &&
			! NATIVE_FIELDS.includes( el.name )
	);
}

/**
 * Replaces WooCommerce cart fragments (mini cart, cart count…).
 *
 * @param {Object} fragments Selector => HTML map from WooCommerce.
 */
function replaceFragments( fragments ) {
	if ( ! fragments || 'object' !== typeof fragments ) {
		return;
	}
	Object.keys( fragments ).forEach( ( selector ) => {
		let nodes = [];
		try {
			nodes = document.querySelectorAll( selector );
		} catch ( e ) {
			return;
		}
		nodes.forEach( ( node ) => {
			const template = document.createElement( 'template' );
			template.innerHTML = fragments[ selector ];
			if ( template.content.firstElementChild ) {
				node.replaceWith( template.content.firstElementChild );
			}
		} );
	} );
}

/**
 * Lets the theme, mini cart and other plugins know the cart changed.
 *
 * @param {Object}      data   AJAX response.
 * @param {HTMLElement} button Bar button.
 */
function announceAddedToCart( data, button ) {
	replaceFragments( data.fragments );

	// Classic themes / WooCommerce cart fragments listen to this jQuery event.
	if ( window.jQuery ) {
		window
			.jQuery( document.body )
			.trigger( 'added_to_cart', [
				data.fragments,
				data.cart_hash,
				window.jQuery( button ),
			] );
	}

	// The Mini-Cart block refreshes on this event.
	document.body.dispatchEvent(
		new window.CustomEvent( 'wc-blocks_added_to_cart', {
			bubbles: true,
			cancelable: true,
		} )
	);
}

/**
 * Initialises one bar.
 *
 * @param {HTMLElement} bar Bar element.
 */
function initBar( bar ) {
	let config;
	try {
		config = JSON.parse( bar.getAttribute( 'data-wbd-satc' ) || '{}' );
	} catch ( e ) {
		return;
	}

	// Escape transformed/overflow ancestors so position: fixed is reliable.
	if ( bar.parentNode !== document.body ) {
		document.body.appendChild( bar );
	}

	const i18n = config.i18n || {};
	const isTop = bar.classList.contains( 'wbd-satc--top' );
	const nativeForm = config.native ? findNativeForm() : null;
	const nativeButton = nativeForm
		? nativeForm.querySelector( NATIVE_BUTTON_SELECTOR )
		: null;
	const button = bar.querySelector( '.wbd-satc__button' );
	const qtyInput = bar.querySelector( '.wbd-satc__qty-input' );
	const actions = bar.querySelector( '.wbd-satc__actions' );
	const status = bar.querySelector( '.wbd-satc__status' );
	const viewCart = bar.querySelector( '.wbd-satc__view-cart' );
	let visible = false;
	let wanted = false;

	/* Visibility ------------------------------------------------------- */

	const hasKeyboardFocus = () => {
		try {
			return !! bar.querySelector( ':focus-visible' );
		} catch ( e ) {
			return false;
		}
	};

	const updateOffset = () => {
		const height = visible ? bar.offsetHeight : 0;
		document.documentElement.style.setProperty(
			'--wbd-satc-offset',
			height + 'px'
		);
		document.body.classList.toggle(
			'wbd-satc-visible-bottom',
			visible && ! isTop
		);
	};

	const setVisible = ( next ) => {
		wanted = next;
		if ( next === visible ) {
			return;
		}
		// Keep the bar open while a keyboard user is working inside it.
		if ( ! next && hasKeyboardFocus() ) {
			return;
		}
		visible = next;
		bar.classList.toggle( 'is-visible', visible );
		bar.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
		bar.inert = ! visible;
		if ( ! visible ) {
			bar.setAttribute( 'inert', '' );
		} else {
			bar.removeAttribute( 'inert' );
		}
		updateOffset();
	};

	const anchor = nativeButton || nativeForm;

	if ( 'always' === config.trigger ) {
		setVisible( true );
	} else if ( anchor && 'IntersectionObserver' in window ) {
		const observer = new window.IntersectionObserver( ( entries ) => {
			const entry = entries[ entries.length - 1 ];
			setVisible(
				! entry.isIntersecting && entry.boundingClientRect.bottom < 0
			);
		} );
		observer.observe( anchor );
	} else {
		// No main button on this page (landing pages): show after a short scroll.
		let ticking = false;
		const onScroll = () => {
			if ( ticking ) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame( () => {
				ticking = false;
				setVisible( window.scrollY > 240 );
			} );
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	window.addEventListener( 'resize', updateOffset, { passive: true } );
	bar.addEventListener( 'focusout', () => {
		window.setTimeout( () => {
			if ( ! wanted && visible && ! hasKeyboardFocus() ) {
				setVisible( false );
			}
		}, 0 );
	} );

	/* Quantity --------------------------------------------------------- */

	const clampQty = ( value ) => {
		const min = parseFloat( qtyInput.min ) || 1;
		const max = parseFloat( qtyInput.max ) || Infinity;
		let qty = parseInt( value, 10 );
		if ( isNaN( qty ) ) {
			qty = min;
		}
		return Math.min( max, Math.max( min, qty ) );
	};

	if ( qtyInput ) {
		bar.querySelectorAll( '.wbd-satc__qty-btn' ).forEach( ( stepper ) => {
			stepper.addEventListener( 'click', () => {
				const step = parseInt( stepper.dataset.step, 10 ) || 0;
				qtyInput.value = clampQty(
					parseInt( qtyInput.value, 10 ) + step
				);
			} );
		} );
		qtyInput.addEventListener( 'change', () => {
			qtyInput.value = clampQty( qtyInput.value );
		} );
	}

	/* Actions ---------------------------------------------------------- */

	const setStatus = ( message ) => {
		if ( status ) {
			status.textContent = message || '';
		}
	};

	const setLoading = ( loading ) => {
		if ( ! button ) {
			return;
		}
		button.classList.toggle( 'is-loading', loading );
		button.disabled = loading;
		button.setAttribute( 'aria-busy', loading ? 'true' : 'false' );
		if ( loading ) {
			setStatus( i18n.adding );
		}
	};

	const scrollToNativeForm = () => {
		const target = nativeForm || anchor;
		if ( ! target ) {
			return;
		}
		target.scrollIntoView( {
			behavior: prefersReducedMotion() ? 'auto' : 'smooth',
			block: 'center',
		} );
		const field = target.querySelector(
			'select, input:not([type="hidden"]), textarea'
		);
		if ( field ) {
			window.setTimeout(
				() => field.focus( { preventScroll: true } ),
				prefersReducedMotion() ? 0 : 450
			);
		}
	};

	/**
	 * Copies the quantity into the native form and presses its button.
	 * Scrolls to the form instead when options still need choosing.
	 */
	const useNativeForm = () => {
		if ( ! nativeForm ) {
			return false;
		}
		const nativeQty = nativeForm.querySelector(
			'input.qty, input[name="quantity"]'
		);
		if ( qtyInput && nativeQty ) {
			nativeQty.value = qtyInput.value;
			nativeQty.dispatchEvent(
				new window.Event( 'input', { bubbles: true } )
			);
			nativeQty.dispatchEvent(
				new window.Event( 'change', { bubbles: true } )
			);
		}

		const needsChoice =
			! nativeButton ||
			nativeButton.disabled ||
			nativeButton.classList.contains( 'disabled' ) ||
			nativeButton.classList.contains(
				'wc-variation-selection-needed'
			) ||
			'grouped' === config.type;

		if ( needsChoice ) {
			scrollToNativeForm();
		} else {
			nativeButton.click();
		}
		return true;
	};

	const afterAdded = () => {
		if ( 'checkout' === config.afterAdd && config.checkoutUrl ) {
			window.location.assign( config.checkoutUrl );
			return;
		}
		if ( 'cart' === config.afterAdd && config.cartUrl ) {
			window.location.assign( config.cartUrl );
			return;
		}
		setStatus( i18n.added );
		bar.classList.add( 'is-added' );
		if ( viewCart ) {
			viewCart.hidden = false;
		}
	};

	const ajaxAdd = () => {
		const body = new window.FormData();
		body.append( 'product_id', String( config.productId ) );
		body.append( 'quantity', qtyInput ? String( qtyInput.value ) : '1' );

		setLoading( true );

		window
			.fetch( config.ajaxUrl.replace( '%%endpoint%%', 'add_to_cart' ), {
				method: 'POST',
				body,
				credentials: 'same-origin',
			} )
			.then( ( response ) => response.json() )
			.then( ( data ) => {
				setLoading( false );
				if ( ! data || data.error ) {
					// WooCommerce asks to visit the product (validation, stock…).
					if ( data && data.product_url ) {
						window.location.assign( data.product_url );
						return;
					}
					setStatus( i18n.error );
					return;
				}
				announceAddedToCart( data, button );
				afterAdded();
			} )
			.catch( () => {
				setLoading( false );
				// Network/JSON failure: fall back to a regular form post.
				if ( actions && 'FORM' === actions.tagName ) {
					HTMLFormElement.prototype.submit.call( actions );
				} else {
					setStatus( i18n.error );
				}
			} );
	};

	if ( actions && 'FORM' === actions.tagName ) {
		actions.addEventListener( 'submit', ( event ) => {
			if ( button && button.disabled ) {
				event.preventDefault();
				return;
			}
			// Extra product fields (add-ons etc.) live in the native form.
			if ( nativeForm && hasExtraFields( nativeForm ) ) {
				event.preventDefault();
				useNativeForm();
				return;
			}
			if ( config.ajax && config.ajaxUrl && window.fetch ) {
				event.preventDefault();
				ajaxAdd();
			}
			// Otherwise: normal POST to the product page (no JS needed).
		} );
	} else if ( button && 'native' === button.dataset.wbdAction ) {
		button.addEventListener( 'click', () => {
			if ( ! useNativeForm() && config.productUrl ) {
				window.location.assign( config.productUrl );
			}
		} );
	}
}

function init() {
	document.querySelectorAll( '.wbd-satc[data-wbd-satc]' ).forEach( initBar );
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
