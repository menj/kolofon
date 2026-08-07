<?php
/**
 * Print branding.
 *
 * Turns a printed post into a branded document. On screen these elements are
 * hidden; only the print stylesheet reveals them. Two pieces are added around
 * the article body via the_content, so no template edit is needed:
 *
 * 1. A letterhead masthead at the top: the site name as a printed identity,
 *    since the screen header is correctly hidden on paper.
 * 2. A colophon at the foot: an auto-generated citation (author, title, site,
 *    date, and the source URL) so a printed page keeps its provenance and can
 *    be cited. This exploits the same publication date the theme already emits
 *    in structured data.
 *
 * Both are marked aria-hidden and carry a print-only class, so screen readers
 * and on-screen layout ignore them entirely.
 *
 * @package Kolofon
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_filter( 'the_content', __NAMESPACE__ . '\\wrap_content_for_print', 20 );

/**
 * Prepend a print letterhead and append a print colophon to single posts.
 *
 * Only single posts and pages in the main query are wrapped, so archives,
 * feeds, and embedded content are untouched. The added markup is inert on
 * screen; the print stylesheet in main.css is what displays it.
 *
 * @param string $content The post content.
 * @return string
 */
function wrap_content_for_print( $content ) {
	if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	// The letterhead identifies any printed page as yours. The citation
	// colophon is added only to posts: a formatted citation with a publication
	// date suits an article, but reads oddly on a static page like About, where
	// the "published" date carries little meaning.
	$colophon = is_singular( 'post' ) ? print_colophon() : '';

	return print_letterhead() . $content . $colophon;
}

/**
 * The print-only letterhead: the site identity as a document masthead.
 *
 * @return string
 */
function print_letterhead() {
	$name = function_exists( 'Kolofon\\opt' ) ? opt( 'hero_heading' ) : '';
	$name = $name ? wp_strip_all_tags( $name ) : get_bloginfo( 'name', 'display' );
	$tag  = get_bloginfo( 'description', 'display' );

	$html  = '<div class="print-letterhead" aria-hidden="true">';
	$html .= '<span class="print-letterhead-name">' . esc_html( $name ) . '</span>';
	if ( $tag ) {
		$html .= '<span class="print-letterhead-tag">' . esc_html( $tag ) . '</span>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * The print-only colophon: an MLA-formatted citation and source line.
 *
 * Follows MLA 9th edition for a work on a website:
 *
 *   Author. "Title of Page." Title of Website, Day Mon. Year, url.
 *
 * The author name must be inverted (Last, First); since the hero heading is a
 * free-form display name that cannot be split reliably, a dedicated
 * citation_author option holds the inverted form, falling back to the display
 * name as written. The website title is italicised (the MLA container), the
 * date uses MLA's day-abbreviated-month-year form, and the URL drops the
 * scheme, as MLA 9 directs. An access date is included on its own line: MLA
 * makes it optional but recommends it for web sources that may change, which a
 * printout consulted later benefits from.
 *
 * @return string
 */
function print_colophon() {
	$has_opt = function_exists( 'Kolofon\\opt' );

	$author = $has_opt ? opt( 'citation_author' ) : '';
	if ( ! $author ) {
		$author = $has_opt ? opt( 'hero_heading' ) : '';
		$author = $author ? wp_strip_all_tags( $author ) : get_bloginfo( 'name', 'display' );
	}

	$site  = get_bloginfo( 'name', 'display' );
	$title = wp_strip_all_tags( get_the_title() );
	$url   = preg_replace( '#^https?://#', '', get_permalink() );
	$date  = mla_date( get_the_date( 'U' ) );

	$accessed = mla_date( time() );

	$html  = '<div class="print-colophon" aria-hidden="true">';
	$html .= '<p class="print-colophon-label">' . esc_html__( 'Cite this page (MLA)', 'kolofon' ) . '</p>';

	// Author. "Title." <cite>Site</cite>, Date, url.
	$html .= '<p class="print-colophon-citation">';
	$html .= esc_html( trim( $author, " \t\n\r\0\x0B." ) ) . '. ';
	$html .= '&ldquo;' . esc_html( $title ) . '.&rdquo; ';
	$html .= '<cite class="print-colophon-site">' . esc_html( $site ) . '</cite>';
	if ( $date ) {
		$html .= ', ' . esc_html( $date );
	}
	$html .= ', ' . esc_html( $url ) . '.';
	$html .= '</p>';

	// Accessed date on its own line.
	$html .= '<p class="print-colophon-source">';
	/* translators: %s: the MLA-formatted date the page was printed. */
	$html .= esc_html( sprintf( __( 'Accessed %s.', 'kolofon' ), $accessed ) );
	$html .= '</p>';

	$html .= '</div>';

	return $html;
}

/**
 * Format a timestamp as an MLA date: day, abbreviated month, year.
 *
 * MLA abbreviates all months except May, June, and July. Example outputs:
 * "15 Jan. 2026", "3 May 2026". The month abbreviations are the MLA set, not
 * the PHP defaults, so they are mapped explicitly rather than taken from a
 * locale, which keeps the citation stable regardless of site language.
 *
 * @param int|string $timestamp Unix timestamp.
 * @return string
 */
function mla_date( $timestamp ) {
	$timestamp = (int) $timestamp;
	if ( ! $timestamp ) {
		return '';
	}

	$months = array(
		1  => 'Jan.',
		2  => 'Feb.',
		3  => 'Mar.',
		4  => 'Apr.',
		5  => 'May',
		6  => 'June',
		7  => 'July',
		8  => 'Aug.',
		9  => 'Sept.',
		10 => 'Oct.',
		11 => 'Nov.',
		12 => 'Dec.',
	);

	$day   = (int) gmdate( 'j', $timestamp );
	$month = $months[ (int) gmdate( 'n', $timestamp ) ];
	$year  = gmdate( 'Y', $timestamp );

	return $day . ' ' . $month . ' ' . $year;
}
