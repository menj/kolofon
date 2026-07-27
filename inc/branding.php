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
 * The Appearance tab of Kolofon Options explains this and links to the Site
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
