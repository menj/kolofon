<?php
/**
 * Settings portability: export, import, reset.
 *
 * Every setting the theme owns lives in one `kolofon_options` row, which
 * makes a configuration cheap to move between installs and cheap to lose to a
 * single bad save. These three handlers cover both.
 *
 * All handlers are nonce-checked and gated on `manage_options`. Import
 * validates against the known key set and then runs the same sanitiser the
 * options form uses, so a hand-edited or hostile file cannot introduce a key
 * the theme does not recognise or a value outside its allowed range.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

const EXPORT_FORMAT = 1;

add_action( 'admin_post_kolofon_export', __NAMESPACE__ . '\\handle_export' );
add_action( 'admin_post_kolofon_import', __NAMESPACE__ . '\\handle_import' );

/**
 * Assert the current request may manage theme settings.
 *
 * @param string $action Nonce action to verify.
 */
function assert_can_manage( $action ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'kolofon' ), '', array( 'response' => 403 ) );
	}

	check_admin_referer( $action );
}

/**
 * Redirect back to the options page carrying a status flag.
 *
 * @param string $status Status slug.
 * @param string $tab    Tab to open on return.
 */
function redirect_to_options( $status, $tab = 'advanced' ) {
	$url = add_query_arg(
		array(
			'page'      => 'kolofon-options',
			'mb_status' => rawurlencode( $status ),
		),
		admin_url( 'themes.php' )
	);

	wp_safe_redirect( $url . '#tab-' . $tab );
	exit;
}

/**
 * Stream the current settings as a JSON download.
 */
function handle_export() {
	assert_can_manage( 'kolofon_export' );

	$stored  = get_option( KOLOFON_OPTION_KEY, array() );
	$options = wp_parse_args( is_array( $stored ) ? $stored : array(), get_defaults() );

	$payload = array(
		'format'       => EXPORT_FORMAT,
		'theme'        => 'kolofon',
		'version'      => KOLOFON_VERSION,
		'exported'     => gmdate( 'c' ),
		'site'         => home_url( '/' ),
		'options'      => $options,
	);

	$filename = sprintf(
		'kolofon-settings-%s-%s.json',
		sanitize_title( wp_parse_url( home_url(), PHP_URL_HOST ) ),
		gmdate( 'Ymd-His' )
	);

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}

/**
 * Read an uploaded JSON file and replace the stored settings.
 */
function handle_import() {
	assert_can_manage( 'kolofon_import' );

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- file upload, validated below.
	$file = isset( $_FILES['kolofon_import_file'] ) ? $_FILES['kolofon_import_file'] : null;

	if ( ! is_array( $file ) || ! isset( $file['error'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
		redirect_to_options( 'import-nofile' );
	}

	$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
	if ( $size <= 0 || $size > 256 * KB_IN_BYTES ) {
		redirect_to_options( 'import-size' );
	}

	$tmp = isset( $file['tmp_name'] ) ? $file['tmp_name'] : '';
	if ( '' === $tmp || ! is_uploaded_file( $tmp ) || ! is_readable( $tmp ) ) {
		redirect_to_options( 'import-unreadable' );
	}

	$raw = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- reading a local upload, not a remote URL.
	if ( false === $raw ) {
		redirect_to_options( 'import-unreadable' );
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) || JSON_ERROR_NONE !== json_last_error() ) {
		redirect_to_options( 'import-invalid' );
	}

	if ( ! isset( $data['theme'] ) || 'kolofon' !== $data['theme'] ) {
		redirect_to_options( 'import-wrongtheme' );
	}

	if ( ! isset( $data['options'] ) || ! is_array( $data['options'] ) ) {
		redirect_to_options( 'import-invalid' );
	}

	// Discard anything the theme does not recognise, then run the same
	// sanitiser the options form uses. Clamps, enum fallbacks, and escaping
	// therefore apply identically to an imported file and a form submission.
	$known    = array_intersect_key( $data['options'], get_defaults() );
	$prepared = wp_parse_args( $known, get_defaults() );
	$clean    = sanitize_options( $prepared );

	update_option( KOLOFON_OPTION_KEY, $clean );

	redirect_to_options( 'import-ok' );
}

/**
 * Human-readable messages for the status flags above.
 *
 * @return array<string, array{type:string, text:string}>
 */
function get_status_messages() {
	return array(
		'import-ok'        => array(
			'type' => 'success',
			'text' => __( 'Settings imported.', 'kolofon' ),
		),
		'reset-ok'         => array(
			'type' => 'success',
			'text' => __( 'Defaults restored.', 'kolofon' ),
		),
		'import-nofile'    => array(
			'type' => 'error',
			'text' => __( 'No file was uploaded.', 'kolofon' ),
		),
		'import-size'      => array(
			'type' => 'error',
			'text' => __( 'That file is empty or larger than 256 KB, so it is not a settings export.', 'kolofon' ),
		),
		'import-unreadable' => array(
			'type' => 'error',
			'text' => __( 'The uploaded file could not be read.', 'kolofon' ),
		),
		'import-invalid'   => array(
			'type' => 'error',
			'text' => __( 'That file is not valid JSON, or has no options to import.', 'kolofon' ),
		),
		'import-wrongtheme' => array(
			'type' => 'error',
			'text' => __( 'That export belongs to a different theme.', 'kolofon' ),
		),
	);
}
