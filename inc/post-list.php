<?php
/**
 * Post list rendering.
 *
 * One renderer shared by the home, archive, and blog-index templates so the
 * hover-preview markup exists in exactly one place.
 *
 * The preview is the post's featured image. Posts without one simply render
 * no preview, so the feature degrades to the plain list rather than needing a
 * placeholder. No JavaScript is involved: visibility is driven by :hover and
 * :focus-within, so keyboard users get the same behaviour as pointer users.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

/**
 * Whether hover previews are switched on and possible in this context.
 *
 * @return bool
 */
function previews_enabled() {
	return 1 === intval( opt( 'hover_preview' ) );
}

/**
 * Class list for a post list wrapper.
 *
 * Adds `has-previews` only when previews are enabled and at least one post in
 * the query carries a featured image. The stylesheet reserves the right-hand
 * gutter on that class alone, so a list that can never show a preview keeps
 * its full width.
 *
 * @param \WP_Query|null $query Query to inspect. Defaults to the main query.
 * @return string
 */
function post_list_classes( $query = null ) {
	$classes = 'post-list';

	if ( ! previews_enabled() ) {
		return $classes;
	}

	if ( ! $query instanceof \WP_Query ) {
		global $wp_query;
		$query = $wp_query;
	}

	foreach ( (array) $query->posts as $post ) {
		if ( has_post_thumbnail( $post ) ) {
			$classes .= ' has-previews';
			break;
		}
	}

	return $classes;
}

/**
 * Render pagination as a counted range with chevron controls.
 *
 * "1 to 8 of 8" tells a reader they have reached the end of a section, which a
 * pair of directional links never does. On a small site that is the more
 * useful fact.
 *
 * Prints nothing when everything fits on one page.
 *
 * @param \WP_Query|null $query Query to paginate. Defaults to the main query.
 */
function render_pagination( $query = null ) {
	if ( ! $query instanceof \WP_Query ) {
		global $wp_query;
		$query = $wp_query;
	}

	$total_pages = (int) $query->max_num_pages;

	if ( $total_pages < 2 ) {
		return;
	}

	$per_page = (int) $query->get( 'posts_per_page' );
	$paged    = max( 1, (int) $query->get( 'paged' ) );
	$found    = (int) $query->found_posts;

	$first = ( ( $paged - 1 ) * $per_page ) + 1;
	$last  = min( $found, $paged * $per_page );

	$prev = $paged > 1 ? get_previous_posts_page_link() : '';
	$next = $paged < $total_pages ? get_next_posts_page_link( $total_pages ) : '';
	?>
	<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'menj-bio' ); ?>">
		<p class="pagination-count">
			<?php
			printf(
				/* translators: 1: first item on this page, 2: last item on this page, 3: total items */
				esc_html__( '%1$s to %2$s of %3$s', 'menj-bio' ),
				esc_html( number_format_i18n( $first ) ),
				esc_html( number_format_i18n( $last ) ),
				esc_html( number_format_i18n( $found ) )
			);
			?>
		</p>

		<div class="pagination-controls">
			<?php if ( $prev ) : ?>
				<a class="pagination-link" href="<?php echo esc_url( $prev ); ?>" rel="prev">
					<span aria-hidden="true">&larr;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Newer posts', 'menj-bio' ); ?></span>
				</a>
			<?php else : ?>
				<span class="pagination-link is-disabled" aria-hidden="true">&larr;</span>
			<?php endif; ?>

			<?php if ( $next ) : ?>
				<a class="pagination-link" href="<?php echo esc_url( $next ); ?>" rel="next">
					<span aria-hidden="true">&rarr;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Older posts', 'menj-bio' ); ?></span>
				</a>
			<?php else : ?>
				<span class="pagination-link is-disabled" aria-hidden="true">&rarr;</span>
			<?php endif; ?>
		</div>
	</nav>
	<?php
}

/**
 * Render one post list item for the current post in the loop.
 *
 * @param array $args {
 *     Optional arguments.
 *
 *     @type string $date_format Date format string. Default from site settings.
 *     @type bool   $show_dek    Whether to show the excerpt as a dek.
 * }
 */
function post_list_item( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'date_format' => '',
			'show_dek'    => false,
		)
	);

	$style = opt( 'list_style' );
	if ( ! in_array( $style, array( 'stacked', 'columns', 'index' ), true ) ) {
		$style = 'stacked';
	}

	// Each style has its own default date treatment. Stacked keeps the site
	// date format; columns wants a compact month-day to keep the column
	// narrow; index wants the year alone, since the row list reads as an
	// annotated table of contents where the exact day would be noise.
	if ( '' === $args['date_format'] ) {
		if ( 'columns' === $style ) {
			$date = get_the_date( 'M j' );
		} elseif ( 'index' === $style ) {
			$date = get_the_date( 'Y' );
		} else {
			$date = get_the_date();
		}
	} else {
		$date = get_the_date( $args['date_format'] );
	}

	$preview   = '';
	$has_thumb = has_post_thumbnail();

	if ( previews_enabled() && $has_thumb ) {
		$preview = sprintf(
			'<span class="post-preview" aria-hidden="true">%s</span>',
			get_the_post_thumbnail(
				null,
				'medium',
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
					'alt'      => '',
				)
			)
		);
	}

	$classes = 'post-item';
	if ( '' !== $preview ) {
		$classes .= ' has-preview';
	}
	if ( 'columns' === $style ) {
		$classes .= ' is-columns';
	} elseif ( 'index' === $style ) {
		$classes .= ' is-index';
	}

	// The section column shows which section the post lives in. One term by
	// construction, since sections are enforced as mutually exclusive.
	// Only surfaced in the columns style.
	$section = null;
	if ( 'columns' === $style ) {
		$cats    = get_the_category();
		$section = ! empty( $cats ) ? $cats[0] : null;
	}

	// The index style promotes the excerpt to the row itself, so a caller's
	// show_dek is honoured but not required.
	$show_dek = $args['show_dek'] || 'index' === $style;
	?>
	<li class="<?php echo esc_attr( $classes ); ?>">
		<a href="<?php the_permalink(); ?>">
			<?php if ( 'columns' === $style ) : ?>
				<span class="post-col post-col-date"><?php echo esc_html( $date ); ?></span>
				<span class="post-col post-col-section">
					<?php echo $section ? esc_html( $section->name ) : ''; ?>
				</span>
			<?php endif; ?>
			<span class="post-item-main">
				<span class="post-title"><?php the_title(); ?></span>
				<?php if ( list_tags_enabled() ) : ?>
					<?php render_post_tags( null, intval( opt( 'list_tag_limit' ) ), 'tags-inline' ); ?>
				<?php endif; ?>
				<?php if ( $show_dek ) : ?>
					<?php $dek = get_the_excerpt(); ?>
					<?php if ( $dek ) : ?>
						<span class="post-dek"><?php echo esc_html( wp_trim_words( $dek, 22 ) ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</span>
			<?php if ( 'columns' !== $style ) : ?>
				<span class="post-date"><?php echo esc_html( $date ); ?></span>
			<?php endif; ?>
			<?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from core template tags above. ?>
		</a>
	</li>
	<?php
}
