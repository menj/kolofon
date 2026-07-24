<?php
/**
 * Extension points.
 *
 * Until now a child theme could override templates and enqueue styles, but
 * could not change behaviour without editing parent files. This module opens
 * that up, and solves the coupling that made it unsafe to do so naively.
 *
 * The coupling, stated plainly. `opt()` memoises on first call and `sanitize()`
 * wrote only the keys it named literally. So an option added by a filter would
 * be read from a cache built before the filter ran, and then dropped the next
 * time anyone pressed Save. Adding filters without fixing that would have
 * produced silent data loss, which is worse than no extension point at all.
 *
 * Two changes resolve it:
 *
 *   1. Defaults are filtered, and neither `get_defaults()` nor `opt()` retains
 *      a memo until `after_setup_theme` has fired. Before that point they
 *      recompute, so an early read cannot freeze a pre-filter view.
 *   2. Sanitising is driven by a registry of key to callback rather than a
 *      hard-coded function body. Any key present in the filtered defaults is
 *      sanitised and stored, whether the theme declared it or a filter did.
 *
 * @package MENJ\Bio
 * @since   1.2.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', __NAMESPACE__ . '\\migrate_stored_options', 20 );

/**
 * Migrate stored options across breaking schema changes.
 *
 * 2.0.0 reduced the colour system to Ivory, Charcoal, and Auto. Rows written
 * by earlier versions may hold a removed scheme or keys that no longer exist.
 * Runs once per change: a row already in shape is left untouched, so this is
 * a cheap array inspection on every load and a write almost never.
 *
 * Removed in 2.0.0: `ink` and `custom` schemes (both map to the Charcoal
 * default), and the
 * keys `auto_light`, `auto_dark`, `custom_bg`, `custom_text`, `custom_accent`,
 * `custom_muted`, `custom_rule`.
 */
function migrate_stored_options() {
	$stored = get_option( MENJ_BIO_OPTION_KEY, array() );

	if ( ! is_array( $stored ) || empty( $stored ) ) {
		return;
	}

	$dirty   = false;
	$removed = array( 'auto_light', 'auto_dark', 'custom_bg', 'custom_text', 'custom_accent', 'custom_muted', 'custom_rule' );

	foreach ( $removed as $key ) {
		if ( array_key_exists( $key, $stored ) ) {
			unset( $stored[ $key ] );
			$dirty = true;
		}
	}

	if ( isset( $stored['colour_scheme'] ) && in_array( $stored['colour_scheme'], array( 'ink', 'custom' ), true ) ) {
		$stored['colour_scheme'] = 'charcoal';
		$dirty                   = true;
	}

	// Font stack renames and removals across 2.1 to 2.5.
	//
	// A stored `serif` migrates to `editorial` (its rename in 2.2.0). Idempotent,
	// since `serif` is no longer valid.
	//
	// The `typewriter` reset is trickier. In 2.2 and 2.3 the slug meant Charter
	// body over Courier headings; 2.5.0 revived it for Monospace body over
	// Special Elite headings. Silently keeping the old value would give someone
	// a different visual than they picked. So old typewriter users are reset
	// once to `editorial`, the closest survivor of their intent, and can opt in
	// to the new typewriter deliberately. The one-shot flag is essential:
	// without it, anyone selecting the new typewriter would have their choice
	// rewritten on the next request.
	if ( isset( $stored['font_stack'] ) ) {
		if ( 'serif' === $stored['font_stack'] ) {
			$stored['font_stack'] = 'editorial';
			$dirty                = true;
		} elseif ( in_array( $stored['font_stack'], array( 'system', 'humanist', 'hybrid', 'grotesque' ), true ) ) {
			$stored['font_stack'] = 'editorial';
			$dirty                = true;
		} elseif ( 'typewriter' === $stored['font_stack'] && ! get_option( 'menj_bio_typewriter_reset' ) ) {
			$stored['font_stack'] = 'editorial';
			$dirty                = true;
			update_option( 'menj_bio_typewriter_reset', 1, false );
		}
	}

	// Set the flag on any migration pass so the typewriter reset never fires
	// on a fresh 2.5 install that has never had the old typewriter.
	if ( ! get_option( 'menj_bio_typewriter_reset' ) ) {
		update_option( 'menj_bio_typewriter_reset', 1, false );
	}

	if ( $dirty ) {
		update_option( MENJ_BIO_OPTION_KEY, $stored );
	}
}

/**
 * Whether it is safe to memoise filtered values.
 *
 * Child themes and plugins register on `after_setup_theme` at the latest, so
 * anything read before that has not seen every filter and must not be cached.
 *
 * @return bool
 */
function filters_settled() {
	return (bool) did_action( 'after_setup_theme' );
}

/**
 * Sanitiser callbacks, keyed by option name.
 *
 * Every key in the filtered defaults must resolve to a callback here, or it
 * falls back to `sanitize_text_field`. A filter adding an option should add its
 * callback too, otherwise its value is stored as plain text.
 *
 * @return array<string, callable>
 */
