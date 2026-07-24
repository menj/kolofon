<?php
/**
 * Tabbed Theme Options page under Appearance -> Theme Options.
 *
 * Uses the Settings API. Tabs are rendered client-side (no page reload)
 * with a single option group so all fields save in one request.
 *
 * @package Kolofon
 * @since   1.0.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

const OPTION_GROUP = 'kolofon_option_group';
const OPTION_PAGE  = 'kolofon_options_page';

add_action( 'admin_menu', __NAMESPACE__ . '\\register_options_page' );
add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_options_assets' );
add_action( 'admin_post_kolofon_reset', __NAMESPACE__ . '\\handle_reset' );

/**
 * Add the Appearance submenu entry.
 */
function register_options_page() {
	add_theme_page(
		__( 'Theme Options', 'kolofon' ),
		__( 'Theme Options', 'kolofon' ),
		'manage_options',
		'kolofon-options',
		__NAMESPACE__ . '\\render_options_page'
	);
}

/**
 * Enqueue the admin CSS and tab-switcher JS only on our screen.
 *
 * @param string $hook Current admin page hook.
 */
function enqueue_options_assets( $hook ) {
	if ( 'appearance_page_kolofon-options' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'kolofon-admin-options',
		KOLOFON_URI . 'assets/css/admin-options.css',
		array(),
		KOLOFON_VERSION
	);

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_media();

	wp_enqueue_script(
		'kolofon-admin-options',
		KOLOFON_URI . 'assets/js/admin-options.js',
		array( 'jquery', 'wp-color-picker' ),
		KOLOFON_VERSION,
		true
	);
}

/**
 * Register the single stored option and all fields.
 */
function register_settings() {
	register_setting(
		OPTION_GROUP,
		KOLOFON_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_options',
			'default'           => get_defaults(),
		)
	);

	// One settings section per tab, so a tab added through the filter gets
	// its section without the extension needing to know they are separate
	// concepts in the Settings API.
	foreach ( array_keys( get_form_tabs() ) as $tab ) {
		add_settings_section( $tab, '', '__return_false', OPTION_PAGE );
	}

	// One registration path for every field, built-in or filtered in.
	foreach ( get_option_fields() as $key => $field ) {
		if ( empty( $field['tab'] ) || empty( $field['type'] ) ) {
			continue;
		}

		$args = isset( $field['args'] ) && is_array( $field['args'] ) ? $field['args'] : array();

		// Help text and choices may be callables when they depend on runtime
		// state, resolved here so renderers only ever see plain values.
		if ( isset( $args['help'] ) && is_callable( $args['help'] ) ) {
			$args['help'] = call_user_func( $args['help'] );
		}
		if ( isset( $args['choices'] ) && is_callable( $args['choices'] ) ) {
			$args['choices'] = call_user_func( $args['choices'] );
		}

		add_field(
			$key,
			isset( $field['label'] ) ? $field['label'] : $key,
			$field['tab'],
			$field['type'],
			$args
		);
	}
}

/**
 * Thin wrapper around add_settings_field.
 *
 * @param string $key   Option key.
 * @param string $label Field label.
 * @param string $tab   Tab / section id.
 * @param string $type  Field type.
 * @param array  $args  Extra render args.
 */
function add_field( $key, $label, $tab, $type, $args = array() ) {
	add_settings_field(
		$key,
		esc_html( $label ),
		__NAMESPACE__ . '\\render_field',
		OPTION_PAGE,
		$tab,
		array_merge( array( 'key' => $key, 'type' => $type ), $args )
	);
}

/**
 * Render a single settings field.
 *
 * @param array $args Field args.
 */
