<?php
/**
 * Default option values and colour scheme presets.
 *
 * Single source of truth. Both the Options page (for placeholder / reset)
 * and the dynamic CSS module read from here.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * The theme's own defaults, before any filter.
 *
 * @return array
 */
function get_raw_defaults() {
	return array(
		// Identity tab.
		'hero_heading'           => 'Mohd Elfie Nieshaem Juferi',
		'citation_author'        => 'Juferi, Mohd Elfie Nieshaem',
		'hero_eyebrow'           => '',
		'hero_body'              => 'I am a writer, apologist, and developer working on scholarly resources for Islamic apologetics and building tools that keep the web open and honest. Over two decades I have built WordPress themes and plugins, custom link directories, and pixel-art games in equal measure.',
		'hero_portrait'          => '',
		'footer_text'            => sprintf( '&copy; %s Mohd Elfie Nieshaem Juferi. All rights reserved.', gmdate( 'Y' ) ),

		// Social tab (one URL per platform, empty means "hide").
		'social_mastodon'        => '',
		'social_x'               => '',
		'social_linkedin'        => '',
		'social_academia'        => '',
		'social_orcid'           => '',
		'social_github'          => 'https://github.com/menj',
		'social_youtube'         => '',
		'social_instagram'       => '',
		'social_facebook'        => '',
		'social_threads'         => '',
		'social_pinterest'       => '',
		'social_goodreads'       => '',
		'social_librarything'    => '',
		'social_gamingtribe'     => '',
		'social_email'           => '',
		'social_rss'             => '',

		// Now tab (RSS feed URLs for the aggregator page).

		// Privacy and hardening.
		'email_obfuscation'      => 'js',
		'disable_file_edit'      => 1,
		'csp_mode'               => 'off',
		'emit_meta_tags'         => 1,
		'planned_badge_label'    => 'Soon',
		'planned_notice_text'    => 'This page is planned and not yet written.',

		// Appearance tab.
		'colour_scheme'          => 'charcoal',
		'font_stack'             => 'editorial',
		'hero_heading_size'      => 56,
		'hero_body_size'         => 18,

		// Sections tab.
		'section_slugs'          => '',
		'section_all_label'      => 'All',
		'enforce_single_section' => 1,
		'scope_adjacent_posts'   => 1,
		'show_section_chooser'   => 1,

		// Layout tab.
		'container_width'        => 1120,
		'portrait_size'          => 220,
		'portrait_style'         => 'floating',
		'chrome_layout'          => 'topbar',
		'keyboard_nav'           => 1,
		'sidebar_social_heading' => 'Stay in touch',
		'list_style'             => 'stacked',
		'list_title_size'        => 20,
		'microblog_enabled'      => 0,
		'fediverse_enabled'      => 0,
		'fediverse_profile'      => 'author',
		'microblog_on_home'      => 0,
		'microblog_noindex'      => 0,
		'microblog_char_limit'   => 500,
		'microblog_page_size'    => 20,
		'hover_preview'          => 1,
		'preview_size'           => 140,
		'show_recent'            => 1,
		'recent_count'           => 5,
	);
}

/**
 * Return the full defaults array, filtered.
 *
 * Not memoised until `after_setup_theme` has fired, so a read that happens
 * before child themes and plugins have registered cannot freeze a pre-filter
 * view into the cache.
 *
 * @return array
 */
function get_defaults() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	/**
	 * Filter the theme's default option values.
	 *
	 * Adding a key here makes it a real option: it gains a value through
	 * `opt()`, and it is sanitised and stored on save. Register a matching
	 * callback on `kolofon_option_sanitizers` unless plain text is correct.
	 *
	 * @param array $defaults Default values.
	 */
	$defaults = apply_filters( 'kolofon_defaults', get_raw_defaults() );

	if ( filters_settled() ) {
		$cache = $defaults;
	}

	return $defaults;
}

/**
 * Return the built-in colour scheme presets.
 *
 * Each preset yields a full set of CSS custom property values.
 *
 * @return array
 */
function get_colour_presets() {
	$presets = array(
		'ivory'    => array(
			'label'  => 'Ivory',
			'bg'     => '#fafaf7',
			'text'   => '#111111',
			'accent' => '#1f3d5a',
			'muted'  => '#6a6a66',
			'rule'   => '#e5e5e0',
		),
		'charcoal' => array(
			'label'  => 'Charcoal (default)',
			'bg'     => '#1a1a1a',
			'text'   => '#e8e6df',
			'accent' => '#d4a574',
			'muted'  => '#9a978e',
			'rule'   => '#2f2f2f',
		),
		'auto'     => array(
			'label' => 'Auto (Ivory by day, Charcoal in the dark)',
		),
	);

	/**
	 * Filter the available colour schemes.
	 *
	 * A preset needs bg, text, accent, muted, and rule. The `custom` entry is
	 * special: it has no colours of its own and reads the custom_* options.
	 *
	 * @param array $presets Slug to preset definition.
	 */
	return apply_filters( 'kolofon_colour_presets', $presets );
}

