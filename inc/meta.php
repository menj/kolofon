<?php
/**
 * Meta tags and structured data.
 *
 * A bio page has one job beyond being read: to be found, and to be represented
 * correctly when its URL is shared. Without this the receiving platform guesses
 * from whatever it can scrape.
 *
 * The whole module stands down when an SEO plugin is active. Emitting a second
 * set of Open Graph tags or a second JSON-LD graph is worse than emitting none,
 * because consumers disagree about which wins. The site this theme was built
 * for runs Rank Math, so shipping without that guard would have produced
 * duplicates on day one.
 *
 * @package Kolofon
 * @since   1.1.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', __NAMESPACE__ . '\\emit_meta_tags', 2 );
add_action( 'wp_head', __NAMESPACE__ . '\\emit_schema', 3 );

/**
 * Known SEO plugins, mapped to the constant or class that proves them active.
 *
 * @return array<string, string>
 */
function get_known_seo_plugins() {
	return array(
		'Yoast SEO'           => 'WPSEO_VERSION',
		'Rank Math'           => 'RANK_MATH_VERSION',
		'All in One SEO'      => 'AIOSEO_VERSION',
		'SEOPress'            => 'SEOPRESS_VERSION',
		'Slim SEO'            => 'SLIM_SEO_VER',
		'The SEO Framework'   => '\\The_SEO_Framework\\Load',
	);
}

/**
 * Name of the detected SEO plugin, or an empty string.
 *
 * @return string
 */
function detected_seo_plugin() {
	foreach ( get_known_seo_plugins() as $label => $token ) {
		if ( 0 === strpos( $token, '\\' ) ) {
			if ( class_exists( $token ) ) {
				return $label;
			}
			continue;
		}

		if ( defined( $token ) ) {
			return $label;
		}
	}

	return '';
}

/**
 * Whether an SEO plugin owns metadata output on this install.
 *
 * Filterable, so a site running a plugin this list does not know about can
 * switch the theme's output off without editing the theme.
 *
 * @return bool
 */
function seo_plugin_active() {
	$active = ( '' !== detected_seo_plugin() );

	/**
	 * Filter whether an SEO plugin owns metadata output.
	 *
	 * @param bool $active Whether a known SEO plugin was detected.
	 */
	return (bool) apply_filters( 'kolofon_seo_plugin_active', $active );
}

/**
 * Whether the theme should emit metadata at all.
 *
 * @return bool
 */
function meta_output_enabled() {
	if ( 1 !== intval( opt( 'emit_meta_tags' ) ) ) {
		return false;
	}

	return ! seo_plugin_active();
}

/**
 * Best available description for the current view.
 *
 * @return string
 */
function meta_description() {
	if ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof \WP_Post ) {
			$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( $post->post_content );
			$excerpt = trim( preg_replace( '/\s+/', ' ', (string) $excerpt ) );

			if ( '' !== $excerpt ) {
				return wp_trim_words( $excerpt, 30, '' );
			}
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$description = term_description();
		if ( $description ) {
			return wp_trim_words( wp_strip_all_tags( $description ), 30, '' );
		}
	}

	// Front page falls back to the hero body, which is the closest thing this
	// theme has to an authored site description.
	$hero = wp_strip_all_tags( (string) opt( 'hero_body' ) );
	$hero = trim( preg_replace( '/\s+/', ' ', $hero ) );

	if ( '' !== $hero ) {
		return wp_trim_words( $hero, 30, '' );
	}

	return get_bloginfo( 'description', 'display' );
}

/**
 * Best available share image URL for the current view.
 *
 * @return string
 */
function meta_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			return $src[0];
		}
	}

	$portrait = opt( 'hero_portrait' );

	return $portrait ? $portrait : default_portrait_url();
}

/**
 * Title for the current view, without the site-name suffix.
 *
 * @return string
 */
function meta_title() {
	if ( is_front_page() || is_home() ) {
		return get_bloginfo( 'name', 'display' );
	}

	if ( is_singular() ) {
		return wp_strip_all_tags( get_the_title() );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}

	if ( is_search() ) {
		/* translators: %s: search term */
		return sprintf( __( 'Search results for %s', 'kolofon' ), get_search_query() );
	}

	return get_bloginfo( 'name', 'display' );
}

/**
 * Canonical URL for the current view.
 *
 * @return string
 */
function meta_canonical() {
	if ( is_singular() ) {
		return (string) get_permalink();
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? home_url( '/' ) : (string) $link;
	}

	return home_url( '/' );
}

/**
 * Emit Open Graph, Twitter card, description, and canonical tags.
 */
