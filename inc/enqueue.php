<?php
/**
 * Front-end asset registration.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * URL for a stylesheet, preferring the minified build when one exists.
 *
 * Sources stay readable and commented in assets/css/; `node tools/minify-css.js`
 * emits the .min.css beside them. SCRIPT_DEBUG forces the readable file, which
 * is what you want while working on the theme.
 *
 * @param string $file Stylesheet filename, for example 'main.css'.
 * @return string
 */
function asset_css( $file ) {
	$debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	$min   = str_replace( '.css', '.min.css', $file );

	if ( ! $debug && is_readable( KOLOFON_DIR . 'assets/css/' . $min ) ) {
		return KOLOFON_URI . 'assets/css/' . $min;
	}

	return KOLOFON_URI . 'assets/css/' . $file;
}

add_action( 'wp_head', __NAMESPACE__ . '\\preload_portrait', 5 );

/**
 * Preload the hero portrait on the front page.
 *
 * The portrait is the largest contentful paint there, and it is referenced
 * from markup rather than CSS, so a preload with high priority lets the
 * browser fetch it alongside the stylesheet instead of after it.
 */
function preload_portrait() {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$portrait = opt( 'hero_portrait' );

	if ( empty( $portrait ) ) {
		$portrait = default_portrait_url();
	}

	if ( empty( $portrait ) ) {
		return;
	}

	/*
	 * Preload the WebP the browser will actually pick, not the PNG fallback.
	 * Preloading the PNG would fetch 165 KiB the page never paints, since the
	 * picture element resolves to the 21 KiB WebP in every current browser.
	 */
	$preload = $portrait;
	$type    = '';

	if ( $portrait === default_portrait_url() ) {
		$webp = KOLOFON_DIR . 'assets/img/profile-359.webp';
		if ( is_readable( $webp ) ) {
			$preload = KOLOFON_URI . 'assets/img/profile-359.webp';
			$type    = ' type="image/webp"';
		}
	}

	printf(
		'<link rel="preload" as="image" href="%s"%s fetchpriority="high" />' . "\n",
		esc_url( $preload ),
		$type // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal attribute built above.
	);
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );

/**
 * Register and enqueue front-end CSS. No front-end JS is loaded by default.
 * Also drops core styles that this theme does not use.
 */
function enqueue_assets() {
	// Trim WP defaults the theme replaces or does not use.
	wp_deregister_script( 'wp-embed' );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );

	wp_enqueue_style(
		'kolofon-main',
		asset_css( 'main.css' ),
		array(),
		KOLOFON_VERSION
	);

	// The navigation toggle is only useful when there is a menu to collapse.
	if ( has_nav_menu( 'primary' ) ) {
		wp_enqueue_script(
			'kolofon-nav-toggle',
			KOLOFON_URI . 'assets/js/nav-toggle.js',
			array(),
			KOLOFON_VERSION,
			true
		);
	}

	// The search overlay enhances search that already works without it, so it
	// loads on every front-end page. The trigger button and overlay are built
	// by the script from a template the header prints.
	wp_enqueue_script(
		'kolofon-search-overlay',
		KOLOFON_URI . 'assets/js/search-overlay.js',
		array(),
		KOLOFON_VERSION,
		true
	);

	/*
	 * Promotes the per-row hover cards into one card that travels between rows.
	 * Only loaded where previews can appear, and the script itself stands down
	 * on touch-only devices and under prefers-reduced-motion. Without it the
	 * CSS fade-in-place behaviour still works.
	 */
	if ( previews_enabled() ) {
		wp_enqueue_script(
			'kolofon-hover-preview',
			KOLOFON_URI . 'assets/js/hover-preview.js',
			array(),
			KOLOFON_VERSION,
			true
		);
	}
}
