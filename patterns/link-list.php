<?php
/**
 * Title: Link List
 * Slug: kolofon/link-list
 * Categories: kolofon
 * Description: A titled list of outbound links with short descriptions.
 */
?>
<!-- wp:group {"tagName":"section"} -->
<section class="wp-block-group">
	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading"><?php echo esc_html__( 'Elsewhere', 'kolofon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:list -->
	<ul class="wp-block-list">
		<!-- wp:list-item -->
		<li><a href="#"><?php echo esc_html__( 'First link', 'kolofon' ); ?></a> — <?php echo esc_html__( 'what it is, in one clause.', 'kolofon' ); ?></li>
		<!-- /wp:list-item -->
		<!-- wp:list-item -->
		<li><a href="#"><?php echo esc_html__( 'Second link', 'kolofon' ); ?></a> — <?php echo esc_html__( 'what it is, in one clause.', 'kolofon' ); ?></li>
		<!-- /wp:list-item -->
	</ul>
	<!-- /wp:list -->
</section>
<!-- /wp:group -->