function render_field( $args ) {
	$key   = $args['key'];
	$type  = $args['type'];
	$value = opt( $key );
	$name  = KOLOFON_OPTION_KEY . '[' . $key . ']';

	switch ( $type ) {
		case 'text':
			printf(
				'<input type="text" class="regular-text" name="%1$s" id="%2$s" value="%3$s" />',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( $value )
			);
			break;

		case 'url':
			$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : 'https://';
			// Some fields (Email) accept a bare address as well as a full URL,
			// so they opt out of the browser's native URL validation.
			$input_type  = isset( $args['input_type'] ) ? $args['input_type'] : 'url';
			printf(
				'<input type="%1$s" class="regular-text" name="%2$s" id="%3$s" value="%4$s" placeholder="%5$s" />',
				esc_attr( $input_type ),
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $placeholder )
			);
			break;

		case 'textarea':
			$rows = isset( $args['rows'] ) ? intval( $args['rows'] ) : 3;
			printf(
				'<textarea class="large-text" rows="%1$d" name="%2$s" id="%3$s">%4$s</textarea>',
				$rows,
				esc_attr( $name ),
				esc_attr( $key ),
				esc_textarea( $value )
			);
			break;

		case 'image':
			printf(
				'<div class="kolofon-image-field">
					<input type="url" class="regular-text kolofon-image-url" name="%1$s" id="%2$s" value="%3$s" placeholder="https://" />
					<button type="button" class="button kolofon-image-choose">%4$s</button>
					<button type="button" class="button-link kolofon-image-clear" %5$s>%6$s</button>
					<div class="kolofon-image-preview" %5$s>
						<img src="%3$s" alt="" />
					</div>
				</div>',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_url( $value ),
				esc_html__( 'Choose image', 'kolofon' ),
				empty( $value ) ? 'hidden' : '',
				esc_html__( 'Remove', 'kolofon' )
			);
			break;

		case 'number':
			$min  = isset( $args['min'] ) ? intval( $args['min'] ) : 0;
			$max  = isset( $args['max'] ) ? intval( $args['max'] ) : 9999;
			$step = isset( $args['step'] ) ? intval( $args['step'] ) : 1;
			printf(
				'<input type="number" name="%1$s" id="%2$s" value="%3$s" min="%4$d" max="%5$d" step="%6$d" />',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( $value ),
				$min,
				$max,
				$step
			);
			break;

		case 'select':
			$choices = isset( $args['choices'] ) ? $args['choices'] : array();
			printf( '<select name="%s" id="%s">', esc_attr( $name ), esc_attr( $key ) );
			foreach ( $choices as $choice_value => $choice_label ) {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $choice_value ),
					selected( $value, $choice_value, false ),
					esc_html( $choice_label )
				);
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf(
				'<label><input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s /> %4$s</label>',
				esc_attr( $name ),
				esc_attr( $key ),
				checked( 1, intval( $value ), false ),
				esc_html__( 'Enabled', 'kolofon' )
			);
			break;

		case 'colour':
			printf(
				'<input type="text" class="kolofon-color-picker" name="%1$s" id="%2$s" value="%3$s" data-default-color="%3$s" />',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( $value )
			);
			break;

		case 'radio_presets':
			foreach ( get_colour_presets() as $slug => $preset ) {
				printf(
					'<label class="kolofon-preset"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br />',
					esc_attr( $name ),
					esc_attr( $slug ),
					checked( $value, $slug, false ),
					esc_html( $preset['label'] )
				);
			}
			break;

		case 'font_stack':
			foreach ( get_font_stacks() as $slug => $stack ) {
				printf(
					'<label class="kolofon-preset"><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br />',
					esc_attr( $name ),
					esc_attr( $slug ),
					checked( $value, $slug, false ),
					esc_html( $stack['label'] )
				);
			}
			break;

		case 'export_button':
			printf(
				'<a href="%1$s" class="button button-secondary">%2$s</a>',
				esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=kolofon_export' ), 'kolofon_export' ) ),
				esc_html__( 'Download JSON', 'kolofon' )
			);
			break;

		case 'import_button':
			// Rendered as a standalone form, since the settings form posts to
			// options.php and cannot carry a file upload to a different handler.
			printf(
				'<div class="kolofon-import">
					<input type="file" form="kolofon-import-form" name="kolofon_import_file" accept="application/json,.json" required />
					<button type="submit" form="kolofon-import-form" class="button button-secondary" onclick="return confirm(\'%1$s\');">%2$s</button>
				</div>',
				esc_js( __( 'This replaces all current settings. Continue?', 'kolofon' ) ),
				esc_html__( 'Upload and replace', 'kolofon' )
			);
			break;

		case 'heading_text':
			printf(
				'<input type="text" class="large-text" name="%1$s" id="%2$s" value="%3$s" />',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr( $value )
			);
			break;

		case 'section_slugs':
			printf(
				'<textarea class="large-text code" rows="3" name="%1$s" id="%2$s" placeholder="%3$s">%4$s</textarea>',
				esc_attr( $name ),
				esc_attr( $key ),
				esc_attr__( 'first-slug, second-slug, third-slug', 'kolofon' ),
				esc_textarea( $value )
			);

			// Resolve each configured slug and report it, so a typo surfaces
			// here rather than as a section that quietly fails to appear.
			$slugs = parse_section_slugs( $value );

			if ( $slugs ) {
				echo '<ul class="kolofon-slug-report">';
				foreach ( $slugs as $slug ) {
					$term = get_term_by( 'slug', $slug, 'category' );
					if ( $term instanceof \WP_Term ) {
						printf(
							'<li class="is-ok"><code>%1$s</code> %2$s <span class="kolofon-slug-count">%3$s</span></li>',
							esc_html( $slug ),
							esc_html( $term->name ),
							esc_html(
								sprintf(
									/* translators: %s: number of posts */
									_n( '%s post', '%s posts', $term->count, 'kolofon' ),
									number_format_i18n( $term->count )
								)
							)
						);
					} else {
						printf(
							'<li class="is-missing"><code>%1$s</code> %2$s</li>',
							esc_html( $slug ),
							esc_html__( 'no category with this slug exists yet', 'kolofon' )
						);
					}
				}
				echo '</ul>';

				printf(
					'<p class="description"><a href="%1$s">%2$s</a></p>',
					esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ),
					esc_html__( 'Manage categories', 'kolofon' )
				);
			}
			break;

		case 'reset_button':
			$reset_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=kolofon_reset' ),
				'kolofon_reset'
			);
			printf(
				'<a href="%1$s" class="button button-secondary" onclick="return confirm(\'%2$s\');">%3$s</a>',
				esc_url( $reset_url ),
				esc_js( __( 'Restore all options to default values. Continue?', 'kolofon' ) ),
				esc_html__( 'Restore defaults', 'kolofon' )
			);
			break;
	}

	if ( ! empty( $args['help'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
	}
}

/**
 * Handle the Restore Defaults action.
 */
function handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do that.', 'kolofon' ) );
	}

	check_admin_referer( 'kolofon_reset' );

	update_option( KOLOFON_OPTION_KEY, get_defaults() );

	redirect_to_options( 'reset-ok' );
}

