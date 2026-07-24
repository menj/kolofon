<?php
/**
 * Front-end asset registration.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

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

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
		esc_url( $portrait )
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
		'menj-bio-main',
		MENJ_BIO_URI . 'assets/css/main.css',
		array(),
		MENJ_BIO_VERSION
	);

	// The navigation toggle is only useful when there is a menu to collapse.
	if ( has_nav_menu( 'primary' ) ) {
		wp_enqueue_script(
			'menj-bio-nav-toggle',
			MENJ_BIO_URI . 'assets/js/nav-toggle.js',
			array(),
			MENJ_BIO_VERSION,
			true
		);
	}
}
