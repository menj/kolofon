<?php
/**
 * Display rules.
 *
 * A single named resolver for "should this feature show here", so a new
 * display option is one function call and one filter, not another
 * `intval( opt( '...' ) ) === 1` check copied into another template.
 *
 * Two features exist today: the homepage's Recent Posts section and the
 * section chooser shown on the homepage and the post index. Both were
 * already independent booleans (`show_recent`, `show_section_chooser`) with
 * no shared resolution logic between them; this gives them one, and gives
 * the next display option somewhere to land instead of a third ad hoc
 * pattern. It intentionally does not invent context groupings (homepage,
 * archive, search) that nothing yet needs: 9.3's goal is one place display
 * behaviour is decided, not a taxonomy speculatively built ahead of the
 * options that would use it.
 *
 * @package Kolofon
 * @since   7.5.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Whether a display feature should show, given the current context.
 *
 * Reads the Theme Option backing the feature, then applies the
 * `kolofon_display_rule` filter so a child theme or a future context-aware
 * rule (a feature shown on the homepage but hidden on an archive, say) has
 * one place to intervene instead of patching the template.
 *
 * @param string $feature One of 'recent_posts', 'section_chooser'.
 * @return bool
 */
function display_rule( $feature ) {
	$option_key = array(
		'recent_posts'    => 'show_recent',
		'section_chooser' => 'show_section_chooser',
	)[ $feature ] ?? null;

	$result = null !== $option_key && 1 === intval( opt( $option_key ) );

	/**
	 * Filters whether a display feature shows in the current context.
	 *
	 * @param bool   $result  Whether the feature would show, from its Theme Option.
	 * @param string $feature The feature being resolved.
	 */
	return (bool) apply_filters( 'kolofon_display_rule', $result, $feature );
}
