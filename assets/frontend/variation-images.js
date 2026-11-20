/**
 * Variation Images — swaps the product gallery when a variation with its own
 * images is chosen, and restores the main gallery when the choice is cleared.
 * Re-initialises WooCommerce's gallery (slider, zoom, lightbox) after each swap.
 */
( function ( $ ) {
	'use strict';

	var params = window.wbdPbbVariationImages;
	// WooCommerce may load its gallery script later (defer), so its
	// availability is checked when a swap happens, not here.
	if ( ! params ) {
		return;
	}

	var cache = {};
	var current = String( params.productId );
	var wanted = current;
	var timer = null;
	var lastVariation = null;

	// A variation without its own gallery may still have its own main image:
	// let WooCommerce put it back after the main gallery is restored.
	function reapplyVariationImage( form ) {
		if ( lastVariation && $.fn.wc_variations_image_update ) {
			$( form ).wc_variations_image_update( lastVariation );
		}
	}

	function galleryOf( form ) {
		var $scope = $( form ).closest( '.product' );
		var $gallery = $scope.find( '.woocommerce-product-gallery' ).first();
		return $gallery.length ? $gallery : $( '.woocommerce-product-gallery' ).first();
	}

	function fetchHtml( key ) {
		if ( ! cache[ key ] ) {
			cache[ key ] = $.ajax( {
				url: params.url,
				data: { id: key },
				dataType: 'json',
			} ).then( function ( res ) {
				return res && res.success && res.data ? res.data.html : '';
			} );
			cache[ key ].fail( function () {
				delete cache[ key ];
			} );
		}
		return cache[ key ];
	}

	function apply( form ) {
		var key = wanted;
		if ( key === current || ! $.fn.wc_product_gallery ) {
			return;
		}
		var $old = galleryOf( form );
		$old.attr( 'aria-busy', 'true' );
		fetchHtml( key ).then( function ( html ) {
			$old.removeAttr( 'aria-busy' );
			// A newer choice was made while loading.
			if ( key !== wanted || ! html ) {
				return;
			}
			var $new = $( $.parseHTML( $.trim( html ) ) ).filter( '.woocommerce-product-gallery' ).first();
			if ( ! $new.length ) {
				return;
			}
			galleryOf( form ).replaceWith( $new );
			$new.wc_product_gallery();
			current = key;
			if ( key === String( params.productId ) ) {
				reapplyVariationImage( form );
			}
			$( document.body ).trigger( 'wbd_variation_gallery_changed', [ $new, key ] );
		} );
	}

	function schedule( form, key ) {
		wanted = String( key );
		window.clearTimeout( timer );
		// reset_data fires right before found_variation; wait for the final state.
		timer = window.setTimeout( function () {
			apply( form );
		}, 60 );
	}

	$( document ).on( 'found_variation', 'form.variations_form', function ( event, variation ) {
		var own = variation && variation.wbd_gallery;
		lastVariation = own ? null : variation;
		schedule( this, own ? variation.variation_id : params.productId );
	} );

	$( document ).on( 'reset_data', 'form.variations_form', function () {
		lastVariation = null;
		schedule( this, params.productId );
	} );
} )( window.jQuery );
