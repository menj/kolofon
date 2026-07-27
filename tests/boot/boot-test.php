<?php
/**
 * Boot test.
 *
 * Loads the theme in a stubbed WordPress environment, fires the activation and
 * request lifecycle, and asserts that nothing fatals and every registered hook
 * callback is callable. This catches the fatal-on-activation class of bug that
 * PHP linting and PHPCS cannot see: a hook pointing at a function that does not
 * exist, a fatal in the setup or activation path, a call to a function that is
 * not defined.
 *
 * It does not, and cannot, prove that any feature behaves correctly against a
 * real database. The stubs return empty queries and identity strings. A green
 * boot test means the theme runs; a live install is still required to prove
 * behaviour. See tests/boot/README.md.
 *
 * Exit code: 0 on pass, 1 on any failure. Run from anywhere:
 *   php tests/boot/boot-test.php
 *
 * @package Kolofon
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.WP.I18n, WordPress.PHP.DevelopmentFunctions, WordPress.Security.EscapeOutput, Squiz.Commenting, Generic.Commenting

require __DIR__ . '/wordpress-stubs.php';

$failures = 0;

/**
 * Print a pass or fail line and count failures.
 *
 * @param bool   $ok      Whether the check passed.
 * @param string $message What was checked.
 */
function boot_check( $ok, $message ) {
	global $failures;
	if ( $ok ) {
		echo "  PASS  $message\n";
	} else {
		echo "  FAIL  $message\n";
		++$failures;
	}
}

echo "Kolofon boot test\n";
echo "-----------------\n";

