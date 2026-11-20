/**
 * Variation Images — gallery picker inside each variation (product edit screen).
 * Variations are loaded with AJAX, so all handlers are delegated.
 */
( function ( $ ) {
	'use strict';

	var i18n = window.wbdPbbVGallery || {};
	var $doc = $( '#woocommerce-product-data' );

	function sync( $box ) {
		var ids = $box.find( '.wbd-vgallery__item' ).map( function () {
			return $( this ).data( 'id' );
		} ).get();
		// Triggering change marks the variation as "needs update" in WooCommerce.
		$box.find( '.wbd-vgallery__input' ).val( ids.join( ',' ) ).trigger( 'change' );
	}

	function makeSortable( $box ) {
		var $list = $box.find( '.wbd-vgallery__list' );
		if ( $.fn.sortable && ! $list.data( 'ui-sortable' ) ) {
			$list.sortable( {
				items: '.wbd-vgallery__item',
				tolerance: 'pointer',
				stop: function () {
					sync( $box );
				},
			} );
		}
	}

	$doc.on( 'click', '.wbd-vgallery__add', function ( event ) {
		event.preventDefault();
		var $box = $( this ).closest( '.wbd-vgallery' );
		var frame = wp.media( {
			title: i18n.title || 'Variation gallery',
			button: { text: i18n.button || 'Add to gallery' },
			library: { type: 'image' },
			multiple: 'add',
		} );
		frame.on( 'select', function () {
			var $list = $box.find( '.wbd-vgallery__list' );
			frame.state().get( 'selection' ).each( function ( attachment ) {
				var data = attachment.toJSON();
				if ( $list.find( '[data-id="' + data.id + '"]' ).length ) {
					return;
				}
				var src = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
				var $li = $( '<li class="wbd-vgallery__item"></li>' ).attr( 'data-id', data.id );
				$( '<img alt="" />' ).attr( 'src', src ).appendTo( $li );
				$( '<button type="button" class="wbd-vgallery__remove">&times;</button>' )
					.attr( 'aria-label', i18n.remove || 'Remove image' )
					.appendTo( $li );
				$list.append( $li );
			} );
			sync( $box );
			makeSortable( $box );
		} );
		frame.open();
	} );

	$doc.on( 'click', '.wbd-vgallery__remove', function ( event ) {
		event.preventDefault();
		var $box = $( this ).closest( '.wbd-vgallery' );
		$( this ).closest( '.wbd-vgallery__item' ).remove();
		sync( $box );
	} );

	$( document.body ).on( 'woocommerce_variations_loaded woocommerce_variations_added', function () {
		$doc.find( '.wbd-vgallery' ).each( function () {
			makeSortable( $( this ) );
		} );
	} );
} )( window.jQuery );
