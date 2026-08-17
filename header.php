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
	<?php \Kolofon\run_guarded_hook( 'wp_head' ); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Template for the search overlay. The overlay script (search-overlay.js)
// clones this content so the form inside is exactly get_search_form(), with
// all its attributes and labels. Printed on every page; inert without the
// script, which is why search still works with JavaScript off through the
// forms on the results and 404 pages.
?>
<template id="kolofon-search-template"
	data-open-label="<?php esc_attr_e( 'Search', 'kolofon' ); ?>"
	data-close-label="<?php esc_attr_e( 'Close search', 'kolofon' ); ?>"
	data-dialog-label="<?php esc_attr_e( 'Search this site', 'kolofon' ); ?>">
	<p class="search-overlay-heading"><?php esc_html_e( 'Search this site', 'kolofon' ); ?></p>
	<?php get_search_form(); ?>
</template>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'kolofon' ); ?></a>

<?php if ( \Kolofon\is_sidebar_layout() ) : ?>

<header class="site-header sidebar-card" role="banner" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="site-branding" itemprop="publisher" itemscope="itemscope" itemtype="https://schema.org/Organization">
		<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<p class="site-title">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><span itemprop="name"><?php bloginfo( 'name' ); ?></span></a>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'kolofon' ); ?>" data-toggle-label="<?php esc_attr_e( 'Menu', 'kolofon' ); ?>" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
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

<header class="site-header" role="banner" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="container">
		<div class="site-branding" itemprop="publisher" itemscope="itemscope" itemtype="https://schema.org/Organization">
			<?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<p class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" itemprop="url"><span itemprop="name"><?php bloginfo( 'name' ); ?></span></a>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'kolofon' ); ?>" data-toggle-label="<?php esc_attr_e( 'Menu', 'kolofon' ); ?>" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
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

<div id="content" class="site-content" itemprop="mainContentOfPage" itemscope="itemscope" itemtype="https://schema.org/WebPageElement">
