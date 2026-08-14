<?php
/**
 * Kolofon theme bootstrap.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

define( 'KOLOFON_VERSION', '7.3.1' );
define( 'KOLOFON_DIR', trailingslashit( get_template_directory() ) );
define( 'KOLOFON_URI', trailingslashit( get_template_directory_uri() ) );
define( 'KOLOFON_OPTION_KEY', 'kolofon_options' );

/**
 * Load module files. Order matters: defaults first, then everything that reads defaults.
 */
$kolofon_modules = array(
	'defaults',
	'hooks',
	'options-schema',
	'setup',
	'activation',
	'branding',
	'login',
	'enqueue',
	'webfonts',
	'security',
	'options',
	'settings-io',
	'social',
	'email-guard',
	'post-list',
	'page-states',
	'chrome',
	'sections',
	'tags',
	'meta',
	'syndication',
	'print-branding',
	'microblog',
	'docs',
	'system-report',
	'dynamic-css',
	'blocks',
	'migration-notice',
);

foreach ( $kolofon_modules as $kolofon_module ) {
	require KOLOFON_DIR . 'inc/' . $kolofon_module . '.php';
}

unset( $kolofon_modules, $kolofon_module );
