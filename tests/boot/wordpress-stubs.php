<?php
/**
 * Minimal WordPress stubs for the boot test.
 *
 * These fake just enough of WordPress for the theme to load, register its
 * hooks, and fire its lifecycle without a database or a running WordPress.
 * The goal is narrow: catch the fatal-on-activation class of bug (a hook
 * pointing at a function that does not exist, a fatal in the setup or
 * activation path, a missing function call) that PHP linting cannot see.
 *
 * These stubs deliberately do NOT emulate real behaviour. Conditionals return
 * fixed values, queries return empty, escaping is identity. A passing boot
 * test proves the theme runs; it does not prove any feature behaves correctly
 * against a real database. That still needs a live install.
 *
 * @package Kolofon
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.WP.I18n, Universal.Files.SeparateFunctionsFromOO, Squiz.Commenting, Generic.Commenting

error_reporting( E_ALL & ~E_DEPRECATED );
ini_set( 'display_errors', '1' );

defined( 'ABSPATH' ) || define( 'ABSPATH', sys_get_temp_dir() . '/wp/' );
defined( 'WPINC' ) || define( 'WPINC', 'wp-includes' );
defined( 'WP_DEBUG' ) || define( 'WP_DEBUG', true );

foreach (
	array(
		'HOUR_IN_SECONDS'   => 3600,
		'MINUTE_IN_SECONDS' => 60,
		'DAY_IN_SECONDS'    => 86400,
		'WEEK_IN_SECONDS'   => 604800,
		'KB_IN_BYTES'       => 1024,
	) as $const_name => $const_value
) {
	defined( $const_name ) || define( $const_name, $const_value );
}

$GLOBALS['_boot_hooks']   = array();
$GLOBALS['_boot_options'] = array();

/*
 * Hook system. Real enough to register callbacks and fire them, so an
 * uncallable callback surfaces as a thrown error rather than a silent skip.
 */
function add_action( $hook, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['_boot_hooks'][ $hook ][] = $cb;
	return true;
}
function add_filter( $hook, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['_boot_hooks'][ $hook ][] = $cb;
	return true;
}
function remove_action() {
	return true; }
function remove_filter() {
	return true; }
function do_action( $hook, ...$args ) {
	foreach ( ( $GLOBALS['_boot_hooks'][ $hook ] ?? array() ) as $cb ) {
		if ( ! is_callable( $cb ) ) {
			throw new Error( "UNCALLABLE action '$hook' -> " . ( is_string( $cb ) ? $cb : gettype( $cb ) ) );
		}
		call_user_func_array( $cb, $args );
	}
}
function apply_filters( $hook, $value, ...$rest ) {
	foreach ( ( $GLOBALS['_boot_hooks'][ $hook ] ?? array() ) as $cb ) {
		if ( ! is_callable( $cb ) ) {
			throw new Error( "UNCALLABLE filter '$hook' -> " . ( is_string( $cb ) ? $cb : gettype( $cb ) ) );
		}
		$value = call_user_func_array( $cb, array_merge( array( $value ), $rest ) );
	}
	return $value;
}
function add_shortcode( $tag, $cb ) {
	if ( ! is_callable( $cb ) ) {
		throw new Error( "UNCALLABLE shortcode '$tag'" );
	}
}

/*
 * Registration functions that validate their callbacks. These are where the
 * real activation bugs hide, so the stubs throw on an uncallable callback.
 */
function add_settings_field( $id, $label, $cb, $page, $section, $args = array() ) {
	if ( ! is_callable( $cb ) ) {
		throw new Error( "UNCALLABLE settings field '$id'" );
	}
}
function register_post_meta( $type, $key, $args = array() ) {
	foreach ( array( 'sanitize_callback', 'auth_callback' ) as $c ) {
		if ( isset( $args[ $c ] ) && ! is_callable( $args[ $c ] ) ) {
			throw new Error( "UNCALLABLE $c for meta '$key'" );
		}
	}
}
function register_rest_route( $ns, $route, $args = array() ) {
	$items = ( isset( $args['callback'] ) || isset( $args['permission_callback'] ) ) ? array( $args ) : $args;
	foreach ( (array) $items as $item ) {
		foreach ( array( 'callback', 'permission_callback' ) as $c ) {
			if ( isset( $item[ $c ] ) && ! is_callable( $item[ $c ] ) ) {
				throw new Error( "UNCALLABLE REST $c on $ns$route" );
			}
		}
	}
}
function add_meta_box( $id, $title, $cb, ...$rest ) {
	if ( ! is_callable( $cb ) ) {
		throw new Error( "UNCALLABLE meta box '$id'" );
	}
}
function register_activation_hook( $file, $cb ) {
	if ( ! is_callable( $cb ) ) {
		throw new Error( 'UNCALLABLE activation hook' );
	}
}

