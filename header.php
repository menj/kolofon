<?php
/**
 * The site header.
 *
 * @package Kolofon
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'kolofon' ); ?></a>

<?php if ( \Kolofon\is_sidebar_layout() ) : ?>

<header class="site-header sidebar-card" role="banner">
	<div class="site-branding">
		<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<p class="site-title">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'kolofon' ); ?>" data-toggle-label="<?php esc_attr_e( 'Menu', 'kolofon' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>
	<?php endif; ?>

	<?php \Kolofon\render_sidebar_social(); ?>
</header>

<?php else : ?>

<header class="site-header" role="banner">
	<div class="container">
		<div class="site-branding">
			<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'kolofon' ); ?>" data-toggle-label="<?php esc_attr_e( 'Menu', 'kolofon' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'nav-menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</header>

<?php endif; ?>

<div id="content" class="site-content">
