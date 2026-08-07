<?php
/**
 * Registers the status custom post type.
 *
 * @package Kolofon\Microblog
 */

namespace Kolofon\Microblog;

defined( 'ABSPATH' ) || exit;

class CPT {

	const POST_TYPE = 'kolofon_status';

	/**
	 * The slug used before the microblog was integrated into the theme.
	 *
	 * Statuses published under the old name are migrated to POST_TYPE on
	 * upgrade; see Kolofon\migrate_status_post_type().
	 */
	const LEGACY_POST_TYPE = 'xfedi_status';

	public static function register(): void {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_filter( 'pre_get_posts', [ __CLASS__, 'maybe_include_on_home' ] );
	}

	public static function register_post_type(): void {
		$labels = [
			'name'               => __( 'Statuses', 'kolofon' ),
			'singular_name'      => __( 'Status', 'kolofon' ),
			'add_new'            => __( 'Add New', 'kolofon' ),
			'add_new_item'       => __( 'Add New Status', 'kolofon' ),
			'edit_item'          => __( 'Edit Status', 'kolofon' ),
			'new_item'           => __( 'New Status', 'kolofon' ),
			'view_item'          => __( 'View Status', 'kolofon' ),
			'search_items'       => __( 'Search Statuses', 'kolofon' ),
			'not_found'          => __( 'No statuses found', 'kolofon' ),
			'not_found_in_trash' => __( 'No statuses in trash', 'kolofon' ),
			'menu_name'          => __( 'Statuses', 'kolofon' ),
		];

		register_post_type(
			self::POST_TYPE,
			[
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				// Upstream nested this under the plugin's own top-level menu. That
				// menu is not carried over, and pointing at a parent that does not
				// exist makes WordPress hide the post type entirely, so Statuses
				// takes its own menu instead.
				'show_in_menu'        => true,
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
	 * When the setting is enabled, include statuses in the main blog loop
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
