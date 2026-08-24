<?php
/**
 * Branding: custom-logo support, favicon fallback, bundled default assets.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', __NAMESPACE__ . '\\add_custom_logo_support' );
add_action( 'wp_head', __NAMESPACE__ . '\\emit_favicon_fallback', 1 );

/**
 * Register custom-logo support so Appearance -> Customize -> Site Identity
 * shows the logo uploader.
 */
function add_custom_logo_support() {
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 100,
			'flex-height' => true,
			'flex-width'  => true,
			'header-text' => array( 'site-title' ),
		)
	);
}

/**
 * URL to a bundled brand asset.
 *
 * @param string $file Filename within assets/img/.
 * @return string
 */
function brand_asset_url( $file ) {
	return KOLOFON_URI . 'assets/img/' . ltrim( $file, '/' );
}

/**
 * URL to the bundled default portrait.
 *
 * @return string
 */
function default_portrait_url() {
	return brand_asset_url( 'profile.png' );
}

/**
 * Emit fallback icon links only when the site owner has not set a Site Icon
 * in the Customizer (Appearance -> Customize -> Site Identity). WordPress's
 * own icon output wins when configured.
 *
 * The bundled default is a serif K in XCharter Bold, ivory on the Charcoal
 * background, matching the wordmark. It is flattened onto a solid background
 * because a transparent favicon disappears against matching browser chrome.
 * The Identity tab of Kolofon Options explains this and links to the Site
 * Icon control.
 */
function emit_favicon_fallback() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$icons = array(
		array(
			'rel'   => 'icon',
			'file'  => 'favicon.png',
			'sizes' => '32x32',
		),
		array(
			'rel'   => 'icon',
			'file'  => 'icon-192.png',
			'sizes' => '192x192',
		),
		array(
			'rel'   => 'apple-touch-icon',
			'file'  => 'apple-touch-icon.png',
			'sizes' => '180x180',
		),
	);

	foreach ( $icons as $icon ) {
		printf(
			'<link rel="%1$s" type="image/png" sizes="%2$s" href="%3$s" />' . "\n",
			esc_attr( $icon['rel'] ),
			esc_attr( $icon['sizes'] ),
			esc_url( brand_asset_url( $icon['file'] ) )
		);
	}
}

/**
 * Markup for the hero portrait, serving WebP where the browser supports it.
 *
 * The bundled default ships as WebP at two widths plus the original PNG as a
 * fallback, which takes the largest variant from 165 KiB to 21 KiB. A portrait
 * uploaded through Theme Options is served as-is, since the theme has no
 * derivatives for it.
 *
 * @param string $url  Portrait URL.
 * @param int    $size Rendered width and height in CSS pixels.
 * @param string $alt  Alternative text.
 * @return string
 */
function portrait_markup( $url, $size, $alt ) {
	$size = max( 1, intval( $size ) );
	$img  = sprintf(
		'<img class="u-photo" itemprop="image" src="%1$s" alt="%2$s" fetchpriority="high" decoding="async" width="%3$d" height="%3$d" />',
		esc_url( $url ),
		esc_attr( $alt ),
		$size
	);

	// Derivatives exist only for the bundled default.
	if ( $url !== default_portrait_url() ) {
		return $img;
	}

	$base = KOLOFON_URI . 'assets/img/';
	$dir  = KOLOFON_DIR . 'assets/img/';

	$sources = array();
	foreach ( array( 200, 359 ) as $width ) {
		if ( is_readable( $dir . 'profile-' . $width . '.webp' ) ) {
			$sources[] = esc_url( $base . 'profile-' . $width . '.webp' ) . ' ' . $width . 'w';
		}
	}

	if ( empty( $sources ) ) {
		return $img;
	}

	return sprintf(
		'<picture><source type="image/webp" srcset="%1$s" sizes="%2$dpx" />%3$s</picture>',
		esc_attr( implode( ', ', $sources ) ),
		$size,
		$img
	);
}
