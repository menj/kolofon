/*!
 * menj.bio navigation toggle.
 *
 * Progressive enhancement. The markup ships with the navigation visible, so a
 * visitor without JavaScript gets a plain wrapping menu rather than a menu
 * that can never be opened. This script injects the button and adds the class
 * the stylesheet keys its collapsed rules off, so the collapsed state only
 * exists when there is something able to expand it.
 */
( function () {
	'use strict';

	function init() {
		var header = document.querySelector( '.site-header' );
		var nav    = document.querySelector( '.site-nav' );

		if ( ! header || ! nav ) {
			return;
		}

		var list = nav.querySelector( 'ul' );
		if ( ! list ) {
			return;
		}

		if ( ! nav.id ) {
			nav.id = 'site-nav';
		}

		var button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'nav-toggle';
		button.setAttribute( 'aria-expanded', 'false' );
		button.setAttribute( 'aria-controls', nav.id );
		button.setAttribute( 'aria-label', nav.dataset.toggleLabel || 'Menu' );
		button.innerHTML =
			'<span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>';

		function setExpanded( expanded ) {
			button.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			header.classList.toggle( 'nav-is-open', expanded );
		}

		button.addEventListener( 'click', function () {
			setExpanded( 'true' !== button.getAttribute( 'aria-expanded' ) );
		} );

		// Escape closes the menu and returns focus to the button.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && header.classList.contains( 'nav-is-open' ) ) {
				setExpanded( false );
				button.focus();
			}
		} );

		// Following a link closes the menu, since navigation may be in-page.
		nav.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) ) {
				setExpanded( false );
			}
		} );

		// Leaving the small-screen range resets state, so the menu is never
		// left hidden when the layout no longer collapses it.
		if ( window.matchMedia ) {
			var mq = window.matchMedia( '(min-width: 768px)' );
			var reset = function ( event ) {
				if ( event.matches ) {
					setExpanded( false );
				}
			};
			if ( mq.addEventListener ) {
				mq.addEventListener( 'change', reset );
			} else if ( mq.addListener ) {
				mq.addListener( reset );
			}
		}

		header.querySelector( '.site-branding' ).insertAdjacentElement( 'afterend', button );
		header.classList.add( 'has-nav-toggle' );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
