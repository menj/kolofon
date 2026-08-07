<?php
/**
 * Microblog and Fediverse support.
 *
 * Merges the XFedi Microblog codebase into the theme, so a short-status post
 * type and its ActivityPub bridge are available without installing a separate
 * plugin. The classes under inc/microblog/ are carried over from XFedi
 * Microblog 1.0.0 by MENJ, GPL-2.0-or-later, with the plugin's own settings
 * screens and its companion-reader bridge left behind; configuration lives on
 * the Fediverse tab of Theme Options instead.
 *
 * The protocol itself is deliberately not merged. Federation needs WebFinger,
 * NodeInfo, an Actor endpoint, an inbox, RSA keys and HTTP signature
 * verification, which the ActivityPub plugin implements across 275 files. This
 * module detects that plugin and hands the status post type to it; when the
 * plugin is absent the microblog still works locally and simply does not
 * federate.
 *
 * @package Kolofon
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Whether the microblog is switched on.
 *
 * @return bool
 */
function microblog_enabled() {
	return 1 === intval( opt( 'microblog_enabled' ) );
}

/**
 * Whether federation should be offered.
 *
 * Requires both the theme option and the ActivityPub plugin. Without the
 * plugin there is nothing to federate through, so the option stands down
 * rather than implying a connection that does not exist.
 *
 * @return bool
 */
function fediverse_enabled() {
	return microblog_enabled() && 1 === intval( opt( 'fediverse_enabled' ) );
}

/**
 * Whether the ActivityPub plugin is present and active.
 *
 * Checked by class rather than by plugin path so it holds however the plugin
 * was installed.
 *
 * @return bool
 */
function activitypub_available() {
	return class_exists( '\\Activitypub\\Activitypub' ) || defined( 'ACTIVITYPUB_PLUGIN_VERSION' );
}

/**
 * Whether the standalone ActivityPub plugin is active.
 *
 * The theme bundles the same engine, so it must stand down when the plugin is
 * present rather than declare its classes twice. Checked by plugin path, since
 * by the time the theme loads the plugin has already defined its constants.
 *
 * @return bool
 */
