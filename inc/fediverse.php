<?php
/**
 * Fediverse integration layer.
 *
 * The one file in the theme allowed to know how the ActivityPub engine is
 * detected, which of its options the theme touches, and how its activation
 * routine is invoked. Everything else, the Microblog module and its bridge
 * included, asks this layer instead of reaching for `\Activitypub` classes,
 * `ACTIVITYPUB_*` constants, or `activitypub_*` options directly.
 *
 * The point is 9.2 of the roadmap: the engine, bundled under
 * `vendor/activitypub/` or installed as the standalone plugin, can change
 * internals or be swapped for the plugin outright without unrelated theme
 * code changing. If the engine's detection signals or option names ever
 * move, this file is the whole diff.
 *
 * @package Kolofon
 * @since   7.5.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Whether an ActivityPub engine is loaded at all, bundled or plugin.
 *
 * Checks every signal the engine has shipped across versions: the main
 * class, the version constant, and the version function older releases
 * exposed. Any one of them means the namespace is owned and the engine is
 * answering.
 *
 * @return bool
 */
function fediverse_engine_available() {
	return class_exists( '\\Activitypub\\Activitypub' )
		|| defined( 'ACTIVITYPUB_PLUGIN_VERSION' )
		|| function_exists( '\\Activitypub\\get_plugin_version' );
}

/**
 * Whether the standalone ActivityPub plugin is active.
 *
 * The theme bundles the same engine, so it must stand down when the plugin
 * is present rather than declare its classes twice. Checked by plugin path,
 * since by the time the theme loads the plugin has already defined its
 * constants.
 *
 * @return bool
 */
function fediverse_standalone_plugin_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		$plugin_api = ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_readable( $plugin_api ) ) {
			require_once $plugin_api;
		}
	}

	if ( function_exists( 'is_plugin_active' ) ) {
		return is_plugin_active( 'activitypub/activitypub.php' );
	}

	// No plugin API available: fall back to the active-plugins option directly.
	$active = (array) get_option( 'active_plugins', array() );

	return in_array( 'activitypub/activitypub.php', $active, true );
}

/**
 * Where the running engine comes from.
 *
 * @return string 'plugin', 'bundled', or 'none'.
 */
function fediverse_engine_source() {
	if ( fediverse_standalone_plugin_active() ) {
		return 'plugin';
	}

	return fediverse_engine_available() ? 'bundled' : 'none';
}

/**
 * The loaded engine's version, or an empty string when no engine is loaded.
 *
 * @return string
 */
function fediverse_engine_version() {
	return defined( 'ACTIVITYPUB_PLUGIN_VERSION' ) ? (string) ACTIVITYPUB_PLUGIN_VERSION : '';
}

/**
 * Read one of the engine's own options.
 *
 * The theme touches exactly two: `actor_mode`, which it owns through the
 * `fediverse_profile` Theme Option and writes through on init, and
 * `blog_identifier`, which it only reads to build the followable handle. The
 * `activitypub_` prefix is applied here so callers name the setting, not the
 * engine's storage convention.
 *
 * @param string $name    Engine option name without the `activitypub_` prefix.
 * @param mixed  $default Value when the option is unset.
 * @return mixed
 */
function fediverse_engine_option( $name, $default = '' ) {
	return get_option( 'activitypub_' . $name, $default );
}

/**
 * Write one of the engine's own options.
 *
 * @param string $name  Engine option name without the `activitypub_` prefix.
 * @param mixed  $value Value to store.
 * @return bool Whether the value changed.
 */
function fediverse_set_engine_option( $name, $value ) {
	return update_option( 'activitypub_' . $name, $value );
}

/**
 * Run the engine's activation routine, or the closest available substitute.
 *
 * Upstream ties this to plugin activation; the bundled copy needs it invoked
 * by hand the first time federation is switched on, and again after an
 * engine update (the caller keys that on fediverse_engine_version()). Falls
 * back to a plain rewrite flush when the engine predates the activate
 * method.
 */
function fediverse_run_engine_activation() {
	if ( ! class_exists( '\\Activitypub\\Activitypub' ) ) {
		return;
	}

	if ( method_exists( '\\Activitypub\\Activitypub', 'activate' ) ) {
		\Activitypub\Activitypub::activate( false );
	} else {
		flush_rewrite_rules();
	}
}