function emit_meta_tags() {
	if ( ! meta_output_enabled() ) {
		return;
	}

	$title       = meta_title();
	$description = meta_description();
	$image       = meta_image();
	$canonical   = meta_canonical();
	$site_name   = get_bloginfo( 'name', 'display' );
	$type        = is_singular( 'post' ) ? 'article' : 'website';

	echo "\n<!-- menj.bio meta -->\n";

	printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $canonical ) );

	if ( '' !== $description ) {
		printf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( $description ) );
	}

	printf( "<meta property=\"og:type\" content=\"%s\" />\n", esc_attr( $type ) );
	printf( "<meta property=\"og:title\" content=\"%s\" />\n", esc_attr( $title ) );
	printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( $canonical ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( $site_name ) );
	printf( "<meta property=\"og:locale\" content=\"%s\" />\n", esc_attr( get_locale() ) );

	if ( '' !== $description ) {
		printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $description ) );
	}

	if ( '' !== $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $image ) );
	}

	if ( 'article' === $type ) {
		printf( "<meta property=\"article:published_time\" content=\"%s\" />\n", esc_attr( get_the_date( DATE_W3C ) ) );
		printf( "<meta property=\"article:modified_time\" content=\"%s\" />\n", esc_attr( get_the_modified_date( DATE_W3C ) ) );

		$section = get_the_category();
		if ( ! empty( $section ) ) {
			printf( "<meta property=\"article:section\" content=\"%s\" />\n", esc_attr( $section[0]->name ) );
		}
	}

	printf( "<meta name=\"twitter:card\" content=\"%s\" />\n", '' !== $image ? 'summary_large_image' : 'summary' );
	printf( "<meta name=\"twitter:title\" content=\"%s\" />\n", esc_attr( $title ) );

	if ( '' !== $description ) {
		printf( "<meta name=\"twitter:description\" content=\"%s\" />\n", esc_attr( $description ) );
	}

	if ( '' !== $image ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\" />\n", esc_url( $image ) );
	}

	$twitter = social_handle_from_url( opt( social_key( 'x' ) ) );
	if ( '' !== $twitter ) {
		printf( "<meta name=\"twitter:creator\" content=\"@%s\" />\n", esc_attr( $twitter ) );
	}
}

/**
 * Extract a handle from a social profile URL.
 *
 * @param string $url Profile URL.
 * @return string Handle without the leading at sign, or an empty string.
 */
function social_handle_from_url( $url ) {
	$path = wp_parse_url( (string) $url, PHP_URL_PATH );

	if ( ! $path ) {
		return '';
	}

	$handle = trim( $path, '/' );
	$handle = ltrim( $handle, '@' );

	// Only a bare handle is useful; anything with further path segments is not.
	if ( '' === $handle || false !== strpos( $handle, '/' ) ) {
		return '';
	}

	return sanitize_text_field( $handle );
}

/**
 * Emit a JSON-LD graph describing the person and the site.
 */
function emit_schema() {
	if ( ! meta_output_enabled() ) {
		return;
	}

	$home      = home_url( '/' );
	$site_name = get_bloginfo( 'name', 'display' );
	$person_id = $home . '#person';
	$site_id   = $home . '#website';

	$same_as = array();
	foreach ( array_keys( get_social_platforms() ) as $slug ) {
		if ( in_array( $slug, array( 'email', 'rss' ), true ) ) {
			continue;
		}

		$url = opt( social_key( $slug ) );
		if ( $url ) {
			$same_as[] = $url;
		}
	}

	$person = array(
		'@type' => 'Person',
		'@id'   => $person_id,
		'name'  => wp_strip_all_tags( (string) opt( 'hero_heading' ) ),
		'url'   => $home,
	);

	$description = wp_strip_all_tags( (string) opt( 'hero_body' ) );
	$description = trim( preg_replace( '/\s+/', ' ', $description ) );

	if ( '' !== $description ) {
		$person['description'] = $description;
	}

	$portrait = opt( 'hero_portrait' ) ? opt( 'hero_portrait' ) : default_portrait_url();
	if ( $portrait ) {
		$person['image'] = $portrait;
	}

	if ( $same_as ) {
		$person['sameAs'] = array_values( $same_as );
	}

	$website = array(
		'@type'     => 'WebSite',
		'@id'       => $site_id,
		'url'       => $home,
		'name'      => $site_name,
		'publisher' => array( '@id' => $person_id ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	$graph = array( $person, $website );

	if ( is_singular( 'post' ) ) {
		$article = array(
			'@type'            => 'BlogPosting',
			'@id'              => get_permalink() . '#article',
			'headline'         => wp_strip_all_tags( get_the_title() ),
			'url'              => get_permalink(),
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'author'           => array( '@id' => $person_id ),
			'publisher'        => array( '@id' => $person_id ),
			'isPartOf'         => array( '@id' => $site_id ),
			'mainEntityOfPage' => array( '@id' => get_permalink() . '#article' ),
		);

		$excerpt = meta_description();
		if ( '' !== $excerpt ) {
			$article['description'] = $excerpt;
		}

		$image = meta_image();
		if ( '' !== $image ) {
			$article['image'] = $image;
		}

		$section = get_the_category();
		if ( ! empty( $section ) ) {
			$article['articleSection'] = $section[0]->name;
		}

		$graph[] = $article;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
