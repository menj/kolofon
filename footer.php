<?php
/**
 * The site footer.
 *
 * @package Kolofon
 */

?>
</div><!-- .site-content -->

<footer class="site-footer" role="contentinfo" itemscope="itemscope" itemtype="https://schema.org/WPFooter">
	<div class="container">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'kolofon' ); ?>" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'nav-menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="footer-text" itemprop="copyrightNotice">
			<?php echo wp_kses_post( \Kolofon\opt( 'footer_text' ) ); ?>
		</div>

		<?php if ( \Kolofon\uses_attributed_icon() ) : ?>
			<div class="footer-attribution">
				<?php
				printf(
					/* translators: %s: "Font Awesome" linked to its site. */
					esc_html__( 'Icons by %s', 'kolofon' ),
					'<a href="https://fontawesome.com" rel="noopener noreferrer">Font Awesome</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static internal markup.
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</footer>

<?php \Kolofon\run_guarded_hook( 'wp_footer' ); ?>
<?php \Kolofon\render_guarded_hook_admin_notice(); ?>
</body>
</html>
