<?php
/**
 * Options schema.
 *
 * Tabs and fields are declared as data rather than as a sequence of
 * registration calls. Two things follow from that.
 *
 * A tab added through `menj_bio_option_tabs` gets its settings section created
 * automatically, so extensions no longer have to know that a tab and a section
 * are separate concepts in the Settings API.
 *
 * Fields added through `menj_bio_option_fields` are registered by the same loop
 * as the theme's own, so there is one code path rather than two, and no way for
 * an extension's field to be handled differently from a built-in one.
 *
 * Where a field's help text depends on runtime state, the declaration holds a
 * callable rather than a string, resolved at registration time.
 *
 * @package MENJ\Bio
 * @since   1.2.0
 */

namespace MENJ\Bio;

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
		'identity'   => __( 'Identity', 'menj-bio' ),
		'sections'   => __( 'Sections', 'menj-bio' ),
		'social'     => __( 'Social', 'menj-bio' ),
		'appearance' => __( 'Appearance', 'menj-bio' ),
		'layout'     => __( 'Layout', 'menj-bio' ),
		'advanced'   => __( 'Advanced', 'menj-bio' ),
		'system'     => __( 'System', 'menj-bio' ),
		'docs'       => __( 'Documentation', 'menj-bio' ),
	);

	/**
	 * Filter the options page tabs.
	 *
	 * A settings section is created for each tab automatically.
	 *
	 * @param array<string, string> $tabs Slug to label, in display order.
	 */
	return apply_filters( 'menj_bio_option_tabs', $tabs );
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

	// These render read-only content outside the settings form, so they have
	// no section and no fields.
	unset( $tabs['docs'], $tabs['system'] );

	return $tabs;
}

/**
 * Help text for the file editor toggle, which reflects wp-config.php state.
 *
 * @return string
 */
function file_edit_help() {
	$help = __( 'Hides Appearance > Theme File Editor and Plugins > Plugin File Editor, and blocks direct access. Editing live PHP from a browser turns a hijacked admin session into arbitrary code execution. Setting DISALLOW_FILE_EDIT in wp-config.php is stronger still, and takes precedence over this.', 'menj-bio' );

	if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
		$help .= ' ' . __( 'Note: DISALLOW_FILE_EDIT is already set in wp-config.php on this install, so the editors stay off regardless of this setting.', 'menj-bio' );
	}

	return $help;
}

/**
 * Help text for the metadata toggle, which reflects detected SEO plugins.
 *
 * @return string
 */
