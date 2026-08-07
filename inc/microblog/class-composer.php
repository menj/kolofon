<?php
/**
 * Composer: admin bar quick-post and REST-backed submission.
 *
 * @package XFediMicroblog
 */

namespace XFediMicroblog;

defined( 'ABSPATH' ) || exit;

class Composer {

	public static function register(): void {
		add_action( 'admin_bar_menu', [ __CLASS__, 'add_admin_bar_button' ], 100 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin' ] );
	}

	public static function add_admin_bar_button( \WP_Admin_Bar $bar ): void {
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		$bar->add_node(
			[
				'id'    => 'xfedi-microblog-compose',
				'title' => '<span class="ab-icon dashicons dashicons-format-status" style="top:3px"></span>' . esc_html__( 'Post', 'xfedi-microblog' ),
				'href'  => '#',
				'meta'  => [
					'class' => 'xfedi-mb-composer-trigger',
				],
			]
		);
	}

	public static function enqueue_frontend(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'publish_posts' ) ) {
			return;
		}
		self::enqueue_composer_assets();
	}

	public static function enqueue_admin(): void {
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}
		self::enqueue_composer_assets();
	}

	private static function enqueue_composer_assets(): void {
		wp_enqueue_style(
			'xfedi-mb-composer',
			XFEDI_MICROBLOG_URL . 'assets/css/composer.css',
			[],
			XFEDI_MICROBLOG_VERSION
		);

		wp_enqueue_script(
			'xfedi-mb-composer',
			XFEDI_MICROBLOG_URL . 'assets/js/composer.js',
			[ 'wp-api-fetch' ],
			XFEDI_MICROBLOG_VERSION,
			true
		);

		wp_localize_script(
			'xfedi-mb-composer',
			'xfediMicroblog',
			[
				'restUrl'   => esc_url_raw( rest_url( 'xfedi-microblog/v1/status' ) ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'charLimit' => (int) Plugin::get_setting( 'char_limit' ),
				'strings'   => [
					'placeholder' => __( "What's happening?", 'xfedi-microblog' ),
					'post'        => __( 'Post', 'xfedi-microblog' ),
					'posting'     => __( 'Posting...', 'xfedi-microblog' ),
					'posted'      => __( 'Posted', 'xfedi-microblog' ),
					'error'       => __( 'Could not post. Try again.', 'xfedi-microblog' ),
					'remaining'   => __( '%d characters remaining', 'xfedi-microblog' ),
					'over'        => __( '%d characters over the limit', 'xfedi-microblog' ),
					'close'       => __( 'Close', 'xfedi-microblog' ),
				],
			]
		);
	}

	/**
	 * Create a status from composer input. Called by REST controller.
	 */
	public static function create_status( string $content, array $meta = [] ) {
		$content    = trim( $content );
		$char_limit = (int) Plugin::get_setting( 'char_limit' );

		if ( $content === '' ) {
			return new \WP_Error( 'xfedi_empty', __( 'Status is empty.', 'xfedi-microblog' ), [ 'status' => 400 ] );
		}

		if ( mb_strlen( $content ) > $char_limit ) {
			return new \WP_Error(
				'xfedi_too_long',
				sprintf(
				/* translators: %d: character limit */
					__( 'Status exceeds the %d character limit.', 'xfedi-microblog' ),
					$char_limit
				),
				[ 'status' => 400 ]
			);
		}

		$title = wp_trim_words( wp_strip_all_tags( $content ), 8, '' );
		if ( $title === '' ) {
			$title = __( 'Status', 'xfedi-microblog' );
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => CPT::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => wp_kses_post( $content ),
				'post_author'  => get_current_user_id(),
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		/**
		 * Fires after a status is published via the composer.
		 *
		 * @param int   $post_id
		 * @param array $meta
		 */
		do_action( 'xfedi_microblog/status_published', $post_id, $meta );

		return $post_id;
	}
}
