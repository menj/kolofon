/*!
 * Theme Options page script.
 * Handles tab switching, colour scheme toggle, and colour picker init.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var tabs   = document.querySelectorAll( '.kolofon-tabs .nav-tab' );
		var panels = document.querySelectorAll( '.kolofon-panel' );
		var form   = document.querySelector( '.kolofon-form' );

		if ( ! tabs.length || ! panels.length || ! form ) {
			return;
		}

		var wrap = document.querySelector( '.kolofon-wrap' );
		if ( wrap ) {
			wrap.classList.add( 'js-tabs' );
		}

		function activate( slug, focusTab ) {
			tabs.forEach( function ( tab ) {
				var isActive = tab.dataset.tab === slug;
				tab.classList.toggle( 'nav-tab-active', isActive );
				// Roving tabindex: the active tab is the only stop, arrows
				// move between the rest per the WAI-ARIA tabs pattern.
				tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
				tab.setAttribute( 'tabindex', isActive ? '0' : '-1' );
				if ( isActive && focusTab ) {
					tab.focus();
				}
			} );
			panels.forEach( function ( panel ) {
				panel.classList.toggle( 'is-active', panel.id === 'tab-' + slug );
			} );
			// Hide the Save button on read-only tabs (Documentation).
			var submitRow = form.querySelector( '.submit' );
			if ( submitRow ) {
				submitRow.style.display = ( 'docs' === slug ) ? 'none' : '';
			}
			try {
				history.replaceState( null, '', '#tab-' + slug );
			} catch ( e ) {}
		}

		tabs.forEach( function ( tab, index ) {
			tab.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				activate( tab.dataset.tab );
			} );

			// Arrow keys move selection, Home and End jump, per the ARIA
			// authoring pattern for tabs.
			tab.addEventListener( 'keydown', function ( event ) {
				var target = null;

				if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
					target = tabs[ ( index + 1 ) % tabs.length ];
				} else if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
					target = tabs[ ( index - 1 + tabs.length ) % tabs.length ];
				} else if ( 'Home' === event.key ) {
					target = tabs[ 0 ];
				} else if ( 'End' === event.key ) {
					target = tabs[ tabs.length - 1 ];
				}

				if ( target ) {
					event.preventDefault();
					activate( target.dataset.tab, true );
				}
			} );
		} );

		// Pick initial tab from hash or default to first.
		var initial = ( window.location.hash || '' ).replace( '#tab-', '' );
		var valid   = Array.from( tabs ).some( function ( t ) { return t.dataset.tab === initial; } );
		activate( valid ? initial : tabs[ 0 ].dataset.tab );

		// Reflect scheme choice on the form so admin-options.css can hide custom fields.
		function syncSchemeAttr() {
			var checked = form.querySelector( 'input[name$="[colour_scheme]"]:checked' );
			if ( checked ) {
				form.setAttribute( 'data-scheme', checked.value );
			}
		}
		form.querySelectorAll( 'input[name$="[colour_scheme]"]' ).forEach( function ( input ) {
			input.addEventListener( 'change', syncSchemeAttr );
		} );
		syncSchemeAttr();

		// Colour picker (WP built-in). Uses jQuery, which WP always enqueues alongside wp-color-picker.
		if ( window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker ) {
			window.jQuery( '.kolofon-color-picker' ).wpColorPicker();
		}

		// Media library picker for image fields.
		document.querySelectorAll( '.kolofon-image-field' ).forEach( function ( field ) {
			var urlInput = field.querySelector( '.kolofon-image-url' );
			var choose   = field.querySelector( '.kolofon-image-choose' );
			var clear    = field.querySelector( '.kolofon-image-clear' );
			var preview  = field.querySelector( '.kolofon-image-preview' );
			var img      = preview.querySelector( 'img' );
			var frame    = null;

			choose.addEventListener( 'click', function () {
				if ( ! window.wp || ! window.wp.media ) { return; }
				if ( ! frame ) {
					frame = window.wp.media( {
						title:    'Choose image',
						multiple: false,
						library:  { type: 'image' }
					} );
					frame.on( 'select', function () {
						var att = frame.state().get( 'selection' ).first().toJSON();
						urlInput.value = att.url;
						img.src        = att.url;
						preview.removeAttribute( 'hidden' );
						clear.removeAttribute( 'hidden' );
					} );
				}
				frame.open();
			} );

			clear.addEventListener( 'click', function () {
				urlInput.value = '';
				img.src        = '';
				preview.setAttribute( 'hidden', 'hidden' );
				clear.setAttribute( 'hidden', 'hidden' );
			} );
		} );
	} );
}() );
