<?php
/**
 * Template Name: Blog Index
 *
 * Chronological blog list, grouped by year.
 *
 * @package Kolofon
 */

get_header();
?>
<main itemprop="mainContentOfPage" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
	<article class="page-index" itemscope="itemscope" itemtype="https://schema.org/CollectionPage">
		<div class="container">
			<header class="content-header">
				<?php the_title( '<h1 class="post-title" itemprop="name">', '</h1>' ); ?>
				<?php if ( get_the_content() ) : ?>
					<div class="description" itemprop="description"><?php the_content(); ?></div>
				<?php endif; ?>
			</header>

			<?php
			$kolofon_blog_query = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => -1,
				)
			);
			?>

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

			<div class="content">
				<?php

				if ( $kolofon_blog_query->have_posts() ) :
					$kolofon_current_year = null;
					while ( $kolofon_blog_query->have_posts() ) :
						$kolofon_blog_query->the_post();
						$kolofon_group_year = get_the_date( 'Y' );

						if ( $kolofon_group_year !== $kolofon_current_year ) :
							if ( null !== $kolofon_current_year ) :
								echo '</ul></section>';
							endif;
							// `post-list` alone, not through `post_list_classes()`, since the blog
							// page is a full chronological list where hover previews would be noise:
							// the reader is here to scan an archive, not preview individual posts.
							// Each year group is its own schema.org ItemList; reset the position
							// counter so ListItem positions start at 1 within each year.
							$GLOBALS['kolofon_list_position'] = 0;
							echo '<section class="year-group" itemscope="itemscope" itemtype="https://schema.org/ItemList"><h2 class="year-heading" itemprop="name">' . esc_html( $kolofon_group_year ) . '</h2><ul class="post-list">';
							$kolofon_current_year = $kolofon_group_year;
						endif;
						\Kolofon\post_list_item( array( 'date_format' => 'M j' ) );
					endwhile;
					echo '</ul></section>';
					wp_reset_postdata();
				else :
					?>
					<p><?php esc_html_e( 'No posts yet.', 'kolofon' ); ?></p>
					<?php
				endif;
				?>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
