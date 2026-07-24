<?php
/**
 * Email address obfuscation.
 *
 * Keeps plain `mailto:` strings out of the served HTML so that the common
 * harvester pattern (fetch page, regex for mailto or for an address) comes
 * back empty.
 *
 * No client-side scheme defeats a headless browser that executes JavaScript.
 * The goal here is the large majority of scrapers, which do not. Modes are
 * offered rather than forced, because the strongest mode has a real cost:
 * without JavaScript the link needs a fallback.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\maybe_enqueue_email_guard', 30 );
add_filter( 'the_content', __NAMESPACE__ . '\\filter_content_mailto', 20 );
add_shortcode( 'menj_email', __NAMESPACE__ . '\\email_shortcode' );

/**
 * Shortcode: [menj_email] or [menj_email address="you@example.com" text="Write to me"]
 *
 * With no address attribute it falls back to the address stored on the
 * Social tab.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function email_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'address' => '',
			'text'    => '',
			'class'   => '',
		),
		$atts,
		'menj_email'
	);

	$email = '' !== $atts['address'] ? $atts['address'] : strip_mailto( opt( social_key( 'email' ) ) );

	if ( '' === $email ) {
		return '';
	}

	$classes = trim( 'k-email ' . $atts['class'] );
	$attrs   = array( 'class' => $classes );

	if ( '' !== $atts['text'] ) {
		$inner = esc_html( $atts['text'] );
	} else {
		// No label given, so the address itself is the label. In JS mode the
		// markup ships a masked placeholder and the script fills it in.
		if ( 'js' === email_mode() ) {
			$inner                 = esc_html__( 'Show email address', 'kolofon' );
			$attrs['data-mbe-fill'] = '1';
		} else {
			$inner = antispambot( strip_mailto( $email ) );
		}
	}

	return protected_mailto( $email, $inner, $attrs );
}

/**
 * Available obfuscation modes.
 *
 * @return array
 */
function get_email_modes() {
	return array(
		'js'       => __( 'JavaScript rebuild (strongest)', 'kolofon' ),
		'entities' => __( 'HTML entities (works without JavaScript)', 'kolofon' ),
		'off'      => __( 'Off, emit plain mailto:', 'kolofon' ),
	);
}

/**
 * Tracks whether a protected link was rendered on this request, so the
 * decoder script is only enqueued on pages that actually need it.
 *
 * @param bool|null $set Pass true to mark as needed.
 * @return bool
 */
function email_guard_needed( $set = null ) {
	static $needed = false;
	if ( true === $set ) {
		$needed = true;
	}
	return $needed;
}

/**
 * Encode an address for client-side reassembly.
 *
 * Base64 first, then ROT13. Order matters: ROT13-then-base64 would mean a
 * scraper that blindly base64-decodes every long attribute recovers something
 * email-shaped. Encoding this way round, a lone base64 decode yields nothing
 * that matches an address pattern, so a single-step sweep finds no candidate.
 *
 * Neither step is cryptography and neither is claimed to be.
 *
 * @param string $email Plain address.
 * @return string
 */
function encode_email( $email ) {
	return str_rot13( base64_encode( $email ) );
}

/**
 * Strip a leading mailto: scheme from a stored value.
 *
 * @param string $value Stored option value.
 * @return string
 */
function strip_mailto( $value ) {
	return preg_replace( '/^mailto:/i', '', trim( (string) $value ) );
}

/**
 * Current obfuscation mode, validated.
 *
 * @return string
 */
function email_mode() {
	$mode  = opt( 'email_obfuscation' );
	$modes = get_email_modes();
	return array_key_exists( $mode, $modes ) ? $mode : 'js';
}

/**
 * Build a protected mailto anchor.
 *
 * @param string $email      Plain address.
 * @param string $inner_html Anchor contents. Already-escaped markup.
 * @param array  $attrs      Extra attributes as key => value.
 * @return string
 */
