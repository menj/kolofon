<?php
/**
 * In-admin markdown documentation rendering.
 *
 * Reads *.md files from the /docs/ directory and renders them on the
 * Documentation tab of the Theme Options page using Parsedown.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a configured Parsedown instance, or null when unavailable.
 *
 * Cached in a static so the class-exists check and the setter guards run once
 * per request. The guards matter: another plugin may have loaded an older
 * Parsedown before us, and calling a setter that version lacks would fatal.
 *
 * @return \Parsedown|null
 */
function docs_parser() {
	static $parser = false; // false means unresolved, null means unavailable.

	if ( false !== $parser ) {
		return $parser;
	}

	if ( ! class_exists( 'Parsedown' ) ) {
		$bundled = MENJ_BIO_DIR . 'vendor/parsedown/Parsedown.php';
		if ( is_readable( $bundled ) ) {
			require_once $bundled;
		}
	}

	if ( ! class_exists( 'Parsedown' ) ) {
		$parser = null;
		return $parser;
	}

	try {
		$instance = new \Parsedown();

		// Safe mode neutralises dangerous URL schemes. Markup escaping means
		// raw HTML in a source file is shown rather than executed. Both are
		// guarded because an older bundled copy may not have them.
		if ( method_exists( $instance, 'setSafeMode' ) ) {
			$instance->setSafeMode( true );
		}
		if ( method_exists( $instance, 'setMarkupEscaped' ) ) {
			$instance->setMarkupEscaped( true );
		}
		if ( method_exists( $instance, 'setBreaksEnabled' ) ) {
			$instance->setBreaksEnabled( false );
		}

		$parser = $instance;
	} catch ( \Exception $e ) {
		$parser = null;
	} catch ( \Error $e ) {
		$parser = null;
	}

	return $parser;
}

/**
 * Render a single markdown doc to sanitised HTML.
 *
 * The result is cached in a transient keyed on the file's modification time,
 * the theme version, and whether a parser was available. Editing a source file
 * therefore invalidates its own cache, so the tab always reflects the file on
 * disk without needing a manual flush.
 *
 * @param string $slug Doc slug, lowercase filename without extension.
 * @return string Sanitised HTML, or an empty string on failure.
 */
function render_doc( $slug ) {
	$docs = list_docs();
	if ( ! isset( $docs[ $slug ] ) ) {
		return '';
	}

	$path = $docs[ $slug ]['path'];
	if ( ! is_readable( $path ) ) {
		return '';
	}

	$parser = docs_parser();
	$engine = $parser ? 'parsedown' : 'none';
	$mtime  = filemtime( $path );
	$key    = 'menj_bio_doc_' . md5( $slug . '|' . $mtime . '|' . MENJ_BIO_VERSION . '|' . $engine );

	$cached = get_transient( $key );
	if ( is_string( $cached ) ) {
		return $cached;
	}

	$markdown = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading a bundled theme file, not a remote URL.
	if ( false === $markdown ) {
		return '';
	}

	if ( ! $parser ) {
		// Without a parser, show the source rather than nothing. Escaped and
		// preformatted, so it stays readable and cannot inject markup.
		$html = '<p><em>' . esc_html__( 'Markdown could not be rendered, so the source is shown below.', 'menj-bio' ) . '</em></p>'
			. '<pre>' . esc_html( $markdown ) . '</pre>';
	} else {
		$html = wp_kses_post( $parser->text( $markdown ) );
	}

	set_transient( $key, $html, DAY_IN_SECONDS );

	return $html;
}

/**
 * Canonical documentation set: slug => display label, in sub-nav order.
 *
 * Files are expected at docs/{slug}.md in lowercase. Any additional .md file
 * found in the directory is appended after these, alphabetically, with an
 * auto-generated label.
 *
 * @return array<string, string>
 */
function get_doc_manifest() {
	return array(
		'readme'    => __( 'Readme', 'menj-bio' ),
		'ssot'      => __( 'SSOT', 'menj-bio' ),
		'upgrading' => __( 'Upgrading', 'menj-bio' ),
		'changelog' => __( 'Changelog', 'menj-bio' ),
	);
}

/**
 * Derive a display label for a doc file outside the manifest.
 *
 * Converts hyphens and underscores to spaces and title-cases the result.
 *
 * @param string $slug Doc slug.
 * @return string
 */
function derive_doc_label( $slug ) {
	return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
}

/**
 * List available doc slugs and their labels for the sub-nav.
 *
 * Manifest documents come first in manifest order, then any extra .md files
 * alphabetically. Only files that exist are listed.
 *
 * @return array<string, array{label:string, path:string}>
 */
function list_docs() {
	$dir      = MENJ_BIO_DIR . 'docs/';
	$out      = array();
	$manifest = get_doc_manifest();

	// Manifest documents first, in declared order.
	foreach ( $manifest as $slug => $label ) {
		$path = $dir . $slug . '.md';
		if ( is_readable( $path ) ) {
			$out[ $slug ] = array(
				'label' => $label,
				'path'  => $path,
			);
		}
	}

	// Any other .md files, alphabetically.
	$files = glob( $dir . '*.md' );
	if ( is_array( $files ) ) {
		sort( $files, SORT_NATURAL | SORT_FLAG_CASE );

		foreach ( $files as $path ) {
			$slug = strtolower( basename( $path, '.md' ) );
			if ( isset( $out[ $slug ] ) ) {
				continue;
			}
			$out[ $slug ] = array(
				'label' => derive_doc_label( $slug ),
				'path'  => $path,
			);
		}
	}

	return $out;
}

/**
 * Render the Documentation panel (sub-nav + parsed markdown).
 * Called from options.php outside the settings form so no submit button appears.
 */
function render_docs_panel() {
	$docs = list_docs();
	if ( empty( $docs ) ) {
		echo '<p>' . esc_html__( 'No documentation files found in the docs/ directory.', 'menj-bio' ) . '</p>';
		return;
	}

	$requested = isset( $_GET['doc'] ) ? sanitize_key( wp_unslash( $_GET['doc'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation.
	$active    = isset( $docs[ $requested ] ) ? $requested : array_key_first( $docs );

	$base_url = admin_url( 'themes.php?page=menj-bio-options' );

	echo '<nav class="menj-bio-docs-nav" aria-label="' . esc_attr__( 'Documents', 'menj-bio' ) . '">';
	foreach ( $docs as $slug => $meta ) {
		$url    = add_query_arg( 'doc', $slug, $base_url ) . '#tab-docs';
		$class  = 'menj-bio-doc-link' . ( $slug === $active ? ' is-current' : '' );
		printf(
			'<a href="%1$s" class="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr( $class ),
			esc_html( $meta['label'] )
		);
	}
	echo '</nav>';

	echo '<article class="menj-bio-doc-body">';
	echo render_doc( $active ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already run through wp_kses_post.
	echo '</article>';

	// Repo + version strip.
	printf(
		'<p class="menj-bio-doc-meta">%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">%2$s</a> &middot; v%3$s</p>',
		esc_html__( 'Source:', 'menj-bio' ),
		'https://github.com/menj/menj-bio',
		esc_html( MENJ_BIO_VERSION )
	);
}
