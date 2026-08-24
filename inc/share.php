<?php
/**
 * Per-post social sharing: target registry, URL builder, and renderer.
 *
 * Distinct from inc/social.php's profile icon row. That row links to the
 * site owner's own accounts and feeds `sameAs`; this row links out to a
 * share-intent URL for the post currently being read, and carries no
 * personal data of its own.
 *
 * @package Kolofon
 * @since   7.4.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

/**
 * Return the ordered list of share targets.
 *
 * Deliberately a curated subset of the full platform registry in
 * inc/social.php: only platforms with a stable, unauthenticated share-intent
 * URL scheme belong here. Icons are looked up from get_social_platforms() by
 * matching slug, so a target only renders if that platform is also in the
 * icon registry — adding a target here with no matching slug there is a
 * silent no-op rather than a broken icon.
 *
 * @return array<string, array{label: string}>
 */
function get_share_targets() {
	$targets = array(
		'x'        => array( 'label' => __( 'Share on X', 'kolofon' ) ),
		'facebook' => array( 'label' => __( 'Share on Facebook', 'kolofon' ) ),
		'linkedin' => array( 'label' => __( 'Share on LinkedIn', 'kolofon' ) ),
		'bluesky'  => array( 'label' => __( 'Share on Bluesky', 'kolofon' ) ),
		'reddit'   => array( 'label' => __( 'Share on Reddit', 'kolofon' ) ),
		'telegram' => array( 'label' => __( 'Share on Telegram', 'kolofon' ) ),
		'whatsapp' => array( 'label' => __( 'Share on WhatsApp', 'kolofon' ) ),
		'email'    => array( 'label' => __( 'Share by email', 'kolofon' ) ),
	);

	/**
	 * Filter the share targets offered.
	 *
	 * Each entry needs a label; the slug must also exist in
	 * get_social_platforms() for its icon, and build_share_url() must know
	 * how to build a URL for it (extend via `kolofon_share_url` for a slug
	 * this function does not already handle).
	 *
	 * @param array $targets Slug to target definition.
	 */
	return apply_filters( 'kolofon_share_targets', $targets );
}

/**
 * Build the share-intent URL for one target.
 *
 * @param string $slug  Share target slug.
 * @param string $url   The post's canonical URL, already absolute.
 * @param string $title The post's plain-text title.
 * @return string Empty string if the slug is unrecognised and no filter
 *                supplies one.
 */
function build_share_url( $slug, $url, $title ) {
	$enc_url   = rawurlencode( $url );
	$enc_title = rawurlencode( $title );

	switch ( $slug ) {
		case 'x':
			$share = "https://twitter.com/intent/tweet?text={$enc_title}&url={$enc_url}";
			break;
		case 'facebook':
			$share = "https://www.facebook.com/sharer/sharer.php?u={$enc_url}";
			break;
		case 'linkedin':
			$share = "https://www.linkedin.com/sharing/share-offsite/?url={$enc_url}";
			break;
		case 'bluesky':
			$share = 'https://bsky.app/intent/compose?text=' . rawurlencode( $title . ' ' . $url );
			break;
		case 'reddit':
			$share = "https://www.reddit.com/submit?url={$enc_url}&title={$enc_title}";
			break;
		case 'telegram':
			$share = "https://t.me/share/url?url={$enc_url}&text={$enc_title}";
			break;
		case 'whatsapp':
			// api.whatsapp.com works on both desktop and mobile without an
			// app-store redirect; wa.me needs a phone number in the path.
			$share = 'https://api.whatsapp.com/send?text=' . rawurlencode( $title . ' ' . $url );
			break;
		case 'email':
			$share = 'mailto:?subject=' . $enc_title . '&body=' . $enc_url;
			break;
		default:
			$share = '';
	}

	/**
	 * Filter or supply the share URL for a target.
	 *
	 * Runs even for slugs handled above, so a filter can override one of the
	 * built-in schemes as well as add a new one.
	 *
	 * @param string $share URL built above, or '' for an unrecognised slug.
	 * @param string $slug  Share target slug.
	 * @param string $url   The post's canonical URL.
	 * @param string $title The post's plain-text title.
	 */
	return apply_filters( 'kolofon_share_url', $share, $slug, $url, $title );
}

/**
 * Render the per-post sharing row: one link per configured target, plus a
 * "copy link" button. Prints nothing if sharing is switched off, if there is
 * no current post, or if every target resolved to an empty URL.
 *
 * @param int|null $post_id Defaults to the post in the current loop.
 */
function render_post_share( $post_id = null ) {
	if ( ! opt( 'post_sharing_enabled' ) ) {
		return;
	}

	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$url   = get_permalink( $post_id );
	$title = wp_strip_all_tags( get_the_title( $post_id ) );

	if ( ! $url ) {
		return;
	}

	$platforms = get_social_platforms();
	$targets   = get_share_targets();
	$rendered  = 0;

	ob_start();
	foreach ( $targets as $slug => $meta ) {
		// The icon registry is the source of truth for what glyph exists; a
		// share target with no matching icon renders nothing rather than a
		// broken image, so a filter can add a target ahead of its icon.
		if ( empty( $platforms[ $slug ] ) ) {
			continue;
		}

		$href = build_share_url( $slug, $url, $title );
		if ( '' === $href ) {
			continue;
		}

		$is_email = ( 'email' === $slug );
		$target   = $is_email ? '' : ' target="_blank" rel="noopener noreferrer"';

		printf(
			'<li><a class="hero-social-link post-share-link" href="%1$s" aria-label="%2$s" title="%2$s"%3$s>%4$s</a></li>',
			esc_url( $href ),
			esc_attr( $meta['label'] ),
			$target, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal attribute string, no user input.
			platform_icon_svg( $platforms[ $slug ] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal SVG markup.
		);
		++$rendered;
	}
	$links = ob_get_clean();

	if ( ! $rendered ) {
		return;
	}

	?>
	<div class="post-share">
		<span class="post-share-label"><?php esc_html_e( 'Share:', 'kolofon' ); ?></span>
		<ul class="post-share-list">
			<?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped above. ?>
			<li>
				<button
					type="button"
					class="hero-social-link post-share-link post-share-copy"
					data-share-url="<?php echo esc_url( $url ); ?>"
					data-copied-label="<?php esc_attr_e( 'Link copied', 'kolofon' ); ?>"
					aria-label="<?php esc_attr_e( 'Copy link', 'kolofon' ); ?>"
					title="<?php esc_attr_e( 'Copy link', 'kolofon' ); ?>"
				>
					<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
						<path fill="currentColor" d="M17 7h-4v2h4a3 3 0 0 1 0 6h-4v2h4a5 5 0 0 0 0-10zm-10 8H3a3 3 0 0 1 0-6h4V7H3a5 5 0 0 0 0 10h4zM8 11h8v2H8z"/>
					</svg>
				</button>
			</li>
		</ul>
		<span class="post-share-status" role="status" aria-live="polite"></span>
	</div>
	<?php
}
