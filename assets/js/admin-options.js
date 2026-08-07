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
		// js-tabs is rendered server-side so tabs show on first paint.
		// The script handles switching between them.

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
			// The Save button is hidden on the read-only Documentation tab via a
			// CSS rule keyed on the active tab (see admin-options.css), not by
			// toggling inline styles here. That keeps the button's visibility
			// out of JavaScript's hands: a script error elsewhere on the page
			// can never leave Save hidden, because showing it is the CSS
			// default and only the docs tab hides it.
			if ( wrap ) {
				wrap.dataset.activeTab = slug;
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

		// ------- Slider fields -------
		// Each slider pairs a range input (the submitting control) with a small
		// number input for precise entry. Keep them in lockstep. The range input
		// carries the option name, so form submission and the live preview both
		// read from it; the number input has no name and only mirrors.
		document.querySelectorAll( '[data-kolofon-slider]' ).forEach( function ( slider ) {
			var range  = slider.querySelector( '.kolofon-slider-range' );
			var number = slider.querySelector( '.kolofon-slider-number' );
			if ( ! range || ! number ) {
				return;
			}

			// Dragging the range updates the readout and fires input on the
			// range, which the preview module already listens for.
			range.addEventListener( 'input', function () {
				number.value = range.value;
			} );

			// Typing an exact value updates the range, then dispatches input on
			// the range so the preview and the submitting control both follow.
			number.addEventListener( 'input', function () {
				var v = parseFloat( number.value );
				var min = parseFloat( range.min );
				var max = parseFloat( range.max );
				if ( isNaN( v ) ) {
					return;
				}
				if ( v < min ) { v = min; }
				if ( v > max ) { v = max; }
				range.value = v;
				range.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			} );

			// On blur, snap the number box to the clamped range value so an
			// out-of-bounds or empty entry does not linger in the readout.
			number.addEventListener( 'blur', function () {
				number.value = range.value;
			} );
		} );

		// ------- Live typography and colour preview -------
		// A specimen block on the Appearance tab that answers the font stack,
		// size, and colour scheme controls as they move. Pure client-side: it
		// reads the live form values and the localised stack/scheme data, and
		// writes inline styles onto the specimen. Nothing is saved until the
		// form is submitted.
		( function initPreview() {
			var data = window.kolofonPreview;
			var stage = document.querySelector( '[data-preview-stage]' );
			if ( ! data || ! stage || ! form ) {
				return;
			}

			var heading = stage.querySelector( '[data-preview-heading]' );
			var lede    = stage.querySelector( '[data-preview-lede]' );
			var body    = stage.querySelector( '[data-preview-body]' );
			var eyebrow = stage.querySelector( '[data-preview-eyebrow]' );

			function currentStack() {
				var checked = form.querySelector( 'input[name$="[font_stack]"]:checked' );
				return checked && data.stacks[ checked.value ] ? data.stacks[ checked.value ] : null;
			}

			function currentScheme() {
				var checked = form.querySelector( 'input[name$="[colour_scheme]"]:checked' );
				if ( ! checked ) {
					return null;
				}
				// 'auto' and 'custom' have no fixed palette here; fall back to a
				// neutral so the specimen stays legible rather than guessing.
				return data.schemes[ checked.value ] || null;
			}

			function num( selector, fallback ) {
				var input = form.querySelector( selector );
				if ( ! input ) { return fallback; }
				var v = parseFloat( input.value );
				return isNaN( v ) ? fallback : v;
			}

			function render() {
				var stack  = currentStack();
				var scheme = currentScheme();

				if ( stack ) {
					if ( heading ) { heading.style.fontFamily = stack.heading; }
					if ( eyebrow ) { eyebrow.style.fontFamily = stack.heading; }
					if ( lede )    { lede.style.fontFamily = stack.body; }
					if ( body )    { body.style.fontFamily = stack.body; }
				}

				// Sizes: the hero controls drive heading and lede. Body copy
				// tracks the lede size a step down so the relationship reads.
				var headingSize = num( 'input[name$="[hero_heading_size]"]', 44 );
				var ledeSize    = num( 'input[name$="[hero_body_size]"]', 20 );
				if ( heading ) { heading.style.fontSize = headingSize + 'px'; }
				if ( lede )    { lede.style.fontSize = ledeSize + 'px'; }
				if ( body )    { body.style.fontSize = Math.max( 15, ledeSize - 3 ) + 'px'; }

				if ( scheme ) {
					stage.style.background  = scheme.bg;
					stage.style.color       = scheme.text;
					stage.style.borderColor = scheme.rule;
					if ( heading ) { heading.style.color = scheme.text; }
					if ( lede )    { lede.style.color = scheme.text; }
					if ( body )    { body.style.color = scheme.text; }
					if ( eyebrow ) { eyebrow.style.color = scheme.accent; }
				}
			}

			// React to any relevant control changing. 'input' catches live
			// typing in the number fields; 'change' catches the radios.
			form.addEventListener( 'input', function ( e ) {
				if ( e.target.name && /\[(font_stack|colour_scheme|hero_heading_size|hero_body_size)\]$/.test( e.target.name ) ) {
					render();
				}
			} );
			form.addEventListener( 'change', function ( e ) {
				if ( e.target.name && /\[(font_stack|colour_scheme)\]$/.test( e.target.name ) ) {
					render();
				}
			} );

			render();
		}() );
	} );
}() );
