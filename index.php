<?php
/**
 * Fallback template for archives, search, and any query without a more specific template.
 *
 * @package MENJ\Bio
 */

get_header();

global $wp_query;
?>
<main>
	<article class="page-index">
		<div class="container">
			<header class="content-header">
				<?php
				if ( is_search() ) {
					printf(
						'<h1 class="post-title">%s <span class="term">%s</span></h1>',
						esc_html__( 'Search results for:', 'menj-bio' ),
						esc_html( get_search_query() )
					);
				} elseif ( is_category() ) {
					printf(
						'<h1 class="post-title">%s <span class="term">%s</span></h1>',
						esc_html__( 'Posts in:', 'menj-bio' ),
						esc_html( single_cat_title( '', false ) )
					);
				} elseif ( is_tag() ) {
					printf(
						'<h1 class="post-title">%s <span class="term">%s</span></h1>',
						esc_html__( 'Posts tagged:', 'menj-bio' ),
						esc_html( single_tag_title( '', false ) )
					);
				} elseif ( is_archive() ) {
					printf( '<h1 class="post-title">%s</h1>', esc_html( get_the_archive_title() ) );
				} else {
					printf( '<h1 class="post-title">%s</h1>', esc_html__( 'Posts', 'menj-bio' ) );
				}
				?>
				<p class="description">
					<?php
					printf(
						/* translators: %d: post count */
						esc_html( _n( '%d post found.', '%d posts found.', intval( $wp_query->found_posts ), 'menj-bio' ) ),
						intval( $wp_query->found_posts )
					);
					?>
				</p>
				<?php
				if ( is_tag() ) {
					\MENJ\Bio\render_tag_sections();
				}
				?>
			</header>

			<?php
			if ( 1 === intval( \MENJ\Bio\opt( 'show_section_chooser' ) ) && ( is_category() || is_home() ) ) {
				\MENJ\Bio\render_section_chooser( is_category() ? (int) get_queried_object_id() : 0 );
			}
			?>

			<div class="content">
				<?php if ( have_posts() ) : ?>
					<ul class="<?php echo esc_attr( \MENJ\Bio\post_list_classes() ); ?>">
						<?php
						while ( have_posts() ) :
							the_post();
							\MENJ\Bio\post_list_item( array( 'show_dek' => true ) );
						endwhile;
						?>
					</ul>
					<?php \MENJ\Bio\render_pagination(); ?>
				<?php else : ?>
					<p><?php esc_html_e( 'Nothing found.', 'menj-bio' ); ?></p>
					<?php get_search_form(); ?>
				<?php endif; ?>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
