<?php
/**
 * The site footer.
 *
 * @package Kolofon
 */

?>
</div><!-- .site-content -->

<footer class="site-footer" role="contentinfo">
	<div class="container">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'kolofon' ); ?>">
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

		<div class="footer-text">
			<?php echo wp_kses_post( \Kolofon\opt( 'footer_text' ) ); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
