<?php
/**
 * Title: Contact Block
 * Slug: menj-bio/contact-block
 * Categories: menj-bio
 * Description: A closing block with a line of prose and a protected email address.
 */
?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<section class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:separator {"className":"is-style-menj-bio-hairline"} -->
	<hr class="wp-block-separator is-style-menj-bio-hairline"/>
	<!-- /wp:separator -->

	<!-- wp:heading {"level":2,"fontSize":"large"} -->
	<h2 class="wp-block-heading has-large-font-size"><?php echo esc_html__( 'Get in touch', 'menj-bio' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php echo esc_html__( 'Corrections, disagreements, and commissions all welcome.', 'menj-bio' ); ?> [menj_email text="<?php echo esc_attr__( 'Write to me', 'menj-bio' ); ?>"]</p>
	<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
