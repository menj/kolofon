<?php
/**
 * Title: Callout
 * Slug: kolofon/callout
 * Categories: kolofon
 * Description: A short aside set off with the accent colour.
 */
?>
<!-- wp:group {"tagName":"aside","className":"menj-callout","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}}} -->
<aside class="wp-block-group menj-callout" style="padding:var(--wp--preset--spacing--40)">
	<!-- wp:paragraph {"fontSize":"small","textColor":"accent"} -->
	<p class="has-accent-color has-text-color has-small-font-size"><strong><?php echo esc_html__( 'Note', 'kolofon' ); ?></strong></p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p><?php echo esc_html__( 'Replace this with the point worth setting apart from the surrounding argument.', 'kolofon' ); ?></p>
	<!-- /wp:paragraph -->
</aside>
<!-- /wp:group -->
