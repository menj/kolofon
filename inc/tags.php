<?php
/**
 * Tags.
 *
 * Tags are the counterpart to sections. A post lives in exactly one section
 * and carries as many tags as it needs, so tags are the only mechanism the
 * theme offers for a topic that cuts across sections.
 *
 * That makes a tag archive the deliberate opposite of a section archive: it
 * gathers posts from everywhere. The section chooser is therefore suppressed
 * on tag archives, since offering it there would imply a filter that does not
 * apply, and a section breakdown is shown instead.
 *
 * @package Kolofon
 * @since   1.1.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Render a post's tags as a list of links.
 *
 * Used at the foot of a single post. Tags were once also rendered against each
 * row of a post list, truncated with a "+N more" tail; that display was removed
 * in 5.0.0 because it crowded the list and pulled the eye off the titles, so
 * the truncation logic went with it.
 *
 * @param int|null $post_id Post ID, or null for the current post.
 */
function render_post_tags( $post_id = null ) {
	$tags = get_the_tags( $post_id );

	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return;
	}
	?>
	<div class="post-tags">
		<span class="post-tags-label"><?php esc_html_e( 'Tagged', 'kolofon' ); ?></span>
		<ul class="tags">
			<?php foreach ( $tags as $tag ) : ?>
				<li>
					<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
						rel="tag"><?php echo esc_html( $tag->name ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Which sections the posts in the current tag archive belong to.
 *
 * Reinforces the model rather than decorating it: a tag archive is the view
 * that deliberately spans sections, so naming the sections it reaches is the
 * useful thing to show where a section chooser would mislead.
 *
 * @param \WP_Query|null $query Query to inspect. Defaults to the main query.
 * @return \WP_Term[]
 */
function sections_represented( $query = null ) {
	if ( ! $query instanceof \WP_Query ) {
		global $wp_query;
		$query = $wp_query;
	}

	$sections = get_sections();

	if ( empty( $sections ) || empty( $query->posts ) ) {
		return array();
	}

	$found = array();

	foreach ( (array) $query->posts as $post ) {
		$assigned = wp_get_post_categories( is_object( $post ) ? $post->ID : (int) $post );

		foreach ( $sections as $section ) {
			if ( in_array( (int) $section->term_id, $assigned, true ) ) {
				$found[ $section->term_id ] = $section;
			}
		}
	}

	// Return them in configured order rather than discovery order.
	$ordered = array();
	foreach ( $sections as $section ) {
		if ( isset( $found[ $section->term_id ] ) ) {
			$ordered[] = $section;
		}
	}

	return $ordered;
}

/**
 * Render the section breakdown shown on a tag archive.
 *
 * @param \WP_Query|null $query Query to inspect.
 */
function render_tag_sections( $query = null ) {
	$sections = sections_represented( $query );

	if ( count( $sections ) < 1 ) {
		return;
	}

	$names = array();
	foreach ( $sections as $section ) {
		$names[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_category_link( $section->term_id ) ),
			esc_html( $section->name )
		);
	}
	?>
	<p class="tag-sections">
		<?php
		printf(
			/* translators: %s: list of linked section names */
			esc_html__( 'Spans: %s', 'kolofon' ),
			wp_kses_post( implode( ', ', $names ) )
		);
		?>
	</p>
	<?php
}

/**
 * Render every tag in use, sized by post count.
 *
 * A browsable topic index. Prints nothing when the site has no tags.
 *
 * @param int $limit Maximum tags to render. 0 for no limit.
 */
function render_tag_index( $limit = 0 ) {
	$args = array(
		'taxonomy'   => 'post_tag',
		'orderby'    => 'count',
		'order'      => 'DESC',
		'hide_empty' => true,
	);

	if ( $limit > 0 ) {
		$args['number'] = $limit;
	}

	$tags = get_terms( $args );

	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return;
	}

	// Sort alphabetically for display, having selected by popularity above.
	usort(
		$tags,
		function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);
	?>
	<ul class="tag-index">
		<?php foreach ( $tags as $tag ) : ?>
			<li>
				<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" rel="tag">
					<?php echo esc_html( $tag->name ); ?>
					<span class="tag-count"><?php echo esc_html( number_format_i18n( $tag->count ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Shortcode: [kolofon_tags] or [kolofon_tags limit="20"]
 * ([menj_tags] is kept as a back-compat alias for pre-rename content.)
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function tag_index_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 0 ), $atts, 'kolofon_tags' );

	ob_start();
	render_tag_index( max( 0, intval( $atts['limit'] ) ) );
	return ob_get_clean();
}
add_shortcode( 'kolofon_tags', __NAMESPACE__ . '\\tag_index_shortcode' );
// Back-compat alias from before the 3.0.0 rename; existing content may use it.
add_shortcode( 'menj_tags', __NAMESPACE__ . '\\tag_index_shortcode' );
