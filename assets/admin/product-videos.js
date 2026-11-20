/**
 * Product Videos — editor on the product edit screen.
 */
( function ( $ ) {
	'use strict';

	var $root = $( '.wbd-videos-admin' );
	if ( ! $root.length ) {
		return;
	}
	var i18n = window.wbdPbbVideos || {};
	var $list = $root.find( '.wbd-videos-admin__list' );
	var template = $root.find( '.wbd-videos-admin__template' ).html();

	function pick( type, title, done ) {
		var frame = wp.media( {
			title: title,
			button: { text: i18n.select || 'Use this' },
			library: { type: type },
			multiple: false,
		} );
		frame.on( 'select', function () {
			done( frame.state().get( 'selection' ).first().toJSON() );
		} );
		frame.open();
	}

	$root.on( 'click', '.wbd-videos-admin__add', function () {
		var index = parseInt( $list.attr( 'data-next-index' ), 10 ) || 0;
		var $row = $( template.replace( /__i__/g, String( index ) ) );
		$list.attr( 'data-next-index', index + 1 ).append( $row );
		$row.find( '.wbd-videos-admin__url' ).trigger( 'focus' );
	} );

	$root.on( 'click', '.wbd-videos-admin__remove', function () {
		$( this ).closest( '.wbd-videos-admin__item' ).remove();
	} );

	$root.on( 'click', '.wbd-videos-admin__upload', function () {
		var $row = $( this ).closest( '.wbd-videos-admin__item' );
		pick( 'video', i18n.videoTitle || 'Choose a video', function ( att ) {
			$row.find( '.wbd-videos-admin__url' ).val( att.url );
			if ( ! $row.find( '.wbd-videos-admin__title' ).val() ) {
				$row.find( '.wbd-videos-admin__title' ).val( att.title || '' );
			}
		} );
	} );

	$root.on( 'click', '.wbd-videos-admin__poster-pick', function () {
		var $row = $( this ).closest( '.wbd-videos-admin__item' );
		pick( 'image', i18n.posterTitle || 'Choose a cover image', function ( att ) {
			var src = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
			$row.find( '.wbd-videos-admin__poster-id' ).val( att.id );
			$row.find( '.wbd-videos-admin__poster-preview' ).empty().append( $( '<img alt="" />' ).attr( 'src', src ) );
			$row.find( '.wbd-videos-admin__poster-clear' ).prop( 'hidden', false );
		} );
	} );

	$root.on( 'click', '.wbd-videos-admin__poster-clear', function () {
		var $row = $( this ).closest( '.wbd-videos-admin__item' );
		$row.find( '.wbd-videos-admin__poster-id' ).val( '' );
		$row.find( '.wbd-videos-admin__poster-preview' ).empty();
		$( this ).prop( 'hidden', true );
	} );
} )( window.jQuery );
