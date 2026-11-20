/**
 * Product Videos — click-to-play cards, a "Watch video" button on the product
 * gallery and an accessible lightbox. Players load only after a click.
 */
( function () {
	'use strict';

	var l10n = window.wbdPbbVideosL10n || {};
	var IFRAME_OK = /^https:\/\/(www\.youtube(-nocookie)?\.com\/embed\/|player\.vimeo\.com\/video\/)/;
	var FILE_OK = /^https?:\/\//;
	var dialog = null;

	function createPlayer( url, type, title ) {
		if ( 'file' === type ) {
			if ( ! FILE_OK.test( url ) ) {
				return null;
			}
			var video = document.createElement( 'video' );
			video.className = 'wbd-pvideo__player';
			video.src = url;
			video.controls = true;
			video.playsInline = true;
			video.autoplay = true;
			video.setAttribute( 'aria-label', title || l10n.title || 'Video' );
			return video;
		}
		if ( ! IFRAME_OK.test( url ) ) {
			return null;
		}
		var iframe = document.createElement( 'iframe' );
		iframe.className = 'wbd-pvideo__player';
		iframe.src = url;
		iframe.title = title || l10n.title || 'Video';
		iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
		iframe.allowFullscreen = true;
		iframe.referrerPolicy = 'strict-origin-when-cross-origin';
		return iframe;
	}

	/* Inline cards ------------------------------------------------------ */

	document.addEventListener( 'click', function ( event ) {
		var facade = event.target.closest( '.wbd-pvideo__facade' );
		if ( ! facade ) {
			return;
		}
		var title = facade.closest( '.wbd-pvideo' ).querySelector( '.wbd-pvideo__title' );
		var player = createPlayer( facade.getAttribute( 'data-wbd-video' ), facade.getAttribute( 'data-wbd-video-type' ), title ? title.textContent : '' );
		if ( ! player ) {
			return;
		}
		var wrap = document.createElement( 'div' );
		wrap.className = 'wbd-pvideo__frame';
		wrap.appendChild( player );
		facade.replaceWith( wrap );
		player.focus();
	} );

	/* Lightbox ---------------------------------------------------------- */

	function closeDialog() {
		if ( dialog && dialog.open ) {
			dialog.close();
		}
	}

	function getDialog() {
		if ( dialog ) {
			return dialog;
		}
		dialog = document.createElement( 'dialog' );
		dialog.className = 'wbd-pvideo-dialog';
		dialog.setAttribute( 'aria-label', l10n.title || 'Product video' );
		dialog.innerHTML = '<div class="wbd-pvideo-dialog__inner"><button type="button" class="wbd-pvideo-dialog__close"></button><div class="wbd-pvideo-dialog__stage"></div><div class="wbd-pvideo-dialog__list"></div></div>';
		var close = dialog.querySelector( '.wbd-pvideo-dialog__close' );
		close.setAttribute( 'aria-label', l10n.close || 'Close video' );
		close.textContent = '×';
		close.addEventListener( 'click', closeDialog );
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				closeDialog();
			}
		} );
		// Stop playback when closed (Esc, button or backdrop).
		dialog.addEventListener( 'close', function () {
			dialog.querySelector( '.wbd-pvideo-dialog__stage' ).textContent = '';
		} );
		document.body.appendChild( dialog );
		return dialog;
	}

	function play( videos, index ) {
		var d = getDialog();
		var stage = d.querySelector( '.wbd-pvideo-dialog__stage' );
		var list = d.querySelector( '.wbd-pvideo-dialog__list' );
		var v = videos[ index ];
		var player = v && createPlayer( v.player, v.type, v.title );
		if ( ! player ) {
			return;
		}
		stage.textContent = '';
		stage.appendChild( player );
		list.textContent = '';
		if ( videos.length > 1 ) {
			videos.forEach( function ( item, i ) {
				var b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'wbd-pvideo-dialog__item' + ( i === index ? ' is-active' : '' );
				b.textContent = item.title || String( i + 1 );
				b.setAttribute( 'aria-pressed', i === index ? 'true' : 'false' );
				b.addEventListener( 'click', function () {
					play( videos, i );
				} );
				list.appendChild( b );
			} );
		}
		if ( ! d.open ) {
			if ( typeof d.showModal === 'function' ) {
				d.showModal();
			} else {
				d.setAttribute( 'open', '' );
			}
		}
	}

	/* Gallery button ---------------------------------------------------- */

	var gallery = window.wbdPbbGalleryVideos;

	function injectGalleryButton() {
		if ( ! gallery || ! gallery.videos || ! gallery.videos.length ) {
			return;
		}
		document.querySelectorAll( '.woocommerce-product-gallery' ).forEach( function ( el ) {
			if ( el.querySelector( '.wbd-pvideo-open' ) ) {
				return;
			}
			var button = document.createElement( 'button' );
			button.type = 'button';
			button.className = 'wbd-pvideo-open wbd-pvideo-open--' + ( 'center' === gallery.style ? 'center' : 'pill' );
			if ( gallery.accent && /^#[0-9a-f]{3,6}$/i.test( gallery.accent ) ) {
				button.style.setProperty( '--wbd-pvideo-accent', gallery.accent );
			}
			button.innerHTML = '<span class="wbd-pvideo-open__icon" aria-hidden="true"></span><span class="wbd-pvideo-open__text"></span>';
			button.querySelector( '.wbd-pvideo-open__text' ).textContent = gallery.text || 'Watch video';
			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				event.stopPropagation();
				play( gallery.videos, 0 );
			} );
			el.classList.add( 'wbd-has-video' );
			el.appendChild( button );
		} );
	}

	function ready() {
		injectGalleryButton();
		// Variation Images swaps the gallery: add the button again.
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'wbd_variation_gallery_changed', injectGalleryButton );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', ready );
	} else {
		ready();
	}
} )();
