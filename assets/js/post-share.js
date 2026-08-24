/**
 * Copy-link button for the per-post share row.
 *
 * The share row's platform links (X, Facebook, LinkedIn, and so on) are
 * plain anchors and need no JavaScript at all. Only the copy-link button
 * does, since there is no href it can point at: copying to the clipboard is
 * an action, not a navigation. Progressive enhancement: if this file never
 * runs, the button still exists but does nothing when clicked, which is why
 * render_post_share() in inc/share.php gives it no href or type="submit" to
 * fall back to.
 */
( function () {
	'use strict';

	function showStatus( button, text ) {
		var row    = button.closest( '.post-share' );
		var status = row && row.querySelector( '.post-share-status' );

		if ( ! status ) {
			return;
		}

		status.textContent = text;
		status.classList.add( 'is-visible' );

		window.clearTimeout( status._kolofonTimer ); // eslint-disable-line no-underscore-dangle
		status._kolofonTimer = window.setTimeout( function () { // eslint-disable-line no-underscore-dangle
			status.classList.remove( 'is-visible' );
		}, 2000 );
	}

	function fallbackCopy( text ) {
		var textarea = document.createElement( 'textarea' );
		textarea.value = text;
		textarea.setAttribute( 'readonly', '' );
		textarea.style.position = 'fixed';
		textarea.style.left = '-9999px';
		document.body.appendChild( textarea );
		textarea.select();

		var ok = false;
		try {
			ok = document.execCommand( 'copy' );
		} catch ( err ) {
			ok = false;
		}

		document.body.removeChild( textarea );
		return ok;
	}

	function handleClick( event ) {
		var button = event.currentTarget;
		var url    = button.getAttribute( 'data-share-url' );
		var copied = button.getAttribute( 'data-copied-label' ) || 'Link copied';

		if ( ! url ) {
			return;
		}

		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( url ).then(
				function () { showStatus( button, copied ); },
				function () {
					if ( fallbackCopy( url ) ) {
						showStatus( button, copied );
					}
				}
			);
			return;
		}

		if ( fallbackCopy( url ) ) {
			showStatus( button, copied );
		}
	}

	var buttons = document.querySelectorAll( '.post-share-copy' );
	for ( var i = 0; i < buttons.length; i++ ) {
		buttons[ i ].addEventListener( 'click', handleClick );
	}
} )();
