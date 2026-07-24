<?php
/**
 * Security headers, comment removal, head cleanup, XML-RPC disable.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

// XML-RPC and pingbacks.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', __NAMESPACE__ . '\\strip_pingback_header' );

// Front-end comments locked off site-wide for this bio microsite.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );

add_action( 'init', __NAMESPACE__ . '\\disable_comment_support', 100 );
add_action( 'admin_init', __NAMESPACE__ . '\\hide_comments_admin' );
add_action( 'admin_menu', __NAMESPACE__ . '\\remove_comments_menu' );
add_action( 'wp_before_admin_bar_render', __NAMESPACE__ . '\\remove_comments_admin_bar' );

// HTTP security headers on the front-end response.
add_action( 'send_headers', __NAMESPACE__ . '\\send_security_headers' );

// Head cleanup.
add_action( 'init', __NAMESPACE__ . '\\clean_wp_head' );

// Big image threshold off (keeps original upload dimensions).
add_filter( 'big_image_size_threshold', '__return_false' );

/*
 * Theme and Plugin File Editors.
 *
 * Editing live PHP from the browser turns any admin session hijack into
 * arbitrary code execution, and the theme ships no mechanism that needs it.
 * Off by default; switchable on the Advanced tab for anyone who wants it back.
 *
 * wp-config.php remains the authoritative place to set this. If the constant
 * is already defined there, that definition wins and this does nothing, which
 * is the correct precedence: a theme should not be able to loosen a policy the
 * site owner set at the configuration level.
 */
disable_file_editors();

/**
 * Define DISALLOW_FILE_EDIT and remove the editor screens.
 */
function disable_file_editors() {
	if ( ! file_editors_disabled() ) {
		return;
	}

	if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}

	// Belt and braces: drop the menu entries and block direct URL access, in
	// case something re-enables the capability later in the request.
	add_action( 'admin_menu', __NAMESPACE__ . '\\remove_file_editor_menus', 999 );
	add_action( 'admin_init', __NAMESPACE__ . '\\block_file_editor_screens' );
}

/**
 * Whether the theme should switch the editors off.
 *
 * Reads the option directly rather than through opt(), because this runs at
 * theme load, before the options module has registered anything.
 *
 * @return bool
 */
function file_editors_disabled() {
	$stored = get_option( KOLOFON_OPTION_KEY, array() );

	if ( is_array( $stored ) && array_key_exists( 'disable_file_edit', $stored ) ) {
		return 1 === intval( $stored['disable_file_edit'] );
	}

	// No stored preference yet, so a fresh install is protected by default.
	return true;
}

/**
 * Remove the editor submenu entries.
 */
function remove_file_editor_menus() {
	remove_submenu_page( 'themes.php', 'theme-editor.php' );
	remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
}

/**
 * Refuse direct requests to the editor screens.
 */
function block_file_editor_screens() {
	global $pagenow;

	if ( in_array( $pagenow, array( 'theme-editor.php', 'plugin-editor.php' ), true ) ) {
		wp_die(
			esc_html__( 'File editing is disabled.', 'kolofon' ),
			esc_html__( 'File editing disabled', 'kolofon' ),
			array( 'response' => 403 )
		);
	}
}

/**
 * Strip the X-Pingback header.
 *
 * @param array $headers Response headers.
 * @return array
 */
function strip_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}

/**
 * Remove comment support from post and page.
 */
function disable_comment_support() {
	remove_post_type_support( 'post', 'comments' );
	remove_post_type_support( 'page', 'comments' );
}

/**
 * Redirect any request to the comments admin screen.
 */
function hide_comments_admin() {
	global $pagenow;

	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}

	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

/**
 * Drop the Comments menu item.
 */
function remove_comments_menu() {
	remove_menu_page( 'edit-comments.php' );
}

/**
 * Drop the Comments item from the admin bar.
 */
function remove_comments_admin_bar() {
	global $wp_admin_bar;
	if ( $wp_admin_bar ) {
		$wp_admin_bar->remove_menu( 'comments' );
	}
}

/**
 * Send common security headers.
 */
function send_security_headers() {
	header( 'Strict-Transport-Security: max-age=15768000' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Permitted-Cross-Domain-Policies: none' );

	// Permissions-Policy: opt out of browser features the site never uses.
	// Every declared feature is closed; the specific list is the browser API
	// surface a bio microsite has no legitimate reason to touch. Fullscreen
	// stays permitted from same-origin for image lightboxes and video posts.
	header( 'Permissions-Policy: geolocation=(), midi=(), sync-xhr=(), accelerometer=(), gyroscope=(), magnetometer=(), camera=(), microphone=(), payment=(), usb=(), fullscreen=(self)' );
}

/**
 * Remove noisy tags from wp_head.
 */
function clean_wp_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
}
