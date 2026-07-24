<?php
/**
 * The site footer.
 *
 * @package MENJ\Bio
 */

?>
</div><!-- .site-content -->

<footer class="site-footer" role="contentinfo">
	<div class="container">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'menj-bio' ); ?>">
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
			<?php echo wp_kses_post( \MENJ\Bio\opt( 'footer_text' ) ); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
