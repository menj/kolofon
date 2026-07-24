/*!
 * menj.bio single-section enforcement for the block editor.
 *
 * Gutenberg renders categories as checkboxes, which invites assigning several.
 * This watches the selection and reduces it to the most recently added term,
 * so the panel behaves like a radio group.
 *
 * The server enforces the same rule on save regardless of what happens here.
 * This exists so the interface agrees with the rule rather than silently
 * discarding a choice after the fact.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.data || ! wp.domReady ) {
		return;
	}

	wp.domReady( function () {
		var editor = wp.data.select( 'core/editor' );
		var dispatch = wp.data.dispatch( 'core/editor' );

		if ( ! editor || ! dispatch ) {
			return;
		}

		var previous = null;
		var settling = false;

		wp.data.subscribe( function () {
			// Ignore our own correction, so the subscription cannot recurse.
			if ( settling ) {
				return;
			}

			var post = wp.data.select( 'core/editor' ).getCurrentPost();
			if ( ! post || 'post' !== post.type ) {
				return;
			}

			var current = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'categories' );
			if ( ! Array.isArray( current ) ) {
				return;
			}

			if ( current.length <= 1 ) {
				previous = current.slice();
				return;
			}

			// Keep whichever term was added last, which is what a person
			// clicking a second box intends.
			var added = previous
				? current.filter( function ( id ) {
					return -1 === previous.indexOf( id );
				} )
				: [];

			var keep = added.length ? added[ added.length - 1 ] : current[ current.length - 1 ];

			settling = true;
			dispatch.editPost( { categories: [ keep ] } );
			previous = [ keep ];

			window.setTimeout( function () {
				settling = false;
			}, 0 );
		} );
	} );
}( window.wp ) );
