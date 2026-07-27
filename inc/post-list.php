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
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

// Running position counter for schema.org ListItem markup, reset at the start
// of each rendered list (see post_list_classes and the blog year groups).
$GLOBALS['kolofon_list_position'] = 0;

/**
 * Whether hover previews are switched on and possible in this context.
 *
 * @return bool
 */
function previews_enabled() {
	return 1 === intval( opt( 'hover_preview' ) );
}

/**
 * A deterministic printer's mark for a post that has no featured image.
 *
 * The theme is named for the colophon, the printer's device that closes a
 * manuscript, so an image-less post gets a generated device of its own rather
 * than an empty card. The composition is seeded from the post slug, so a given
 * post always draws the same mark, and it is built from the scheme's own
 * tokens, so it recolours with Charcoal, Ivory, or any custom scheme.
 *
 * Inline SVG: no HTTP request, no image file, no external dependency.
 *
 * @param string $seed Stable per-post string, normally the slug.
 * @return string SVG markup.
 */
function preview_device( $seed ) {
	$hash = md5( $seed );

	/**
	 * Pull a bounded integer out of the hash at a fixed offset.
	 *
	 * @param int $offset Character offset into the hash.
	 * @param int $mod    Exclusive upper bound.
	 * @return int
	 */
	$pick = function ( $offset, $mod ) use ( $hash ) {
		return hexdec( substr( $hash, $offset, 2 ) ) % $mod;
	};

	$variant = $pick( 0, 5 );
	$rotate  = $pick( 2, 90 );
	$count   = 5 + $pick( 4, 4 );
	$shift   = -18 + $pick( 6, 36 );

	$marks = '';

	switch ( $variant ) {
		case 0:
			// Concentric roundel, one ring broken like an impression that
			// did not take.
			for ( $i = 0; $i < $count; $i++ ) {
				$r      = 14 + ( $i * 9 );
				$dash   = ( 1 === $i % 3 ) ? ' stroke-dasharray="4 7"' : '';
				$marks .= sprintf(
					'<circle cx="150" cy="100" r="%d" fill="none" stroke="currentColor" stroke-width="1.25" opacity="%s"%s/>',
					$r,
					number_format( 0.85 - ( $i * 0.09 ), 2 ),
					$dash
				);
			}
			break;

		case 1:
			// Nested squares, each turned a little further.
			for ( $i = 0; $i < $count; $i++ ) {
				$s      = 24 + ( $i * 13 );
				$marks .= sprintf(
					'<rect x="%1$s" y="%2$s" width="%3$d" height="%3$d" fill="none" stroke="currentColor" stroke-width="1.25" opacity="%4$s" transform="rotate(%5$d 150 100)"/>',
					number_format( 150 - ( $s / 2 ), 2 ),
					number_format( 100 - ( $s / 2 ), 2 ),
					$s,
					number_format( 0.8 - ( $i * 0.09 ), 2 ),
					$rotate + ( $i * 9 )
				);
			}
			break;

		case 2:
			// Radiating rule, a compass-rose device.
			$spokes = 12 + ( $count * 2 );
			for ( $i = 0; $i < $spokes; $i++ ) {
				$angle  = ( 360 / $spokes ) * $i;
				$len    = ( 0 === $i % 3 ) ? 62 : 44;
				$marks .= sprintf(
					'<line x1="150" y1="100" x2="150" y2="%d" stroke="currentColor" stroke-width="1.25" opacity="0.55" transform="rotate(%s 150 100)"/>',
					100 - $len,
					number_format( $angle + $rotate, 2 )
				);
			}
			$marks .= '<circle cx="150" cy="100" r="9" fill="none" stroke="currentColor" stroke-width="1.25" opacity="0.85"/>';
			break;

		case 3:
			// A stack of rules, the ghost of a set page.
			for ( $i = 0; $i < $count + 3; $i++ ) {
				$w      = 40 + ( ( ( $i * 37 ) + abs( $shift ) ) % 120 );
				$marks .= sprintf(
					'<rect x="%s" y="%d" width="%d" height="2" fill="currentColor" opacity="%s"/>',
					number_format( 150 - ( $w / 2 ), 2 ),
					58 + ( $i * 11 ),
					$w,
					number_format( 0.7 - ( $i * 0.06 ), 2 )
				);
			}
			break;

		default:
			// A quiet grid of dots, sized by position.
			for ( $row = 0; $row < 5; $row++ ) {
				for ( $col = 0; $col < 7; $col++ ) {
					$r      = 1.5 + ( ( ( $row * 7 ) + $col + $shift ) % 4 );
					$marks .= sprintf(
						'<circle cx="%d" cy="%d" r="%s" fill="currentColor" opacity="%s"/>',
						96 + ( $col * 18 ),
						56 + ( $row * 22 ),
						number_format( max( 1.2, $r ), 2 ),
						number_format( 0.28 + ( ( ( $row + $col ) % 4 ) * 0.16 ), 2 )
					);
				}
			}
			break;
	}

	return sprintf(
		'<svg class="post-preview-device" viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid slice">%s</svg>',
		$marks
	);
}

