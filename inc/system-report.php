<?php
/**
 * System report.
 *
 * Theme-owned runtime facts on one screen, deliberately not restating Site
 * Health. Every row answers a question that has actually come up: is the
 * bundled parser loaded, which icon is winning, which mode is a feature in,
 * where is the portrait coming from.
 *
 * @package MENJ\Bio
 * @since   1.4.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

/**
 * Build the report rows.
 *
 * Each row: label, value, and an optional note explaining consequence.
 *
 * @return array<int, array{label:string, value:string, note:string}>
 */
function get_system_report() {
	$rows = array();

	// Versions.
	$rows[] = array(
		'label' => __( 'Theme version', 'menj-bio' ),
		'value' => MENJ_BIO_VERSION,
		'note'  => is_child_theme()
			? __( 'Running as the parent of an active child theme.', 'menj-bio' )
			: __( 'Running directly, no child theme active.', 'menj-bio' ),
	);

	// Markdown parser.
	$parser = docs_parser();
	$rows[] = array(
		'label' => __( 'Documentation parser', 'menj-bio' ),
		'value' => $parser ? get_class( $parser ) : __( 'Unavailable', 'menj-bio' ),
		'note'  => $parser
			? __( 'Documentation renders as HTML on the Documentation tab.', 'menj-bio' )
			: __( 'Documentation falls back to escaped source in a pre block.', 'menj-bio' ),
	);

	// Icon precedence.
	$has_site_icon = (bool) get_option( 'site_icon' );
	$rows[]        = array(
		'label' => __( 'Favicon source', 'menj-bio' ),
		'value' => $has_site_icon ? __( 'Site Icon (Customiser)', 'menj-bio' ) : __( 'Theme bundled icons', 'menj-bio' ),
		'note'  => $has_site_icon
			? __( 'A Site Icon is set, so WordPress emits it and the theme stands down.', 'menj-bio' )
			: __( 'No Site Icon set; the theme emits its bundled favicon set.', 'menj-bio' ),
	);

	// Email protection.
	$modes  = get_email_modes();
	$mode   = email_mode();
	$rows[] = array(
		'label' => __( 'Email protection', 'menj-bio' ),
		'value' => isset( $modes[ $mode ] ) ? $modes[ $mode ] : $mode,
		'note'  => 'js' === $mode
			? __( 'The decoder script is enqueued only on pages that render a protected link.', 'menj-bio' )
			: '',
	);

	// Colour scheme.
	$scheme  = opt( 'colour_scheme' );
	$presets = get_colour_presets();
	$label   = isset( $presets[ $scheme ]['label'] ) ? $presets[ $scheme ]['label'] : $scheme;

	if ( 'auto' === $scheme ) {
		$note = __( 'Ivory when the device prefers light, Charcoal when it prefers dark.', 'menj-bio' );
	} else {
		$note = '';
	}

	$rows[] = array(
		'label' => __( 'Colour scheme', 'menj-bio' ),
		'value' => $label,
		'note'  => $note,
	);

	// Portrait source.
	$portrait = opt( 'hero_portrait' );
	$rows[]   = array(
		'label' => __( 'Hero portrait', 'menj-bio' ),
		'value' => $portrait ? __( 'Uploaded image', 'menj-bio' ) : __( 'Bundled default', 'menj-bio' ),
		'note'  => $portrait ? esc_url( $portrait ) : MENJ_BIO_URI . 'assets/img/profile.png',
	);

	// Metadata ownership.
	$detected = detected_seo_plugin();
	$emitting = ( '' === $detected ) && 1 === intval( opt( 'emit_meta_tags' ) );
	$rows[]   = array(
		'label' => __( 'Meta tags and schema', 'menj-bio' ),
		'value' => $emitting ? __( 'Emitted by the theme', 'menj-bio' ) : __( 'Standing down', 'menj-bio' ),
		'note'  => '' !== $detected
			? sprintf(
				/* translators: %s: plugin name */
				__( '%s is active and owns this output.', 'menj-bio' ),
				$detected
			)
			: ( $emitting ? '' : __( 'Disabled on the Advanced tab.', 'menj-bio' ) ),
	);

	// File editors.
	$config_locked = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;
	$rows[]        = array(
		'label' => __( 'File editors', 'menj-bio' ),
		'value' => ( $config_locked || file_editors_disabled() ) ? __( 'Disabled', 'menj-bio' ) : __( 'Available', 'menj-bio' ),
		'note'  => $config_locked
			? __( 'Locked by DISALLOW_FILE_EDIT in wp-config.php, which takes precedence over the theme setting.', 'menj-bio' )
			: __( 'Controlled by the theme setting on the Advanced tab.', 'menj-bio' ),
	);

	// Blog index page — where "View all" points, and whether the activation
	// hook has provisioned it. Written to surface the class of bug where the
	// hook did not fire, since a 404 on /blog is otherwise mysterious.
	$blog_url            = get_blog_index_url();
	$page_for_posts      = (int) get_option( 'page_for_posts' );
	$blog_template_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'page-blog.php',     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	if ( $page_for_posts ) {
		$blog_source = __( 'Settings > Reading (Posts page)', 'menj-bio' );
	} elseif ( ! empty( $blog_template_pages ) ) {
		$blog_source = __( 'Page with Blog Index template', 'menj-bio' );
	} else {
		$blog_source = __( 'Convention (/blog); no page found', 'menj-bio' );
	}

	$rows[] = array(
		'label' => __( 'Blog index', 'menj-bio' ),
		'value' => $blog_url,
		'note'  => empty( $blog_template_pages ) && ! $page_for_posts
			? __( 'No page provisioned. Reactivate the theme to trigger the activation hook, or set a Posts page under Settings > Reading.', 'menj-bio' )
			: $blog_source,
	);

	// Sections.
	$sections = get_sections();
	$slugs    = parse_section_slugs( opt( 'section_slugs' ) );
	$rows[]   = array(
		'label' => __( 'Sections', 'menj-bio' ),
		'value' => sprintf(
			/* translators: 1: resolved count, 2: configured count */
			__( '%1$d of %2$d configured slugs resolve', 'menj-bio' ),
			count( $sections ),
			count( $slugs )
		),
		'note'  => count( $sections ) < count( $slugs )
			? __( 'At least one configured slug has no matching category. The Sections tab lists which.', 'menj-bio' )
			: '',
	);

	// Documentation set.
	$docs   = list_docs();
	$rows[] = array(
		'label' => __( 'Documentation files', 'menj-bio' ),
		'value' => (string) count( $docs ),
		'note'  => implode( ', ', array_keys( $docs ) ),
	);

	/**
	 * Filter the system report rows.
	 *
	 * @param array $rows Report rows.
	 */
	return apply_filters( 'menj_bio_system_report', $rows );
}

/**
 * Render the report panel.
 */
function render_system_panel() {
	?>
	<div class="menj-bio-system-panel">
		<table class="widefat striped menj-bio-system-table">
			<tbody>
				<?php foreach ( get_system_report() as $row ) : ?>
					<tr>
						<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
						<td>
							<strong><?php echo esc_html( $row['value'] ); ?></strong>
							<?php if ( '' !== $row['note'] ) : ?>
								<p class="description"><?php echo esc_html( $row['note'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
