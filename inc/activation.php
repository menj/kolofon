<?php
/**
 * Theme activation.
 *
 * Runs once when the theme is switched in. Provisions the blog index page so
 * "View all" and the section chooser have somewhere real to point on a fresh
 * install, instead of the site owner having to know to create a page and
 * assign the Blog Index template.
 *
 * Kept deliberately narrow. The theme does not touch the Settings > Reading
 * options: it does not set the created page as the Posts page, since that
 * interacts with the Front page setting and would surprise anyone with a
 * custom home page. `get_blog_index_url()` finds a page by template as its
 * second fallback, which is enough.
 *
 * Idempotent by construction: existence is checked by template, so a page
 * that was renamed or moved is still recognised, and a deleted page is
 * recreated on the next activation.
 *
 * @package MENJ\Bio
 * @since   2.6.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', __NAMESPACE__ . '\\ensure_blog_index_page' );

/**
 * Provision the blog index page if none exists.
 *
 * "Exists" means a published page carrying the `page-blog.php` template. A
 * page-with-slug-blog under a different template does not count, since it is
 * not the blog listing the theme is looking for.
 *
 * @return int Page ID of the resolved or created page, or 0 on failure.
 */
function ensure_blog_index_page() {
	// Respect an existing Settings > Reading choice. If the site owner already
	// picked a Posts page, that IS the blog index for their site, and the
	// theme should not create a competing page. `get_blog_index_url()` will
	// route through their choice anyway.
	if ( (int) get_option( 'page_for_posts' ) ) {
		return (int) get_option( 'page_for_posts' );
	}

	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'page-blog.php',     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	// Slug clash: if a page already exists at /blog under a different template,
	// WordPress appends `-2` on insert. Log so the site owner sees it in the
	// error log rather than silently ending up with two pages named Blog. The
	// admin notice route would be nicer, but requires a transient dance for a
	// notice that only ever fires on activation.
	$page_id = wp_insert_post(
		array(
			'post_title'     => __( 'Blog', 'menj-bio' ),
			'post_name'      => 'blog',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => array(
				'_wp_page_template' => 'page-blog.php',
			),
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		error_log( sprintf( '[menj-bio] Could not create blog index page: %s', $page_id->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return 0;
	}

	$post = get_post( $page_id );
	if ( $post && 'blog' !== $post->post_name ) {
		error_log( sprintf( '[menj-bio] Blog index page created at slug "%s" because "/blog" was taken. Rename or remove the other page if desired.', $post->post_name ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	return (int) $page_id;
}
