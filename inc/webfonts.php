<?php
/**
 * Self-hosted webfonts.
 *
 * The theme normally leans on system fonts, since a fresh page render should
 * not wait on a network fetch. Two exceptions ship: XCharter and Special Elite,
 * both self-hosted. They load only when the active font stack asks for them,
 * so a reader on Editorial serif, Monospace, or Modern grotesque incurs no
 * request for a face they never see.
 *
 * A stack is treated as needing a webfont when it declares `webfont` in its
 * definition. `kolofon_font_stacks` can therefore register more, and the
 * loader will do the right thing with them.
 *
 * Preload and stylesheet emission are conditional. `font-display: swap` keeps
 * text visible while the file arrives rather than blocking on it. Files are
 * shipped whole rather than subset, since subsetting needs tooling this
 * environment does not have; that is a documented followup, not a promise the
 * word "self-hosted" would imply on its own.
 *
 * @package Kolofon
 * @since   2.4.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', __NAMESPACE__ . '\\preload_active_webfont', 4 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_active_webfont', 20 );

/**
 * The active stack's webfont definition, if any.
 *
 * Returns an array with `family`, `files`, and `preload` when the stack
 * declares a webfont; null otherwise.
 *
 * @return array|null
 */
function active_webfont() {
	$stacks = get_font_stacks();
	$slug   = opt( 'font_stack' );

	if ( ! isset( $stacks[ $slug ]['webfont'] ) ) {
		return null;
	}

	$def = $stacks[ $slug ]['webfont'];

	if ( empty( $def['family'] ) || empty( $def['files'] ) || ! is_array( $def['files'] ) ) {
		return null;
	}

	return $def;
}

/**
 * Preload the primary weight so the paint does not wait for the stylesheet
 * to be parsed before the font request is discovered.
 */
function preload_active_webfont() {
	$def = active_webfont();

	if ( null === $def || empty( $def['preload'] ) ) {
		return;
	}

	$path = KOLOFON_DIR . 'assets/fonts/' . ltrim( $def['preload'], '/' );

	if ( ! is_readable( $path ) ) {
		return;
	}

	$ext  = strtolower( pathinfo( $def['preload'], PATHINFO_EXTENSION ) );
	$type = ( 'otf' === $ext ) ? 'font/otf' : ( 'ttf' === $ext ? 'font/ttf' : 'font/woff2' );

	printf(
		'<link rel="preload" as="font" type="%s" href="%s" crossorigin />' . "\n",
		esc_attr( $type ),
		esc_url( KOLOFON_URI . 'assets/fonts/' . ltrim( $def['preload'], '/' ) )
	);
}

/**
 * Attach the @font-face declarations inline on the main stylesheet.
 *
 * Only one stack's fonts are ever loaded per request, so an inline block is
 * cheaper than an extra stylesheet handle.
 */
function enqueue_active_webfont() {
	$def = active_webfont();

	if ( null === $def ) {
		return;
	}

	$rules = '';

	foreach ( $def['files'] as $file ) {
		if ( empty( $file['src'] ) ) {
			continue;
		}

		$path = KOLOFON_DIR . 'assets/fonts/' . ltrim( $file['src'], '/' );
		if ( ! is_readable( $path ) ) {
			continue;
		}

		$ext    = strtolower( pathinfo( $file['src'], PATHINFO_EXTENSION ) );
		$format = ( 'otf' === $ext ) ? 'opentype' : ( 'ttf' === $ext ? 'truetype' : $ext );
		$weight = isset( $file['weight'] ) ? $file['weight'] : 'normal';
		$style  = isset( $file['style'] ) ? $file['style'] : 'normal';

		$rules .= sprintf(
			"@font-face{font-family:'%s';src:url('%s') format('%s');font-weight:%s;font-style:%s;font-display:swap;}\n",
			esc_html( $def['family'] ),
			esc_url( KOLOFON_URI . 'assets/fonts/' . ltrim( $file['src'], '/' ) ),
			esc_html( $format ),
			esc_html( $weight ),
			esc_html( $style )
		);
	}

	if ( '' !== $rules ) {
		wp_add_inline_style( 'kolofon-main', $rules );
	}
}

/**
 * Build @font-face rules for every stack that ships a webfont.
 *
 * The front end loads only the active font. The Theme Options live preview
 * needs all of them, so switching stacks in the picker shows each in its true
 * face rather than falling back to a system serif or monospace. Returns the
 * CSS as a string for the caller to enqueue where it wants.
 *
 * @return string
 */
function all_webfont_faces() {
	$rules = '';
	$seen  = array();

	foreach ( get_font_stacks() as $stack ) {
		if ( empty( $stack['webfont']['files'] ) ) {
			continue;
		}

		$family = $stack['webfont']['family'];

		foreach ( $stack['webfont']['files'] as $file ) {
			if ( empty( $file['src'] ) ) {
				continue;
			}

			// De-duplicate: several stacks share Special Elite.
			$id = $family . '|' . $file['src'];
			if ( isset( $seen[ $id ] ) ) {
				continue;
			}
			$seen[ $id ] = true;

			$path = KOLOFON_DIR . 'assets/fonts/' . ltrim( $file['src'], '/' );
			if ( ! is_readable( $path ) ) {
				continue;
			}

			$ext    = strtolower( pathinfo( $file['src'], PATHINFO_EXTENSION ) );
			$format = ( 'otf' === $ext ) ? 'opentype' : ( 'ttf' === $ext ? 'truetype' : $ext );
			$weight = isset( $file['weight'] ) ? $file['weight'] : 'normal';
			$style  = isset( $file['style'] ) ? $file['style'] : 'normal';

			$rules .= sprintf(
				"@font-face{font-family:'%s';src:url('%s') format('%s');font-weight:%s;font-style:%s;font-display:swap;}\n",
				esc_html( $family ),
				esc_url( KOLOFON_URI . 'assets/fonts/' . ltrim( $file['src'], '/' ) ),
				esc_html( $format ),
				esc_html( $weight ),
				esc_html( $style )
			);
		}
	}

	return $rules;
}
