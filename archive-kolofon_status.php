<?php
/**
 * Status archive.
 *
 * Mirrors the blog page rather than the generic archive: a clean heading, the
 * count set off by hairlines, and entries grouped by year. The generic archive
 * header puts the count inline and prefixes the title with "Archives:", which
 * reads as a taxonomy listing rather than a timeline.
 *
 * The filename must use the post type slug exactly, so it keeps the underscore
 * that WordPress mandates rather than the theme's usual kebab-case.
 *
 * @package Kolofon
 */

get_header();

$kolofon_status_query = $GLOBALS['wp_query'];
?>
<main id="main" class="site-main" itemscope="itemscope" itemtype="https://schema.org/CollectionPage">
	<div class="container">
		<header class="content-header">
			<h1 class="post-title" itemprop="name"><?php esc_html_e( 'Statuses', 'kolofon' ); ?></h1>
			<?php
			$kolofon_status_desc = get_the_post_type_description();
			if ( $kolofon_status_desc ) :
				?>
				<div class="description" itemprop="description"><?php echo wp_kses_post( $kolofon_status_desc ); ?></div>
			<?php endif; ?>
		</header>

		<div class="content-meta">
			<p class="post-count">
				<?php
				printf(
					/* translators: %d: status count */
					esc_html( _n( '%d status', '%d statuses', intval( $kolofon_status_query->found_posts ), 'kolofon' ) ),
					intval( $kolofon_status_query->found_posts )
				);
				?>
			</p>
		</div>

		<div class="content">
			<?php
			if ( have_posts() ) :
				$kolofon_current_year = null;

				while ( have_posts() ) :
					the_post();
					$kolofon_group_year = get_the_date( 'Y' );

					if ( $kolofon_group_year !== $kolofon_current_year ) :
						if ( null !== $kolofon_current_year ) :
							echo '</ul></section>';
						endif;

						// Each year group is its own ItemList, so ListItem
						// positions restart at 1 within the year.
						$GLOBALS['kolofon_list_position'] = 0;
						printf(
							'<section class="year-group" itemscope="itemscope" itemtype="https://schema.org/ItemList"><h2 class="year-heading" itemprop="name">%s</h2><ul class="post-list is-statuses">',
							esc_html( $kolofon_group_year )
						);
						$kolofon_current_year = $kolofon_group_year;
					endif;

					\Kolofon\post_list_item( array( 'date_format' => 'M j' ) );
				endwhile;

				echo '</ul></section>';

				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => esc_html__( 'Newer', 'kolofon' ),
						'next_text' => esc_html__( 'Older', 'kolofon' ),
					)
				);
			else :
				?>
				<p><?php esc_html_e( 'No statuses yet.', 'kolofon' ); ?></p>
				<?php
			endif;
			?>
		</div>
	</div>
</main>
<?php
get_footer();
