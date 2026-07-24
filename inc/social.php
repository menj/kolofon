<?php
/**
 * Social platforms: registry, URL normaliser, and front-end icon renderer.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

/**
 * Return the ordered list of supported platforms with metadata and SVG paths.
 *
 * Paths are 24x24 viewBox, single-fill, using currentColor so the palette drives colour.
 *
 * @return array
 */
function get_social_platforms() {
	$platforms = array(
		'mastodon'  => array(
			'label' => __( 'Mastodon', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M21.58 13.913c-.29 1.469-2.592 3.121-5.238 3.396-1.379.166-2.737.317-4.185.257-2.368-.09-4.236-.535-4.236-.535 0 .234.014.457.043.665.316 2.363 2.4 2.505 4.374 2.57 1.992.067 3.766-.472 3.766-.472l.082 1.75s-1.395.72-3.878.856c-1.37.075-3.07-.032-5.05-.548-4.294-1.116-5.032-5.564-5.145-10.075-.035-1.34-.014-2.604-.014-3.662 0-4.612 3.121-5.964 3.121-5.964 1.575-.706 4.276-1.003 7.084-1.026h.069c2.808.023 5.51.32 7.085 1.026 0 0 3.12 1.352 3.12 5.964 0 0 .04 3.402-.42 5.798M18.372 7.937v5.428h-2.183V8.093c0-1.099-.474-1.657-1.42-1.657-1.045 0-1.569.657-1.569 1.958v2.836h-2.171V8.394c0-1.301-.524-1.958-1.57-1.958-.946 0-1.42.558-1.42 1.657v5.272H5.856V7.937c0-1.098.286-1.97.861-2.617.593-.647 1.37-.98 2.335-.98 1.117 0 1.962.418 2.522 1.253l.544.892.544-.892c.56-.835 1.405-1.253 2.522-1.253.965 0 1.742.333 2.335.98.575.647.853 1.519.853 2.617z"/>',
		),
		'x'         => array(
			'label' => __( 'X', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>',
		),
		'linkedin'  => array(
			'label' => __( 'LinkedIn', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.063 2.063 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>',
		),
		'github'    => array(
			'label' => __( 'GitHub', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>',
		),
		'youtube'   => array(
			'label' => __( 'YouTube', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
		),
		'instagram' => array(
			'label' => __( 'Instagram', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M12 2.163c3.204 0 3.584.012 4.849.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.849.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>',
		),
		'facebook'  => array(
			'label' => __( 'Facebook', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
		),
		'threads'   => array(
			'label' => __( 'Threads', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M17.688 10.708c-.091-.043-.184-.086-.279-.126-.163-3.014-1.81-4.74-4.576-4.757H12.8c-1.655 0-3.032.706-3.881 1.994l1.523 1.045c.634-.962 1.629-1.167 2.359-1.167h.026c.909.006 1.594.271 2.037.788.322.377.537.898.643 1.554-.796-.135-1.656-.176-2.575-.124-2.586.148-4.248 1.657-4.137 3.751.057 1.062.586 1.976 1.49 2.574.766.506 1.751.753 2.775.7 1.352-.074 2.412-.59 3.152-1.532.562-.716.916-1.643 1.074-2.811.65.393 1.132.91 1.399 1.532.454 1.058.48 2.797-.937 4.213-1.24 1.24-2.732 1.777-4.983 1.793-2.494-.019-4.381-.818-5.606-2.376-1.148-1.46-1.742-3.57-1.764-6.271.022-2.7.616-4.811 1.764-6.271 1.225-1.558 3.112-2.357 5.606-2.376 2.513.019 4.432.821 5.706 2.386.625.767 1.096 1.732 1.407 2.855l1.808-.482c-.376-1.383-.966-2.575-1.766-3.557C17.128 1.303 14.712.263 11.744.24h-.023c-2.962.023-5.324 1.068-7.02 3.106C3.171 5.16 2.401 7.68 2.375 10.649v.014c.026 2.968.796 5.489 2.288 7.302 1.694 2.039 4.056 3.084 7.02 3.107h.023c2.634-.023 4.494-.72 6.024-2.263 2.001-2.018 1.94-4.547 1.28-6.109-.472-1.107-1.377-2.007-2.611-2.599-.234-.109-.481-.213-.712-.293zm-4.761 4.66c-1.13.064-2.303-.443-2.359-1.508-.043-.792.558-1.68 2.417-1.786.213-.011.421-.017.626-.017.66 0 1.284.064 1.85.187-.216 2.583-1.415 3.055-2.534 3.124z"/>',
		),
		'pinterest' => array(
			'label' => __( 'Pinterest', 'menj-bio' ),
			'rel'   => 'me',
			'svg'   => '<path fill="currentColor" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>',
		),
		'email'     => array(
			'label' => __( 'Email', 'menj-bio' ),
			'rel'   => '',
			'svg'   => '<path fill="currentColor" d="M22 4H2C.9 4 0 4.9 0 6v12c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 4-10 6L2 8V6l10 6 10-6Z"/>',
		),
		'rss'       => array(
			'label' => __( 'RSS', 'menj-bio' ),
			'rel'   => '',
			'svg'   => '<path fill="currentColor" d="M6.503 20.752c0 1.834-1.507 3.331-3.375 3.331C1.257 24.083 0 22.834 0 20.752c0-1.831 1.257-3.331 3.128-3.331 1.868 0 3.375 1.5 3.375 3.331zM24 24h-4.376c0-10.771-8.752-19.521-19.624-19.521V0C13.328 0 24 10.669 24 24zm-10.377 0h-4.242c0-6.243-4.855-11.111-10.881-11.111V8.884c8.196 0 15.123 6.845 15.123 15.116z"/>',
		),
	);

	/**
	 * Filter the social platforms offered.
	 *
	 * Each entry needs a label, a rel value, and an inline SVG path using
	 * currentColor. Adding one here also adds its option, field, and sanitiser,
	 * because all three derive from this list. Add a matching default through
	 * `menj_bio_defaults` so the key exists before anything reads it.
	 *
	 * @param array $platforms Slug to platform definition.
	 */
	return apply_filters( 'menj_bio_social_platforms', $platforms );
}

/**
 * Return the option key for a given platform slug.
 *
 * @param string $slug Platform slug.
 * @return string
 */
function social_key( $slug ) {
	return 'social_' . $slug;
}

/**
 * Normalise a raw social URL. Treats bare emails as mailto:.
 *
 * @param string $raw Raw input.
 * @return string
 */
function normalise_social_url( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( is_email( $raw ) ) {
		return 'mailto:' . sanitize_email( $raw );
	}
	return esc_url_raw( $raw );
}

/**
 * Platforms with a stored URL, in registry order.
 *
 * @return array<string, array> Slug to platform meta plus `url`.
 */
function get_active_social_links() {
	$active = array();

	foreach ( get_social_platforms() as $slug => $meta ) {
		$url = opt( social_key( $slug ) );
		if ( ! empty( $url ) ) {
			$active[ $slug ] = array( 'url' => $url ) + $meta;
		}
	}

	return $active;
}

/**
 * Render the "Find me on:" line with icons for each populated platform.
 * Prints nothing if no platform URLs are set.
 */
function render_social_icons() {
	$active = get_active_social_links();

	if ( empty( $active ) ) {
		return;
	}

	echo '<p class="hero-social"><span class="hero-social-label">' . esc_html__( 'Find me on:', 'menj-bio' ) . '</span>';
	echo '<span class="hero-social-icons">';

	foreach ( $active as $slug => $meta ) {
		$rel  = ! empty( $meta['rel'] ) ? ' rel="' . esc_attr( $meta['rel'] ) . '"' : '';
		$icon = sprintf(
			'<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">%s</svg>',
			$meta['svg'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal SVG markup.
		);

		// The email address is never emitted as a plain mailto: link.
		if ( 'email' === $slug ) {
			echo protected_mailto( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- builder escapes internally.
				$meta['url'],
				$icon,
				array(
					'class'      => 'hero-social-link mb-email',
					'aria-label' => $meta['label'],
					'title'      => $meta['label'],
				)
			);
			continue;
		}

		printf(
			'<a class="hero-social-link" href="%1$s" aria-label="%2$s" title="%2$s"%3$s>%4$s</a>',
			esc_url( $meta['url'] ),
			esc_attr( $meta['label'] ),
			$rel, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
			$icon // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above.
		);
	}

	echo '</span></p>';
}
