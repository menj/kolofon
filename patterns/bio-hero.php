<?php
/**
 * Title: Bio Hero
 * Slug: kolofon/bio-hero
 * Categories: kolofon
 * Description: Two-column hero with intro copy, native social icons, and a circular portrait.
 *
 * @package Kolofon
 */

?>
<!-- wp:columns {"verticalAlignment":"top","align":"wide"} -->
<div class="wp-block-columns alignwide are-vertically-aligned-top">
	<!-- wp:column {"verticalAlignment":"top","width":"66.66%"} -->
	<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:66.66%">
		<!-- wp:heading {"level":1,"fontSize":"x-large"} -->
		<h1 class="wp-block-heading has-x-large-font-size"><?php echo esc_html__( "Hi, I'm Mohd Elfie", 'kolofon' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"fontSize":"large"} -->
		<p class="has-large-font-size"><?php echo esc_html__( 'Writer, apologist, and developer. Replace this paragraph with your own introduction.', 'kolofon' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:social-links {"iconColor":"muted","iconColorValue":"var(--k-muted)","openInNewTab":true} -->
		<ul class="wp-block-social-links has-icon-color">
			<!-- wp:social-link {"url":"#","service":"mastodon"} /-->
			<!-- wp:social-link {"url":"#","service":"github"} /-->
			<!-- wp:social-link {"url":"#","service":"mail"} /-->
		</ul>
		<!-- /wp:social-links -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"top","width":"33.33%"} -->
	<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:33.33%">
		<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"50%"}}} -->
		<figure class="wp-block-image size-large has-custom-border">
			<img src="" alt="" style="border-radius:50%" />
		</figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
