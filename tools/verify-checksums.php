#!/usr/bin/env php
<?php
/**
 * Verify an installed Kolofon against its shipped checksum manifest.
 *
 * Answers the question a security scanner cannot: are these files the ones the
 * release shipped, or has something changed? A scanner only sees that theme
 * files are newer than wp-config.php, which is true of every freshly installed
 * theme and proves nothing either way.
 *
 * Run over SSH from the theme directory:
 *
 *   php tools/verify-checksums.php
 *
 * Exit status is 0 when everything matches, 1 when anything does not, so it can
 * be wired into a cron job or a deployment check.
 *
 * @package Kolofon
 */

$root     = dirname( __DIR__ );
$manifest = $root . '/checksums.sha256';

if ( ! is_readable( $manifest ) ) {
	fwrite( STDERR, "No checksums.sha256 found. This release did not ship one.\n" );
	exit( 1 );
}

$expected = array();
foreach ( file( $manifest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) {
	if ( '#' === substr( $line, 0, 1 ) ) {
		continue;
	}
	$parts = preg_split( '/\s+/', trim( $line ), 2 );
	if ( 2 === count( $parts ) ) {
		$expected[ $parts[1] ] = $parts[0];
	}
}

$modified = array();
$missing  = array();

foreach ( $expected as $path => $hash ) {
	$full = $root . '/' . $path;

	if ( ! is_readable( $full ) ) {
		$missing[] = $path;
		continue;
	}

	if ( ! hash_equals( $hash, hash_file( 'sha256', $full ) ) ) {
		$modified[] = $path;
	}
}

// A file present on disk but absent from the manifest is the shape an injected
// backdoor actually takes, so it is reported separately and treated as a
// failure rather than ignored.
$skip  = array( '/tests/', '/tools/', '/node_modules/', '/.git/' );
$extra = array();

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
);

foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}

	$path = str_replace( $root . DIRECTORY_SEPARATOR, '', $file->getPathname() );
	$path = str_replace( DIRECTORY_SEPARATOR, '/', $path );

	foreach ( $skip as $fragment ) {
		if ( false !== strpos( '/' . $path, $fragment ) ) {
			continue 2;
		}
	}

	if ( ! isset( $expected[ $path ] ) ) {
		$extra[] = $path;
	}
}

printf( "Checked %d files against the manifest.\n\n", count( $expected ) );

if ( empty( $modified ) && empty( $missing ) && empty( $extra ) ) {
	echo "PASS. Every file matches the release. Nothing has been added, removed or altered.\n";
	exit( 0 );
}

if ( $modified ) {
	printf( "MODIFIED (%d):\n", count( $modified ) );
	foreach ( $modified as $p ) {
		echo '  ' . $p . "\n";
	}
	echo "\n";
}

if ( $extra ) {
	printf( "NOT IN THE RELEASE (%d). Inspect these first:\n", count( $extra ) );
	foreach ( $extra as $p ) {
		echo '  ' . $p . "\n";
	}
	echo "\n";
}

if ( $missing ) {
	printf( "MISSING (%d):\n", count( $missing ) );
	foreach ( $missing as $p ) {
		echo '  ' . $p . "\n";
	}
	echo "\n";
}

exit( 1 );