/**
 * Return available font stack options.
 *
 * @return array
 */
function get_font_stacks() {
	$stacks = array(
		'editorial'     => array(
			'label'   => 'The reader (default)',
			'body'    => 'Charter, Georgia, "Iowan Old Style", "Times New Roman", serif',
			'heading' => 'Charter, Georgia, "Iowan Old Style", "Times New Roman", serif',
		),
		'xcharter'      => array(
			'label'   => 'Charter, but loud',
			'body'    => '"XCharter", Charter, Georgia, "Iowan Old Style", "Times New Roman", serif',
			'heading' => '"XCharter", Charter, Georgia, "Iowan Old Style", "Times New Roman", serif',
			'webfont' => array(
				'family'  => 'XCharter',
				'preload' => 'xcharter/XCharter-Roman.woff2',
				'files'   => array(
					array(
						'src'    => 'xcharter/XCharter-Roman.woff2',
						'weight' => '400',
						'style'  => 'normal',
					),
					array(
						'src'    => 'xcharter/XCharter-Italic.woff2',
						'weight' => '400',
						'style'  => 'italic',
					),
					array(
						'src'    => 'xcharter/XCharter-Bold.woff2',
						'weight' => '700',
						'style'  => 'normal',
					),
					array(
						'src'    => 'xcharter/XCharter-BoldItalic.woff2',
						'weight' => '700',
						'style'  => 'italic',
					),
				),
			),
		),
		'special-elite' => array(
			'label'   => 'Typed',
			'body'    => '"Special Elite", "Courier Prime", Courier, "Courier New", monospace',
			'heading' => '"Special Elite", "Courier Prime", Courier, "Courier New", monospace',
			'webfont' => array(
				'family'  => 'Special Elite',
				'preload' => 'special-elite/SpecialElite-Regular.woff2',
				'files'   => array(
					array(
						'src'    => 'special-elite/SpecialElite-Regular.woff2',
						'weight' => '400',
						'style'  => 'normal',
					),
				),
			),
		),
		'typewriter'    => array(
			// Special Elite headings over system Monospace body: typewritten
			// evidence sits at the head, clean monospace does the reading.
			'label'   => 'Office memo',
			'body'    => 'ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace',
			'heading' => '"Special Elite", "Courier Prime", Courier, "Courier New", monospace',
			'webfont' => array(
				'family'  => 'Special Elite',
				'preload' => 'special-elite/SpecialElite-Regular.woff2',
				'files'   => array(
					array(
						'src'    => 'special-elite/SpecialElite-Regular.woff2',
						'weight' => '400',
						'style'  => 'normal',
					),
				),
			),
		),
		'mono'          => array(
			'label'   => 'Plaintext',
			'body'    => 'ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace',
			'heading' => 'ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace',
		),
	);

	/**
	 * Filter the available font stacks.
	 *
	 * @param array $stacks Slug to stack definition.
	 */
	return apply_filters( 'kolofon_font_stacks', $stacks );
}

/**
 * Return available hero portrait styles.
 *
 * "floating" assumes a cut-out image with a transparent background and
 * applies no mask, so the subject sits directly on the page background.
 *
 * @return array
 */
function get_portrait_styles() {
	$styles = array(
		'floating' => __( 'Floating (transparent cut-out, no mask)', 'kolofon' ),
		'circle'   => __( 'Circle', 'kolofon' ),
		'rounded'  => __( 'Rounded square', 'kolofon' ),
		'square'   => __( 'Square', 'kolofon' ),
	);

	/**
	 * Filter the available hero portrait styles.
	 *
	 * @param array $styles Slug to label.
	 */
	return apply_filters( 'kolofon_portrait_styles', $styles );
}

/**
 * Inline elements permitted inside the hero heading.
 *
 * Deliberately tiny. `mark` is the semantic element for highlighted text, so a
 * screen reader announces the emphasis rather than the theme faking it with a
 * span and a class. Everything else here is inline formatting a heading might
 * legitimately need.
 *
 * @return array
 */
function allowed_heading_html() {
	return array(
		'mark'   => array(),
		'em'     => array(),
		'strong' => array(),
		'br'     => array(),
	);
}

/**
 * Retrieve a single option value with default fallback.
 *
 * @param string $key Option key.
 * @return mixed
 */
function opt( $key ) {
	static $cache = null;

	if ( null === $cache ) {
		$stored   = get_option( KOLOFON_OPTION_KEY, array() );
		$resolved = wp_parse_args( is_array( $stored ) ? $stored : array(), get_defaults() );

		// Hold the memo only once every filter has had a chance to register.
		// A read during theme load would otherwise cache a pre-filter view and
		// make filtered-in options permanently invisible for that request.
		if ( filters_settled() ) {
			$cache = $resolved;
		}

		return array_key_exists( $key, $resolved ) ? $resolved[ $key ] : null;
	}

	return array_key_exists( $key, $cache ) ? $cache[ $key ] : null;
}
