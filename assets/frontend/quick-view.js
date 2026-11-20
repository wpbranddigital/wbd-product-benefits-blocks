/**
 * Smart Quick View — popup with product summary and add to cart.
 * Vanilla JS; uses jQuery only when present for WooCommerce's variation form
 * and cart-fragment events.
 */
( function () {
	'use strict';

	var cfg = window.wbdPbbQuickView;
	if ( ! cfg ) {
		return;
	}
	var i18n = cfg.i18n || {};
	var dialog = null;
	var body = null;
	var ids = [];
	var index = -1;
	var request = 0;

	function collectIds() {
		var seen = {};
		ids = [];
		document.querySelectorAll( '[data-wbd-qv]' ).forEach( function ( el ) {
			var id = el.getAttribute( 'data-wbd-qv' );
			if ( ! seen[ id ] ) {
				seen[ id ] = true;
				ids.push( id );
			}
		} );
	}

	function build() {
		if ( dialog ) {
			return;
		}
		dialog = document.createElement( 'dialog' );
		dialog.className = 'wbd-qv-dialog wbd-qv-dialog--' + ( cfg.effect || 'zoom' );
		dialog.setAttribute( 'aria-label', i18n.dialog || 'Product quick view' );
		dialog.innerHTML =
			'<div class="wbd-qv-dialog__frame">' +
			'<button type="button" class="wbd-qv-dialog__close"><span aria-hidden="true">&times;</span></button>' +
			'<div class="wbd-qv-dialog__body"></div>' +
			'<button type="button" class="wbd-qv-dialog__nav wbd-qv-dialog__nav--prev"><span aria-hidden="true">&#8249;</span></button>' +
			'<button type="button" class="wbd-qv-dialog__nav wbd-qv-dialog__nav--next"><span aria-hidden="true">&#8250;</span></button>' +
			'</div>';
		body = dialog.querySelector( '.wbd-qv-dialog__body' );
		var close = dialog.querySelector( '.wbd-qv-dialog__close' );
		close.setAttribute( 'aria-label', i18n.close || 'Close' );
		close.addEventListener( 'click', function () {
			dialog.close();
		} );
		var prev = dialog.querySelector( '.wbd-qv-dialog__nav--prev' );
		var next = dialog.querySelector( '.wbd-qv-dialog__nav--next' );
		prev.setAttribute( 'aria-label', i18n.prev || 'Previous product' );
		next.setAttribute( 'aria-label', i18n.next || 'Next product' );
		prev.addEventListener( 'click', function () {
			step( -1 );
		} );
		next.addEventListener( 'click', function () {
			step( 1 );
		} );
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				dialog.close();
			}
		} );
		dialog.addEventListener( 'keydown', function ( event ) {
			var tag = ( event.target.tagName || '' ).toLowerCase();
			if ( 'input' === tag || 'select' === tag || 'textarea' === tag ) {
				return;
			}
			if ( 'ArrowLeft' === event.key ) {
				step( -1 );
			} else if ( 'ArrowRight' === event.key ) {
				step( 1 );
			}
		} );
		dialog.addEventListener( 'close', function () {
			body.textContent = '';
			document.documentElement.classList.remove( 'wbd-qv-open' );
		} );
		document.body.appendChild( dialog );
	}

	function updateNav() {
		var show = !! cfg.nav && ids.length > 1 && index > -1;
		dialog.querySelectorAll( '.wbd-qv-dialog__nav' ).forEach( function ( b ) {
			b.hidden = ! show;
		} );
	}

	function step( dir ) {
		if ( ! cfg.nav || ids.length < 2 || index < 0 ) {
			return;
		}
		index = ( index + dir + ids.length ) % ids.length;
		load( ids[ index ] );
	}

	function setStatus( message ) {
		var status = body.querySelector( '.wbd-qv-product__status' );
		if ( status ) {
			status.textContent = message || '';
		}
	}

	function initContent() {
		var main = body.querySelector( '.wbd-qv-product__image' );
		var original = main ? { src: main.getAttribute( 'src' ), srcset: main.getAttribute( 'srcset' ) } : null;

		body.querySelectorAll( '.wbd-qv-product__thumb' ).forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				if ( main && thumb.getAttribute( 'data-full' ) ) {
					main.src = thumb.getAttribute( 'data-full' );
					main.removeAttribute( 'srcset' );
				}
				body.querySelectorAll( '.wbd-qv-product__thumb' ).forEach( function ( t ) {
					t.classList.toggle( 'is-active', t === thumb );
					t.setAttribute( 'aria-pressed', t === thumb ? 'true' : 'false' );
				} );
			} );
		} );

		if ( typeof window.wbdPbbBundlesInit === 'function' ) {
			window.wbdPbbBundlesInit( body );
		}

		var $ = window.jQuery;
		if ( $ && $.fn.wc_variation_form ) {
			$( body )
				.find( '.variations_form' )
				.each( function () {
					var $form = $( this );
					$form.wc_variation_form();
					$form.on( 'found_variation', function ( e, variation ) {
						if ( main && variation && variation.image && variation.image.src ) {
							main.src = variation.image.src;
							if ( variation.image.srcset ) {
								main.setAttribute( 'srcset', variation.image.srcset );
							} else {
								main.removeAttribute( 'srcset' );
							}
						}
					} );
					$form.on( 'reset_data', function () {
						if ( main && original ) {
							main.src = original.src;
							if ( original.srcset ) {
								main.setAttribute( 'srcset', original.srcset );
							}
						}
					} );
				} );
		}

		// Simple products: add without leaving the page.
		var product = body.querySelector( '.wbd-qv-product' );
		var form = body.querySelector( 'form.cart' );
		if ( ! form || ! product || ! product.classList.contains( 'type-simple' ) ) {
			return;
		}
		form.addEventListener( 'submit', function ( event ) {
			var extra = Array.prototype.some.call( form.elements, function ( el ) {
				return el.name && 'hidden' !== el.type && [ 'quantity', 'add-to-cart' ].indexOf( el.name ) === -1 && 'submit' !== el.type && 'button' !== el.type;
			} );
			if ( extra || ! window.fetch ) {
				return; // Add-ons etc.: let the normal form post handle it.
			}
			event.preventDefault();
			var button = form.querySelector( '[type="submit"]' );
			var qty = form.querySelector( 'input.qty, input[name="quantity"]' );
			var data = new window.FormData();
			data.append( 'product_id', product.getAttribute( 'data-product-id' ) );
			data.append( 'quantity', qty ? qty.value : '1' );
			if ( button ) {
				button.classList.add( 'loading' );
				button.disabled = true;
			}
			window
				.fetch( cfg.cartUrl, { method: 'POST', body: data, credentials: 'same-origin' } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( res ) {
					if ( button ) {
						button.classList.remove( 'loading' );
						button.disabled = false;
					}
					if ( ! res || res.error ) {
						if ( res && res.product_url ) {
							window.location.assign( res.product_url );
						}
						return;
					}
					setStatus( i18n.added );
					if ( button ) {
						button.classList.add( 'added' );
					}
					if ( $ ) {
						$( document.body ).trigger( 'added_to_cart', [ res.fragments, res.cart_hash, $( button ) ] );
					}
					document.body.dispatchEvent( new window.CustomEvent( 'wc-blocks_added_to_cart', { bubbles: true, cancelable: true } ) );
				} )
				.catch( function () {
					HTMLFormElement.prototype.submit.call( form );
				} );
		} );
	}

	function load( id ) {
		var current = ++request;
		dialog.classList.add( 'is-loading' );
		body.innerHTML = '<p class="wbd-qv-dialog__loading" role="status"></p>';
		body.firstChild.textContent = i18n.loading || 'Loading…';
		updateNav();
		window
			.fetch( cfg.url + ( cfg.url.indexOf( '?' ) > -1 ? '&' : '?' ) + 'id=' + encodeURIComponent( id ), { credentials: 'same-origin' } )
			.then( function ( r ) {
				return r.json();
			} )
			.then( function ( res ) {
				if ( current !== request ) {
					return;
				}
				dialog.classList.remove( 'is-loading' );
				if ( ! res || ! res.success ) {
					body.innerHTML = '<p class="wbd-qv-dialog__error"></p>';
					body.firstChild.textContent = i18n.error || 'Error';
					return;
				}
				// Server-rendered, escaped product summary.
				body.innerHTML = res.data.html;
				initContent();
				var title = body.querySelector( '.wbd-qv-product__title a' );
				if ( title ) {
					title.focus( { preventScroll: true } );
				}
			} )
			.catch( function () {
				if ( current === request ) {
					dialog.classList.remove( 'is-loading' );
					body.textContent = i18n.error || 'Error';
				}
			} );
	}

	function open( id ) {
		build();
		collectIds();
		index = ids.indexOf( String( id ) );
		if ( ! dialog.open ) {
			if ( typeof dialog.showModal === 'function' ) {
				dialog.showModal();
			} else {
				dialog.setAttribute( 'open', '' );
			}
			document.documentElement.classList.add( 'wbd-qv-open' );
		}
		load( id );
	}

	document.addEventListener( 'click', function ( event ) {
		var trigger = event.target.closest( '[data-wbd-qv]' );
		if ( ! trigger || event.metaKey || event.ctrlKey || event.shiftKey ) {
			return;
		}
		event.preventDefault();
		open( trigger.getAttribute( 'data-wbd-qv' ) );
	} );

	// role="button" links: Space opens too.
	document.addEventListener( 'keydown', function ( event ) {
		if ( ' ' === event.key && event.target.matches && event.target.matches( 'a[data-wbd-qv]' ) ) {
			event.preventDefault();
			open( event.target.getAttribute( 'data-wbd-qv' ) );
		}
	} );

	if ( window.wbdPbbQuickViewOpen ) {
		var auto = function () {
			open( window.wbdPbbQuickViewOpen );
		};
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', auto );
		} else {
			auto();
		}
	}
} )();