/**
 * Class list for a post list wrapper.
 *
 * Adds `has-previews` whenever previews are enabled — every row now gets a
 * peek (photographic when a featured image exists, typographic when not), so
 * the gutter mechanic applies list-wide rather than being conditional on the
 * query's thumbnail state.
 *
 * @param \WP_Query|null $query Kept in the signature for backward compat with
 *                              existing callers; no longer consulted since
 *                              every row emits a preview.
 * @return string
 */
function post_list_classes( $query = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- retained for backward compatibility with existing callers, see docblock.
	// Each rendered list is a schema.org ItemList; reset the per-list position
	// counter so ListItem positions start at 1 for every list on the page.
	$GLOBALS['kolofon_list_position'] = 0;

	$classes = 'post-list';

	if ( previews_enabled() ) {
		// Every row gets a preview (photographic or typographic), so the
		// gutter mechanic activates for the whole list whenever previews
		// are enabled — no need to inspect individual posts.
		$classes .= ' has-previews';
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
	<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'kolofon' ); ?>">
		<p class="pagination-count">
			<?php
			printf(
				/* translators: 1: first item on this page, 2: last item on this page, 3: total items */
				esc_html__( '%1$s to %2$s of %3$s', 'kolofon' ),
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
					<span class="screen-reader-text"><?php esc_html_e( 'Newer posts', 'kolofon' ); ?></span>
				</a>
			<?php else : ?>
				<span class="pagination-link is-disabled" aria-hidden="true">&larr;</span>
			<?php endif; ?>

			<?php if ( $next ) : ?>
				<a class="pagination-link" href="<?php echo esc_url( $next ); ?>" rel="next">
					<span aria-hidden="true">&rarr;</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Older posts', 'kolofon' ); ?></span>
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
	// date format; columns shows the full day-month-year on every row, so
	// there's never any ambiguity about when a post was written; index wants
	// the year alone, since the row list reads as an annotated table of
	// contents where the exact day would be noise.
	if ( '' === $args['date_format'] ) {
		if ( 'columns' === $style ) {
			$date = get_the_date( 'j M Y' );
		} elseif ( 'index' === $style ) {
			$date = get_the_date( 'Y' );
		} else {
			$date = get_the_date();
		}
	} else {
		$date = get_the_date( $args['date_format'] );
	}

	$preview = '';

	if ( previews_enabled() ) {
		if ( has_post_thumbnail() ) {
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
		} else {
			// No featured image: draw a generated printer's mark seeded from
			// the post slug, with the title set over it. Same 3:2 anchor and
			// slot as an image preview, so image-less posts get a peek with
			// real presence rather than an empty card.
			$post_object = get_post();
			$seed        = ( $post_object && $post_object->post_name ) ? $post_object->post_name : get_the_title();

			$preview = sprintf(
				'<span class="post-preview is-typographic" aria-hidden="true">%1$s<span class="post-preview-title">%2$s</span></span>',
				preview_device( $seed ),
				esc_html( wp_trim_words( get_the_title(), 10, '…' ) )
			);
		}
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
	<li class="<?php echo esc_attr( $classes ); ?>" itemprop="itemListElement" itemscope="itemscope" itemtype="https://schema.org/ListItem">
		<meta itemprop="position" content="<?php echo esc_attr( (string) ( ++$GLOBALS['kolofon_list_position'] ) ); ?>" />
		<a href="<?php the_permalink(); ?>" itemprop="url">
			<span itemprop="item" itemscope="itemscope" itemtype="https://schema.org/BlogPosting">
			<meta itemprop="url" content="<?php the_permalink(); ?>" />
			<?php if ( 'columns' === $style ) : ?>
				<span class="post-col post-col-date"><time itemprop="datePublished" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( $date ); ?></time></span>
				<span class="post-col post-col-section">
					<?php echo $section ? esc_html( $section->name ) : ''; ?>
				</span>
			<?php endif; ?>
			<span class="post-item-main">
				<span class="post-title" itemprop="headline name"><?php the_title(); ?></span>
				<?php if ( $show_dek ) : ?>
					<?php $dek = get_the_excerpt(); ?>
					<?php if ( $dek ) : ?>
						<span class="post-dek" itemprop="abstract"><?php echo esc_html( wp_trim_words( $dek, 22 ) ); ?></span>
					<?php endif; ?>
				<?php endif; ?>
			</span>
			<?php if ( 'columns' !== $style ) : ?>
				<span class="post-date"><time itemprop="datePublished" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( $date ); ?></time></span>
			<?php endif; ?>
			<?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from core template tags above. ?>
			</span>
		</a>
	</li>
	<?php
}