function activitypub_plugin_active() {
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
 * Load the bundled ActivityPub engine.
 *
 * Runs on after_setup_theme, which is early enough: the engine registers all of
 * its own work on init and later, so nothing is missed by loading here rather
 * than at plugins_loaded.
 *
 * The engine is only loaded when federation is switched on. Left off, none of
 * its 275 files are touched, so a site not using the Fediverse pays nothing for
 * carrying it.
 */
function boot_fediverse_engine() {
	if ( 1 !== intval( opt( 'fediverse_enabled' ) ) ) {
		return;
	}

	// The standalone plugin owns the namespace if it is active.
	if ( activitypub_plugin_active() || class_exists( '\\Activitypub\\Activitypub' ) ) {
		return;
	}

	$entry = KOLOFON_DIR . 'inc/activitypub/activitypub.php';
	if ( ! is_readable( $entry ) ) {
		return;
	}

	require_once $entry;

	/*
	 * Run the engine's activation routine the first time it is switched on.
	 *
	 * Upstream ties this to plugin activation, and the bundled copy maps that to
	 * after_switch_theme. Neither fires at the right moment here: the theme is
	 * usually activated with federation off, so after_switch_theme has long
	 * passed by the time the toggle is flipped. Without this the rewrite rules
	 * for the Actor and WebFinger endpoints are never flushed and the delivery
	 * schedules are never registered, so discovery fails and nothing is sent.
	 *
	 * Keyed on the engine version so it also re-runs after an engine update
	 * changes the rules.
	 */
	$done = get_option( 'kolofon_fediverse_activated' );
	if ( ACTIVITYPUB_PLUGIN_VERSION !== $done ) {
		add_action( 'init', __NAMESPACE__ . '\\activate_fediverse_engine', 30 );
	}
}

/**
 * Flush rewrite rules and register schedules for the bundled engine.
 *
 * Runs once per engine version, late on init so the engine has registered its
 * rewrite rules and post types first.
 */
function activate_fediverse_engine() {
	if ( ! class_exists( '\\Activitypub\\Activitypub' ) ) {
		return;
	}

	if ( method_exists( '\\Activitypub\\Activitypub', 'activate' ) ) {
		\Activitypub\Activitypub::activate( false );
	} else {
		flush_rewrite_rules();
	}

	update_option( 'kolofon_fediverse_activated', ACTIVITYPUB_PLUGIN_VERSION, false );
}

/**
 * Clear the activation marker when federation is switched off.
 *
 * Ensures the routine runs again if it is later switched back on, since the
 * rewrite rules are dropped in between.
 *
 * @param mixed $old_value The previous option value.
 * @param mixed $value     The new option value.
 */
function reset_fediverse_activation( $old_value, $value ) {
	$was = isset( $old_value['fediverse_enabled'] ) ? intval( $old_value['fediverse_enabled'] ) : 0;
	$now = isset( $value['fediverse_enabled'] ) ? intval( $value['fediverse_enabled'] ) : 0;

	if ( $was === $now ) {
		return;
	}

	delete_option( 'kolofon_fediverse_activated' );

	// Rules change in both directions, so flush on the way out as well.
	if ( 0 === $now ) {
		flush_rewrite_rules();
	}
}
add_action( 'update_option_kolofon_options', __NAMESPACE__ . '\\reset_fediverse_activation', 10, 2 );
add_action( 'after_setup_theme', __NAMESPACE__ . '\\boot_fediverse_engine', 4 );

/**
 * Load and boot the merged microblog classes.
 *
 * Runs on after_setup_theme so the post type is registered before init, which
 * is where WordPress expects post types to appear.
 */
function boot_microblog() {
	if ( ! microblog_enabled() ) {
		return;
	}

	// The carried-over classes expect these two constants.
	if ( ! defined( 'XFEDI_MICROBLOG_VERSION' ) ) {
		define( 'XFEDI_MICROBLOG_VERSION', KOLOFON_VERSION );
	}
	if ( ! defined( 'XFEDI_MICROBLOG_URL' ) ) {
		define( 'XFEDI_MICROBLOG_URL', KOLOFON_URI . 'inc/microblog/' );
	}

	// Stand down entirely if the standalone plugin is also active, so the post
	// type is not registered twice.
	if ( class_exists( '\\XFediMicroblog\\CPT' ) ) {
		return;
	}

	$dir = KOLOFON_DIR . 'inc/microblog/';
	foreach ( array( 'class-plugin.php', 'class-cpt.php', 'class-composer.php', 'class-timeline.php', 'class-rest.php', 'class-activitypub-bridge.php' ) as $file ) {
		if ( is_readable( $dir . $file ) ) {
			require_once $dir . $file;
		}
	}

	if ( class_exists( '\\XFediMicroblog\\CPT' ) ) {
		\XFediMicroblog\CPT::register();
	}
	if ( class_exists( '\\XFediMicroblog\\Composer' ) ) {
		\XFediMicroblog\Composer::register();
	}
	if ( class_exists( '\\XFediMicroblog\\Timeline' ) ) {
		\XFediMicroblog\Timeline::register();
	}
	if ( class_exists( '\\XFediMicroblog\\REST' ) ) {
		\XFediMicroblog\REST::register();
	}

	// The bridge hands the status post type to ActivityPub. Registered late so
	// the plugin has finished its own bootstrap before we ask about it.
	if ( fediverse_enabled() && class_exists( '\\XFediMicroblog\\ActivityPub_Bridge' ) ) {
		add_action( 'init', __NAMESPACE__ . '\\register_fediverse_bridge', 20 );
	}
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\\boot_microblog', 5 );

/**
 * Hand the status post type to the ActivityPub plugin.
 */
function register_fediverse_bridge() {
	if ( method_exists( '\\XFediMicroblog\\ActivityPub_Bridge', 'maybe_register' ) ) {
		\XFediMicroblog\ActivityPub_Bridge::maybe_register();
	}
}

/**
 * The status post type slug, or an empty string when the microblog is off.
 *
 * @return string
 */
function status_post_type() {
	return class_exists( '\\XFediMicroblog\\CPT' ) ? \XFediMicroblog\CPT::POST_TYPE : 'xfedi_status';
}

/**
 * Whether the current post is a status.
 *
 * Statuses carry no title, so templates use this to lead with content instead.
 *
 * @param int|null $post_id Optional post ID.
 * @return bool
 */
function is_status( $post_id = null ) {
	return get_post_type( $post_id ) === status_post_type();
}

/**
 * Help text for the federation toggle, reporting the real plugin state.
 *
 * The theme carries the microblog but deliberately not the protocol, so this
 * says plainly whether the piece that does the federating is present.
 *
 * @return string
 */
function fediverse_help() {
	if ( activitypub_plugin_active() ) {
		return __( 'Switches on federation. The standalone ActivityPub plugin is active, so the theme defers to it and uses its engine rather than the bundled copy.', 'kolofon' );
	}

	return __( 'Switches on federation. The ActivityPub engine is bundled with this theme, so no plugin is needed: turning this on loads it, generates your keys and opens your Actor, inbox and WebFinger endpoints. Leaving it off loads none of it.', 'kolofon' );
}

/**
 * Your Fediverse identity and whether the site can actually federate.
 *
 * There is nothing to register. A site running ActivityPub is its own
 * Fediverse server: the handle is simply the account name joined to this
 * site's own domain, and remote servers discover it over WebFinger. What can
 * go wrong is environmental, so this reports the handle to share and the
 * conditions that have to hold for a remote server to resolve it.
 *
 * @return array{handle:string,actor:string,webfinger:string,checks:array}
 */
function fediverse_identity() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	// Guard the lookup: this panel must not fatal in any context where the
	// current-user API is unavailable.
	$user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$name = ( is_object( $user ) && ! empty( $user->user_login ) )
		? $user->user_login
		: sanitize_title( get_bloginfo( 'name' ) );

	$handle = $name . '@' . $host;

	$checks = array();

	$engine_on = 1 === intval( opt( 'fediverse_enabled' ) );
	$checks[]  = array(
		'label' => __( 'ActivityPub engine', 'kolofon' ),
		'ok'    => $engine_on,
		'note'  => $engine_on
			? (
				activitypub_plugin_active()
					? __( 'Running from the standalone plugin, which the theme defers to.', 'kolofon' )
					: __( 'Running from the copy bundled in this theme. No plugin needed.', 'kolofon' )
			)
			: __( 'Switched off. Turn on "Federate statuses" above to load it.', 'kolofon' ),
	);

	$is_https = ( function_exists( 'is_ssl' ) && is_ssl() ) || 0 === strpos( (string) home_url(), 'https://' );
	$checks[] = array(
		'label' => __( 'HTTPS', 'kolofon' ),
		'ok'    => $is_https,
		'note'  => $is_https
			? __( 'On. Remote servers refuse to talk to plain HTTP.', 'kolofon' )
			: __( 'Off. Most Fediverse servers will not federate with an insecure site.', 'kolofon' ),
	);

	$permalinks = (string) get_option( 'permalink_structure' );
	$checks[]   = array(
		'label' => __( 'Pretty permalinks', 'kolofon' ),
		'ok'    => '' !== $permalinks,
		'note'  => '' !== $permalinks
			? __( 'Set. WebFinger and the Actor endpoint need rewrite rules.', 'kolofon' )
			: __( 'Plain permalinks are set. Discovery endpoints will not resolve.', 'kolofon' ),
	);

	$public   = '1' === (string) get_option( 'blog_public' );
	$checks[] = array(
		'label' => __( 'Search engine visibility', 'kolofon' ),
		'ok'    => $public,
		'note'  => $public
			? __( 'Site is public.', 'kolofon' )
			: __( 'Reading settings discourage indexing, which also holds back federation.', 'kolofon' ),
	);

	$reachable = false === strpos( (string) $host, 'localhost' ) && false === strpos( (string) $host, '.local' );
	$checks[]  = array(
		'label' => __( 'Publicly reachable host', 'kolofon' ),
		'ok'    => $reachable,
		'note'  => $reachable
			? __( 'Remote servers can reach this domain.', 'kolofon' )
			: __( 'A local or staging host cannot be reached by other servers.', 'kolofon' ),
	);

	return array(
		'handle'    => $handle,
		'actor'     => home_url( '/author/' . $name . '/' ),
		'webfinger' => home_url( '/.well-known/webfinger?resource=acct:' . rawurlencode( $handle ) ),
		'checks'    => $checks,
	);
}
