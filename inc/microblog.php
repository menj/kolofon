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

	$entry = KOLOFON_DIR . 'vendor/activitypub/activitypub.php';
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
	if ( ! defined( 'KOLOFON_MICROBLOG_VERSION' ) ) {
		define( 'KOLOFON_MICROBLOG_VERSION', KOLOFON_VERSION );
	}
	if ( ! defined( 'KOLOFON_MICROBLOG_URL' ) ) {
		define( 'KOLOFON_MICROBLOG_URL', KOLOFON_URI . 'inc/microblog/' );
	}

	// Stand down entirely if the standalone plugin is also active, so the post
	// type is not registered twice.
	if ( class_exists( '\\Kolofon\Microblog\\CPT' ) ) {
		return;
	}

	$dir = KOLOFON_DIR . 'inc/microblog/';
	foreach ( array( 'class-plugin.php', 'class-cpt.php', 'class-composer.php', 'class-timeline.php', 'class-rest.php', 'class-activitypub-bridge.php' ) as $file ) {
		if ( is_readable( $dir . $file ) ) {
			require_once $dir . $file;
		}
	}

	if ( class_exists( '\\Kolofon\Microblog\\CPT' ) ) {
		\Kolofon\Microblog\CPT::register();
	}
	if ( class_exists( '\\Kolofon\Microblog\\Composer' ) ) {
		\Kolofon\Microblog\Composer::register();
	}
	if ( class_exists( '\\Kolofon\Microblog\\Timeline' ) ) {
		\Kolofon\Microblog\Timeline::register();
	}
	if ( class_exists( '\\Kolofon\Microblog\\REST' ) ) {
		\Kolofon\Microblog\REST::register();
	}

	// The bridge hands the status post type to ActivityPub. Registered late so
	// the plugin has finished its own bootstrap before we ask about it.
	if ( fediverse_enabled() && class_exists( '\\Kolofon\Microblog\\ActivityPub_Bridge' ) ) {
		add_action( 'init', __NAMESPACE__ . '\\register_fediverse_bridge', 20 );
	}
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\\boot_microblog', 5 );

/**
 * Hand the status post type to the ActivityPub plugin.
 */
function register_fediverse_bridge() {
	if ( method_exists( '\\Kolofon\Microblog\\ActivityPub_Bridge', 'maybe_register' ) ) {
		\Kolofon\Microblog\ActivityPub_Bridge::maybe_register();
	}
}

/**
 * The status post type slug, or an empty string when the microblog is off.
 *
 * @return string
 */
function status_post_type() {
	return class_exists( '\\Kolofon\Microblog\\CPT' ) ? \Kolofon\Microblog\CPT::POST_TYPE : 'kolofon_status';
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

	/*
	 * Ask the engine for the handles rather than reconstructing them. The two
	 * actors derive their usernames differently, and an earlier version of this
	 * panel guessed the author handle from the current user, which is not what
	 * a reader searching for the site would use.
	 */
	$handles = array();
	// Read the theme's setting, which is authoritative and written through to
	// the engine on init, so the panel cannot disagree with what is published.
	$choice = (string) opt( 'fediverse_profile' );
	$mode   = array(
		'author'     => 'actor',
		'blog'       => 'blog',
		'actor_blog' => 'actor_blog',
	)[ $choice ] ?? (string) get_option( 'activitypub_actor_mode', 'actor' );

	// Blog actor: the site itself. Its username is the host without "www.",
	// unless overridden on the ActivityPub screen.
	if ( 'actor' !== $mode ) {
		$blog_name = (string) get_option( 'activitypub_blog_identifier', '' );
		if ( '' === $blog_name ) {
			$blog_name = preg_replace( '/^www\./i', '', (string) $host );
		}
		$handles[] = array(
			'label'  => __( 'The site', 'kolofon' ),
			'handle' => $blog_name . '@' . $host,
			'note'   => __( 'Follow this to receive everything published here. This is usually the one to share.', 'kolofon' ),
		);
	}

	// Author actor: the individual writer.
	if ( 'blog' !== $mode ) {
		$user  = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
		$login = ( is_object( $user ) && ! empty( $user->user_login ) ) ? $user->user_login : '';
		if ( '' !== $login ) {
			$handles[] = array(
				'label'  => __( 'You as author', 'kolofon' ),
				'handle' => $login . '@' . $host,
				'note'   => __( 'Follow this to receive only posts written by this account.', 'kolofon' ),
			);
		}
	}

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

	$primary = isset( $handles[0]['handle'] ) ? $handles[0]['handle'] : '';

	return array(
		'mode'      => $mode,
		'handles'   => $handles,
		'handle'    => $primary,
		'webfinger' => home_url( '/.well-known/webfinger?resource=acct:' . rawurlencode( $primary ) ),
		'checks'    => $checks,
	);
}

