/**
 * Product FAQs — repeater on the product edit screen.
 */
( function ( $ ) {
	'use strict';

	var $root = $( '.wbd-faqs-admin' );
	if ( ! $root.length ) {
		return;
	}

	var $list = $root.find( '.wbd-faqs-admin__list' );
	var template = $root.find( '.wbd-faqs-admin__template' ).html();
	var i18n = window.wbdPbbFaqs || {};

	function refresh() {
		$root.find( '.wbd-faqs-admin__empty' ).prop( 'hidden', $list.children().length > 0 );
	}

	$root.on( 'click', '.wbd-faqs-admin__add', function () {
		var index = parseInt( $list.attr( 'data-next-index' ), 10 ) || 0;
		var $row = $( template.replace( /__i__/g, String( index ) ) );
		$list.attr( 'data-next-index', index + 1 ).append( $row );
		refresh();
		$row.find( '.wbd-faqs-admin__q' ).trigger( 'focus' );
	} );

	$root.on( 'click', '.wbd-faqs-admin__remove', function () {
		var $row = $( this ).closest( '.wbd-faqs-admin__item' );
		var hasText = $.trim( $row.find( '.wbd-faqs-admin__q' ).val() + $row.find( '.wbd-faqs-admin__a' ).val() ) !== '';
		if ( hasText && ! window.confirm( i18n.confirmRemove || 'Remove?' ) ) {
			return;
		}
		$row.remove();
		refresh();
	} );

	$root.on( 'click', '.wbd-faqs-admin__up', function () {
		var $row = $( this ).closest( '.wbd-faqs-admin__item' );
		$row.prev().before( $row );
		$( this ).trigger( 'focus' );
	} );

	$root.on( 'click', '.wbd-faqs-admin__down', function () {
		var $row = $( this ).closest( '.wbd-faqs-admin__item' );
		$row.next().after( $row );
		$( this ).trigger( 'focus' );
	} );

	if ( $.fn.sortable ) {
		$list.sortable( {
			handle: '.wbd-faqs-admin__handle',
			axis: 'y',
			placeholder: 'wbd-faqs-admin__placeholder',
			forcePlaceholderSize: true,
		} );
	}
} )( window.jQuery );
