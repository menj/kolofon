<?php
/**
 * Branded login screen.
 *
 * Replaces the WordPress logo on wp-login.php with the site's own mark and
 * tints the primary control with the theme accent. It uses media the owner has
 * already set, in the order Custom Logo, Site Icon, bundled portrait, so there
 * is nothing to configure and no hand-placed file to remember.
 *
 * This absorbs what a login-branding plugin does, which is why the live site
 * can drop its own.
 *
 * @package MENJ\Bio
 * @since   1.1.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\login_styles' );
add_filter( 'login_headerurl', __NAMESPACE__ . '\\login_logo_url' );
add_filter( 'login_headertext', __NAMESPACE__ . '\\login_logo_text' );

/**
 * Resolve the image to use as the login mark.
 *
 * @return string URL, or an empty string when nothing suitable exists.
 */
function login_mark_url() {
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'medium' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			return $src[0];
		}
	}

	if ( function_exists( 'get_site_icon_url' ) ) {
		$icon = get_site_icon_url( 192 );
		if ( $icon ) {
			return $icon;
		}
	}

	return default_portrait_url();
}

/**
 * Print the login stylesheet.
 *
 * Colours come from the active scheme, so the login screen follows whatever
 * the site is wearing rather than carrying a second hard-coded palette.
 */
function login_styles() {
	$mark   = login_mark_url();
	$scheme = opt( 'colour_scheme' );

	// Auto pairs two palettes; the login screen takes the light half rather
	// than shipping a second media-query block for one form.
	$palette = resolve_palette( 'auto' === $scheme ? 'ivory' : $scheme );
	$bg      = $palette['bg'];
	$text    = $palette['text'];
	$accent  = $palette['accent'];
	$rule    = $palette['rule'];

	$stacks = get_font_stacks();
	$font   = $stacks[ opt( 'font_stack' ) ] ?? reset( $stacks );

	$css = '
		body.login {
			background: ' . $bg . ';
			color: ' . $text . ';
			font-family: ' . $font['body'] . ';
		}
		.login h1 a {
			background-image: url(' . esc_url_raw( $mark ) . ');
			background-size: contain;
			background-position: center;
			width: 84px;
			height: 84px;
			margin-bottom: 1.25rem;
		}
		.login form {
			background: ' . $bg . ';
			border: 1px solid ' . $rule . ';
			border-radius: 8px;
			box-shadow: none;
		}
		.login label,
		.login form .input,
		.login input[type="text"],
		.login input[type="password"] {
			color: ' . $text . ';
		}
		.login form .input,
		.login input[type="text"],
		.login input[type="password"] {
			background: ' . $bg . ';
			border: 1px solid ' . $rule . ';
			border-radius: 4px;
		}
		.login form .input:focus,
		.login input[type="text"]:focus,
		.login input[type="password"]:focus {
			border-color: ' . $accent . ';
			box-shadow: 0 0 0 1px ' . $accent . ';
			outline: 2px solid transparent;
		}
		.wp-core-ui .button-primary {
			background: ' . $accent . ';
			border-color: ' . $accent . ';
			color: ' . $bg . ';
			text-shadow: none;
			box-shadow: none;
		}
		.wp-core-ui .button-primary:hover,
		.wp-core-ui .button-primary:focus {
			background: ' . $text . ';
			border-color: ' . $text . ';
			color: ' . $bg . ';
		}
		.login #backtoblog a,
		.login #nav a,
		.login .privacy-policy-page-link a {
			color: ' . $accent . ';
		}
		.login #backtoblog a:hover,
		.login #nav a:hover {
			color: ' . $text . ';
		}
		.login .message,
		.login .notice {
			border-left-color: ' . $accent . ';
		}
	';

	wp_register_style( 'menj-bio-login', false, array(), MENJ_BIO_VERSION );
	wp_enqueue_style( 'menj-bio-login' );
	wp_add_inline_style( 'menj-bio-login', $css );
}

/**
 * Point the login mark at the site rather than at wordpress.org.
 *
 * @return string
 */
function login_logo_url() {
	return home_url( '/' );
}

/**
 * Use the site name as the mark's accessible text.
 *
 * @return string
 */
function login_logo_text() {
	return get_bloginfo( 'name', 'display' );
}
