/**
 * Sliding hover preview.
 *
 * The CSS gives every row its own preview card, which fades in where it sits.
 * That works without JavaScript but means the card appears and disappears in
 * place rather than travelling between rows. This promotes the markup to a
 * single shared card that moves to whichever row is hovered, so the preview
 * reads as one object following the pointer.
 *
 * Progressive enhancement: if this file never runs, the CSS behaviour stands.
 * The script adds .js-preview to the list, which is what switches the CSS from
 * per-row cards to the shared one.
 */
( function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function init( list ) {
		var rows = Array.prototype.slice.call(
			list.querySelectorAll( '.post-item.has-preview' )
		);

		if ( rows.length < 1 ) {
			return;
		}

		// One card for the whole list, built from the first row's markup so the
		// shared card inherits whatever the server rendered.
		var card = document.createElement( 'div' );
		card.className = 'post-preview-shared';
		card.setAttribute( 'aria-hidden', 'true' );
		list.appendChild( card );
		list.classList.add( 'js-preview' );

		var active = null;
		var hideTimer = null;

		function show( row ) {
			var source = row.querySelector( '.post-preview' );
			if ( ! source ) {
				return;
			}

			window.clearTimeout( hideTimer );

			// Only rebuild the contents when the row actually changes, so moving
			// between rows animates the position instead of restarting the fade.
			if ( active !== row ) {
				card.innerHTML = source.innerHTML;
				card.className = 'post-preview-shared' +
					( source.classList.contains( 'is-typographic' ) ? ' is-typographic' : '' );
				active = row;
			}

			// Position against the list, since the card is a child of the list.
			var listBox = list.getBoundingClientRect();
			var rowBox = row.getBoundingClientRect();
			var top = rowBox.top - listBox.top + ( rowBox.height / 2 );

			card.style.setProperty( '--preview-y', top + 'px' );
			card.classList.add( 'is-visible' );
		}

		function hide() {
			// A short delay stops the card flickering out and back while the
			// pointer crosses the gap between two rows.
			hideTimer = window.setTimeout( function () {
				card.classList.remove( 'is-visible' );
				active = null;
			}, 80 );
		}

		rows.forEach( function ( row ) {
			row.addEventListener( 'mouseenter', function () {
				show( row );
			} );
			row.addEventListener( 'mouseleave', hide );

			// Keyboard parity: the card follows focus as well as the pointer.
			var link = row.querySelector( 'a' );
			if ( link ) {
				link.addEventListener( 'focus', function () {
					show( row );
				} );
				link.addEventListener( 'blur', hide );
			}
		} );

		list.addEventListener( 'mouseleave', hide );

		// A resize invalidates the measured offsets, so drop the card rather
		// than leave it stranded beside the wrong row.
		window.addEventListener( 'resize', function () {
			card.classList.remove( 'is-visible' );
			active = null;
		}, { passive: true } );
	}

	function boot() {
		// Previews are a pointer affordance; skip the work on touch-only
		// devices, where there is no hover to follow.
		if ( window.matchMedia && ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}

		if ( REDUCED ) {
			// Motion is the point of this enhancement. With reduced motion
			// requested, leave the CSS fade-in-place behaviour alone.
			return;
		}

		Array.prototype.forEach.call(
			document.querySelectorAll( '.post-list.has-previews' ),
			init
		);
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
}() );
