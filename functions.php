<?php
/**
 * menj.bio theme bootstrap.
 *
 * @package MENJ\Bio
 * @since   1.0.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

define( 'MENJ_BIO_VERSION', '2.9.4' );
define( 'MENJ_BIO_DIR', trailingslashit( get_template_directory() ) );
define( 'MENJ_BIO_URI', trailingslashit( get_template_directory_uri() ) );
define( 'MENJ_BIO_OPTION_KEY', 'menj_bio_options' );

/**
 * Load module files. Order matters: defaults first, then everything that reads defaults.
 */
$menj_bio_modules = array(
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
	'docs',
	'system-report',
	'dynamic-css',
	'blocks',
);

foreach ( $menj_bio_modules as $menj_bio_module ) {
	require MENJ_BIO_DIR . 'inc/' . $menj_bio_module . '.php';
}

unset( $menj_bio_modules, $menj_bio_module );
