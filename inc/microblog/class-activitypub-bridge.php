<?php
/**
 * Integration with the ActivityPub plugin by Matthias Pfefferle / Automattic.
 * When present and the user has enabled federation, the status CPT is
 * opted in and posts are declared as short-form Notes rather than Articles.
 *
 * @package Kolofon\Microblog
 */

namespace Kolofon\Microblog;

defined( 'ABSPATH' ) || exit;

class ActivityPub_Bridge {

	public static function maybe_register(): void {
		if ( ! self::activitypub_present() ) {
			return;
		}

		if ( ! Plugin::get_setting( 'federate_via_ap' ) ) {
			return;
		}

		add_filter( 'activitypub_supported_post_types', [ __CLASS__, 'add_cpt' ] );
		add_filter( 'activitypub_object_type', [ __CLASS__, 'force_note_type' ], 10, 2 );
	}

	public static function activitypub_present(): bool {
		return defined( 'ACTIVITYPUB_PLUGIN_VERSION' ) || function_exists( '\Activitypub\get_plugin_version' );
	}

	public static function add_cpt( $types ) {
		$types   = (array) $types;
		$types[] = CPT::POST_TYPE;
		return array_values( array_unique( $types ) );
	}

	public static function force_note_type( $type, $post ) {
		if ( $post instanceof \WP_Post && $post->post_type === CPT::POST_TYPE ) {
			return 'note';
		}
		return $type;
	}
}
