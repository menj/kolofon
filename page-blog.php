<?php
/**
 * Template Name: Blog Index
 *
 * Chronological blog list, grouped by year.
 *
 * @package MENJ\Bio
 */

get_header();
?>
<main>
	<article class="page-index">
		<div class="container">
			<header class="content-header">
				<?php the_title( '<h1 class="post-title">', '</h1>' ); ?>
				<?php if ( get_the_content() ) : ?>
					<div class="description"><?php the_content(); ?></div>
				<?php endif; ?>
			</header>

			<?php
			$blog_query = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => -1,
					'no_found_rows'  => true,
				)
			);

			$sections = \MENJ\Bio\get_sections();
			?>

			<div class="content-meta">
				<p class="post-count">
					<?php
					printf(
						/* translators: %d: post count */
						esc_html( _n( '%d post', '%d posts', $blog_query->found_posts, 'menj-bio' ) ),
						intval( $blog_query->found_posts )
					);
					?>
				</p>
				<?php if ( ! empty( $sections ) ) : ?>
					<nav class="content-filter" aria-label="<?php esc_attr_e( 'Filter by section', 'menj-bio' ); ?>">
						<span class="content-filter-label"><?php esc_html_e( 'Sections:', 'menj-bio' ); ?></span>
						<a class="content-filter-link is-current" href="<?php echo esc_url( \MENJ\Bio\get_blog_index_url() ); ?>"><?php esc_html_e( 'All', 'menj-bio' ); ?></a>
						<?php foreach ( $sections as $section ) : ?>
							<a class="content-filter-link" href="<?php echo esc_url( get_category_link( $section ) ); ?>">
								<?php echo esc_html( $section->name ); ?>
							</a>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</div>

			<div class="content">
				<?php

				if ( $blog_query->have_posts() ) :
					$current_year = null;
					while ( $blog_query->have_posts() ) :
						$blog_query->the_post();
						$year = get_the_date( 'Y' );

						if ( $year !== $current_year ) :
							if ( null !== $current_year ) :
								echo '</ul></section>';
							endif;
							echo '<section class="year-group"><h2 class="year-heading">' . esc_html( $year ) . '</h2><ul class="' . esc_attr( \MENJ\Bio\post_list_classes( $blog_query ) ) . '">';
							$current_year = $year;
						endif;
						\MENJ\Bio\post_list_item( array( 'date_format' => 'M j' ) );
					endwhile;
					echo '</ul></section>';
					wp_reset_postdata();
				else :
					?>
					<p><?php esc_html_e( 'No posts yet.', 'menj-bio' ); ?></p>
					<?php
				endif;
				?>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