/**
 * Render the options page shell (tabs + Settings API form).
 */
function render_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$tabs = get_option_tabs();

	// The Documentation tab renders outside the settings form, so it carries
	// no section and no fields.
	$form_tabs = get_form_tabs();
	?>
	<div class="wrap kolofon-wrap">
		<h1><?php esc_html_e( 'Theme Options', 'kolofon' ); ?></h1>

		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flag set by our own redirect.
		$status = isset( $_GET['mb_status'] ) ? sanitize_key( wp_unslash( $_GET['mb_status'] ) ) : '';
		$notices = get_status_messages();

		if ( $status && isset( $notices[ $status ] ) ) {
			printf(
				'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
				esc_attr( $notices[ $status ]['type'] ),
				esc_html( $notices[ $status ]['text'] )
			);
		}
		?>

		<?php settings_errors(); ?>

		<?php
		/*
		 * The import form is declared here rather than inside the settings
		 * form, because HTML forbids nesting forms and the settings form must
		 * post to options.php. The file input and button inside the Advanced
		 * tab reference this form by id, which HTML5 permits.
		 */
		?>
		<form id="kolofon-import-form" method="post" enctype="multipart/form-data"
			action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="kolofon-hidden-form">
			<input type="hidden" name="action" value="kolofon_import" />
			<?php wp_nonce_field( 'kolofon_import' ); ?>
		</form>

		<h2 class="screen-reader-text"><?php esc_html_e( 'Settings sections', 'kolofon' ); ?></h2>
		<div class="nav-tab-wrapper kolofon-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Theme options sections', 'kolofon' ); ?>">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<button type="button" role="tab" id="tabctl-<?php echo esc_attr( $slug ); ?>"
					class="nav-tab" data-tab="<?php echo esc_attr( $slug ); ?>"
					aria-controls="tab-<?php echo esc_attr( $slug ); ?>" aria-selected="false" tabindex="-1">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<form action="options.php" method="post" class="kolofon-form">
			<?php settings_fields( OPTION_GROUP ); ?>

			<?php foreach ( array_keys( $form_tabs ) as $slug ) : ?>
				<div class="kolofon-panel" id="tab-<?php echo esc_attr( $slug ); ?>" role="tabpanel"
					aria-labelledby="tabctl-<?php echo esc_attr( $slug ); ?>" tabindex="0">
					<table class="form-table" role="presentation">
						<?php do_settings_fields( OPTION_PAGE, $slug ); ?>
					</table>
				</div>
			<?php endforeach; ?>

			<?php submit_button(); ?>
		</form>

		<div class="kolofon-panel" id="tab-system" role="tabpanel" aria-labelledby="tabctl-system" tabindex="0">
			<?php render_system_panel(); ?>
		</div>

		<div class="kolofon-panel kolofon-docs-panel" id="tab-docs" role="tabpanel" aria-labelledby="tabctl-docs" tabindex="0">
			<?php render_docs_panel(); ?>
		</div>
	</div>
	<?php
}