// 1. The theme loads through functions.php in the real module order.
try {
	require KOLOFON_TEST_THEME_DIR . '/functions.php';
	boot_check( true, 'theme loads (all modules included, no include-time fatal)' );
} catch ( Throwable $e ) {
	boot_check( false, 'theme loads: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() );
	echo "\nRESULT: FAIL (theme could not load)\n";
	exit( 1 );
}

// 2. The full lifecycle fires without a fatal. after_switch_theme is the
//    activation hook; if the theme white-screens on activation, it is here.
$lifecycle = array(
	'after_setup_theme',
	'init',
	'after_switch_theme',
	'widgets_init',
	'wp_enqueue_scripts',
	'rest_api_init',
	'admin_menu',
	'admin_init',
	'wp_head',
	'wp_footer',
);
foreach ( $lifecycle as $hook ) {
	try {
		ob_start();
		do_action( $hook );
		ob_end_clean();
		boot_check( true, "lifecycle: $hook" );
	} catch ( Throwable $e ) {
		ob_end_clean();
		boot_check( false, "lifecycle: $hook -> " . $e->getMessage() );
	}
}

// 3. Key filters run without a fatal.
$filters = array(
	'the_content'            => 'sample',
	'wp_robots'              => array(),
	'body_class'             => array(),
	'kolofon_font_stacks'    => array(),
	'kolofon_colour_presets' => array(),
);
foreach ( $filters as $hook => $value ) {
	try {
		apply_filters( $hook, $value );
		boot_check( true, "filter: $hook" );
	} catch ( Throwable $e ) {
		boot_check( false, "filter: $hook -> " . $e->getMessage() );
	}
}

// 4. Every registered hook callback is callable. A callback naming a function
//    that does not exist passes lint but fatals the moment the hook fires on a
//    real site; this catches it without needing the hook to fire.
$uncallable = array();
$total      = 0;
foreach ( $GLOBALS['_boot_hooks'] as $hook => $cbs ) {
	foreach ( $cbs as $cb ) {
		++$total;
		if ( ! is_callable( $cb ) ) {
			$uncallable[] = "$hook -> " . ( is_string( $cb ) ? $cb : gettype( $cb ) );
		}
	}
}
boot_check( empty( $uncallable ), "all $total hook callbacks callable" . ( $uncallable ? ': ' . implode( '; ', $uncallable ) : '' ) );

// 5. Every field type renders without a fatal (the options page body).
try {
	$fields = \Kolofon\get_option_fields();
	$seen   = array();
	foreach ( $fields as $key => $field ) {
		$field_type = $field['type'] ?? '?';
		if ( isset( $seen[ $field_type ] ) ) {
			continue;
		}
		$seen[ $field_type ] = true;
		$render_args         = array_merge(
			array(
				'key'  => $key,
				'type' => $field_type,
			),
			$field['args'] ?? array()
		);
		ob_start();
		\Kolofon\render_field( $render_args );
		ob_end_clean();
	}
	boot_check( true, 'every field type renders (' . count( $seen ) . ' types: ' . implode( ', ', array_keys( $seen ) ) . ')' );
} catch ( Throwable $e ) {
	if ( ob_get_level() > 0 ) {
		ob_end_clean();
	}
	boot_check( false, 'field rendering: ' . $e->getMessage() );
}

// 6. Defaults round-trip through the sanitiser with no key lost. This is the
//    single-source-of-truth contract that keeps a fresh install correct.
try {
	$defaults  = \Kolofon\get_defaults();
	$sanitised = \Kolofon\sanitize_options( $defaults );
	$dropped   = array_diff( array_keys( $defaults ), array_keys( $sanitised ) );
	boot_check( empty( $dropped ), count( $defaults ) . ' defaults sanitise cleanly' . ( $dropped ? ', LOST: ' . implode( ', ', $dropped ) : '' ) );
} catch ( Throwable $e ) {
	boot_check( false, 'sanitiser round-trip: ' . $e->getMessage() );
}

// 7. Typography builders execute (font stacks, colour presets, webfont faces).
try {
	$stacks  = \Kolofon\get_font_stacks();
	$presets = \Kolofon\get_colour_presets();
	$faces   = \Kolofon\all_webfont_faces();
	boot_check(
		count( $stacks ) > 0 && count( $presets ) > 0,
		count( $stacks ) . ' font stacks, ' . count( $presets ) . ' colour presets, ' . strlen( $faces ) . ' bytes of @font-face'
	);
} catch ( Throwable $e ) {
	boot_check( false, 'typography builders: ' . $e->getMessage() );
}

// 8. The options page renders with no nested forms. A form printed inside the
//    settings form is invalid HTML: the browser drops the inner open tag and
//    the inner close tag ends the OUTER form early, leaving every later field
//    and the Save button outside any form. Clicking Save then does nothing.
//    This is exactly what the Now tab's inline "Create Now page" form did in
//    3.4.0 through 4.0.0, and this check exists so the class can never ship
//    again. Forms are allowed side by side (the import form uses the HTML5
//    form-attribute pattern from outside the settings form); they are never
//    allowed to overlap.
try {
	$GLOBALS['_boot_can'] = true;
	ob_start();
	\Kolofon\render_options_page();
	$rendered = ob_get_clean();
	unset( $GLOBALS['_boot_can'] );

	$depth  = 0;
	$max    = 0;
	$opens  = preg_match_all( '/<form\b/i', $rendered );
	$closes = preg_match_all( '/<\/form>/i', $rendered );
	$tokens = preg_split( '/(<form\b|<\/form>)/i', $rendered, -1, PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $tokens as $token ) {
		if ( 0 === strcasecmp( '<form', substr( $token, 0, 5 ) ) ) {
			++$depth;
			$max = max( $max, $depth );
		} elseif ( 0 === strcasecmp( '</form>', $token ) ) {
			--$depth;
		}
	}
	boot_check(
		$opens === $closes && 1 === $max && 0 === $depth,
		"options page forms: $opens open, $closes close, max nesting $max (must be 1)"
	);
} catch ( Throwable $e ) {
	unset( $GLOBALS['_boot_can'] );
	if ( ob_get_level() > 0 ) {
		ob_end_clean();
	}
	boot_check( false, 'options page render: ' . $e->getMessage() );
}

echo "\nRESULT: " . ( $failures ? "FAIL ($failures)" : 'PASS' ) . "\n";
exit( $failures ? 1 : 0 );
