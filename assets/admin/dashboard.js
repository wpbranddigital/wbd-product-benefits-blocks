/**
 * WBD Blocks dashboard — "Copy system info" button.
 */
( function () {
	'use strict';

	var i18n = window.wbdPbbDashboard || {};

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-wbd-copy]' );
		if ( ! button ) {
			return;
		}
		var field = document.getElementById( button.getAttribute( 'data-wbd-copy' ) );
		var status = button.parentNode.querySelector( '.wbd-dash__copy-status' );
		if ( ! field ) {
			return;
		}
		var done = function ( ok ) {
			if ( status ) {
				status.textContent = ok ? i18n.copied || 'Copied!' : i18n.failed || '';
			}
		};
		field.select();
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( field.value ).then(
				function () {
					done( true );
				},
				function () {
					done( false );
				}
			);
			return;
		}
		try {
			done( document.execCommand( 'copy' ) );
		} catch ( e ) {
			done( false );
		}
	} );
} )();
