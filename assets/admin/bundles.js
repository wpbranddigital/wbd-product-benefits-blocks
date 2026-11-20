/**
 * Bundles & Composite — product edit screen.
 */
( function ( $ ) {
	'use strict';

	var types = ( window.wbdPbbBundlesAdmin && window.wbdPbbBundlesAdmin.types ) || [];

	// Let WooCommerce's own show/hide logic display price fields for our types
	// and hide the shipping tab (the included products are shipped instead).
	// Only add show_if_* to elements that already use show_if_* classes: on any
	// other element it would hide that element for every other product type.
	types.forEach( function ( type ) {
		$( '.options_group.pricing' ).addClass( 'show_if_' + type );
		$( '.shipping_options, ._sold_individually_field' ).addClass( 'hide_if_' + type );
	} );
	$( 'select#product-type' ).trigger( 'change' );

	// Repeaters (bundle rows, composite components).
	$( '[data-wbd-repeater]' ).each( function () {
		var $root = $( this );
		var $list = $root.find( '.wbd-items-admin__list' );
		var template = $root.find( '.wbd-items-admin__template' ).html();

		$root.on( 'click', '.wbd-items-admin__add', function () {
			var index = parseInt( $list.attr( 'data-next-index' ), 10 ) || 0;
			var $row = $( template.replace( /__i__/g, String( index ) ) );
			$list.attr( 'data-next-index', index + 1 ).append( $row );
			$( document.body ).trigger( 'wc-enhanced-select-init' );
			$row.find( 'input[type="text"]' ).first().trigger( 'focus' );
		} );

		$root.on( 'click', '.wbd-items-admin__remove', function () {
			$( this ).closest( '.wbd-items-admin__row, .wbd-component-admin' ).remove();
		} );

		if ( $.fn.sortable ) {
			$list.sortable( {
				handle: '.wbd-items-admin__handle',
				items: '> .wbd-items-admin__row, > .wbd-component-admin',
				axis: 'y',
			} );
		}
	} );

	// Discount field only for "with a discount".
	function toggleDiscount() {
		$( 'select[id$="_pricing"]' ).each( function () {
			var $field = $( this ).closest( '.options_group' ).find( '.wbd-discount-field' );
			$field.toggle( 'auto_discount' === $( this ).val() );
		} );
	}
	$( document ).on( 'change', 'select[id$="_pricing"]', toggleDiscount );
	toggleDiscount();
} )( window.jQuery );
