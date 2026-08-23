<?php
/**
 * Template Name: Blog Index
 *
 * The full archive, rendered as a year-anchored ledger: each year gets a
 * numeral in a left rail, its posts listed to the right as a numbered index.
 * Deliberately distinct from the Main Index, which keeps its own hero and
 * short "Recent Posts" list — the two pages no longer share a row treatment.
 *
 * Paginated on its own `blog_per_page` setting (Theme Options → Layout).
 *
 * @package Kolofon
 */

use function Kolofon\blog_index_query_args;

get_header();

$kolofon_args         = blog_index_query_args();
$kolofon_current_page = (int) $kolofon_args['paged'];
$kolofon_per_page     = (int) $kolofon_args['posts_per_page'];

$kolofon_blog_query = new WP_Query( $kolofon_args );
?>
<main itemprop="mainContentOfPage" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
	<article class="page-index page-ledger" itemscope="itemscope" itemtype="https://schema.org/CollectionPage">
		<div class="container">
			<header class="content-header">
				<?php the_title( '<h1 class="post-title" itemprop="name">', '</h1>' ); ?>
				<?php if ( get_the_content() ) : ?>
					<div class="description" itemprop="description"><?php the_content(); ?></div>
				<?php endif; ?>
			</header>

			<div class="content-meta">
				<p class="post-count">
					<?php
					printf(
						/* translators: %d: post count */
						esc_html( _n( '%d post', '%d posts', $kolofon_blog_query->found_posts, 'kolofon' ) ),
						intval( $kolofon_blog_query->found_posts )
					);
					?>
				</p>
			</div>

			<div class="content ledger">
				<?php
				if ( $kolofon_blog_query->have_posts() ) :
					$kolofon_current_year = null;
					$kolofon_year_rows    = '';
					$kolofon_year_count   = 0;

					while ( $kolofon_blog_query->have_posts() ) :
						$kolofon_blog_query->the_post();
						$kolofon_group_year = get_the_date( 'Y' );

						if ( $kolofon_group_year !== $kolofon_current_year ) :
							if ( null !== $kolofon_current_year ) :
								// Flush the completed group: the entry count is only
								// known once every post in it has been visited.
								printf(
									'<section class="year-block" itemscope="itemscope" itemtype="https://schema.org/ItemList"><div class="year-rail"><p class="year-num" itemprop="name">%1$s <span class="year-count">&middot; %2$s</span></p></div><ol class="index-list">%3$s</ol></section>',
									esc_html( $kolofon_current_year ),
									esc_html(
										sprintf(
											/* translators: %d: number of posts in this year, on this page */
											_n( '%d entry', '%d entries', $kolofon_year_count, 'kolofon' ),
											$kolofon_year_count
										)
									),
									$kolofon_year_rows // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments below.
								);
							endif;
							// Each year group is its own schema.org ItemList; reset the
							// position counter so ListItem positions — and the printed
							// index number, which is the same integer — start at 1
							// within each year.
							$GLOBALS['kolofon_list_position'] = 0;
							$kolofon_current_year             = $kolofon_group_year;
							$kolofon_year_rows                = '';
							$kolofon_year_count                = 0;
						endif;

						++$kolofon_year_count;

						$kolofon_cats    = get_the_category();
						$kolofon_section = ! empty( $kolofon_cats ) ? $kolofon_cats[0] : null;

						ob_start();
						?>
						<li class="entry h-entry" itemprop="itemListElement" itemscope="itemscope" itemtype="https://schema.org/ListItem">
							<meta itemprop="position" content="<?php echo esc_attr( (string) ( ++$GLOBALS['kolofon_list_position'] ) ); ?>" />
							<span class="entry-index" aria-hidden="true"><?php echo esc_html( str_pad( (string) $kolofon_year_count, 2, '0', STR_PAD_LEFT ) ); ?></span>
							<a class="entry-link u-url" href="<?php the_permalink(); ?>" itemprop="url">
								<span itemprop="item" itemscope="itemscope" itemtype="https://schema.org/BlogPosting">
									<meta itemprop="url" content="<?php the_permalink(); ?>" />
									<span class="entry-meta">
										<time class="entry-date" itemprop="datePublished" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'M j' ) ); ?></time>
										<?php if ( $kolofon_section ) : ?>
											<span class="entry-section"><?php echo esc_html( $kolofon_section->name ); ?></span>
										<?php endif; ?>
									</span>
									<span class="entry-title p-name" itemprop="headline name"><?php the_title(); ?></span>
								</span>
							</a>
						</li>
						<?php
						$kolofon_year_rows .= ob_get_clean();
					endwhile;

					// Flush the final group; the loop above only flushes on a year
					// transition, so the last year needs one more push after it ends.
					printf(
						'<section class="year-block" itemscope="itemscope" itemtype="https://schema.org/ItemList"><div class="year-rail"><p class="year-num" itemprop="name">%1$s <span class="year-count">&middot; %2$s</span></p></div><ol class="index-list">%3$s</ol></section>',
						esc_html( $kolofon_current_year ),
						esc_html(
							sprintf(
								/* translators: %d: number of posts in this year, on this page */
								_n( '%d entry', '%d entries', $kolofon_year_count, 'kolofon' ),
								$kolofon_year_count
							)
						),
						$kolofon_year_rows // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments above.
					);

					wp_reset_postdata();
				else :
					?>
					<p><?php esc_html_e( 'No posts yet.', 'kolofon' ); ?></p>
					<?php
				endif;
				?>
			</div>

			<?php if ( $kolofon_blog_query->max_num_pages > 1 ) : ?>
				<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'kolofon' ); ?>">
					<p class="pagination-count">
						<?php
						$kolofon_first = ( ( $kolofon_current_page - 1 ) * $kolofon_per_page ) + 1;
						$kolofon_last  = min( $kolofon_blog_query->found_posts, $kolofon_current_page * $kolofon_per_page );
						printf(
							/* translators: 1: first item on this page, 2: last item on this page, 3: total items */
							esc_html__( '%1$s to %2$s of %3$s', 'kolofon' ),
							esc_html( number_format_i18n( $kolofon_first ) ),
							esc_html( number_format_i18n( $kolofon_last ) ),
							esc_html( number_format_i18n( $kolofon_blog_query->found_posts ) )
						);
						?>
					</p>

					<?php
					// Built with get_pagenum_link() directly rather than the shared
					// render_pagination() helper: that helper's prev/next links come
					// from get_next_posts_page_link()/get_previous_posts_page_link(),
					// which read the global main query's pagination state. This is a
					// secondary WP_Query on a static Page, so the global state does not
					// describe it — reusing that helper here would print links that
					// look right but do not point at the correct page.
					?>
					<div class="pagination-controls">
						<?php if ( $kolofon_current_page > 1 ) : ?>
							<a class="pagination-link" href="<?php echo esc_url( get_pagenum_link( $kolofon_current_page - 1 ) ); ?>" rel="prev">
								<span aria-hidden="true">&larr;</span>
								<span class="screen-reader-text"><?php esc_html_e( 'Newer posts', 'kolofon' ); ?></span>
							</a>
						<?php else : ?>
							<span class="pagination-link is-disabled" aria-hidden="true">&larr;</span>
						<?php endif; ?>

						<?php if ( $kolofon_current_page < $kolofon_blog_query->max_num_pages ) : ?>
							<a class="pagination-link" href="<?php echo esc_url( get_pagenum_link( $kolofon_current_page + 1 ) ); ?>" rel="next">
								<span aria-hidden="true">&rarr;</span>
								<span class="screen-reader-text"><?php esc_html_e( 'Older posts', 'kolofon' ); ?></span>
							</a>
						<?php else : ?>
							<span class="pagination-link is-disabled" aria-hidden="true">&rarr;</span>
						<?php endif; ?>
					</div>
				</nav>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
