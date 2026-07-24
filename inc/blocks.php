<?php
/**
 * Block editor support: pattern category registration and block styles.
 *
 * Patterns themselves live in /patterns/ and are auto-registered by WordPress.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

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
		'menj-bio',
		array( 'label' => __( 'menj.bio', 'menj-bio' ) )
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
			'name'  => 'menj-bio-hairline',
			'label' => __( 'Hairline', 'menj-bio' ),
		)
	);

	register_block_style(
		'core/quote',
		array(
			'name'  => 'menj-bio-accent',
			'label' => __( 'Accent border', 'menj-bio' ),
		)
	);
}
