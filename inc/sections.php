<?php
/**
 * Sections.
 *
 * A section is a top-level category that a post belongs to exclusively. The
 * distinction the theme draws is:
 *
 *   Categories are sections. Exactly one per post. Where a post lives.
 *   Tags are topics. As many as you like. What a post is about.
 *
 * That line exists because sections overlap in subject even when they do not
 * overlap as sections. Without tags as the escape valve there is constant
 * pressure to tick a second box, and the scheme collapses the first time
 * someone does.
 *
 * Which categories count as sections, and in what order, is configured on the
 * Sections tab as a list of slugs. Nothing here hard-codes a taxonomy term.
 *
 * Known coupling: taxonomy terms are content and survive a theme switch, but
 * this enforcement does not. Strictly it belongs in a companion plugin. For a
 * single-author site that controls its own theme the trade is acceptable, and
 * it is recorded in ssot.md rather than left implicit.
 *
 * @package Kolofon
 * @since   1.1.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'save_post', __NAMESPACE__ . '\\enforce_single_section', 20, 3 );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_section_editor_script' );

/**
 * URL for the site-wide blog listing.
 *
 * Three fallbacks, in order of correctness:
 *
 *   1. The "Posts page" configured under Settings > Reading, if any. This is
 *      the WordPress-native answer and lets the site owner rename or move the
 *      page without touching template code.
 *   2. A page carrying the Blog Index template (`page-blog.php`), located by
 *      metadata query. Catches the case where the site has a Blog Index page
 *      but has not selected it under Settings > Reading, which is a common
 *      configuration mistake worth being forgiving about.
 *   3. `/blog`, the conventional slug. Works whenever a page exists there,
 *      even under a different template.
 *
 * The post-type archive is not a fallback because WordPress does not register
 * one for the built-in `post` type; the call returns false.
 *
 * @return string
 */
function get_blog_index_url() {
	$page_for_posts = (int) get_option( 'page_for_posts' );

	if ( $page_for_posts ) {
		$url = get_permalink( $page_for_posts );
		if ( $url ) {
			return $url;
		}
	}

	$blog_index_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'page-blog.php',     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $blog_index_pages ) ) {
		$url = get_permalink( $blog_index_pages[0] );
		if ( $url ) {
			return $url;
		}
	}

	return home_url( '/blog' );
}

/**
 * The primary section term for a post.
 *
 * "Primary" follows the same deterministic rule as `enforce_single_section()`:
 * prefer a configured section in configured order, otherwise the first
 * assigned category. Read at display time, so posts that predate enforcement
 * still resolve to a stable answer without needing a migration.
 *
 * @param int $post_id Post ID.
 * @return \WP_Term|null
 */
function get_primary_section( $post_id ) {
	$assigned = wp_get_post_categories( (int) $post_id );

	if ( empty( $assigned ) ) {
		return null;
	}

	$section_ids = get_section_ids();
	$term_id     = 0;

	foreach ( $section_ids as $section_id ) {
		if ( in_array( $section_id, $assigned, true ) ) {
			$term_id = $section_id;
			break;
		}
	}

	if ( ! $term_id ) {
		$term_id = (int) reset( $assigned );
	}

	$term = get_term( $term_id, 'category' );

	return ( $term && ! is_wp_error( $term ) ) ? $term : null;
}

/**
 * Ordered list of section terms.
 *
 * Reads the configured slugs in order and resolves each to a term. Slugs that
 * do not resolve are skipped rather than faked, so a typo shows up as a
 * missing section instead of a fatal.
 *
 * @return \WP_Term[]
 */
function get_sections() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();
	$slugs = parse_section_slugs( opt( 'section_slugs' ) );

	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term instanceof \WP_Term ) {
			$cache[] = $term;
		}
	}

	return $cache;
}

/**
 * Split a configured slug list into a clean ordered array.
 *
 * Accepts commas, newlines, or both, so the field is forgiving about how the
 * list is typed.
 *
 * @param string $raw Raw option value.
 * @return string[]
 */
function parse_section_slugs( $raw ) {
	$parts = preg_split( '/[\s,]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! is_array( $parts ) ) {
		return array();
	}

	return array_values( array_unique( array_map( 'sanitize_title', $parts ) ) );
}

