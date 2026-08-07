<?php
/**
 * Timeline rendering: shortcode and dynamic block.
 *
 * @package Kolofon\Microblog
 */

namespace Kolofon\Microblog;

defined( 'ABSPATH' ) || exit;

class Timeline {

	public static function register(): void {
		add_action( 'init', [ __CLASS__, 'register_shortcode' ] );
		add_action( 'init', [ __CLASS__, 'register_block' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
		add_action( 'wp_head', [ __CLASS__, 'maybe_noindex_archive' ] );
	}

	public static function register_shortcode(): void {
		// Canonical name in the theme; the original plugin name is kept as an
		// alias so content written before the merge keeps working.
		add_shortcode( 'kolofon_microblog', [ __CLASS__, 'render_shortcode' ] );
		// Retained so content written before the microblog was integrated keeps
		// rendering. New content should use [kolofon_microblog].
		add_shortcode( 'xfedi_microblog', [ __CLASS__, 'render_shortcode' ] );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'kolofon/microblog-timeline',
			[
				'render_callback' => [ __CLASS__, 'render_block' ],
				'attributes'      => [
					'perPage' => [
						'type'    => 'number',
						'default' => 20,
					],
					'author'  => [
						'type'    => 'number',
						'default' => 0,
					],
				],
			]
		);
	}

	public static function enqueue(): void {
		wp_register_style(
			'kolofon-microblog-timeline',
			KOLOFON_MICROBLOG_URL . 'assets/css/timeline.css',
			[],
			KOLOFON_MICROBLOG_VERSION
		);

		wp_add_inline_style( 'kolofon-microblog-timeline', self::build_scheme_vars() );
	}

	private static function build_scheme_vars(): string {
		$scheme = (string) Plugin::get_setting( 'colour_scheme' );
		$map    = self::colour_schemes();
		$palette = $map[ $scheme ] ?? $map['ink'];

		$vars = '';
		foreach ( $palette as $key => $value ) {
			$vars .= '--xfmb-' . $key . ':' . $value . ';';
		}
		return '.kolofon-microblog-timeline{' . $vars . '}';
	}

	public static function colour_schemes(): array {
		return [
			'ink' => [
				'bg'      => '#ffffff',
				'surface' => '#f6f6f4',
				'text'    => '#111111',
				'muted'   => '#6b6b68',
				'accent'  => '#8a1c2b',
				'border'  => '#e6e6e2',
			],
			'graphite' => [
				'bg'      => '#14161a',
				'surface' => '#1c1f24',
				'text'    => '#e8e8e6',
				'muted'   => '#9a9a97',
				'accent'  => '#d97757',
				'border'  => '#2a2d33',
			],
			'paper' => [
				'bg'      => '#faf8f2',
				'surface' => '#f2efe6',
				'text'    => '#2a2a26',
				'muted'   => '#6b6b62',
				'accent'  => '#3a5a3a',
				'border'  => '#e0dccf',
			],
			'oxblood' => [
				'bg'      => '#fbfaf7',
				'surface' => '#f4f2ec',
				'text'    => '#1a1a18',
				'muted'   => '#5f5f5a',
				'accent'  => '#6d1a24',
				'border'  => '#e2ded4',
			],
		];
	}

	public static function render_shortcode( $atts ): string {
		$atts = shortcode_atts(
			[
				'per_page' => (int) Plugin::get_setting( 'timeline_page_size' ),
				'author'   => 0,
			],
			$atts,
			'kolofon_microblog'
		);

		return self::render( (int) $atts['per_page'], (int) $atts['author'] );
	}

	public static function render_block( array $attributes ): string {
		return self::render(
			(int) ( $attributes['perPage'] ?? 20 ),
			(int) ( $attributes['author'] ?? 0 )
		);
	}

	public static function render( int $per_page, int $author ): string {
		wp_enqueue_style( 'kolofon-microblog-timeline' );

		$args = [
			'post_type'      => CPT::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 100, $per_page ) ),
			'no_found_rows'  => true,
		];

		if ( $author > 0 ) {
			$args['author'] = $author;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<div class="kolofon-microblog-timeline kolofon-microblog-empty">' . esc_html__( 'No statuses yet.', 'kolofon' ) . '</div>';
		}

		$density = esc_attr( (string) Plugin::get_setting( 'density' ) );
		$out     = '<div class="kolofon-microblog-timeline" data-density="' . $density . '">';

		while ( $query->have_posts() ) {
			$query->the_post();
			$out .= self::render_status( get_post() );
		}

		wp_reset_postdata();

		$out .= '</div>';
		return $out;
	}

	private static function render_status( \WP_Post $post ): string {
		$author_id     = (int) $post->post_author;
		$author_name   = get_the_author_meta( 'display_name', $author_id );
		$author_url    = get_author_posts_url( $author_id );
		$avatar        = get_avatar( $author_id, 48, '', $author_name, [ 'class' => 'kolofon-microblog-avatar' ] );
		$permalink     = get_permalink( $post );
		$time_iso      = get_the_date( 'c', $post );
		$time_display  = get_the_date( '', $post ) . ' · ' . get_the_time( '', $post );
		$content       = apply_filters( 'the_content', $post->post_content );

		$out  = '<article class="kolofon-microblog-status" id="status-' . (int) $post->ID . '">';
		$out .= '<header class="kolofon-microblog-status-head">';
		$out .= $avatar;
		$out .= '<div class="kolofon-microblog-status-meta">';
		$out .= '<a class="kolofon-microblog-status-author" href="' . esc_url( $author_url ) . '">' . esc_html( $author_name ) . '</a>';
		$out .= '<a class="kolofon-microblog-status-time" href="' . esc_url( $permalink ) . '"><time datetime="' . esc_attr( $time_iso ) . '">' . esc_html( $time_display ) . '</time></a>';
		$out .= '</div>';
		$out .= '</header>';
		$out .= '<div class="kolofon-microblog-status-body">' . $content . '</div>';
		$out .= '</article>';

		return $out;
	}

	public static function maybe_noindex_archive(): void {
		if ( ! Plugin::get_setting( 'noindex_archive' ) ) {
			return;
		}

		if ( is_post_type_archive( CPT::POST_TYPE ) || is_singular( CPT::POST_TYPE ) ) {
			echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
		}
	}
}
