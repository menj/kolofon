/*!
 * Kolofon search overlay.
 *
 * Progressive enhancement. Search already works without JavaScript through the
 * forms on the results and 404 pages, so this script adds only an affordance:
 * a search icon in the header that opens a full-screen overlay wrapping the
 * theme's own search form. No script means no icon, and nothing is lost.
 *
 * The overlay markup is cloned from a template the header prints, so the form
 * inside is exactly get_search_form() with all its attributes and labels. The
 * script never builds a form of its own.
 *
 * Behaviour mirrors the navigation toggle: aria-expanded on the button,
 * Escape closes and restores focus, and focus is trapped inside the overlay
 * while it is open. Ctrl/Cmd+K opens it too, guarded so it never fires while
 * the visitor is typing in a field.
 */
( function () {
	'use strict';

	function init() {
		var header   = document.querySelector( '.site-header' );
		var template = document.getElementById( 'kolofon-search-template' );

		if ( ! header || ! template || ! template.content ) {
			return;
		}

		// Build the overlay from the template. It holds a heading, a close
		// button, and the cloned search form.
		var overlay = document.createElement( 'div' );
		overlay.className = 'search-overlay';
		overlay.id = 'kolofon-search-overlay';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-modal', 'true' );
		overlay.setAttribute( 'aria-label', template.dataset.dialogLabel || 'Search' );
		overlay.hidden = true;

		var inner = document.createElement( 'div' );
		inner.className = 'search-overlay-inner';

		var close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'search-overlay-close';
		close.setAttribute( 'aria-label', template.dataset.closeLabel || 'Close search' );
		close.innerHTML = '<span aria-hidden="true">\u00d7</span>';

		inner.appendChild( close );
		inner.appendChild( template.content.cloneNode( true ) );
		overlay.appendChild( inner );
		document.body.appendChild( overlay );

		// Inject the trigger button into the header.
		var trigger = document.createElement( 'button' );
		trigger.type = 'button';
		trigger.className = 'search-toggle';
		trigger.setAttribute( 'aria-expanded', 'false' );
		trigger.setAttribute( 'aria-controls', overlay.id );
		trigger.setAttribute( 'aria-label', template.dataset.openLabel || 'Search' );
		trigger.innerHTML =
			'<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">' +
			'<circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/>' +
			'<line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>' +
			'</svg>';

		var branding = header.querySelector( '.site-branding' );
		if ( branding ) {
			branding.insertAdjacentElement( 'afterend', trigger );
		} else {
			header.appendChild( trigger );
		}
		header.classList.add( 'has-search-toggle' );

		var field       = overlay.querySelector( 'input[type="search"]' );
		var lastFocused = null;

		function focusable() {
			return Array.prototype.slice.call(
				overlay.querySelectorAll(
					'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
				)
			).filter( function ( el ) {
				return ! el.disabled && null !== el.offsetParent;
			} );
		}

		function isOpen() {
			return overlay.classList.contains( 'is-open' );
		}

		function open() {
			if ( isOpen() ) {
				return;
			}
			lastFocused = document.activeElement;
			overlay.hidden = false;
			// Force a reflow so the transition runs from the closed state.
			void overlay.offsetWidth;
			overlay.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			document.body.classList.add( 'search-overlay-open' );
			if ( field ) {
				field.focus();
			}
		}

		function hide() {
			if ( ! isOpen() ) {
				return;
			}
			// The CSS keys visibility, opacity, and pointer-events off this
			// class, so removing it both fades the overlay out and, crucially,
			// stops it catching clicks. The hidden attribute is set too as a
			// no-JS belt, but the class is what the closed state depends on.
			overlay.classList.remove( 'is-open' );
			overlay.hidden = true;
			trigger.setAttribute( 'aria-expanded', 'false' );
			document.body.classList.remove( 'search-overlay-open' );

			if ( lastFocused && lastFocused.focus ) {
				lastFocused.focus();
			}
		}

		trigger.addEventListener( 'click', open );
		close.addEventListener( 'click', hide );

		// Clicking the backdrop (outside the inner panel) closes.
		overlay.addEventListener( 'click', function ( event ) {
			if ( event.target === overlay ) {
				hide();
			}
		} );

		// Submitting is a real navigation; let it proceed and close the overlay.
		var form = overlay.querySelector( 'form' );
		if ( form ) {
			form.addEventListener( 'submit', function () {
				document.body.classList.remove( 'search-overlay-open' );
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			// Ctrl/Cmd+K opens, unless the visitor is typing somewhere.
			if ( ( event.ctrlKey || event.metaKey ) && 'k' === event.key.toLowerCase() ) {
				var el  = document.activeElement;
				var tag = el ? ( el.tagName || '' ).toLowerCase() : '';
				var typing = 'input' === tag || 'textarea' === tag || 'select' === tag ||
					( el && el.isContentEditable );
				if ( ! typing || overlay.contains( el ) ) {
					event.preventDefault();
					if ( isOpen() ) {
						hide();
					} else {
						open();
					}
				}
				return;
			}

			if ( ! isOpen() ) {
				return;
			}

			if ( 'Escape' === event.key ) {
				hide();
				return;
			}

			// Trap focus within the overlay while it is open.
			if ( 'Tab' === event.key ) {
				var items = focusable();
				if ( ! items.length ) {
					return;
				}
				var first = items[ 0 ];
				var last  = items[ items.length - 1 ];

				if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
