<?php
/**
 * Fallback template for archives, search, and any query without a more specific template.
 *
 * @package Kolofon
 */

get_header();

global $wp_query;
?>
<main itemprop="mainContentOfPage" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
	<article class="page-index" itemscope="itemscope" itemtype="https://schema.org/CollectionPage">
		<div class="container">
			<header class="content-header">
				<?php
				if ( is_search() ) {
					printf(
						'<h1 class="post-title" itemprop="name">%s <span class="term term-query">%s</span></h1>',
						esc_html__( 'Search results for:', 'kolofon' ),
						esc_html( get_search_query() )
					);
				} elseif ( is_category() ) {
					printf(
						'<h1 class="post-title" itemprop="name">%s <span class="term">%s</span></h1>',
						esc_html__( 'Posts in:', 'kolofon' ),
						esc_html( single_cat_title( '', false ) )
					);
				} elseif ( is_tag() ) {
					printf(
						'<h1 class="post-title" itemprop="name">%s <span class="term">%s</span></h1>',
						esc_html__( 'Posts tagged:', 'kolofon' ),
						esc_html( single_tag_title( '', false ) )
					);
				} elseif ( is_post_type_archive( \Kolofon\status_post_type() ) ) {
					printf(
						'<h1 class="post-title" itemprop="name"><span class="term">%s</span></h1>',
						esc_html__( 'Statuses', 'kolofon' )
					);
				} elseif ( is_archive() ) {
					/*
					 * get_the_archive_title() returns markup: WordPress wraps the
					 * name in its own span. Escaping it printed the tags as
					 * literal text, so the label is stripped and re-wrapped in the
					 * theme's own span to match the category and tag headings.
					 */
					printf(
						'<h1 class="post-title" itemprop="name"><span class="term">%s</span></h1>',
						esc_html( wp_strip_all_tags( get_the_archive_title() ) )
					);
				} else {
					printf( '<h1 class="post-title" itemprop="name">%s</h1>', esc_html__( 'Posts', 'kolofon' ) );
				}
				?>
				<p class="description" itemprop="description">
					<?php
					printf(
						/* translators: %d: post count */
						esc_html( _n( '%d post found.', '%d posts found.', intval( $wp_query->found_posts ), 'kolofon' ) ),
						intval( $wp_query->found_posts )
					);
					?>
				</p>
				<?php
				if ( is_tag() ) {
					\Kolofon\render_tag_sections();
				}
				?>
			</header>

			<?php
			if ( \Kolofon\display_rule( 'section_chooser' ) && ( is_category() || is_home() ) ) {
				\Kolofon\render_section_chooser( is_category() ? (int) get_queried_object_id() : 0 );
			}
			?>

			<div class="content">
				<?php if ( have_posts() ) : ?>
					<ul class="<?php echo esc_attr( \Kolofon\post_list_classes() ); ?>" itemprop="mainEntity" itemscope="itemscope" itemtype="https://schema.org/ItemList">
						<?php
						while ( have_posts() ) :
							the_post();
							\Kolofon\post_list_item();
						endwhile;
						?>
					</ul>
					<?php \Kolofon\render_pagination(); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'Nothing found.', 'kolofon' ); ?></p>
					<?php get_search_form(); ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
