<?php
/**
 * Registers the xfedi_status custom post type.
 *
 * @package XFediMicroblog
 */

namespace XFediMicroblog;

defined( 'ABSPATH' ) || exit;

class CPT {

	const POST_TYPE = 'xfedi_status';

	public static function register(): void {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_filter( 'pre_get_posts', [ __CLASS__, 'maybe_include_on_home' ] );
	}

	public static function register_post_type(): void {
		$labels = [
			'name'               => __( 'Statuses', 'xfedi-microblog' ),
			'singular_name'      => __( 'Status', 'xfedi-microblog' ),
			'add_new'            => __( 'Add New', 'xfedi-microblog' ),
			'add_new_item'       => __( 'Add New Status', 'xfedi-microblog' ),
			'edit_item'          => __( 'Edit Status', 'xfedi-microblog' ),
			'new_item'           => __( 'New Status', 'xfedi-microblog' ),
			'view_item'          => __( 'View Status', 'xfedi-microblog' ),
			'search_items'       => __( 'Search Statuses', 'xfedi-microblog' ),
			'not_found'          => __( 'No statuses found', 'xfedi-microblog' ),
			'not_found_in_trash' => __( 'No statuses in trash', 'xfedi-microblog' ),
			'menu_name'          => __( 'Statuses', 'xfedi-microblog' ),
		];

		register_post_type(
			self::POST_TYPE,
			[
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => 'xfedi-microblog',
				'show_in_rest'        => true,
				'rest_base'           => 'statuses',
				'has_archive'         => 'statuses',
				'rewrite'             => [
					'slug'       => 'status',
					'with_front' => false,
				],
				'supports'            => [ 'editor', 'author', 'thumbnail', 'custom-fields', 'comments' ],
				'menu_icon'           => 'dashicons-format-status',
				'menu_position'       => 21,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
			]
		);
	}

	/**
	 * When the setting is enabled, include xfedi_status in the main blog loop
	 * alongside standard posts. Off by default so the microblog stays separate.
	 */
	public static function maybe_include_on_home( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! ( $query->is_home() || $query->is_feed() ) ) {
			return;
		}

		if ( ! Plugin::get_setting( 'show_on_main_blog' ) ) {
			return;
		}

		$existing = (array) $query->get( 'post_type' );
		if ( empty( $existing ) || $existing === [ '' ] ) {
			$existing = [ 'post' ];
		}
		$existing[] = self::POST_TYPE;
		$query->set( 'post_type', array_unique( $existing ) );
	}
}
