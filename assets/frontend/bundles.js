/**
 * Bundles & Composite — live total on the product page.
 * The server re-validates everything; this only updates what the shopper sees.
 */
( function () {
	'use strict';

	var cfg = window.wbdPbbBundles || {};

	function formatNumber( n ) {
		var decimals = parseInt( cfg.decimals, 10 ) || 0;
		var parts = Math.abs( n ).toFixed( decimals ).split( '.' );
		parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, cfg.thousand || '' );
		return ( n < 0 ? '-' : '' ) + parts.join( cfg.decimal || '.' );
	}

	function formatPrice( n ) {
		return String( cfg.format || '%1$s%2$s' ).replace( '%1$s', cfg.symbol || '' ).replace( '%2$s', formatNumber( n ) );
	}

	function num( value ) {
		var n = parseFloat( value );
		return isNaN( n ) ? 0 : n;
	}

	function compute( form ) {
		var sum = 0;
		var count = 0;
		form.querySelectorAll( '.wbd-bundle__item' ).forEach( function ( li ) {
			var toggle = li.querySelector( '.wbd-bundle__toggle' );
			if ( toggle && ! toggle.checked ) {
				return;
			}
			if ( li.classList.contains( 'is-unavailable' ) ) {
				return;
			}
			sum += num( li.getAttribute( 'data-price' ) ) * ( parseInt( li.getAttribute( 'data-qty' ), 10 ) || 1 );
			count++;
		} );
		form.querySelectorAll( '.wbd-composite__step' ).forEach( function ( step ) {
			var picked = step.querySelector( 'input[type="radio"]:checked' );
			if ( picked && '0' !== picked.value ) {
				sum += num( picked.getAttribute( 'data-price' ) ) * ( parseInt( step.getAttribute( 'data-qty' ), 10 ) || 1 );
				count++;
			}
		} );

		var base = num( form.getAttribute( 'data-base' ) );
		var fixed = form.getAttribute( 'data-fixed' );
		var discount = num( form.getAttribute( 'data-discount' ) );
		var regular = base + sum;
		var total = '' !== fixed && null !== fixed ? num( fixed ) : base + sum * ( 1 - discount / 100 );
		return { total: total, regular: regular, count: count };
	}

	function update( form ) {
		var r = compute( form );
		var totalEl = form.querySelector( '.wbd-bundle__total-price' );
		if ( totalEl ) {
			totalEl.textContent = formatPrice( r.total );
		}
		var save = form.querySelector( '.wbd-bundle__save' );
		if ( save ) {
			var amount = r.regular - r.total;
			save.hidden = ! ( amount > 0.004 );
			var amountEl = save.querySelector( '.wbd-bundle__save-amount' );
			if ( amountEl ) {
				amountEl.textContent = formatPrice( Math.max( 0, amount ) );
			}
		}
		form.querySelectorAll( '.wbd-option' ).forEach( function ( label ) {
			var input = label.querySelector( 'input' );
			label.classList.toggle( 'is-selected', !! ( input && input.checked ) );
		} );
		return r;
	}

	function showError( form, message ) {
		var el = form.querySelector( '.wbd-bundle__error' );
		if ( el ) {
			el.textContent = message || '';
			el.hidden = ! message;
		}
	}

	function initForm( form ) {
		if ( form.wbdBundleReady ) {
			return;
		}
		form.wbdBundleReady = true;
		update( form );
		form.addEventListener( 'change', function () {
			showError( form, '' );
			update( form );
		} );
		form.addEventListener( 'submit', function ( event ) {
			var r = update( form );
			if ( ! r.count ) {
				event.preventDefault();
				showError( form, cfg.nothing );
				return;
			}
			var missing = Array.prototype.some.call( form.querySelectorAll( '.wbd-composite__step' ), function ( step ) {
				return step.querySelector( 'input[required]' ) && ! step.querySelector( 'input[type="radio"]:checked' );
			} );
			if ( missing ) {
				event.preventDefault();
				showError( form, cfg.required );
			}
		} );
		form.addEventListener(
			'invalid',
			function () {
				showError( form, cfg.required );
			},
			true
		);
	}

	// Also used by Quick View for forms loaded into its popup.
	window.wbdPbbBundlesInit = function ( root ) {
		( root || document ).querySelectorAll( 'form[data-wbd-bundle]' ).forEach( initForm );
	};
	window.wbdPbbBundlesInit( document );
} )();