function get_option_sanitizers() {
	$defaults = get_raw_defaults();

	$bool = function ( $value ) {
		return empty( $value ) ? 0 : 1;
	};

	$int_between = function ( $min, $max, $fallback ) {
		return function ( $value ) use ( $min, $max, $fallback ) {
			$value = ( null === $value || '' === $value ) ? $fallback : $value;
			return max( $min, min( $max, intval( $value ) ) );
		};
	};

	$enum_from = function ( callable $source, $fallback ) {
		return function ( $value ) use ( $source, $fallback ) {
			$allowed = call_user_func( $source );
			return array_key_exists( (string) $value, $allowed ) ? (string) $value : $fallback;
		};
	};

	$map = array(
		// Sections.
		'section_slugs'          => function ( $value ) {
			return implode( ', ', parse_section_slugs( $value ) );
		},
		'section_all_label'      => 'sanitize_text_field',
		'enforce_single_section' => $bool,
		'scope_adjacent_posts'   => $bool,
		'show_section_chooser'   => $bool,
		'show_list_tags'         => $bool,
		'list_tag_limit'         => $int_between( 1, 10, $defaults['list_tag_limit'] ),

		// Identity.
		'hero_eyebrow'           => 'sanitize_text_field',
		'hero_heading'           => function ( $value ) {
			return wp_kses( $value, allowed_heading_html() );
		},
		'hero_body'              => 'wp_kses_post',
		'hero_portrait'          => 'esc_url_raw',
		'footer_text'            => 'wp_kses_post',

		// Privacy and hardening.
		'email_obfuscation'      => $enum_from( __NAMESPACE__ . '\\get_email_modes', $defaults['email_obfuscation'] ),
		'disable_file_edit'      => $bool,
		'emit_meta_tags'         => $bool,
		'planned_badge_label'    => 'sanitize_text_field',
		'planned_notice_text'    => 'sanitize_text_field',

		// Appearance.
		'colour_scheme'          => $enum_from( __NAMESPACE__ . '\\get_colour_presets', $defaults['colour_scheme'] ),
		'font_stack'             => $enum_from( __NAMESPACE__ . '\\get_font_stacks', $defaults['font_stack'] ),
		'hero_heading_size'      => $int_between( 28, 96, $defaults['hero_heading_size'] ),
		'hero_body_size'         => $int_between( 14, 28, $defaults['hero_body_size'] ),

		// Layout.
		'container_width'        => $int_between( 600, 1600, $defaults['container_width'] ),
		'portrait_size'          => $int_between( 120, 400, $defaults['portrait_size'] ),
		'portrait_style'         => $enum_from( __NAMESPACE__ . '\\get_portrait_styles', $defaults['portrait_style'] ),
		'chrome_layout'          => function ( $value ) use ( $defaults ) {
			return in_array( (string) $value, array( 'topbar', 'sidebar' ), true ) ? (string) $value : $defaults['chrome_layout'];
		},
		'keyboard_nav'           => $bool,
		'sidebar_social_heading' => 'sanitize_text_field',
		'list_style'             => function ( $value ) use ( $defaults ) {
			return in_array( (string) $value, array( 'stacked', 'columns', 'index' ), true ) ? (string) $value : $defaults['list_style'];
		},
		'hover_preview'          => $bool,
		'preview_size'           => $int_between( 100, 240, $defaults['preview_size'] ),
		'show_recent'            => $bool,
		'recent_count'           => $int_between( 1, 20, $defaults['recent_count'] ),
	);

	// Social URLs share one rule, and the platform list is itself filterable,
	// so adding a platform needs no change here.
	foreach ( array_keys( get_social_platforms() ) as $slug ) {
		$map[ social_key( $slug ) ] = __NAMESPACE__ . '\\normalise_social_url';
	}

	/**
	 * Filter the sanitiser registry.
	 *
	 * Add an entry when adding an option through `menj_bio_defaults`, otherwise
	 * the value is stored with `sanitize_text_field`.
	 *
	 * @param array<string, callable> $map Key to callback.
	 */
	return apply_filters( 'menj_bio_option_sanitizers', $map );
}

/**
 * Sanitise the whole option array.
 *
 * Iterates the filtered defaults rather than a literal list, so an option
 * introduced by a filter survives a save instead of being silently discarded.
 *
 * @param mixed $input Raw input from the settings form or an import.
 * @return array
 */
function sanitize_options( $input ) {
	$input      = is_array( $input ) ? $input : array();
	$defaults   = get_defaults();
	$sanitizers = get_option_sanitizers();
	$clean      = array();

	foreach ( $defaults as $key => $fallback ) {
		$callback = isset( $sanitizers[ $key ] ) ? $sanitizers[ $key ] : 'sanitize_text_field';
		$raw      = array_key_exists( $key, $input ) ? $input[ $key ] : null;

		// Checkbox-style values are absent from a POST when unticked, which is
		// meaningful. Everything else falls back to its default when absent.
		$is_bool = ( 0 === $fallback || 1 === $fallback );

		if ( null === $raw && ! $is_bool ) {
			$raw = $fallback;
		}

		$clean[ $key ] = is_callable( $callback ) ? call_user_func( $callback, $raw ) : sanitize_text_field( (string) $raw );
	}

	/**
	 * Filter the sanitised option array immediately before it is stored.
	 *
	 * @param array $clean Sanitised values.
	 * @param array $input Raw input.
	 */
	return apply_filters( 'menj_bio_sanitized_options', $clean, $input );
}
