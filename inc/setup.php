<?php
/**
 * Theme setup: supports, nav menus, image sizes, widget areas.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup' );
add_action( 'widgets_init', __NAMESPACE__ . '\\register_widget_areas' );
add_filter( 'intermediate_image_sizes_advanced', __NAMESPACE__ . '\\trim_default_image_sizes' );

/**
 * Register theme supports and translation domain.
 */
function setup() {
	load_theme_textdomain( 'menj-bio', MENJ_BIO_DIR . 'languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'menj-bio' ),
			'footer'  => __( 'Footer Menu', 'menj-bio' ),
		)
	);

	remove_image_size( '1536x1536' );
	remove_image_size( '2048x2048' );
}

/**
 * Widget area for the home intro block.
 */
function register_widget_areas() {
	register_sidebar(
		array(
			'name'          => __( 'Intro', 'menj-bio' ),
			'id'            => 'intro',
			'description'   => __( 'The intro area at the top of the home page.', 'menj-bio' ),
			'before_widget' => '<div class="intro-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}

/**
 * Drop unused core image sizes.
 *
 * @param array $sizes Sizes to be generated.
 * @return array
 */
function trim_default_image_sizes( $sizes ) {
	unset( $sizes['thumbnail'], $sizes['medium_large'] );
	return $sizes;
}
