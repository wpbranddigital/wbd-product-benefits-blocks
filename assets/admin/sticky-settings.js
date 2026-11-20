/**
 * Sticky Add to Cart — settings page: color pickers, live preview, copy buttons.
 */
( function ( $ ) {
	'use strict';

	var settings = window.wbdPbbStickyAdmin || {};
	var form = document.querySelector( '.wbd-satc-admin__form' );
	var preview = document.querySelector( '#wbd-satc-preview .wbd-satc' );

	if ( ! form ) {
		return;
	}

	function field( key ) {
		return form.querySelector( '[name="wbd_pbb_sticky_atc[' + key + ']"]' );
	}

	function setVar( input ) {
		if ( ! preview || ! input.dataset.previewVar ) {
			return;
		}
		var value = String( input.value || '' ).trim();
		if ( '--wbd-satc-radius' === input.dataset.previewVar ) {
			value = '' === value ? '' : Math.min( 50, Math.max( 0, parseInt( value, 10 ) || 0 ) ) + 'px';
		} else if ( ! /^#([0-9a-f]{3}){1,2}$/i.test( value ) ) {
			value = '';
		}
		if ( value ) {
			preview.style.setProperty( input.dataset.previewVar, value );
		} else {
			preview.style.removeProperty( input.dataset.previewVar );
		}
	}

	function updateTemplate() {
		var checked = form.querySelector( '[name="wbd_pbb_sticky_atc[template]"]:checked' );
		if ( ! preview || ! checked ) {
			return;
		}
		Array.prototype.slice.call( preview.classList ).forEach( function ( cls ) {
			if ( /^wbd-satc--(classic|minimal|floating|dark)$/.test( cls ) ) {
				preview.classList.remove( cls );
			}
		} );
		preview.classList.add( 'wbd-satc--' + checked.value );
	}

	function updateElements() {
		if ( ! preview ) {
			return;
		}
		form.querySelectorAll( '[data-preview-element]' ).forEach( function ( input ) {
			var el = preview.querySelector( '[data-wbd-el="' + input.dataset.previewElement + '"]' );
			if ( el ) {
				el.hidden = ! input.checked;
			}
		} );
	}

	function updateButtonText() {
		var input = field( 'button_text' );
		var button = preview ? preview.querySelector( '.wbd-satc__button' ) : null;
		if ( input && button ) {
			button.textContent = String( input.value ).trim() || settings.defaultButtonText || 'Add to cart';
		}
	}

	form.addEventListener( 'change', function ( event ) {
		var target = event.target;
		if ( target.name === 'wbd_pbb_sticky_atc[template]' ) {
			updateTemplate();
		} else if ( target.dataset.previewElement ) {
			updateElements();
		} else if ( target.dataset.previewVar ) {
			setVar( target );
		}
	} );

	form.addEventListener( 'input', function ( event ) {
		if ( event.target.name === 'wbd_pbb_sticky_atc[button_text]' ) {
			updateButtonText();
		} else if ( event.target.dataset.previewVar ) {
			setVar( event.target );
		}
	} );

	// Color pickers (WordPress core Iris).
	if ( $ && $.fn.wpColorPicker ) {
		$( '.wbd-satc-admin__color' ).each( function () {
			var input = this;
			$( input ).wpColorPicker( {
				change: function ( event, ui ) {
					input.value = ui.color.toString();
					setVar( input );
				},
				clear: function () {
					input.value = '';
					setVar( input );
				},
			} );
		} );
	}

	updateButtonText();

	// Copy shortcode buttons.
	document.querySelectorAll( '.wbd-satc-admin__copy' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var text = button.getAttribute( 'data-copy' ) || '';
			var done = function () {
				button.textContent = settings.copied || 'Copied!';
				window.setTimeout( function () {
					button.textContent = settings.copy || 'Copy';
				}, 1500 );
			};
			if ( navigator.clipboard && window.isSecureContext ) {
				navigator.clipboard.writeText( text ).then( done );
			} else {
				var area = document.createElement( 'textarea' );
				area.value = text;
				document.body.appendChild( area );
				area.select();
				try {
					document.execCommand( 'copy' );
					done();
				} catch ( e ) {}
				document.body.removeChild( area );
			}
		} );
	} );
} )( window.jQuery );
