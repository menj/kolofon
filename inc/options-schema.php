<?php
/**
 * Options schema.
 *
 * Tabs and fields are declared as data rather than as a sequence of
 * registration calls. Two things follow from that.
 *
 * A tab added through `kolofon_option_tabs` gets its settings section created
 * automatically, so extensions no longer have to know that a tab and a section
 * are separate concepts in the Settings API.
 *
 * Fields added through `kolofon_option_fields` are registered by the same loop
 * as the theme's own, so there is one code path rather than two, and no way for
 * an extension's field to be handled differently from a built-in one.
 *
 * Where a field's help text depends on runtime state, the declaration holds a
 * callable rather than a string, resolved at registration time.
 *
 * @package Kolofon
 * @since   1.2.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Tabs, in display order.
 *
 * The single source for both the settings sections and the rendered tab strip,
 * so the two cannot drift.
 *
 * @return array<string, string> Slug to label.
 */
function get_option_tabs() {
	$tabs = array(
		'identity'  => __( 'Identity', 'kolofon' ),
		'layout'    => __( 'Layout', 'kolofon' ),
		'social'    => __( 'Social', 'kolofon' ),
		'fediverse' => __( 'Fediverse', 'kolofon' ),
		'advanced'  => __( 'Advanced', 'kolofon' ),
		'docs'      => __( 'Documentation', 'kolofon' ),
	);

	/**
	 * Filter the options page tabs.
	 *
	 * A settings section is created for each tab automatically.
	 *
	 * @param array<string, string> $tabs Slug to label, in display order.
	 */
	return apply_filters( 'kolofon_option_tabs', $tabs );
}

/**
 * Tabs that carry settings fields.
 *
 * The Documentation tab renders read-only content outside the settings form,
 * so it has no section and no fields.
 *
 * @return array<string, string>
 */
function get_form_tabs() {
	$tabs = get_option_tabs();

	// The Documentation tab renders outside the settings form, so it carries
	// no section and no fields. (`system` was folded into Advanced in 6.3.0
	// and is no longer a tab of its own; the report renders inside that
	// panel.)
	unset( $tabs['docs'] );

	return $tabs;
}

/**
 * Help text for the file editor toggle, which reflects wp-config.php state.
 *
 * @return string
 */
function file_edit_help() {
	$help = __( 'Hides Appearance > Theme File Editor and Plugins > Plugin File Editor, and blocks direct access. Editing live PHP from a browser turns a hijacked admin session into arbitrary code execution. Setting DISALLOW_FILE_EDIT in wp-config.php is stronger still, and takes precedence over this.', 'kolofon' );

	if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
		$help .= ' ' . __( 'Note: DISALLOW_FILE_EDIT is already set in wp-config.php on this install, so the editors stay off regardless of this setting.', 'kolofon' );
	}

	return $help;
}

/**
 * Help text for the metadata toggle, which reflects detected SEO plugins.
 *
 * @return string
 */
function meta_tags_help() {
	$help     = __( 'Emits Open Graph, Twitter card, canonical, and JSON-LD Person and WebSite markup. Without these a shared link renders as whatever the receiving platform guesses.', 'kolofon' );
	$detected = detected_seo_plugin();

	if ( '' !== $detected ) {
		$help .= ' ' . sprintf(
			/* translators: %s: name of the detected SEO plugin */
			__( 'Currently standing down: %s is active and owns this output. Emitting a second set would leave consumers to disagree about which wins.', 'kolofon' ),
			$detected
		);
	}

	return $help;
}

/**
 * Field declarations.
 *
 * Each entry: key => array( label, tab, type, args ). `args` may carry a
 * `help` that is either a string or a callable resolved at registration.
 *
 * @return array<string, array>
 */