/* Options: a real in-memory store so opt() round-trips work. */
function get_option( $key, $default = false ) {
	return $GLOBALS['_boot_options'][ $key ] ?? $default;
}
function update_option( $key, $value, $autoload = null ) {
	$GLOBALS['_boot_options'][ $key ] = $value;
	return true;
}
function delete_option( $key ) {
	unset( $GLOBALS['_boot_options'][ $key ] );
	return true;
}

/* Context conditionals: fixed values for a normal front-end request. */
function is_admin() {
	return false; }
if ( ! function_exists( 'is_page' ) ) {
	function is_page() {
	return false; }
}
if ( ! function_exists( 'is_singular' ) ) {
	function is_singular() {
	return false; }
}
if ( ! function_exists( 'is_home' ) ) {
	function is_home() {
	return false; }
}
if ( ! function_exists( 'is_front_page' ) ) {
	function is_front_page() {
	return true; }
}
if ( ! function_exists( 'is_category' ) ) {
	function is_category() {
	return false; }
}
if ( ! function_exists( 'is_tag' ) ) {
	function is_tag() {
	return false; }
}
if ( ! function_exists( 'is_tax' ) ) {
	function is_tax() {
	return false; }
}
if ( ! function_exists( 'is_archive' ) ) {
	function is_archive() {
	return false; }
}
if ( ! function_exists( 'is_search' ) ) {
	function is_search() {
	return false; }
}
function is_main_query() {
	return true; }
function in_the_loop() {
	return false; }
function has_nav_menu() {
	return false; }
function has_site_icon() {
	return false; }
function has_post_thumbnail() {
	return false; }
function has_custom_logo() {
	return false; }
function has_excerpt() {
	return false; }
function is_active_sidebar() {
	return false; }
function is_user_logged_in() {
	return false; }
function current_user_can() {
	// Controllable so the boot test can render capability-gated admin pages:
	// set $GLOBALS['_boot_can'] = true before calling, unset after.
	return ! empty( $GLOBALS['_boot_can'] ); }
function is_child_theme() {
	return false; }
function have_posts() {
	return false; }
function is_email( $e ) {
	return strpos( (string) $e, '@' ) !== false; }
function is_wp_error( $t ) {
	return $t instanceof WP_Error; }

class WP_Error {
	public function get_error_message() {
		return 'stub error'; }
	public function get_error_code() {
		return 'stub'; }
}

/* i18n and escaping: identity functions. */
function __( $s, $d = null ) {
	return $s; }
function _e( $s, $d = null ) {
	echo $s; }
function _x( $s, $c, $d = null ) {
	return $s; }
function _n( $a, $b, $n, $d = null ) {
	return 1 === $n ? $a : $b; }
function _nx( $a, $b, $n, $c, $d = null ) {
	return 1 === $n ? $a : $b; }
function esc_html( $s ) {
	return $s; }
function esc_attr( $s ) {
	return $s; }
function esc_url( $s ) {
	return $s; }
function esc_html__( $s, $d = null ) {
	return $s; }
function esc_html_e( $s, $d = null ) {
	echo $s; }
function esc_html_x( $s, $c, $d = null ) {
	return $s; }
function esc_attr__( $s, $d = null ) {
	return $s; }
function esc_attr_e( $s, $d = null ) {
	echo $s; }
function esc_url_raw( $s ) {
	return $s; }
function esc_textarea( $s ) {
	return $s; }
function esc_js( $s ) {
	return $s; }
function checked( $a, $b = true, $e = true ) {
	$r = (string) $a === (string) $b ? 'checked' : '';
	return $e ? print( $r ) : $r;
}
function selected( $a, $b = true, $e = true ) {
	$r = (string) $a === (string) $b ? 'selected' : '';
	return $e ? print( $r ) : $r;
}

/* The WP __return_* helpers, used as ready-made hook callbacks. */
function __return_false() {
	return false; }
function __return_true() {
	return true; }
function __return_empty_array() {
	return array(); }
function __return_null() {
	return null; }
function __return_zero() {
	return 0; }
function __return_empty_string() {
	return ''; }

