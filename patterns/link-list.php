<?php
/**
 * Title: Link List
 * Slug: menj-bio/link-list
 * Categories: menj-bio
 * Description: A titled list of outbound links with short descriptions.
 */
?>
<!-- wp:group {"tagName":"section"} -->
<section class="wp-block-group">
	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading"><?php echo esc_html__( 'Elsewhere', 'menj-bio' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:list -->
	<ul class="wp-block-list">
		<!-- wp:list-item -->
		<li><a href="#"><?php echo esc_html__( 'First link', 'menj-bio' ); ?></a> — <?php echo esc_html__( 'what it is, in one clause.', 'menj-bio' ); ?></li>
		<!-- /wp:list-item -->
		<!-- wp:list-item -->
		<li><a href="#"><?php echo esc_html__( 'Second link', 'menj-bio' ); ?></a> — <?php echo esc_html__( 'what it is, in one clause.', 'menj-bio' ); ?></li>
		<!-- /wp:list-item -->
	</ul>
	<!-- /wp:list -->
</section>
<!-- /wp:group -->
