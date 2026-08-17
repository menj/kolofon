<?php
/**
 * Plugin Name: ActivityPub
 * Plugin URI: https://github.com/Automattic/wordpress-activitypub
 * Description: The ActivityPub protocol is a decentralized social networking protocol based upon the ActivityStreams 2.0 data format.
 * Version: 9.2.0
 * Author: Matthias Pfefferle & Automattic
 * Author URI: https://automattic.com/
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Requires PHP: 7.4
 * Text Domain: activitypub
 * Domain Path: /languages
 *
 * @package Activitypub
 */

namespace Activitypub;

\define( 'ACTIVITYPUB_PLUGIN_VERSION', '9.2.0' );

/*
 * Path constants. Upstream derives these from the plugin location; bundled in a
 * theme they are derived from the theme directory instead. Nothing else in the
 * codebase needs to know the difference.
 */
\define( 'ACTIVITYPUB_PLUGIN_DIR', trailingslashit( __DIR__ ) );
\define( 'ACTIVITYPUB_PLUGIN_BASENAME', \basename( \get_template_directory() ) . '/vendor/activitypub/activitypub.php' );
\define( 'ACTIVITYPUB_PLUGIN_FILE', ACTIVITYPUB_PLUGIN_DIR . basename( __FILE__ ) );
\define( 'ACTIVITYPUB_PLUGIN_URL', \get_template_directory_uri() . '/vendor/activitypub/' );
\define( 'ACTIVITYPUB_BUNDLED_IN_THEME', true );

require_once __DIR__ . '/includes/class-autoloader.php';
require_once __DIR__ . '/includes/compat.php';
require_once __DIR__ . '/includes/constants.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/functions-activity.php';
require_once __DIR__ . '/includes/functions-comment.php';
require_once __DIR__ . '/includes/functions-federation.php';
require_once __DIR__ . '/includes/functions-media.php';
require_once __DIR__ . '/includes/functions-post.php';
require_once __DIR__ . '/includes/functions-request.php';
require_once __DIR__ . '/includes/functions-url.php';
require_once __DIR__ . '/includes/functions-user.php';
require_once __DIR__ . '/integration/load.php';

Autoloader::register_path( __NAMESPACE__, __DIR__ . '/includes' );

/*
 * Upstream registers activation, deactivation and uninstall hooks against the
 * plugin file. A theme has no equivalent, so the same routines run on the theme
 * lifecycle: activate on switching to this theme, deactivate on switching away.
 * Uninstall is deliberately not wired, because removing a theme should not
 * destroy the actor keys and follower list.
 */
\add_action( 'after_switch_theme', array( Activitypub::class, 'activate' ) );
\add_action( 'switch_theme', array( Activitypub::class, 'deactivate' ) );

/**
 * Initialize REST routes.
 */
function rest_init() {
	Rest\Server::init();
	( new Rest\Actors_Controller() )->register_routes();
	( new Rest\Actors_Inbox_Controller() )->register_routes();
	( new Rest\Admin\Actions_Controller() )->register_routes();
	( new Rest\Admin\Statistics_Controller() )->register_routes();
	( new Rest\Application_Controller() )->register_routes();
	( new Rest\Stats_Image_Controller() )->register_routes();
	( new Rest\Collections_Controller() )->register_routes();
	( new Rest\Comments_Controller() )->register_routes();
	( new Rest\Followers_Controller() )->register_routes();
	( new Rest\Following_Controller() )->register_routes();
	( new Rest\Liked_Controller() )->register_routes();
	( new Rest\Inbox_Controller() )->register_routes();
	( new Rest\Interaction_Controller() )->register_routes();
	( new Rest\Moderators_Controller() )->register_routes();
	if ( \get_option( 'activitypub_api', false ) ) {
		( new Rest\Actor_Autocomplete_Controller() )->register_routes();
		( new Rest\OAuth\Authorization_Controller() )->register_routes();
		( new Rest\OAuth\Clients_Controller() )->register_routes();
		( new Rest\OAuth\Token_Controller() )->register_routes();
	}
	( new Rest\Outbox_Controller() )->register_routes();
	( new Rest\Post_Controller() )->register_routes();
	( new Rest\Replies_Controller() )->register_routes();
	( new Rest\Webfinger_Controller() )->register_routes();

	// Load NodeInfo endpoints only if blog is public.
	if ( is_blog_public() ) {
		( new Rest\Nodeinfo_Controller() )->register_routes();
	}
	( new Rest\Proxy_Controller() )->register_routes();
}
\add_action( 'rest_api_init', __NAMESPACE__ . '\rest_init' );

