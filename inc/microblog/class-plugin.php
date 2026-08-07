<?php
/**
 * Settings shim for the merged microblog.
 *
 * The carried-over classes read their configuration through
 * Plugin::get_setting(). Upstream that reads a plugin option row; here the
 * values come from Kolofon's own options so everything is configured in one
 * place, on the Fediverse tab.
 *
 * This file is written for the theme. It replaces the plugin's class-plugin.php
 * rather than carrying it over, because the original also owned the plugin
 * bootstrap and its own settings screens, neither of which apply.
 *
 * @package Kolofon\Microblog
 */

namespace Kolofon\Microblog;

defined( 'ABSPATH' ) || exit;

/**
 * Provides the settings API the merged classes expect.
 */
class Plugin {

	/**
	 * Values not exposed in Theme Options, kept at the upstream defaults.
	 *
	 * @var array<string, mixed>
	 */
	const FIXED = array(
		'allow_images'   => 1,
		'colour_scheme'  => 'ink',
		'density'        => 'comfortable',
		'federate_via_ap' => 1,
	);

	/**
	 * Read a setting.
	 *
	 * Settings surfaced in Theme Options are read from Kolofon's option row;
	 * the rest fall back to the upstream defaults.
	 *
	 * @param string $key      Setting name.
	 * @param mixed  $fallback Returned when the setting is unknown.
	 * @return mixed
	 */
	public static function get_setting( string $key, $fallback = null ) {
		switch ( $key ) {
			case 'show_on_main_blog':
				return \Kolofon\opt( 'microblog_on_home' ) ? 1 : 0;

			case 'noindex_archive':
				return \Kolofon\opt( 'microblog_noindex' ) ? 1 : 0;

			case 'char_limit':
				return max( 1, intval( \Kolofon\opt( 'microblog_char_limit' ) ) );

			case 'timeline_page_size':
				return max( 1, intval( \Kolofon\opt( 'microblog_page_size' ) ) );

			case 'federate_via_ap':
				return \Kolofon\fediverse_enabled() ? 1 : 0;
		}

		if ( array_key_exists( $key, self::FIXED ) ) {
			return self::FIXED[ $key ];
		}

		return $fallback;
	}

	/**
	 * All settings as an array.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$keys = array(
			'show_on_main_blog',
			'noindex_archive',
			'char_limit',
			'timeline_page_size',
			'federate_via_ap',
		);

		$out = self::FIXED;
		foreach ( $keys as $key ) {
			$out[ $key ] = self::get_setting( $key );
		}

		return $out;
	}
}
