<?php
/**
 * Migration notice.
 *
 * Shows a dismissible admin notice on the first Kolofon page load after a
 * migration from menj-bio has run. Gives the site owner positive confirmation
 * that their prior configuration carried over rather than trusting silence.
 *
 * @package Kolofon
 * @since   3.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', __NAMESPACE__ . '\\render_migration_notice' );
add_action( 'admin_post_kolofon_dismiss_migration_notice', __NAMESPACE__ . '\\dismiss_migration_notice' );

/**
 * Render the migration notice if the flag is present.
 *
 * The flag `kolofon_rename_migration_notice` is set by `migrate_stored_options()`
 * when it actually copies a legacy option row. If the flag is not present the
 * migration either did not run or has already been dismissed.
 */
function render_migration_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$flag = get_option( 'kolofon_rename_migration_notice', null );

	if ( ! is_array( $flag ) || empty( $flag['count'] ) ) {
		return;
	}

	$count = intval( $flag['count'] );

	$dismiss_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=kolofon_dismiss_migration_notice' ),
		'kolofon_dismiss_migration_notice'
	);

	?>
	<div class="notice notice-success is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Kolofon: migration complete.', 'kolofon' ); ?></strong>
			<?php
			printf(
				/* translators: %d: number of stored options migrated */
				esc_html( _n(
					'%d setting was carried over from the previous menj-bio theme.',
					'%d settings were carried over from the previous menj-bio theme.',
					$count,
					'kolofon'
				) ),
				$count
			);
			?>
			<?php esc_html_e( 'Review the Kolofon options page to confirm everything is where you expect it.', 'kolofon' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=kolofon-options' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Open Kolofon Options', 'kolofon' ); ?>
			</a>
			<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button">
				<?php esc_html_e( 'Dismiss', 'kolofon' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/**
 * Delete the migration flag, redirect back.
 *
 * The "is-dismissible" X button on the notice dismisses only for the session;
 * this explicit dismiss removes the flag permanently, so the notice does not
 * reappear on the next admin visit.
 */
function dismiss_migration_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to perform this action.', 'kolofon' ) );
	}

	check_admin_referer( 'kolofon_dismiss_migration_notice' );

	delete_option( 'kolofon_rename_migration_notice' );

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
