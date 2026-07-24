<?php
/**
 * Page states.
 *
 * A page can be marked as planned: it stays in the navigation carrying a
 * badge, and its content is replaced by a short notice saying what it will
 * contain. The deliberate inverse of unlisting. An empty site with intent
 * reads as a roadmap instead of a shell.
 *
 * State lives in post meta, so it survives a theme switch as data even though
 * the rendering here does not.
 *
 * @package MENJ\Bio
 * @since   1.4.0
 */

namespace MENJ\Bio;

defined( 'ABSPATH' ) || exit;

const PAGE_STATE_META = '_menj_bio_page_state';

add_action( 'init', __NAMESPACE__ . '\\register_page_state_meta' );
add_action( 'add_meta_boxes_page', __NAMESPACE__ . '\\add_page_state_box' );
add_action( 'save_post_page', __NAMESPACE__ . '\\save_page_state' );
add_filter( 'nav_menu_item_title', __NAMESPACE__ . '\\badge_nav_item', 10, 2 );
add_filter( 'the_content', __NAMESPACE__ . '\\replace_planned_content', 5 );
add_filter( 'body_class', __NAMESPACE__ . '\\planned_body_class' );

/**
 * Register the meta so it is visible to REST and survives exports.
 */
function register_page_state_meta() {
	register_post_meta(
		'page',
		PAGE_STATE_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => __NAMESPACE__ . '\\sanitize_page_state',
			'auth_callback'     => function () {
				return current_user_can( 'edit_pages' );
			},
		)
	);
}

/**
 * Valid states.
 *
 * @return array<string, string>
 */
function get_page_states() {
	return array(
		''     => __( 'Published normally', 'menj-bio' ),
		'soon' => __( 'Planned, not yet written', 'menj-bio' ),
	);
}

/**
 * Clamp a state value to the known set.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function sanitize_page_state( $value ) {
	$value = (string) $value;
	return array_key_exists( $value, get_page_states() ) ? $value : '';
}

/**
 * Whether a page is planned.
 *
 * @param int|null $page_id Page ID, or null for the current post.
 * @return bool
 */
function page_is_planned( $page_id = null ) {
	$page_id = $page_id ? (int) $page_id : get_the_ID();

	if ( ! $page_id ) {
		return false;
	}

	return 'soon' === get_post_meta( $page_id, PAGE_STATE_META, true );
}

/**
 * Meta box on the page editor.
 */
function add_page_state_box() {
	add_meta_box(
		'menj-bio-page-state',
		__( 'Page state', 'menj-bio' ),
		__NAMESPACE__ . '\\render_page_state_box',
		'page',
		'side',
		'default'
	);
}

/**
 * Render the meta box.
 *
 * @param \WP_Post $post Current page.
 */
function render_page_state_box( $post ) {
	wp_nonce_field( 'menj_bio_page_state', 'menj_bio_page_state_nonce' );

	$current = get_post_meta( $post->ID, PAGE_STATE_META, true );
	?>
	<p>
		<?php foreach ( get_page_states() as $value => $label ) : ?>
			<label style="display:block;margin-bottom:4px;">
				<input type="radio" name="menj_bio_page_state" value="<?php echo esc_attr( $value ); ?>" <?php checked( $current, $value ); ?> />
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'A planned page stays in the navigation with a badge, and shows its excerpt as a short description of what it will contain.', 'menj-bio' ); ?>
	</p>
	<?php
}

/**
 * Persist the meta box.
 *
 * @param int $post_id Page ID.
 */
function save_page_state( $post_id ) {
	if ( ! isset( $_POST['menj_bio_page_state_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['menj_bio_page_state_nonce'] ) ), 'menj_bio_page_state' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$state = isset( $_POST['menj_bio_page_state'] ) ? sanitize_text_field( wp_unslash( $_POST['menj_bio_page_state'] ) ) : '';

	if ( '' === sanitize_page_state( $state ) ) {
		delete_post_meta( $post_id, PAGE_STATE_META );
	} else {
		update_post_meta( $post_id, PAGE_STATE_META, sanitize_page_state( $state ) );
	}
}

/**
 * Append the badge to navigation items that point at a planned page.
 *
 * @param string    $title Item title.
 * @param \WP_Post  $item  Menu item.
 * @return string
 */
function badge_nav_item( $title, $item ) {
	if ( is_admin() ) {
		return $title;
	}

	if ( ! isset( $item->object, $item->object_id ) || 'page' !== $item->object ) {
		return $title;
	}

	if ( ! page_is_planned( (int) $item->object_id ) ) {
		return $title;
	}

	return $title . ' <span class="nav-badge">' . esc_html( opt( 'planned_badge_label' ) ) . '</span>';
}

/**
 * Replace a planned page's content with the notice.
 *
 * Runs early on the_content so later filters (email guard, embeds) still see
 * the final markup. The page's excerpt, when set, becomes the description of
 * what the page will contain; the body is held back until it is real.
 *
 * @param string $content Page content.
 * @return string
 */
function replace_planned_content( $content ) {
	if ( ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! page_is_planned() ) {
		return $content;
	}

	$excerpt = get_the_excerpt();

	$html  = '<div class="planned-notice">';
	$html .= '<p class="planned-badge-line"><span class="nav-badge">' . esc_html( opt( 'planned_badge_label' ) ) . '</span></p>';

	if ( $excerpt ) {
		$html .= '<p class="planned-description">' . esc_html( $excerpt ) . '</p>';
	}

	$html .= '<p class="planned-note">' . esc_html( opt( 'planned_notice_text' ) ) . '</p>';
	$html .= '</div>';

	return $html;
}

/**
 * Body class for styling planned pages.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function planned_body_class( $classes ) {
	if ( is_page() && page_is_planned() ) {
		$classes[] = 'is-planned-page';
	}

	return $classes;
}