/**
 * Term IDs of the configured sections.
 *
 * @return int[]
 */
function get_section_ids() {
	return wp_list_pluck( get_sections(), 'term_id' );
}

/**
 * Whether single-section enforcement is switched on.
 *
 * @return bool
 */
function enforcement_enabled() {
	return 1 === intval( opt( 'enforce_single_section' ) );
}

/**
 * Whether adjacent post links should stay inside the current section.
 *
 * @return bool
 */
function adjacent_links_scoped() {
	return 1 === intval( opt( 'scope_adjacent_posts' ) );
}

/**
 * Reduce a post to exactly one category on save.
 *
 * Server-side is the authority. The editor script below is a courtesy that
 * makes the UI agree with the rule; this is what holds when a post arrives
 * through REST, WP-CLI, Quick Edit, or an importer.
 *
 * Selection rule: if the post carries any configured section, keep the first
 * one in configured order. Otherwise keep the first assigned category. Ties
 * resolve deterministically so the same input always produces the same result.
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post    Post object.
 * @param bool     $update  Whether this is an existing post.
 */
function enforce_single_section( $post_id, $post, $update ) {
	unset( $update );

	if ( ! enforcement_enabled() ) {
		return;
	}

	if ( 'post' !== $post->post_type ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$assigned = wp_get_post_categories( $post_id );

	if ( count( $assigned ) <= 1 ) {
		return;
	}

	$section_ids = get_section_ids();
	$keep        = 0;

	// Prefer a configured section, honouring configured order.
	foreach ( $section_ids as $section_id ) {
		if ( in_array( $section_id, $assigned, true ) ) {
			$keep = $section_id;
			break;
		}
	}

	if ( ! $keep ) {
		$keep = (int) reset( $assigned );
	}

	wp_set_post_categories( $post_id, array( $keep ), false );
}

/**
 * Load the editor script that makes the category panel behave as single-select.
 */
function enqueue_section_editor_script() {
	if ( ! enforcement_enabled() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'post' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_script(
		'kolofon-single-section',
		KOLOFON_URI . 'assets/js/single-section.js',
		array( 'wp-data', 'wp-dom-ready' ),
		KOLOFON_VERSION,
		true
	);
}

/**
 * Render the section chooser.
 *
 * A row of links to category archives, with the active one marked. Server-side
 * navigation rather than client-side filtering, so it works without JavaScript
 * and every section keeps a real, shareable, indexable URL.
 *
 * Prints nothing when no sections are configured.
 *
 * @param int $current_id Term ID of the active section, or 0 for the "all" state.
 */
function render_section_chooser( $current_id = 0 ) {
	$sections = get_sections();

	if ( empty( $sections ) ) {
		return;
	}

	$all_label = opt( 'section_all_label' );
	$blog_url  = get_blog_index_url();
	?>
	<nav class="section-chooser" aria-label="<?php esc_attr_e( 'Sections', 'kolofon' ); ?>">
		<ul class="section-list">
			<li class="section-item">
				<a href="<?php echo esc_url( $blog_url ); ?>"
					class="section-link<?php echo 0 === $current_id ? ' is-current' : ''; ?>"
					<?php echo 0 === $current_id ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html( $all_label ); ?>
				</a>
			</li>
			<?php foreach ( $sections as $section ) : ?>
				<?php $is_current = ( (int) $section->term_id === (int) $current_id ); ?>
				<li class="section-item">
					<a href="<?php echo esc_url( get_category_link( $section->term_id ) ); ?>"
						class="section-link<?php echo $is_current ? ' is-current' : ''; ?>"
						<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $section->name ); ?>
						<span class="section-count"><?php echo esc_html( number_format_i18n( $section->count ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Render adjacent post links, scoped to the current section when configured.
 *
 * @param string $previous_format Format for the previous link.
 * @param string $next_format     Format for the next link.
 */
function render_adjacent_links( $previous_format = '&larr; %link', $next_format = '%link &rarr;' ) {
	$in_same_term = adjacent_links_scoped();

	echo '<span class="prev">';
	previous_post_link( $previous_format, '%title', $in_same_term, '', 'category' );
	echo '</span>';

	echo '<span class="next">';
	next_post_link( $next_format, '%title', $in_same_term, '', 'category' );
	echo '</span>';
}