/**
 * Initialize plugin.
 */
function plugin_init() {
	\add_action( 'init', array( __NAMESPACE__ . '\Activitypub', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Application', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Avatars', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Blurhash', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Cache', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Comment', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Dispatcher', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Embed', 'init' ) );
	if ( \get_option( 'activitypub_api', false ) ) {
		\add_action( 'init', array( __NAMESPACE__ . '\Event_Stream', 'init' ) );
	}
	\add_action( 'init', array( __NAMESPACE__ . '\Handler', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Hashtag', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Link', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Mailer', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Mention', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Migration', 'init' ), 1 );
	\add_action( 'init', array( __NAMESPACE__ . '\Move', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Options', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Post_Types', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Router', 'init' ) );
	// Priority 0 ensures Scheduler hooks are registered before Migration (priority 1) runs.
	\add_action( 'init', array( __NAMESPACE__ . '\Scheduler', 'init' ), 0 );
	\add_action( 'init', array( __NAMESPACE__ . '\Search', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\Signature', 'init' ) );
	// Only load OAuth Server if the ActivityPub API is enabled.
	if ( \get_option( 'activitypub_api', false ) ) {
		\add_action( 'init', array( __NAMESPACE__ . '\OAuth\Server', 'init' ) );
	}

	if ( site_supports_blocks() ) {
		\add_action( 'init', array( __NAMESPACE__ . '\Blocks', 'init' ) );
	}

	// Only load relay if relay mode is enabled.
	if ( \get_option( 'activitypub_relay_mode', false ) ) {
		\add_action( 'init', array( __NAMESPACE__ . '\Relay', 'init' ) );
	}

	// Load development tools.
	if ( 'local' === wp_get_environment_type() ) {
		$loader_file = __DIR__ . '/local/load.php';
		if ( \file_exists( $loader_file ) && \is_readable( $loader_file ) ) {
			require_once $loader_file;
		}
	}
}
/*
 * Upstream defers this to plugins_loaded. Bundled in a theme the file is
 * required from after_setup_theme, by which point plugins_loaded has already
 * fired and the callback would never run, so it is invoked directly. Everything
 * plugin_init() registers targets init and later, so nothing is missed.
 */
if ( \defined( 'ACTIVITYPUB_BUNDLED_IN_THEME' ) && \did_action( 'plugins_loaded' ) ) {
	plugin_init();
} else {
	\add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin_init' );
}

/**
 * Initialize plugin admin.
 */
function plugin_admin_init() {
	// Screen Options and Menus are set before `admin_init`.
	\add_action( 'init', array( __NAMESPACE__ . '\WP_Admin\Heartbeat', 'init' ), 9 ); // Before script loader.
	\add_filter( 'init', array( __NAMESPACE__ . '\WP_Admin\Screen_Options', 'init' ) );
	\add_action( 'init', array( __NAMESPACE__ . '\WP_Admin\Menu', 'init' ) );

	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Admin', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Advanced_Settings_Fields', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\App', 'init' ), 0 ); // Before admin bar init.
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Blog_Settings_Fields', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Health_Check', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Settings', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Settings_Fields', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Dashboard', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\User_Settings_Fields', 'init' ) );
	\add_action( 'admin_init', array( __NAMESPACE__ . '\WP_Admin\Welcome_Fields', 'init' ) );

	if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
		require_once __DIR__ . '/includes/wp-admin/import/load.php';
		\add_action( 'admin_init', __NAMESPACE__ . '\WP_Admin\Import\load' );
	}
}
if ( \defined( 'ACTIVITYPUB_BUNDLED_IN_THEME' ) && \did_action( 'plugins_loaded' ) ) {
	plugin_admin_init();
} else {
	\add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin_admin_init' );
}

/**
 * Redirect to the welcome page after plugin activation.
 *
 * @param string $plugin The plugin basename.
 */
function activation_redirect( $plugin ) {
	if ( ACTIVITYPUB_PLUGIN_BASENAME === $plugin ) {
		\wp_safe_redirect( \admin_url( 'options-general.php?page=activitypub' ) );
		exit;
	}
}
\add_action( 'activated_plugin', __NAMESPACE__ . '\activation_redirect' );

// Check for CLI env, to add the CLI commands.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	Cli::register();
}

// Register OAuth login form handler early (before wp-login.php processes).
\add_action( 'login_form_activitypub_authorize', array( __NAMESPACE__ . '\OAuth\Server', 'login_form_authorize' ) );
