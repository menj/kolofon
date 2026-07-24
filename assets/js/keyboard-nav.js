/*!
 * menj.bio keyboard navigation.
 *
 * Digits 0-9 follow the correspondingly numbered navigation link. The boxed
 * digits in the sidebar exist to advertise exactly this.
 *
 * Two rules are load-bearing rather than polish. Shortcuts never fire while
 * focus is in anything that accepts text, because stealing digits from an
 * input is hostile. And the whole scheme is an option the site owner can turn
 * off, because single-key shortcuts conflict with some assistive tooling.
 */
( function () {
	'use strict';

	function typingContext( el ) {
		if ( ! el ) {
			return false;
		}

		var tag = ( el.tagName || '' ).toLowerCase();

		if ( 'input' === tag || 'textarea' === tag || 'select' === tag ) {
			return true;
		}

		return !! ( el.isContentEditable );
	}

	document.addEventListener( 'keydown', function ( event ) {
		// Modifier chords belong to the browser and the OS.
		if ( event.ctrlKey || event.metaKey || event.altKey || event.shiftKey ) {
			return;
		}

		if ( event.key < '0' || event.key > '9' ) {
			return;
		}

		if ( typingContext( document.activeElement ) ) {
			return;
		}

		var link = document.querySelector( '.site-nav a[data-mb-key="' + event.key + '"]' );

		if ( ! link || ! link.href ) {
			return;
		}

		event.preventDefault();
		window.location.assign( link.href );
	} );
}() );
