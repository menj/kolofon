<?php
/**
 * The 404 template.
 *
 * Structured as a proper page rather than a fallback: a large decorative
 * numeric backdrop, a heading, a description, and two paths forward (search
 * and home). The design vocabulary matches the theme's editorial identity —
 * the numeric backdrop is set in the heading font at display scale, palette
 * colours only, no decorative chrome that would date the artwork.
 *
 * @package Kolofon
 */

get_header();
?>
<main itemprop="mainContentOfPage" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
	<article class="page-index is-404">
		<div class="container">
			<div class="not-found">
				<div class="not-found-backdrop" aria-hidden="true">404</div>

				<div class="not-found-body">
					<h1 class="not-found-title" itemprop="headline"><?php esc_html_e( 'Page not found', 'kolofon' ); ?></h1>
					<p class="not-found-description">
						<?php esc_html_e( 'The page you requested does not exist, or has been moved.', 'kolofon' ); ?>
					</p>

					<div class="not-found-actions">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="not-found-home">
							<?php esc_html_e( 'Return home', 'kolofon' ); ?>
						</a>
					</div>

					<div class="not-found-search">
						<p class="not-found-search-label"><?php esc_html_e( 'Or search for something', 'kolofon' ); ?></p>
						<?php get_search_form(); ?>
					</div>
				</div>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