function meta_tags_help() {
	$help     = __( 'Emits Open Graph, Twitter card, canonical, and JSON-LD Person and WebSite markup. Without these a shared link renders as whatever the receiving platform guesses.', 'menj-bio' );
	$detected = detected_seo_plugin();

	if ( '' !== $detected ) {
		$help .= ' ' . sprintf(
			/* translators: %s: name of the detected SEO plugin */
			__( 'Currently standing down: %s is active and owns this output. Emitting a second set would leave consumers to disagree about which wins.', 'menj-bio' ),
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
	$fields['hero_eyebrow']  = array( 'label' => __( 'Hero eyebrow', 'menj-bio' ), 'tab' => 'identity', 'type' => 'text', 'args' => array( 'help' => __( 'A short line above the heading, set in small letterspaced capitals. Leave empty to hide it.', 'menj-bio' ) ) );
	$fields['hero_heading']  = array( 'label' => __( 'Hero heading', 'menj-bio' ), 'tab' => 'identity', 'type' => 'heading_text', 'args' => array( 'help' => __( 'Wrap a phrase in <mark> to give it the accent colour. Only mark, em, strong, and br are allowed.', 'menj-bio' ) ) );
	$fields['hero_body']     = array( 'label' => __( 'Hero body copy', 'menj-bio' ), 'tab' => 'identity', 'type' => 'textarea', 'args' => array( 'help' => __( 'The intro paragraph. Basic HTML allowed.', 'menj-bio' ), 'rows' => 5 ) );
	$fields['hero_portrait'] = array( 'label' => __( 'Hero portrait', 'menj-bio' ), 'tab' => 'identity', 'type' => 'image', 'args' => array( 'help' => __( 'Square image works best. Rendered as a circle.', 'menj-bio' ) ) );
	$fields['footer_text']   = array( 'label' => __( 'Footer text', 'menj-bio' ), 'tab' => 'identity', 'type' => 'textarea', 'args' => array( 'help' => __( 'Basic HTML is allowed.', 'menj-bio' ) ) );

	// Sections.
	$fields['section_slugs']          = array( 'label' => __( 'Section categories', 'menj-bio' ), 'tab' => 'sections', 'type' => 'section_slugs', 'args' => array( 'help' => __( 'Category slugs in the order they should appear, separated by commas or new lines. Each must be an existing category. Sections are mutually exclusive: a post belongs to exactly one. Use tags for topics that cut across sections.', 'menj-bio' ) ) );
	$fields['show_section_chooser']   = array( 'label' => __( 'Show section chooser', 'menj-bio' ), 'tab' => 'sections', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Displays the row of section links on the home page and on section archives.', 'menj-bio' ) ) );
	$fields['section_all_label']      = array( 'label' => __( 'Label for the all-sections link', 'menj-bio' ), 'tab' => 'sections', 'type' => 'text' );
	$fields['show_list_tags']         = array( 'label' => __( 'Show tags in post lists', 'menj-bio' ), 'tab' => 'sections', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Tags are the counterpart to sections: a post lives in one section and carries any number of tags, so tags are how a topic crosses sections.', 'menj-bio' ) ) );
	$fields['list_tag_limit']         = array( 'label' => __( 'Tags shown per row', 'menj-bio' ), 'tab' => 'sections', 'type' => 'number', 'args' => array( 'min' => 1, 'max' => 10, 'step' => 1, 'help' => __( 'Any beyond this are summarised as a count.', 'menj-bio' ) ) );
	$fields['enforce_single_section'] = array( 'label' => __( 'One category per post', 'menj-bio' ), 'tab' => 'sections', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Reduces every post to a single category on save, and makes the block editor category panel behave as a single-choice list. Applies to posts created through REST, WP-CLI, and imports too.', 'menj-bio' ) ) );
	$fields['scope_adjacent_posts']   = array( 'label' => __( 'Keep previous and next within a section', 'menj-bio' ), 'tab' => 'sections', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Without this, the links at the foot of a post run chronologically across every section, so a reader following one topic is dropped into another.', 'menj-bio' ) ) );

	// Social. The platform list is filterable, so this loop picks up additions.
	$fields['email_obfuscation'] = array(
		'label' => __( 'Email protection', 'menj-bio' ),
		'tab'   => 'social',
		'type'  => 'select',
		'args'  => array(
			'choices' => get_email_modes(),
			'help'    => __( 'Keeps plain mailto: strings out of the served HTML so harvesters find nothing to scrape. Applies to the icon below, to mailto: links in post content, and to the [menj_email] shortcode.', 'menj-bio' ),
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
				'help'        => $is_email ? __( 'Bare email works; mailto: is added automatically.', 'menj-bio' ) : '',
			),
		);
	}

	// Appearance.
	$fields['colour_scheme'] = array( 'label' => __( 'Colour scheme', 'menj-bio' ), 'tab' => 'appearance', 'type' => 'radio_presets' );


	$fields['font_stack']        = array( 'label' => __( 'Font stack', 'menj-bio' ), 'tab' => 'appearance', 'type' => 'font_stack' );
	$fields['hero_heading_size'] = array( 'label' => __( 'Lede heading size (px)', 'menj-bio' ), 'tab' => 'appearance', 'type' => 'number', 'args' => array( 'min' => 28, 'max' => 96, 'step' => 2, 'help' => __( 'Maximum size. The heading scales down fluidly on narrow screens.', 'menj-bio' ) ) );
	$fields['hero_body_size']    = array( 'label' => __( 'Lede body size (px)', 'menj-bio' ), 'tab' => 'appearance', 'type' => 'number', 'args' => array( 'min' => 14, 'max' => 28, 'step' => 1, 'help' => __( 'Size of the intro paragraph under the heading.', 'menj-bio' ) ) );

	// Layout.
	$fields['chrome_layout'] = array( 'label' => __( 'Chrome layout', 'menj-bio' ), 'tab' => 'layout', 'type' => 'select', 'args' => array( 'choices' => array( 'topbar' => __( 'Top bar: full-width header above the content', 'menj-bio' ), 'sidebar' => __( 'Sidebar: floating card in a left rail', 'menj-bio' ) ), 'help' => __( 'The sidebar card holds the wordmark, a numbered navigation, and the stay-in-touch links, leaving the content column free. Collapses to the top bar on narrow screens.', 'menj-bio' ) ) );
	$fields['keyboard_nav'] = array( 'label' => __( 'Keyboard shortcuts', 'menj-bio' ), 'tab' => 'layout', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Digits 0 to 9 follow the correspondingly numbered navigation link. Sidebar layout only. Never fires while typing in a field. Turn off if it conflicts with assistive tooling.', 'menj-bio' ) ) );
	$fields['sidebar_social_heading'] = array( 'label' => __( 'Stay-in-touch heading', 'menj-bio' ), 'tab' => 'layout', 'type' => 'text' );
	$fields['container_width'] = array( 'label' => __( 'Content width (px)', 'menj-bio' ), 'tab' => 'layout', 'type' => 'number', 'args' => array( 'min' => 600, 'max' => 1600, 'step' => 20, 'help' => __( 'Widescreen sites sit well between 1040 and 1280.', 'menj-bio' ) ) );
	$fields['portrait_size']   = array( 'label' => __( 'Hero portrait size (px)', 'menj-bio' ), 'tab' => 'layout', 'type' => 'number', 'args' => array( 'min' => 120, 'max' => 400, 'step' => 10, 'help' => __( 'Diameter or edge length of the hero portrait.', 'menj-bio' ) ) );
	$fields['portrait_style']  = array( 'label' => __( 'Hero portrait style', 'menj-bio' ), 'tab' => 'layout', 'type' => 'select', 'args' => array( 'choices' => get_portrait_styles(), 'help' => __( 'Floating suits a cut-out PNG with a transparent background.', 'menj-bio' ) ) );
	$fields['list_style'] = array( 'label' => __( 'Post list style', 'menj-bio' ), 'tab' => 'layout', 'type' => 'select', 'args' => array( 'choices' => array( 'stacked' => __( 'Stacked: title with date at the end', 'menj-bio' ), 'columns' => __( 'Columns: date, section, title in aligned columns', 'menj-bio' ), 'index'   => __( 'Index: title with year, excerpt underneath, indexical', 'menj-bio' ) ), 'help' => __( 'Index reads as a table of contents: a hairline-ruled row list where the title carries the eye and the excerpt sits below. Suits sites organised by title rather than by date.', 'menj-bio' ) ) );
	$fields['hover_preview']   = array( 'label' => __( 'Post hover previews', 'menj-bio' ), 'tab' => 'layout', 'type' => 'checkbox', 'args' => array( 'help' => __( 'Reveals a post featured image beside the list on hover or keyboard focus. Posts without a featured image show nothing. Pointer devices only.', 'menj-bio' ) ) );
	$fields['preview_size']    = array( 'label' => __( 'Hover preview width (px)', 'menj-bio' ), 'tab' => 'layout', 'type' => 'number', 'args' => array( 'min' => 100, 'max' => 240, 'step' => 10, 'help' => __( 'Width of the small floating preview that appears below-right of a post row on hover. Kept small so it reads as a peek rather than a card.', 'menj-bio' ) ) );
	$fields['show_recent']     = array( 'label' => __( 'Show recent posts on home', 'menj-bio' ), 'tab' => 'layout', 'type' => 'checkbox' );
	$fields['recent_count']    = array( 'label' => __( 'Recent posts count', 'menj-bio' ), 'tab' => 'layout', 'type' => 'number', 'args' => array( 'min' => 1, 'max' => 20, 'step' => 1 ) );

	// Advanced. Help text here depends on runtime state, so it is deferred.
	$fields['disable_file_edit'] = array( 'label' => __( 'Disable file editors', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'checkbox', 'args' => array( 'help' => __NAMESPACE__ . '\\file_edit_help' ) );
	$fields['emit_meta_tags']    = array( 'label' => __( 'Emit meta tags and schema', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'checkbox', 'args' => array( 'help' => __NAMESPACE__ . '\\meta_tags_help' ) );
	$fields['planned_badge_label'] = array( 'label' => __( 'Planned page badge', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'text', 'args' => array( 'help' => __( 'Shown in the navigation next to pages marked as planned, and on the page itself. Mark a page as planned in its editor sidebar.', 'menj-bio' ) ) );
	$fields['planned_notice_text'] = array( 'label' => __( 'Planned page notice', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'text', 'args' => array( 'help' => __( 'The sentence shown in place of content on a planned page.', 'menj-bio' ) ) );
	$fields['export_action']     = array( 'label' => __( 'Export settings', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'export_button', 'args' => array( 'help' => __( 'Downloads every theme setting as a JSON file. Useful as a backup and for moving a configuration to another install.', 'menj-bio' ) ) );
	$fields['import_action']     = array( 'label' => __( 'Import settings', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'import_button', 'args' => array( 'help' => __( 'Replaces all current settings. Unknown keys are discarded and every value is re-validated, so an edited file cannot introduce anything the theme does not recognise.', 'menj-bio' ) ) );
	$fields['reset_action']      = array( 'label' => __( 'Reset to defaults', 'menj-bio' ), 'tab' => 'advanced', 'type' => 'reset_button' );

	/**
	 * Filter the options page fields.
	 *
	 * Each entry: key => array( label, tab, type, args ). Registered by the
	 * same loop as the theme's own fields, so an added field behaves
	 * identically to a built-in one. Pair with `menj_bio_defaults` and
	 * `menj_bio_option_sanitizers` so the value has a default and is stored
	 * correctly.
	 *
	 * @param array<string, array> $fields Field declarations.
	 */
	return apply_filters( 'menj_bio_option_fields', $fields );
}

