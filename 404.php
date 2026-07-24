<?php
/**
 * The 404 template.
 *
 * @package Kolofon
 */

get_header();
?>
<main>
	<article class="page-index">
		<div class="container">
			<header class="content-header">
				<h1 class="post-title"><?php esc_html_e( 'Not found', 'kolofon' ); ?></h1>
				<p class="description"><?php esc_html_e( 'The page you requested does not exist.', 'kolofon' ); ?></p>
			</header>
			<div class="content">
				<?php get_search_form(); ?>
				<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'kolofon' ); ?></a></p>
			</div>
		</div>
	</article>
</main>
<?php
get_footer();
