<?php
/**
 * Title: Bio Card
 * Slug: menj-bio/bio-card
 * Categories: menj-bio
 * Description: Portrait, name, one-line description, and a short bio paragraph.
 */
?>
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
	<!-- wp:column {"verticalAlignment":"center","width":"120px"} -->
	<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:120px">
		<!-- wp:image {"sizeSlug":"large","style":{"border":{"radius":"50%"}}} -->
		<figure class="wp-block-image size-large has-custom-border">
			<img src="" alt="" style="border-radius:50%"/>
		</figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"verticalAlignment":"center"} -->
	<div class="wp-block-column is-vertically-aligned-center">
		<!-- wp:heading {"level":2} -->
		<h2 class="wp-block-heading"><?php echo esc_html__( 'Your name here', 'menj-bio' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textColor":"muted"} -->
		<p class="has-muted-color has-text-color"><?php echo esc_html__( 'A short one-line description of what you do.', 'menj-bio' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph -->
		<p><?php echo esc_html__( 'Write a longer paragraph here. Keep it factual, direct, and free of filler.', 'menj-bio' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->
