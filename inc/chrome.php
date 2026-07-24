<?php
/**
 * Chrome layout.
 *
 * Two arrangements for the site chrome. Topbar is the original full-bleed
 * header. Sidebar puts everything into a floating card in a left rail:
 * wordmark, numbered navigation, and a stay-in-touch block, leaving the
 * content column free. Offered as an option rather than imposed; topbar
 * remains the default.
 *
 * The numbered navigation doubles as keyboard shortcuts, and the digits exist
 * to advertise that. Both the numbering and the shortcuts apply only in the
 * sidebar layout, because a row of digits in a horizontal top bar reads as
 * noise where a vertical list reads as an index.
 *
 * @package Kolofon
 * @since   1.5.0
 */

namespace Kolofon;

defined( 'ABSPATH' ) || exit;

add_filter( 'body_class', __NAMESPACE__ . '\\chrome_body_class' );
add_filter( 'wp_nav_menu_objects', __NAMESPACE__ . '\\index_nav_items', 10, 2 );
add_filter( 'nav_menu_item_title', __NAMESPACE__ . '\\number_nav_item', 20, 2 );
add_filter( 'nav_menu_link_attributes', __NAMESPACE__ . '\\nav_key_attribute', 10, 2 );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_keyboard_nav', 30 );

/**
 * Active chrome layout, validated.
 *
 * @return string `topbar` or `sidebar`.
 */
function chrome_layout() {
	$layout = opt( 'chrome_layout' );
	return in_array( $layout, array( 'topbar', 'sidebar' ), true ) ? $layout : 'topbar';
}

/**
 * Whether the sidebar layout is active.
 *
 * @return bool
 */
function is_sidebar_layout() {
	return 'sidebar' === chrome_layout();
}

/**
 * Whether keyboard navigation is on. Sidebar layout only.
 *
 * @return bool
 */
function keyboard_nav_enabled() {
	return is_sidebar_layout() && 1 === intval( opt( 'keyboard_nav' ) );
}

/**
 * Body class for the active layout, so the stylesheet branches on one hook.
 *
 * Also surfaces the active font stack, since a stack that pairs two families
 * needs to tune heading rhythm distinctly from a single-family stack.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function chrome_body_class( $classes ) {
	$classes[] = 'layout-' . chrome_layout();
	$classes[] = 'font-' . sanitize_html_class( (string) opt( 'font_stack' ) );
	return $classes;
}

/**
 * Attach a position index to primary menu items.
 *
 * Runs before the title filter, which needs to know each item's place. Top
 * level only; the sidebar menu renders at depth 1.
 *
 * @param array     $items Menu items.
 * @param \stdClass $args  Menu args.
 * @return array
 */
function index_nav_items( $items, $args ) {
	if ( ! is_sidebar_layout() || 'primary' !== $args->theme_location ) {
		return $items;
	}

	$position = 0;

	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		$item->kolofon_index = $position;
		++$position;
	}

	return $items;
}

/**
 * Prepend the boxed digit to a numbered item's title.
 *
 * Composes with the planned-page badge filter, which appends at priority 10;
 * this runs at 20 and prepends, so both render.
 *
 * @param string   $title Item title.
 * @param \WP_Post $item  Menu item.
 * @return string
 */
function number_nav_item( $title, $item ) {
	if ( is_admin() || ! isset( $item->kolofon_index ) ) {
		return $title;
	}

	// Ten digits exist; later items go unnumbered rather than wrapping.
	if ( $item->kolofon_index > 9 ) {
		return $title;
	}

	return '<span class="nav-num" aria-hidden="true">' . intval( $item->kolofon_index ) . '</span>' . $title;
}

/**
 * Data attribute carrying the shortcut key, read by the keyboard script.
 *
 * @param array    $atts Link attributes.
 * @param \WP_Post $item Menu item.
 * @return array
 */
function nav_key_attribute( $atts, $item ) {
	if ( keyboard_nav_enabled() && isset( $item->kolofon_index ) && $item->kolofon_index <= 9 ) {
		$atts['data-mb-key'] = (string) intval( $item->kolofon_index );
	}

	return $atts;
}

/**
 * Enqueue the shortcut listener only when it can do something.
 */
function enqueue_keyboard_nav() {
	if ( ! keyboard_nav_enabled() || ! has_nav_menu( 'primary' ) ) {
		return;
	}

	wp_enqueue_script(
		'kolofon-keyboard-nav',
		KOLOFON_URI . 'assets/js/keyboard-nav.js',
		array(),
		KOLOFON_VERSION,
		true
	);
}

/**
 * Render the stay-in-touch block for the sidebar card.
 *
 * Text links rather than icons, one per row with an outward arrow, because a
 * vertical card gives each link room to carry its name. The email entry goes
 * through the obfuscator like everywhere else.
 */
function render_sidebar_social() {
	$active = get_active_social_links();

	if ( empty( $active ) ) {
		return;
	}
	?>
	<div class="sidebar-social">
		<p class="sidebar-social-heading"><?php echo esc_html( opt( 'sidebar_social_heading' ) ); ?></p>
		<ul class="sidebar-social-list">
			<?php foreach ( $active as $slug => $meta ) : ?>
				<li>
					<?php if ( 'email' === $slug ) : ?>
						<?php
						echo protected_mailto( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- builder escapes internally.
							$meta['url'],
							esc_html( $meta['label'] ) . '<span class="sidebar-social-arrow" aria-hidden="true">&#8599;</span>',
							array( 'class' => 'sidebar-social-link k-email' )
						);
						?>
					<?php else : ?>
						<a class="sidebar-social-link" href="<?php echo esc_url( $meta['url'] ); ?>"
							<?php echo ! empty( $meta['rel'] ) ? 'rel="' . esc_attr( $meta['rel'] ) . '"' : ''; ?>>
							<?php echo esc_html( $meta['label'] ); ?><span class="sidebar-social-arrow" aria-hidden="true">&#8599;</span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}
