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
		'Yoast SEO'         => 'WPSEO_VERSION',
		'Rank Math'         => 'RANK_MATH_VERSION',
		'All in One SEO'    => 'AIOSEO_VERSION',
		'SEOPress'          => 'SEOPRESS_VERSION',
		'Slim SEO'          => 'SLIM_SEO_VER',
		'The SEO Framework' => '\\The_SEO_Framework\\Load',
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
		// A singular view can itself be paginated — native <!--nextpage-->
		// content, or a template like the Blog Index that runs its own
		// WP_Query with `paged`. Either way WordPress reflects the current
		// page in the `page` (or, less commonly, `paged`) query var, and the
		// bare permalink is only correct on page 1: on page 2+ it would
		// canonicalise every page back to page 1, telling crawlers not to
		// index the rest. get_pagenum_link() builds the right `/page/N/` URL
		// for the current request regardless of which mechanism is paginating
		// it, so this stays correct without knowing which one is in play.
		$view_page = max( 1, (int) get_query_var( 'page' ), (int) get_query_var( 'paged' ) );
		return 1 === $view_page ? (string) get_permalink() : (string) get_pagenum_link( $view_page );
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

	echo "\n<!-- Kolofon meta -->\n";

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
		'@type'           => 'WebSite',
		'@id'             => $site_id,
		'url'             => $home,
		'name'            => $site_name,
		'publisher'       => array( '@id' => $person_id ),
		'inLanguage'      => get_bloginfo( 'language' ),
		// Enables the sitelinks search box in Google results. The target is the
		// theme's own search URL; the query token is the standard placeholder.
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$graph = array( $person, $website );

	// The front page of a single-author microsite is that author's profile.
	// ProfilePage tells crawlers and AI tools "this page is about this Person",
	// which is exactly the "who is this" question they try to answer.
	if ( is_front_page() ) {
		$graph[] = array(
			'@type'      => 'ProfilePage',
			'@id'        => $home . '#profilepage',
			'url'        => $home,
			'name'       => $site_name,
			'about'      => array( '@id' => $person_id ),
			'mainEntity' => array( '@id' => $person_id ),
			'isPartOf'   => array( '@id' => $site_id ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
	}

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

	// A single page (not a post, not the front page) is a WebPage. The site's
	// About page, if its slug or title says so, is the more specific AboutPage.
	if ( is_page() && ! is_front_page() ) {
		$queried  = get_queried_object();
		$is_about = ( $queried instanceof \WP_Post )
			&& ( 'about' === $queried->post_name || 0 === strcasecmp( 'about', (string) $queried->post_title ) );

		$page_node = array(
			'@type'      => $is_about ? 'AboutPage' : 'WebPage',
			'@id'        => get_permalink() . '#webpage',
			'url'        => get_permalink(),
			'name'       => wp_strip_all_tags( get_the_title() ),
			'isPartOf'   => array( '@id' => $site_id ),
			'about'      => array( '@id' => $person_id ),
			'inLanguage' => get_bloginfo( 'language' ),
		);

		$page_desc = meta_description();
		if ( '' !== $page_desc ) {
			$page_node['description'] = $page_desc;
		}

		$graph[] = $page_node;
	}

	// Archives (category, tag, date, author) and the Blog Index page are
	// collections. CollectionPage plus an ItemList of the posts on the page
	// tells a crawler exactly which posts this listing contains and in what
	// order, which is what AI tools extract when summarising a section.
	$is_blog_template = is_page() && get_page_template_slug() === 'page-blog.php';

	if ( is_archive() || is_home() || $is_blog_template ) {
		$collection = build_collection_schema( $site_id, $person_id );
		if ( $collection ) {
			$graph[] = $collection;
		}
	}

	// Search results are their own page type.
	if ( is_search() ) {
		$graph[] = array(
			'@type'      => 'SearchResultsPage',
			'@id'        => $home . '?s=' . rawurlencode( get_search_query() ) . '#searchresults',
			'url'        => $home . '?s=' . rawurlencode( get_search_query() ),
			'name'       => sprintf(
				/* translators: %s: the search query */
				__( 'Search results for %s', 'kolofon' ),
				get_search_query()
			),
			'isPartOf'   => array( '@id' => $site_id ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
	}

	// Breadcrumbs on every non-front view. Google renders these directly in
	// results, and they give crawlers the page's place in the site hierarchy.
	$breadcrumb = build_breadcrumb_schema( $home );
	if ( $breadcrumb ) {
		$graph[] = $breadcrumb;
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

/**
 * Build a CollectionPage node with an ItemList of the posts in the loop.
 *
 * Covers category, tag, date, and author archives, the posts index, and the
 * Blog Index page template. The ItemList positions each post so a crawler
 * reads the listing as an ordered set of specific URLs rather than opaque
 * pagination. Reads the main query without disturbing it.
 *
 * @param string $site_id   The WebSite node @id, for isPartOf.
 * @param string $person_id The Person node @id, unused placeholder for parity.
 * @return array|null The node, or null when the loop is empty.
 */
function build_collection_schema( $site_id, $person_id ) {
	unset( $person_id ); // Reserved; collection authorship is site-level.

	// The Blog Index template runs its own paginated WP_Query for the posts
	// it lists (see page-blog.php); the global main query for that view is
	// the static Page itself, a single-item array containing the Page post,
	// not the ledger's posts. Reading global $wp_query here would build an
	// ItemList of one item — the "Blog" page — never the posts actually
	// shown. blog_index_query_args() is the same source page-blog.php builds
	// its query from, so this always describes exactly what the page renders.
	if ( is_page() && get_page_template_slug() === 'page-blog.php' ) {
		$blog_query = new WP_Query( blog_index_query_args() );
		$posts      = $blog_query->posts;
	} else {
		global $wp_query;
		$posts = $wp_query->posts;
	}

	if ( empty( $posts ) || ! is_array( $posts ) ) {
		return null;
	}

	$items    = array();
	$position = 0;

	foreach ( $posts as $post_object ) {
		if ( ! isset( $post_object->ID ) ) {
			continue;
		}
		++$position;
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'url'      => get_permalink( $post_object->ID ),
			'name'     => wp_strip_all_tags( get_the_title( $post_object->ID ) ),
		);
	}

	if ( empty( $items ) ) {
		return null;
	}

	$current = home_url( add_query_arg( array() ) );

	return array(
		'@type'      => 'CollectionPage',
		'@id'        => $current . '#collection',
		'url'        => $current,
		'name'       => wp_strip_all_tags( collection_title() ),
		'isPartOf'   => array( '@id' => $site_id ),
		'inLanguage' => get_bloginfo( 'language' ),
		'mainEntity' => array(
			'@type'           => 'ItemList',
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		),
	);
}

/**
 * A human title for the current collection view.
 *
 * @return string
 */
function collection_title() {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	if ( is_author() ) {
		return get_the_author();
	}
	if ( is_date() ) {
		return get_the_archive_title();
	}
	return get_bloginfo( 'name', 'display' );
}

/**
 * Build a BreadcrumbList reflecting the current view's place in the site.
 *
 * Home is always the first crumb. Posts add their primary category then the
 * post; pages add the page; archives add the archive label. Google renders
 * this trail directly in results and crawlers use it for hierarchy.
 *
 * @param string $home Home URL with trailing slash.
 * @return array|null The node, or null on the front page where a trail is noise.
 */
function build_breadcrumb_schema( $home ) {
	if ( is_front_page() ) {
		return null;
	}

	$crumbs   = array();
	$position = 0;

	$add = function ( $name, $url ) use ( &$crumbs, &$position ) {
		$name = wp_strip_all_tags( (string) $name );
		if ( '' === $name ) {
			return;
		}
		++$position;
		$crumb = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $name,
		);
		if ( $url ) {
			$crumb['item'] = $url;
		}
		$crumbs[] = $crumb;
	};

	$add( __( 'Home', 'kolofon' ), $home );

	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( ! empty( $cats ) ) {
			$add( $cats[0]->name, get_category_link( $cats[0]->term_id ) );
		}
		$add( get_the_title(), get_permalink() );
	} elseif ( is_page() ) {
		$add( get_the_title(), get_permalink() );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$add( single_term_title( '', false ), null );
	} elseif ( is_author() ) {
		$add( get_the_author(), null );
	} elseif ( is_date() ) {
		$add( get_the_archive_title(), null );
	} elseif ( is_search() ) {
		$add(
			sprintf(
				/* translators: %s: the search query */
				__( 'Search results for %s', 'kolofon' ),
				get_search_query()
			),
			null
		);
	} elseif ( is_404() ) {
		$add( __( 'Not found', 'kolofon' ), null );
	}

	// A lone Home crumb is not a trail.
	if ( count( $crumbs ) < 2 ) {
		return null;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => home_url( add_query_arg( array() ) ) . '#breadcrumb',
		'itemListElement' => $crumbs,
	);
}
