<?php
/**
 * Resilience: keep one broken plugin from taking the whole page down.
 *
 * WordPress runs `wp_head` and `wp_footer` as a flat list of callbacks from
 * every active plugin. If one of them throws — a missing file, a bad
 * `require_once`, anything that surfaces as an uncaught `Error` — the entire
 * request dies mid-render and the visitor sees WordPress's raw recovery-mode
 * text sitting under whatever half of the page had already streamed out.
 *
 * Since PHP 7, that class of fatal is a `\Throwable` and is genuinely
 * catchable — it's the older, un-catchable fatal errors that aren't. This
 * module wraps those two hooks so a single plugin's fatal is caught,
 * logged, and skipped, instead of crashing the page for every visitor.
 *
 * This is a safety net, not a fix: the underlying plugin bug still needs
 * fixing. It exists so a broken third-party hook degrades to "the footer
 * scripts didn't run" instead of "the site is down."
 *
 * @package Kolofon
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Run an action, catching any Throwable thrown by its callbacks.
 *
 * @param string $hook Action hook to run in place of a direct do_action() call.
 */
function run_guarded_hook( $hook ) {
	try {
		do_action( $hook );
	} catch ( \Throwable $e ) {
		log_guarded_hook_failure( $hook, $e );
	}
}

/**
 * Log a caught hook failure and remember it for the current request so an
 * admin notice can surface it once, without re-triggering the fatal.
 *
 * @param string     $hook Hook name that failed.
 * @param \Throwable $e    The caught error.
 */
function log_guarded_hook_failure( $hook, \Throwable $e ) {
	error_log(
		sprintf(
			'Kolofon: caught fatal in "%s" callbacks — %s in %s:%d. Page continued to render; the underlying plugin still needs fixing.',
			$hook,
			$e->getMessage(),
			$e->getFile(),
			$e->getLine()
		)
	);

	// Stash for this request only; nothing persists to the database.
	$GLOBALS['kolofon_guarded_hook_failures'][] = array(
		'hook'    => $hook,
		'class'   => get_class( $e ),
		'message' => $e->getMessage(),
		'file'    => $e->getFile(),
		'line'    => $e->getLine(),
	);
}

/**
 * Identify what kind of failure actually occurred, from the exception's
 * class and message. This is pattern matching, not a stack-trace analysis —
 * good enough to name the failure type in plain language without needing
 * to understand the plugin's internals.
 *
 * @param string $class   Exception/Error class name, e.g. "TypeError".
 * @param string $message Exception message.
 * @return string A short, plain-language noun phrase naming the failure,
 *                e.g. "a file it needed was missing".
 */
function classify_guarded_hook_error( $class, $message ) {
	if ( false !== stripos( $message, 'failed to open stream' )
		|| false !== stripos( $message, 'failed opening required' )
		|| false !== stripos( $message, 'no such file or directory' ) ) {
		return __( 'a missing file', 'kolofon' );
	}

	if ( false !== stripos( $message, 'call to undefined function' ) ) {
		return __( 'a missing function (outdated plugin)', 'kolofon' );
	}

	if ( false !== stripos( $message, 'call to undefined method' )
		|| false !== stripos( $message, 'call to a member function' ) ) {
		return __( 'a plugin conflict', 'kolofon' );
	}

	if ( false !== stripos( $message, "class \"" ) || false !== stripos( $message, "class '" ) ) {
		return __( 'a missing dependency', 'kolofon' );
	}

	if ( 'TypeError' === $class ) {
		return __( 'a plugin bug', 'kolofon' );
	}

	if ( 'DivisionByZeroError' === $class || 'ArithmeticError' === $class ) {
		return __( 'a calculation error', 'kolofon' );
	}

	if ( false !== stripos( $message, 'allowed memory size' ) ) {
		return __( 'a memory limit', 'kolofon' );
	}

	if ( false !== stripos( $message, 'maximum execution time' ) ) {
		return __( 'a timeout', 'kolofon' );
	}

	return $class;
}

/**
 * Render a small, admin-only notice in the footer when a hook was caught
 * and skipped during this request. Visitors never see this; only someone
 * logged in with manage_options does, and only on the page where it happened.
 *
 * The visible summary is deliberately non-technical — this notice is as
 * likely to be read by a site owner as by a developer. The technical
 * detail (message, file, line) sits behind a native <details> toggle so it
 * costs nothing to include and needs no JavaScript to reveal.
 */
function render_guarded_hook_admin_notice() {
	if ( empty( $GLOBALS['kolofon_guarded_hook_failures'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	foreach ( $GLOBALS['kolofon_guarded_hook_failures'] as $failure ) {
		printf(
			'<div class="kolofon-resilience-notice">' .
			'<p>%1$s</p>' .
			'<details><summary>%2$s</summary>' .
			'<p><code>%3$s</code> &mdash; %4$s</p></details>' .
			'</div>',
			esc_html( plain_language_guarded_hook_summary( $failure ) ),
			esc_html__( 'Technical details (visible only to admins)', 'kolofon' ),
			esc_html( $failure['hook'] ),
			esc_html( $failure['message'] . ' in ' . $failure['file'] . ':' . $failure['line'] )
		);
	}
}

/**
 * Turn a failure into a single plain-language sentence combining where it
 * happened and what actually went wrong — not a generic "something broke".
 *
 * @param array $failure Failure record from log_guarded_hook_failure():
 *                        'hook', 'class', 'message', 'file', 'line'.
 * @return string Plain-language summary, one sentence.
 */
function plain_language_guarded_hook_summary( array $failure ) {
	$where = array(
		'wp_head'   => __( 'A header script', 'kolofon' ),
		'wp_footer' => __( 'A footer script', 'kolofon' ),
	);

	$what_failed = isset( $where[ $failure['hook'] ] ) ? $where[ $failure['hook'] ] : __( 'A plugin', 'kolofon' );
	$what_broke  = classify_guarded_hook_error( $failure['class'], $failure['message'] );

	return sprintf(
		/* translators: 1: what failed, e.g. "A footer script". 2: what went wrong, e.g. "a missing file". */
		__( '%1$s failed to load: %2$s. Not a theme issue — rest of the page is fine.', 'kolofon' ),
		$what_failed,
		$what_broke
	);
}
