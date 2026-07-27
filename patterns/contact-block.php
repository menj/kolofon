<?php
/**
 * Title: Contact Block
 * Slug: kolofon/contact-block
 * Categories: kolofon
 * Description: A closing block with a line of prose and a protected email address.
 *
 * @package Kolofon
 */

?>
<!-- wp:group {"tagName":"section","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<section class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:separator {"className":"is-style-kolofon-hairline"} -->
	<hr class="wp-block-separator is-style-kolofon-hairline"/>
	<!-- /wp:separator -->

	<!-- wp:heading {"level":2,"fontSize":"large"} -->
	<h2 class="wp-block-heading has-large-font-size"><?php echo esc_html__( 'Get in touch', 'kolofon' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p><?php echo esc_html__( 'Corrections, disagreements, and commissions all welcome.', 'kolofon' ); ?> [kolofon_email text="<?php echo esc_attr__( 'Write to me', 'kolofon' ); ?>"]</p>
	<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