function protected_mailto( $email, $inner_html, $attrs = array() ) {
	$email = strip_mailto( $email );

	if ( '' === $email || ! is_email( $email ) ) {
		return '';
	}

	$mode = email_mode();

	// The JS-mode template needs its own k-email class, and HTML drops a
	// second class attribute on the same element, silently discarding the
	// caller's. So the class is merged here and emitted exactly once.
	$classes = trim( 'k-email ' . ( isset( $attrs['class'] ) ? (string) $attrs['class'] : '' ) );
	unset( $attrs['class'] );

	$attr_html = ' class="' . esc_attr( implode( ' ', array_unique( explode( ' ', $classes ) ) ) ) . '"';
	foreach ( $attrs as $key => $value ) {
		$attr_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	if ( 'off' === $mode ) {
		return sprintf(
			'<a href="%1$s"%2$s>%3$s</a>',
			esc_url( 'mailto:' . $email ),
			$attr_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute above.
			$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- caller supplies escaped markup.
		);
	}

	if ( 'entities' === $mode ) {
		// antispambot() entity-encodes the address. Encode the scheme too, so
		// the literal string "mailto:" never appears in the served bytes.
		$scheme = 'mailto:';
		$hex    = '';
		foreach ( str_split( $scheme ) as $char ) {
			$hex .= '&#' . ord( $char ) . ';';
		}
		return sprintf(
			'<a href="%1$s%2$s"%3$s>%4$s</a>',
			$hex, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generated numeric entities.
			antispambot( $email ),
			$attr_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute above.
			$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- caller supplies escaped markup.
		);
	}

	// JavaScript mode. No address and no scheme in the markup at all. The
	// rel is merged for the same reason the class is: HTML keeps only the
	// first occurrence of a repeated attribute.
	email_guard_needed( true );

	$rel = trim( 'nofollow ' . ( isset( $attrs['rel'] ) ? (string) $attrs['rel'] : '' ) );
	unset( $attrs['rel'] );

	$attr_html = ' class="' . esc_attr( implode( ' ', array_unique( explode( ' ', $classes ) ) ) ) . '"';
	$attr_html .= ' rel="' . esc_attr( implode( ' ', array_unique( explode( ' ', $rel ) ) ) ) . '"';
	foreach ( $attrs as $key => $value ) {
		$attr_html .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	return sprintf(
		'<a href="#" data-mbe="%1$s"%2$s>%3$s</a>',
		esc_attr( encode_email( $email ) ),
		$attr_html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute above.
		$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- caller supplies escaped markup.
	);
}

/**
 * Rewrite plain mailto: anchors inside post content.
 *
 * Catches addresses typed into the editor, which are otherwise the easiest
 * thing on the site to harvest.
 *
 * @param string $content Post content.
 * @return string
 */
function filter_content_mailto( $content ) {
	if ( 'off' === email_mode() || false === stripos( $content, 'mailto:' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'#<a\s([^>]*?)href=(["\'])mailto:([^"\']+)\2([^>]*)>(.*?)</a>#is',
		function ( $m ) {
			$email = html_entity_decode( $m[3], ENT_QUOTES, 'UTF-8' );
			$inner = $m[5];

			$link = protected_mailto( $email, $inner );

			// If the address failed validation, leave the original untouched.
			return '' === $link ? $m[0] : $link;
		},
		$content
	);
}

/**
 * Enqueue the decoder only when a protected link was rendered.
 *
 * Runs late on wp_enqueue_scripts; templates that render the hero social row
 * have already run by then. Content-filtered links register during rendering,
 * so the script is also registered eagerly and printed in the footer.
 */
function maybe_enqueue_email_guard() {
	if ( 'js' !== email_mode() ) {
		return;
	}

	wp_register_script(
		'kolofon-email-guard',
		KOLOFON_URI . 'assets/js/email-guard.js',
		array(),
		KOLOFON_VERSION,
		true
	);
	wp_enqueue_script( 'kolofon-email-guard' );
}
