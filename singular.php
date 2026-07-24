<?php
/**
 * Template for individual posts and pages.
 *
 * @package Kolofon
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'h-entry' ); ?> itemscope itemtype="http://schema.org/BlogPosting">
			<div class="container">
				<header class="content-header">
					<?php if ( 'post' === get_post_type() ) : ?>
						<?php
						// Section eyebrow: the primary section this post lives
						// in, with the category description underneath if one
						// is set. Reinforces which section the reader is in
						// before they read the title, which is otherwise the
						// only anchor for a post arrived at from search or a
						// social share.
						$primary_section = \Kolofon\get_primary_section( get_the_ID() );
						if ( $primary_section ) :
							$section_desc = term_description( $primary_section->term_id, 'category' );
							?>
							<div class="section-eyebrow">
								<a class="section-eyebrow-name" href="<?php echo esc_url( get_category_link( $primary_section ) ); ?>">
									<?php echo esc_html( $primary_section->name ); ?>
								</a>
								<?php if ( $section_desc ) : ?>
									<div class="section-eyebrow-desc"><?php echo wp_kses_post( $section_desc ); ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="meta">
							<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
						</div>
					<?php endif; ?>

					<?php the_title( '<h1 class="post-title p-name" itemprop="name headline">', '</h1>' ); ?>

					<?php
					$tags = get_the_tags();
					if ( $tags ) :
						?>
						<ul class="tags">
							<?php foreach ( $tags as $tag ) : ?>
								<li><a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><?php echo esc_html( $tag->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</header>

				<div class="content e-content" itemprop="articleBody">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'large', array( 'class' => 'featured-image' ) );
					}
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'kolofon' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</div>
		</article>

		<?php if ( 'post' === get_post_type() ) : ?>
			<nav class="post-nav container" aria-label="<?php esc_attr_e( 'Post navigation', 'kolofon' ); ?>">
				<?php \Kolofon\render_adjacent_links(); ?>
			</nav>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
