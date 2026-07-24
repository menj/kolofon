/*!
 * menj.bio email guard.
 * Rebuilds mailto: hrefs from an encoded payload so the served HTML contains
 * no address-shaped string for harvesters to collect.
 */
( function () {
	'use strict';

	function rot13( s ) {
		return s.replace( /[a-zA-Z]/g, function ( c ) {
			var base = c <= 'Z' ? 65 : 97;
			return String.fromCharCode( ( ( c.charCodeAt( 0 ) - base + 13 ) % 26 ) + base );
		} );
	}

	function decode( payload ) {
		try {
			return atob( rot13( payload ) );
		} catch ( e ) {
			return '';
		}
	}

	function activate( link ) {
		var address = decode( link.getAttribute( 'data-mbe' ) || '' );
		if ( ! address ) {
			return;
		}

		link.setAttribute( 'href', 'mailto:' + address );
		link.removeAttribute( 'data-mbe' );
		link.removeAttribute( 'rel' );
		link.classList.add( 'is-resolved' );

		// Only fill in visible text when the anchor is a placeholder. Anchors
		// wrapping an icon or custom label keep their own contents.
		if ( link.dataset.mbeFill === '1' ) {
			link.textContent = address;
		}
	}

	function init() {
		var links = document.querySelectorAll( 'a.mb-email[data-mbe]' );
		Array.prototype.forEach.call( links, activate );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