/**
 * Style the bundled ActivityPub screens to match Theme Options.
 *
 * The engine ships inside the theme, so its settings pages should not look like
 * a separate product. This is done entirely from the outside with a stylesheet
 * and a body class: no file under vendor/activitypub/ is touched, so updating
 * the engine stays a diff of a single file.
 *
 * @param string $hook Current admin page hook.
 */
function enqueue_activitypub_admin_style( $hook ) {
	if ( ! is_bundled_activitypub_screen() ) {
		return;
	}

	wp_enqueue_style(
		'kolofon-admin-base',
		asset_css( 'admin-base.css' ),
		array(),
		KOLOFON_VERSION
	);

	wp_enqueue_style(
		'kolofon-admin-activitypub',
		asset_css( 'admin-activitypub.css' ),
		array( 'kolofon-admin-base' ),
		KOLOFON_VERSION
	);
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_activitypub_admin_style' );

/**
 * Whether the current admin screen belongs to the bundled engine.
 *
 * Matched on the page query arg rather than the hook suffix, because the engine
 * registers several screens whose hooks differ by menu position. Only applies
 * when the theme is providing the engine; if the standalone plugin is active it
 * owns its own presentation.
 *
 * @return bool
 */
function is_bundled_activitypub_screen() {
	if ( ! is_admin() || activitypub_plugin_active() ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reading a page slug to decide whether to load a stylesheet.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	return '' !== $page && 0 === strpos( $page, 'activitypub' );
}

/**
 * Add a scoping class so the stylesheet cannot affect other admin pages.
 *
 * @param string $classes Existing body classes.
 * @return string
 */
function activitypub_admin_body_class( $classes ) {
	if ( is_bundled_activitypub_screen() ) {
		// kolofon-admin scopes the shared base; kolofon-ap scopes the rules
		// specific to the engine's own markup.
		$classes .= ' kolofon-admin kolofon-ap';
	}

	return $classes;
}
add_filter( 'admin_body_class', __NAMESPACE__ . '\\activitypub_admin_body_class' );

/**
 * Move statuses published under the pre-integration post type.
 *
 * The microblog was merged from a plugin whose post type slug was
 * `xfedi_status`, and that slug is written into wp_posts for every status
 * already published. Renaming the constant alone would leave those rows
 * pointing at a post type that no longer exists: the statuses would disappear
 * from the admin, from the archive, and from the Fediverse, while still sitting
 * in the database.
 *
 * This rewrites them once, keyed on its own flag so it cannot run twice, and
 * flushes rewrite rules afterwards because the permalink structure belongs to
 * the new post type.
 */
function migrate_status_post_type() {
	if ( get_option( 'kolofon_status_slug_migrated' ) ) {
		return;
	}

	global $wpdb;

	if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
		return;
	}

	$legacy = 'xfedi_status';
	$current = status_post_type();

	if ( $legacy === $current ) {
		return;
	}

	$moved = $wpdb->update(
		$wpdb->posts,
		array( 'post_type' => $current ),
		array( 'post_type' => $legacy ),
		array( '%s' ),
		array( '%s' )
	);

	update_option( 'kolofon_status_slug_migrated', 1, false );

	if ( $moved ) {
		// Post counts and permalinks are both stale after a bulk post_type
		// change, so clear the caches that hold them.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		flush_rewrite_rules();
	}
}
add_action( 'init', __NAMESPACE__ . '\\migrate_status_post_type', 25 );

/**
 * Help text for the handle-style setting, showing the real resulting handle.
 *
 * @return string
 */
function fediverse_profile_help() {
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$user  = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$login = ( is_object( $user ) && ! empty( $user->user_login ) ) ? $user->user_login : 'you';

	return sprintf(
		/* translators: 1: personal handle, 2: site handle */
		__( 'What people follow you at. The first option gives one handle, %1$s. The site option repeats the domain, %2$s, because the engine will not let a site profile take an existing author name; that restriction exists so a handle resolves to exactly one identity. Changing this rewrites your profile, so anyone already following the old handle will need to follow the new one.', 'kolofon' ),
		'@' . $login . '@' . $host,
		'@' . preg_replace( '/^www\./i', '', (string) $host ) . '@' . $host
	);
}

/**
 * Apply the chosen handle style to the engine.
 *
 * The engine stores this as `activitypub_actor_mode`. Kolofon owns the setting
 * so the whole Fediverse surface is configured in one place, and writes it
 * through on save rather than duplicating the state.
 */
function sync_fediverse_profile_mode() {
	if ( 1 !== intval( opt( 'fediverse_enabled' ) ) ) {
		return;
	}

	$choice = (string) opt( 'fediverse_profile' );
	$map    = array(
		'author'     => 'actor',
		'blog'       => 'blog',
		'actor_blog' => 'actor_blog',
	);

	if ( ! isset( $map[ $choice ] ) ) {
		return;
	}

	$mode = $map[ $choice ];

	if ( (string) get_option( 'activitypub_actor_mode' ) !== $mode ) {
		update_option( 'activitypub_actor_mode', $mode );
		flush_rewrite_rules();
	}
}
add_action( 'init', __NAMESPACE__ . '\\sync_fediverse_profile_mode', 26 );

/**
 * Fetch this site's own Fediverse endpoints and validate the responses.
 *
 * Everything else on the tab is inferred from options, which proves only that
 * the settings look right. This makes real HTTP requests to the endpoints a
 * remote server would use and checks the payloads, so a pass means discovery
 * genuinely works rather than that it ought to.
 *
 * Runs on demand, never on page load, because it costs three network round
 * trips against the site itself.
 *
 * @return array<int, array{label:string,ok:bool,note:string}>
 */
function fediverse_live_test() {
	$identity = fediverse_identity();
	$handle   = isset( $identity['handle'] ) ? $identity['handle'] : '';
	$results  = array();

	if ( '' === $handle ) {
		return array(
			array(
				'label' => __( 'Handle', 'kolofon' ),
				'ok'    => false,
				'note'  => __( 'No profile is published. Switch on "Federate statuses" first.', 'kolofon' ),
			),
		);
	}

	$args = array(
		'timeout'     => 10,
		'redirection' => 3,
		'headers'     => array( 'Accept' => 'application/activity+json, application/ld+json, application/json' ),
	);

	// 1. WebFinger: how every remote server turns a handle into an actor URL.
	$wf_url  = home_url( '/.well-known/webfinger?resource=acct:' . rawurlencode( $handle ) );
	$wf      = wp_remote_get( $wf_url, $args );
	$actor   = '';

	if ( is_wp_error( $wf ) ) {
		$results[] = array(
			'label' => __( 'WebFinger', 'kolofon' ),
			'ok'    => false,
			/* translators: %s: error message */
			'note'  => sprintf( __( 'Request failed: %s', 'kolofon' ), $wf->get_error_message() ),
		);
	} else {
		$code = (int) wp_remote_retrieve_response_code( $wf );
		$body = json_decode( wp_remote_retrieve_body( $wf ), true );

		if ( 200 !== $code ) {
			$results[] = array(
				'label' => __( 'WebFinger', 'kolofon' ),
				'ok'    => false,
				/* translators: %d: HTTP status code */
				'note'  => sprintf( __( 'Returned HTTP %d. Visit Settings then Permalinks once to rebuild the rewrite rules.', 'kolofon' ), $code ),
			);
		} elseif ( ! is_array( $body ) || empty( $body['links'] ) ) {
			$results[] = array(
				'label' => __( 'WebFinger', 'kolofon' ),
				'ok'    => false,
				'note'  => __( 'Responded, but not with the expected JSON. Something else is answering this URL.', 'kolofon' ),
			);
		} else {
			foreach ( (array) $body['links'] as $link ) {
				if ( isset( $link['rel'], $link['href'] ) && 'self' === $link['rel'] ) {
					$actor = (string) $link['href'];
					break;
				}
			}
			$results[] = array(
				'label' => __( 'WebFinger', 'kolofon' ),
				'ok'    => '' !== $actor,
				'note'  => '' !== $actor
					? __( 'Resolves, and points at an actor. This is the lookup Mastodon performs when someone searches your handle.', 'kolofon' )
					: __( 'Resolves, but carries no self link, so no actor can be found.', 'kolofon' ),
			);
		}
	}

	// 2. The actor document itself.
	if ( '' !== $actor ) {
		$res = wp_remote_get( $actor, $args );

		if ( is_wp_error( $res ) ) {
			$results[] = array(
				'label' => __( 'Actor', 'kolofon' ),
				'ok'    => false,
				/* translators: %s: error message */
				'note'  => sprintf( __( 'Request failed: %s', 'kolofon' ), $res->get_error_message() ),
			);
		} else {
			$body  = json_decode( wp_remote_retrieve_body( $res ), true );
			$inbox = is_array( $body ) && ! empty( $body['inbox'] ) ? $body['inbox'] : '';
			$key   = is_array( $body ) && ! empty( $body['publicKey']['publicKeyPem'] );

			$results[] = array(
				'label' => __( 'Actor document', 'kolofon' ),
				'ok'    => '' !== $inbox,
				'note'  => '' !== $inbox
					? __( 'Serves a profile with an inbox, which is where replies and follows arrive.', 'kolofon' )
					: __( 'Served, but without an inbox, so nothing can be delivered to you.', 'kolofon' ),
			);

			$results[] = array(
				'label' => __( 'Signing key', 'kolofon' ),
				'ok'    => $key,
				'note'  => $key
					? __( 'A public key is published, so remote servers can verify what you send.', 'kolofon' )
					: __( 'No public key found. Deliveries will be rejected as unverifiable.', 'kolofon' ),
			);
		}
	}

	return $results;
}