/* Functions with behaviour the theme actually depends on during boot. */
function trailingslashit( $s ) {
	return rtrim( $s, '/' ) . '/';
}
function wp_parse_args( $args, $defaults = array() ) {
	if ( is_array( $args ) ) {
		$r = $args;
	} else {
		parse_str( (string) $args, $r );
	}
	return array_merge( (array) $defaults, (array) $r );
}
function wp_list_pluck( $list, $field ) {
	$out = array();
	foreach ( (array) $list as $k => $v ) {
		$out[ $k ] = is_array( $v ) ? ( $v[ $field ] ?? null ) : ( is_object( $v ) ? ( $v->$field ?? null ) : null );
	}
	return $out;
}
function shortcode_atts( $defaults, $atts, $shortcode = '' ) {
	return array_merge( $defaults, (array) $atts );
}
function current_time( $type ) {
	return 'timestamp' === $type ? time() : gmdate( 'Y-m-d H:i:s' );
}

/*
 * The theme path. Resolves relative to this file so the test runs wherever the
 * repository is checked out, then the template-directory functions report it.
 */
define( 'KOLOFON_TEST_THEME_DIR', dirname( __DIR__, 2 ) );
function get_template_directory() {
	return KOLOFON_TEST_THEME_DIR;
}
function get_template_directory_uri() {
	return 'https://example.test/wp-content/themes/kolofon';
}
function get_stylesheet_directory() {
	return get_template_directory();
}

/*
 * Everything else the theme touches, as safe no-ops. Pre-declared rather than
 * caught at call time so the theme's own code paths run to completion. Any WP
 * function the theme starts using that is not here will surface as an
 * "undefined function" fatal in the boot test, which is the correct signal to
 * add it.
 */
$kolofon_boot_noops = <<<'NAMES'
add_editor_style load_theme_textdomain register_nav_menus add_theme_support add_theme_page add_management_page
remove_theme_support set_post_thumbnail_size add_image_size remove_image_size remove_menu_page remove_submenu_page
remove_meta_box remove_post_type_support register_block_pattern register_block_pattern_category register_block_style
register_sidebar wp_enqueue_style wp_enqueue_script wp_register_style wp_register_script wp_enqueue_media
wp_dequeue_style wp_deregister_script wp_localize_script wp_add_inline_style wp_add_inline_script
get_bloginfo bloginfo body_class post_class language_attributes wp_body_open wp_head wp_footer get_header
get_footer get_search_form dynamic_sidebar the_custom_logo register_setting add_settings_section
do_settings_fields do_settings_sections settings_fields settings_errors submit_button wp_nonce_field
wp_nonce_url wp_create_nonce wp_verify_nonce check_admin_referer wp_die number_format_i18n
sanitize_text_field sanitize_textarea_field sanitize_key sanitize_title sanitize_email sanitize_html_class
wp_kses_post wp_kses wp_unslash absint wp_parse_url home_url admin_url wp_customize_url add_query_arg
rawurlencode get_site_icon_url wp_json_encode antispambot wpautop html_entity_decode get_posts wp_insert_post
wp_update_post get_post get_post_field get_post_meta update_post_meta delete_post_meta get_the_tags get_the_ID
get_the_title get_the_content get_the_excerpt get_the_date get_the_modified_date get_the_category
get_the_archive_title get_the_post_thumbnail the_post_thumbnail get_post_thumbnail_id get_permalink the_permalink
the_title the_content the_post get_edit_post_link get_post_modified_time get_post_time get_queried_object
get_queried_object_id get_term get_term_by get_term_link wp_insert_term get_terms get_tag_link get_category_link
single_cat_title single_tag_title term_description wp_get_post_categories wp_set_post_categories human_time_diff
wp_trim_words wp_strip_all_tags nocache_headers set_transient get_transient fetch_feed wp_remote_get
rest_ensure_response rest_url rest_authorization_required_code set_post_format get_post_format get_post_type
get_post_mime_type get_attached_file wp_get_attachment_image_src get_theme_mod get_locale wp_reset_postdata
wp_link_pages wp_nav_menu next_post_link previous_post_link get_next_posts_page_link get_previous_posts_page_link
get_search_query wp_safe_redirect wp_get_referer get_current_screen wp_is_post_autosave wp_is_post_revision
did_action filemtime wp_get_theme get_stylesheet is_wp_version_compatible
NAMES;

foreach ( preg_split( '/\s+/', trim( $kolofon_boot_noops ) ) as $kolofon_boot_fn ) {
	if ( $kolofon_boot_fn && ! function_exists( $kolofon_boot_fn ) ) {
		eval( "function $kolofon_boot_fn() { return ''; }" ); // phpcs:ignore Squiz.PHP.Eval.Discouraged -- test-only stub generation.
	}
}
unset( $kolofon_boot_noops, $kolofon_boot_fn );
