/* XFedi Microblog composer */
( function () {
	'use strict';

	if ( typeof window.xfediMicroblog === 'undefined' ) {
		return;
	}

	var cfg = window.xfediMicroblog;
	var strings = cfg.strings || {};
	var panel;

	function buildPanel() {
		if ( panel ) {
			return panel;
		}
		panel = document.createElement( 'div' );
		panel.className = 'xfmb-composer-panel';
		panel.innerHTML =
			'<div class="xfmb-composer-head">' +
				'<span>' + escapeHtml( strings.placeholder || '' ) + '</span>' +
				'<button type="button" class="xfmb-composer-close" aria-label="' + escapeHtml( strings.close || 'Close' ) + '">&times;</button>' +
			'</div>' +
			'<div class="xfmb-composer-body">' +
				'<textarea class="xfmb-composer-textarea" placeholder="' + escapeHtml( strings.placeholder || '' ) + '"></textarea>' +
			'</div>' +
			'<div class="xfmb-composer-foot">' +
				'<span class="xfmb-composer-counter"></span>' +
				'<button type="button" class="xfmb-composer-submit">' + escapeHtml( strings.post || 'Post' ) + '</button>' +
			'</div>';
		document.body.appendChild( panel );

		var textarea = panel.querySelector( '.xfmb-composer-textarea' );
		var counter = panel.querySelector( '.xfmb-composer-counter' );
		var submit = panel.querySelector( '.xfmb-composer-submit' );
		var closeBtn = panel.querySelector( '.xfmb-composer-close' );

		textarea.addEventListener( 'input', function () {
			updateCounter( textarea.value, counter, submit );
		} );

		submit.addEventListener( 'click', function () {
			post( textarea, submit );
		} );

		closeBtn.addEventListener( 'click', function () {
			panel.classList.remove( 'is-open' );
		} );

		updateCounter( '', counter, submit );
		return panel;
	}

	function updateCounter( value, counter, submit ) {
		var limit = parseInt( cfg.charLimit, 10 ) || 500;
		var len = Array.from( value ).length;
		var remaining = limit - len;
		if ( remaining < 0 ) {
			counter.textContent = ( strings.over || '%d over' ).replace( '%d', String( -remaining ) );
			counter.classList.add( 'is-over' );
			submit.disabled = true;
		} else {
			counter.textContent = ( strings.remaining || '%d remaining' ).replace( '%d', String( remaining ) );
			counter.classList.remove( 'is-over' );
			submit.disabled = len === 0;
		}
	}

	function post( textarea, submit ) {
		var content = textarea.value.trim();
		if ( ! content ) {
			return;
		}
		submit.disabled = true;
		submit.textContent = strings.posting || 'Posting...';

		var body = new FormData();
		body.append( 'content', content );

		fetch( cfg.restUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': cfg.nonce },
			body: body
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'request failed' );
				}
				return response.json();
			} )
			.then( function () {
				showMessage( strings.posted || 'Posted', 'is-success' );
				textarea.value = '';
				updateCounter( '', panel.querySelector( '.xfmb-composer-counter' ), submit );
				submit.textContent = strings.post || 'Post';
			} )
			.catch( function () {
				showMessage( strings.error || 'Error', 'is-error' );
				submit.disabled = false;
				submit.textContent = strings.post || 'Post';
			} );
	}

	function showMessage( text, cls ) {
		var existing = panel.querySelector( '.xfmb-composer-message' );
		if ( existing ) {
			existing.remove();
		}
		var msg = document.createElement( 'div' );
		msg.className = 'xfmb-composer-message ' + cls;
		msg.textContent = text;
		panel.appendChild( msg );
		setTimeout( function () {
			msg.remove();
		}, 4000 );
	}

	function escapeHtml( s ) {
		return String( s )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#39;' );
	}

	document.addEventListener( 'click', function ( event ) {
		var trigger = event.target.closest ? event.target.closest( '.xfedi-mb-composer-trigger' ) : null;
		if ( ! trigger ) {
			return;
		}
		event.preventDefault();
		buildPanel().classList.toggle( 'is-open' );
	} );
} )();
