<?php
/**
 * Block editor support: pattern category registration and block styles.
 *
 * Patterns themselves live in /patterns/ and are auto-registered by WordPress.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'init', __NAMESPACE__ . '\\register_pattern_category' );
add_action( 'init', __NAMESPACE__ . '\\register_block_styles' );

/**
 * Register a pattern category so our patterns group cleanly in the inserter.
 */
function register_pattern_category() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category(
		'kolofon',
		array( 'label' => __( 'Kolofon', 'kolofon' ) )
	);
}

/**
 * Register a small set of block styles.
 */
function register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/separator',
		array(
			'name'  => 'kolofon-hairline',
			'label' => __( 'Hairline', 'kolofon' ),
		)
	);

	register_block_style(
		'core/quote',
		array(
			'name'  => 'kolofon-accent',
			'label' => __( 'Accent border', 'kolofon' ),
		)
	);
}
