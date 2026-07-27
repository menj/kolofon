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
	<main itemprop="mainEntity" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'h-entry' ); ?> itemscope="itemscope" itemtype="https://schema.org/BlogPosting">
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
						$kolofon_primary_section = \Kolofon\get_primary_section( get_the_ID() );
						if ( $kolofon_primary_section ) :
							$kolofon_section_desc = term_description( $kolofon_primary_section->term_id );
							?>
							<div class="section-eyebrow">
								<a class="section-eyebrow-name" href="<?php echo esc_url( get_category_link( $kolofon_primary_section ) ); ?>" itemprop="articleSection">
									<?php echo esc_html( $kolofon_primary_section->name ); ?>
								</a>
								<?php if ( $kolofon_section_desc ) : ?>
									<div class="section-eyebrow-desc"><?php echo wp_kses_post( $kolofon_section_desc ); ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<div class="meta">
							<time class="dt-published" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>" itemprop="datePublished">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
							<meta itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>" />
							<meta itemprop="author" content="<?php echo esc_attr( wp_strip_all_tags( \Kolofon\opt( 'hero_heading' ) ) ); ?>" />
							<meta itemprop="mainEntityOfPage" content="<?php the_permalink(); ?>" />
						</div>
					<?php endif; ?>

					<?php the_title( '<h1 class="post-title p-name" itemprop="name headline">', '</h1>' ); ?>
				</header>

				<div class="content e-content" itemprop="articleBody">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail(
							'large',
							array(
								'class'    => 'featured-image',
								'itemprop' => 'image',
							)
						);
					}
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'kolofon' ),
							'after'  => '</nav>',
						)
					);

					// Tags at the foot of the post rather than at the head.
					// Editorial convention: topical connections follow the
					// content rather than framing it. Uses the shared renderer
					// from inc/tags.php for a single source of truth on markup.
					\Kolofon\render_post_tags();
					?>
				</div>
			</div>
		</article>

		<?php if ( 'post' === get_post_type() ) : ?>
			<nav class="post-nav container" aria-label="<?php esc_attr_e( 'Post navigation', 'kolofon' ); ?>" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
				<?php \Kolofon\render_adjacent_links(); ?>
			</nav>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
