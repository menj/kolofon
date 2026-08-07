<?php
/**
 * REST endpoint used by the composer JS.
 *
 * @package XFediMicroblog
 */

namespace XFediMicroblog;

defined( 'ABSPATH' ) || exit;

class REST {

	const NAMESPACE_STR = 'xfedi-microblog/v1';

	public static function register(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE_STR,
			'/status',
			[
				'methods'             => 'POST',
				'callback'            => [ __CLASS__, 'create_status' ],
				'permission_callback' => [ __CLASS__, 'can_publish' ],
				'args'                => [
					'content' => [
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'wp_kses_post',
					],
				],
			]
		);
	}

	public static function can_publish(): bool {
		return is_user_logged_in() && current_user_can( 'publish_posts' );
	}

	public static function create_status( \WP_REST_Request $request ) {
		$content = (string) $request->get_param( 'content' );
		$result  = Composer::create_status( $content );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$post = get_post( $result );
		return rest_ensure_response(
			[
				'id'        => (int) $result,
				'permalink' => get_permalink( $post ),
				'title'     => $post->post_title,
			]
		);
	}
}