function get_option_fields() {
	$fields = array();

	// Identity.
	$fields['hero_eyebrow']    = array(
		'label' => __( 'Hero eyebrow', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'text',
		'args'  => array( 'help' => __( 'A short line above the heading, set in small letterspaced capitals. Leave empty to hide it.', 'kolofon' ) ),
	);
	$fields['hero_heading']    = array(
		'label' => __( 'Hero heading', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'heading_text',
		'args'  => array( 'help' => __( 'Wrap a phrase in <mark> to give it the accent colour. Only mark, em, strong, and br are allowed.', 'kolofon' ) ),
	);
	$fields['citation_author'] = array(
		'label' => __( 'Citation name', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'text',
		'args'  => array(
			'help' => __( 'Your name inverted for citations, as MLA requires: Last, First (for example, "Lovelace, Ada"). Used in the citation printed at the foot of a post. If left empty, the hero heading is used as written.', 'kolofon' ),
		),
	);
	$fields['hero_body']       = array(
		'label' => __( 'Hero body copy', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'textarea',
		'args'  => array(
			'help' => __( 'The intro paragraph. Basic HTML allowed, including links: <a href="...">like this</a>. Links render bold with an accent underline. Blank lines become paragraphs.', 'kolofon' ),
			'rows' => 5,
		),
	);
	$fields['hero_portrait']   = array(
		'label' => __( 'Hero portrait', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'image',
		'args'  => array( 'help' => __( 'Square image works best. Rendered as a circle.', 'kolofon' ) ),
	);
	$fields['footer_text']     = array(
		'label' => __( 'Footer text', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'textarea',
		'args'  => array( 'help' => __( 'Basic HTML is allowed.', 'kolofon' ) ),
	);
	$fields['same_as_urls']    = array(
		'label' => __( 'Additional profile pages (sameAs)', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'textarea',
		'args'  => array(
			'help' => __( 'One URL per line. For pages about you that aren\'t social media — a Gravatar profile, a Wikipedia page, a Crunchbase or IMDb entry, a personal wiki. These feed the sameAs structured data alongside the Social tab URLs, but never render as an icon or a link anywhere on the site: this field exists only to tell search engines and AI crawlers these other pages describe the same person.', 'kolofon' ),
			'rows' => 4,
		),
	);

	// Sections.
	$fields['section_slugs']          = array(
		'label' => __( 'Section categories', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'section_slugs',
		'args'  => array( 'help' => __( 'Category slugs in the order they should appear, separated by commas or new lines. Each must be an existing category. Sections are mutually exclusive: a post belongs to exactly one. Use tags for topics that cut across sections.', 'kolofon' ) ),
	);
	$fields['show_section_chooser']   = array(
		'label' => __( 'Show section chooser', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Displays the row of section links on the home page and on section archives.', 'kolofon' ) ),
	);
	$fields['section_all_label']      = array(
		'label' => __( 'Label for the all-sections link', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'text',
	);
	$fields['enforce_single_section'] = array(
		'label' => __( 'One category per post', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Reduces every post to a single category on save, and makes the block editor category panel behave as a single-choice list. Applies to posts created through REST, WP-CLI, and imports too.', 'kolofon' ) ),
	);
	$fields['scope_adjacent_posts']   = array(
		'label' => __( 'Keep previous and next within a section', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Without this, the links at the foot of a post run chronologically across every section, so a reader following one topic is dropped into another.', 'kolofon' ) ),
	);

	// Social. The platform list is filterable, so this loop picks up additions.
	$fields['email_obfuscation'] = array(
		'label' => __( 'Email protection', 'kolofon' ),
		'tab'   => 'social',
		'type'  => 'select',
		'args'  => array(
			'choices' => get_email_modes(),
			'help'    => __( 'Keeps plain mailto: strings out of the served HTML so harvesters find nothing to scrape. Applies to the icon below, to mailto: links in post content, and to the [kolofon_email] shortcode.', 'kolofon' ),
		),
	);

	foreach ( get_social_platforms() as $slug => $meta ) {
		$is_email = ( 'email' === $slug );

		$fields[ social_key( $slug ) ] = array(
			'label' => $meta['label'],
			'tab'   => 'social',
			'type'  => 'url',
			'args'  => array(
				// Email accepts a bare address, which fails native url validation.
				'input_type'  => $is_email ? 'text' : 'url',
				'placeholder' => $is_email ? 'you@example.com' : 'https://',
				'help'        => $is_email ? __( 'Bare email works; mailto: is added automatically.', 'kolofon' ) : '',
			),
		);
	}

	// Appearance.
	$fields['colour_scheme'] = array(
		'label' => __( 'Colour scheme', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'radio_presets',
	);

	$fields['font_stack']        = array(
		'label' => __( 'Font stack', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'font_stack',
	);
	$fields['hero_heading_size'] = array(
		'label' => __( 'Lede heading size (px)', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'slider',
		'args'  => array(
			'min'  => 28,
			'max'  => 96,
			'step' => 2,
			'unit' => 'px',
			'help' => __( 'Maximum size. The heading scales down fluidly on narrow screens. Drag to preview, or type an exact value.', 'kolofon' ),
		),
	);
	$fields['hero_body_size']    = array(
		'label' => __( 'Lede body size (px)', 'kolofon' ),
		'tab'   => 'identity',
		'type'  => 'slider',
		'args'  => array(
			'min'  => 14,
			'max'  => 28,
			'step' => 1,
			'unit' => 'px',
			'help' => __( 'Size of the intro paragraph under the heading. Drag to preview, or type an exact value.', 'kolofon' ),
		),
	);

	// Layout.
	$fields['chrome_layout']          = array(
		'label' => __( 'Chrome layout', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'select',
		'args'  => array(
			'choices' => array(
				'topbar'  => __( 'Top bar: full-width header above the content', 'kolofon' ),
				'sidebar' => __( 'Sidebar: floating card in a left rail', 'kolofon' ),
			),
			'help'    => __( 'The sidebar card holds the wordmark, a numbered navigation, and the stay-in-touch links, leaving the content column free. Collapses to the top bar on narrow screens.', 'kolofon' ),
		),
	);
	$fields['keyboard_nav']           = array(
		'label' => __( 'Keyboard shortcuts', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Digits 0 to 9 follow the correspondingly numbered navigation link. Sidebar layout only. Never fires while typing in a field. Turn off if it conflicts with assistive tooling.', 'kolofon' ) ),
	);
	$fields['sidebar_social_heading'] = array(
		'label' => __( 'Stay-in-touch heading', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'text',
	);
	$fields['container_width']        = array(
		'label' => __( 'Content width (px)', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 600,
			'max'  => 1600,
			'step' => 20,
			'help' => __( 'Widescreen sites sit well between 1040 and 1280.', 'kolofon' ),
		),
	);
	$fields['portrait_size']          = array(
		'label' => __( 'Hero portrait size (px)', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 120,
			'max'  => 400,
			'step' => 10,
			'help' => __( 'Diameter or edge length of the hero portrait.', 'kolofon' ),
		),
	);
	$fields['portrait_style']         = array(
		'label' => __( 'Hero portrait style', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'select',
		'args'  => array(
			'choices' => get_portrait_styles(),
			'help'    => __( 'Floating suits a cut-out PNG with a transparent background.', 'kolofon' ),
		),
	);
	$fields['fediverse_identity']     = array(
		'label' => __( 'Your Fediverse address', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'fediverse_identity',
	);
	$fields['microblog_enabled']      = array(
		'label' => __( 'Microblog', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'checkbox',
		'args'  => array(
			'help' => __( 'Adds a Statuses post type for short, title-less posts, with a composer in the toolbar and a timeline shortcode. Merged into the theme, so no separate microblog plugin is needed.', 'kolofon' ),
		),
	);
	$fields['fediverse_enabled']      = array(
		'label' => __( 'Federate statuses', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'checkbox',
		'args'  => array(
			'help' => 'Kolofon\\fediverse_help',
		),
	);
	$fields['fediverse_profile']      = array(
		'label' => __( 'Your Fediverse handle', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'select',
		'args'  => array(
			'choices' => array(
				'author'     => __( 'One handle from your username, e.g. @you@example.com', 'kolofon' ),
				'blog'       => __( 'One handle for the site, e.g. @example.com@example.com', 'kolofon' ),
				'actor_blog' => __( 'Both a site handle and a personal one', 'kolofon' ),
			),
			'help'    => 'Kolofon\\fediverse_profile_help',
		),
	);
	$fields['microblog_on_home']      = array(
		'label' => __( 'Show statuses on the blog', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'checkbox',
		'args'  => array(
			'help' => __( 'Mixes statuses into the main post list and the RSS feed alongside articles. Off by default, so statuses stay on their own archive at /statuses/ and do not crowd the front page.', 'kolofon' ),
		),
	);
	$fields['microblog_noindex']      = array(
		'label' => __( 'Hide statuses from search engines', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'checkbox',
		'args'  => array(
			'help' => __( 'Adds a noindex header to the status archive. Statuses still federate and are still readable; they simply are not indexed.', 'kolofon' ),
		),
	);
	$fields['microblog_char_limit']   = array(
		'label' => __( 'Status length limit', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'number',
		'args'  => array(
			'min'  => 50,
			'max'  => 5000,
			'step' => 10,
			'help' => __( 'Characters allowed in the composer. Mastodon shows 500 by default, so anything longer may be truncated by some servers.', 'kolofon' ),
		),
	);
	$fields['microblog_page_size']    = array(
		'label' => __( 'Statuses per timeline page', 'kolofon' ),
		'tab'   => 'fediverse',
		'type'  => 'number',
		'args'  => array(
			'min'  => 1,
			'max'  => 100,
			'step' => 1,
			'help' => __( 'How many statuses the [kolofon_microblog] shortcode shows at a time.', 'kolofon' ),
		),
	);
	$fields['list_title_size']        = array(
		'label' => __( 'Post list title size (px)', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 14,
			'max'  => 30,
			'step' => 1,
			'help' => __( 'Size of the post titles in list views, including the main page. Stored in px, applied in rem so it still honours the reader\'s browser font size.', 'kolofon' ),
		),
	);
	$fields['list_style']             = array(
		'label' => __( 'Post list style', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'select',
		'args'  => array(
			'choices' => array(
				'stacked' => __( 'Stacked: title with date at the end', 'kolofon' ),
				'columns' => __( 'Columns: date, section, title in aligned columns', 'kolofon' ),
				'index'   => __( 'Index: title with year, excerpt underneath, indexical', 'kolofon' ),
			),
			'help'    => __( 'Index reads as a table of contents: a hairline-ruled row list where the title carries the eye and the excerpt sits below. Suits sites organised by title rather than by date.', 'kolofon' ),
		),
	);
	$fields['hover_preview']          = array(
		'label' => __( 'Post hover previews', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Reveals a post featured image beside the list on hover or keyboard focus. Posts without a featured image show nothing. Pointer devices only.', 'kolofon' ) ),
	);
	$fields['preview_size']           = array(
		'label' => __( 'Hover preview width (px)', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 100,
			'max'  => 240,
			'step' => 10,
			'help' => __( 'Width of the small floating preview that appears below-right of a post row on hover. Kept small so it reads as a peek rather than a card.', 'kolofon' ),
		),
	);
	$fields['show_recent']            = array(
		'label' => __( 'Show recent posts on home', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
	);
	$fields['recent_count']           = array(
		'label' => __( 'Recent posts count', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 1,
			'max'  => 20,
			'step' => 1,
		),
	);
	$fields['blog_per_page']          = array(
		'label' => __( 'Blog page posts per page', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'number',
		'args'  => array(
			'min'  => 5,
			'max'  => 100,
			'step' => 5,
			'help' => __( 'How many posts appear on each page of the Blog Index template before pagination kicks in. A year of posts can split across two pages once it runs past this count.', 'kolofon' ),
		),
	);
	$fields['post_sharing_enabled']    = array(
		'label' => __( 'Show sharing icons on posts', 'kolofon' ),
		'tab'   => 'layout',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __( 'Adds a row of share links at the foot of each post: X, Facebook, LinkedIn, Bluesky, Reddit, Telegram, WhatsApp, email, and a copy-link button. Only platforms with both an icon and a working share link render, so a target added through the kolofon_share_targets filter needs a matching icon in the registry.', 'kolofon' ) ),
	);

	// Advanced. Help text here depends on runtime state, so it is deferred.
	$fields['csp_mode']            = array(
		'label' => __( 'Content Security Policy', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'select',
		'args'  => array(
			'choices' => get_csp_modes(),
			'help'    => __( 'Off by default. A policy strict enough to matter blocks inline scripts, which WordPress core and most plugins emit, so enforcing one without preparation can blank the front end. Start on Report only, watch the browser console across the site, and switch to Enforce once nothing legitimate is being flagged. Never applied to admin screens. Adjust the directives with the kolofon_csp_directives filter.', 'kolofon' ),
		),
	);
	$fields['disable_file_edit']   = array(
		'label' => __( 'Disable file editors', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __NAMESPACE__ . '\\file_edit_help' ),
	);
	$fields['emit_meta_tags']      = array(
		'label' => __( 'Emit meta tags and schema', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'checkbox',
		'args'  => array( 'help' => __NAMESPACE__ . '\\meta_tags_help' ),
	);
	$fields['planned_badge_label'] = array(
		'label' => __( 'Planned page badge', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'text',
		'args'  => array( 'help' => __( 'Shown in the navigation next to pages marked as planned, and on the page itself. Mark a page as planned in its editor sidebar.', 'kolofon' ) ),
	);
	$fields['planned_notice_text'] = array(
		'label' => __( 'Planned page notice', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'text',
		'args'  => array( 'help' => __( 'The sentence shown in place of content on a planned page.', 'kolofon' ) ),
	);
	$fields['export_action']       = array(
		'label' => __( 'Export settings', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'export_button',
		'args'  => array( 'help' => __( 'Downloads every theme setting as a JSON file. Useful as a backup and for moving a configuration to another install.', 'kolofon' ) ),
	);
	$fields['import_action']       = array(
		'label' => __( 'Import settings', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'import_button',
		'args'  => array( 'help' => __( 'Replaces all current settings. Unknown keys are discarded and every value is re-validated, so an edited file cannot introduce anything the theme does not recognise.', 'kolofon' ) ),
	);
	$fields['reset_action']        = array(
		'label' => __( 'Reset to defaults', 'kolofon' ),
		'tab'   => 'advanced',
		'type'  => 'reset_button',
	);

	/**
	 * Filter the options page fields.
	 *
	 * Each entry: key => array( label, tab, type, args ). Registered by the
	 * same loop as the theme's own fields, so an added field behaves
	 * identically to a built-in one. Pair with `kolofon_defaults` and
	 * `kolofon_option_sanitizers` so the value has a default and is stored
	 * correctly.
	 *
	 * @param array<string, array> $fields Field declarations.
	 */
	return apply_filters( 'kolofon_option_fields', $fields );
}
