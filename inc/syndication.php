<?php
/**
 * Syndication.
 *
 * Two small things a bio microsite genuinely benefits from and WordPress does
 * not do by default: featured images inside the RSS feed body, so posts read
 * correctly in Feedly/NetNewsWire/Inoreader without a click through, and a
 * fediverse:creator meta tag so a linked Mastodon profile can verify authorship.
 *
 * Both derive from patterns in Chris Wiegman's cwplugin. Menj-bio takes them
 * as theme features rather than a companion plugin because they are structural
 * outputs of the theme's identity, same as the SEO stand-down and the icon set.
 *
 * @package MENJ\Bio
 * @since   2.9.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

add_action( 'rss2_item', __NAMESPACE__ . '\\add_feed_enclosure' );
add_filter( 'the_content_feed', __NAMESPACE__ . '\\prepend_feed_thumbnail' );
add_action( 'wp_head', __NAMESPACE__ . '\\emit_fediverse_creator', 5 );

/**
 * Featured image as an RSS <enclosure> tag on each item.
 *
 * Enclosures are the spec-defined way to attach media to a feed item. Some
 * readers respect them for images, most use them for podcasts. Cheap to emit
 * regardless.
 */
function add_feed_enclosure() {
	global $post;

	if ( ! $post || ! has_post_thumbnail( $post->ID ) ) {
		return;
	}

	$attachment_id = get_post_thumbnail_id( $post->ID );
	$image         = wp_get_attachment_image_src( $attachment_id, 'large' );

	if ( ! $image ) {
		return;
	}

	$file = get_attached_file( $attachment_id );
	$size = ( $file && is_readable( $file ) ) ? filesize( $file ) : 0;
	$type = get_post_mime_type( $attachment_id );

	printf(
		'<enclosure url="%s" length="%d" type="%s" />' . "\n",
		esc_url( $image[0] ),
		esc_attr( (string) $size ),
		esc_attr( $type )
	);
}

/**
 * Prepend the featured image inline to the RSS content.
 *
 * The enclosure covers spec-compliant readers; this covers the many that only
 * render the item body. Between the two, the image shows up somewhere.
 *
 * Strips the srcset filter around the render so the emitted markup is a plain
 * `<img>` without responsive sizing hints that would confuse a feed reader.
 *
 * @param string $content Feed item content.
 * @return string
 */
function prepend_feed_thumbnail( $content ) {
	global $post;

	if ( ! $post || ! has_post_thumbnail( $post->ID ) ) {
		return $content;
	}

	add_filter( 'wp_calculate_image_srcset_meta', '__return_null' );

	$thumbnail = get_the_post_thumbnail(
		$post->ID,
		'large',
		array(
			'fetchpriority' => false,
			'decoding'      => false,
			'class'         => 'menj-bio-feed-thumbnail',
			'style'         => 'margin-bottom:1em;height:auto;max-width:100%;',
		)
	);

	remove_filter( 'wp_calculate_image_srcset_meta', '__return_null' );

	return '<div>' . $thumbnail . '</div>' . $content;
}

/**
 * Emit fediverse:creator meta tag if a Mastodon URL is configured.
 *
 * Derives the handle from the URL rather than asking for a separate setting:
 * `https://mastodon.social/@user` becomes `@user@mastodon.social`. That is
 * the format Mastodon expects for verification.
 *
 * Runs only on singular views. Home and archives do not have a single creator
 * to attribute; on those pages the tag would be ambiguous.
 */
function emit_fediverse_creator() {
	if ( ! is_singular() ) {
		return;
	}

	$handle = derive_fediverse_handle( (string) opt( 'social_mastodon' ) );

	if ( '' === $handle ) {
		return;
	}

	printf(
		'<meta name="fediverse:creator" content="%s" />' . "\n",
		esc_attr( $handle )
	);
}

/**
 * Parse a Mastodon profile URL into a fediverse handle.
 *
 * Accepts both `https://instance.tld/@user` and `https://instance.tld/users/user`
 * shapes, since both appear in the wild. Returns an empty string if the URL
 * is missing a host or a recognisable username segment.
 *
 * @param string $url Mastodon profile URL.
 * @return string Handle in `@user@instance.tld` form, or empty string.
 */
function derive_fediverse_handle( $url ) {
	if ( '' === $url ) {
		return '';
	}

	$parts = wp_parse_url( $url );

	if ( empty( $parts['host'] ) || empty( $parts['path'] ) ) {
		return '';
	}

	$path = trim( $parts['path'], '/' );

	// Two shapes: `@user` or `users/user`.
	if ( preg_match( '#^@([A-Za-z0-9_]+)$#', $path, $matches ) ) {
		$user = $matches[1];
	} elseif ( preg_match( '#^users/([A-Za-z0-9_]+)$#', $path, $matches ) ) {
		$user = $matches[1];
	} else {
		return '';
	}

	return '@' . $user . '@' . $parts['host'];
}
