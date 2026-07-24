<?php
/**
 * Inject the active colour scheme and font stack as :root CSS custom properties.
 *
 * Front-end: printed in wp_head after the main stylesheet so it overrides.
 * Editor: added via wp_add_inline_style on the editor sheet.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\attach_inline_vars', 20 );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\attach_editor_inline_vars' );

/**
 * Build the CSS custom property block for the active scheme.
 *
 * @return string
 */
/**
 * Resolve a scheme slug to its five colours.
 *
 * `auto` is not resolvable here, since it is a pairing rather than a palette;
 * callers handle it before asking. Anything unknown, including values stored
 * by earlier versions that offered more schemes, falls back to Charcoal, the
 * theme default, so "default" means one value everywhere.
 *
 * @param string $scheme Scheme slug.
 * @return array{bg:string,text:string,accent:string,muted:string,rule:string}
 */
function resolve_palette( $scheme ) {
	$presets = get_colour_presets();

	if ( ! isset( $presets[ $scheme ]['bg'] ) ) {
		$scheme = 'charcoal';
	}

	$p = $presets[ $scheme ];

	return array(
		'bg'     => $p['bg'],
		'text'   => $p['text'],
		'accent' => $p['accent'],
		'muted'  => $p['muted'],
		'rule'   => $p['rule'],
	);
}

/**
 * The five colour declarations for one palette, without the selector.
 *
 * @param array $c Palette from resolve_palette().
 * @return string
 */
function palette_vars( $c ) {
	return sprintf(
		'--k-bg:%s;--k-text:%s;--k-accent:%s;--k-muted:%s;--k-rule:%s;',
		esc_html( $c['bg'] ),
		esc_html( $c['text'] ),
		esc_html( $c['accent'] ),
		esc_html( $c['muted'] ),
		esc_html( $c['rule'] )
	);
}

function build_root_css() {
	$scheme = opt( 'colour_scheme' );

	if ( 'auto' === $scheme ) {
		// Auto pairs a light palette with a dark one and lets the device
		// decide. The light palette is the base, so browsers that do not
		// support the media query get a complete light theme.
		$light = resolve_palette( 'ivory' );
		$dark  = resolve_palette( 'charcoal' );
	} else {
		$light = resolve_palette( $scheme );
		$dark  = null;
	}

	$bg     = $light['bg'];
	$text   = $light['text'];
	$accent = $light['accent'];
	$muted  = $light['muted'];
	$rule   = $light['rule'];

	$stacks     = get_font_stacks();
	$font_key   = opt( 'font_stack' );
	$font       = $stacks[ $font_key ] ?? reset( $stacks );
	$body_font  = $font['body'];
	$head_font  = $font['heading'];

	$width    = intval( opt( 'container_width' ) );
	$portrait = intval( opt( 'portrait_size' ) );
	$preview  = intval( opt( 'preview_size' ) );

	// Lede sizes are stored in px for a familiar UI but emitted in rem so they
	// honour the reader's browser font-size setting. The heading floor is 60%
	// of the ceiling, which keeps the fluid clamp proportional at any setting.
	$head_px   = intval( opt( 'hero_heading_size' ) );
	$head_max  = round( $head_px / 16, 4 );
	$head_min  = round( ( $head_px * 0.6 ) / 16, 4 );
	$body_rem  = round( intval( opt( 'hero_body_size' ) ) / 16, 4 );

	$css = sprintf(
		':root{--k-bg:%s;--k-text:%s;--k-accent:%s;--k-muted:%s;--k-rule:%s;--k-font-body:%s;--k-font-heading:%s;--k-container:%dpx;--k-portrait:%dpx;--k-preview:%dpx;--k-lede-heading-max:%srem;--k-lede-heading-min:%srem;--k-lede-body:%srem;}',
		esc_html( $bg ),
		esc_html( $text ),
		esc_html( $accent ),
		esc_html( $muted ),
		esc_html( $rule ),
		$body_font,
		$head_font,
		$width,
		$portrait,
		$preview,
		$head_max,
		$head_min,
		$body_rem
	);

	if ( null !== $dark ) {
		$css .= sprintf(
			'@media (prefers-color-scheme: dark){:root{%s}}',
			palette_vars( $dark )
		);

		// Colour transitions between modes are jarring; declare the intent.
		$css .= ':root{color-scheme:light dark;}';
	}

	/**
	 * Filter the emitted :root custom property block.
	 *
	 * Appending a declaration here is the supported way to add a custom
	 * property without editing the stylesheet.
	 *
	 * @param string $css Rendered :root block, including the dark-mode block
	 *                    when the Auto scheme is active.
	 */
	return apply_filters( 'kolofon_root_css', $css );
}

/**
 * Attach the vars inline to the front-end main stylesheet.
 */
function attach_inline_vars() {
	wp_add_inline_style( 'kolofon-main', build_root_css() );
}

/**
 * Attach the vars inline to the block editor.
 */
function attach_editor_inline_vars() {
	wp_register_style( 'kolofon-editor-vars', false, array(), KOLOFON_VERSION );
	wp_enqueue_style( 'kolofon-editor-vars' );
	wp_add_inline_style( 'kolofon-editor-vars', build_root_css() );
}
