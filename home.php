<?php
/**
 * The home template.
 *
 * @package Kolofon
 */

use function Kolofon\opt;
use function Kolofon\display_rule;

get_header();
?>

<main class="home" itemprop="mainEntity" itemscope="itemscope" itemtype="https://schema.org/Person">
	<?php do_action( 'kolofon_before_hero' ); ?>

	<?php
	/*
	 * The hero doubles as the representative h-card. Fediverse and IndieWeb
	 * parsers use it to attribute posts on this domain to a person: without it
	 * they can find the posts but cannot reliably say who wrote them, which is
	 * what makes a site read as a blog that happens to federate rather than as
	 * a native presence. u-url and u-uid on the home link mark it as
	 * representative.
	 */
	?>
	<section class="hero h-card">
		<div class="container">
			<div class="hero-inner">
				<a class="u-url u-uid" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="me" hidden="hidden" aria-hidden="true"></a>
				<div class="hero-copy">
					<?php $kolofon_eyebrow = opt( 'hero_eyebrow' ); ?>
					<?php if ( $kolofon_eyebrow ) : ?>
						<p class="hero-eyebrow p-job-title" itemprop="jobTitle"><?php echo esc_html( $kolofon_eyebrow ); ?></p>
					<?php endif; ?>

					<h1 class="hero-heading p-name" itemprop="name">
						<?php echo wp_kses( opt( 'hero_heading' ), \Kolofon\allowed_heading_html() ); ?>
					</h1>

					<?php $kolofon_body = opt( 'hero_body' ); ?>
					<?php if ( $kolofon_body ) : ?>
						<div class="hero-body p-note" itemprop="description"><?php echo wp_kses_post( wpautop( $kolofon_body ) ); ?></div>
					<?php endif; ?>

					<?php \Kolofon\render_social_icons(); ?>

					<?php if ( is_active_sidebar( 'intro' ) ) : ?>
						<div class="intro"><?php dynamic_sidebar( 'intro' ); ?></div>
					<?php endif; ?>
				</div>

				<?php
				$kolofon_portrait = opt( 'hero_portrait' );
				if ( '' === $kolofon_portrait ) {
					$kolofon_portrait = \Kolofon\default_portrait_url();
				}
				$kolofon_pstyle = sanitize_html_class( opt( 'portrait_style' ) );
				?>
				<div class="hero-portrait is-<?php echo esc_attr( $kolofon_pstyle ); ?>">
					<?php
					echo \Kolofon\portrait_markup(
						$kolofon_portrait,
						intval( opt( 'portrait_size' ) ),
						wp_strip_all_tags( opt( 'hero_heading' ) )
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the helper.
					?>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'kolofon_after_hero' ); ?>

	<?php if ( display_rule( 'recent_posts' ) ) : ?>
		<?php
		$kolofon_recent = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => intval( opt( 'recent_count' ) ),
				'no_found_rows'  => true,
			)
		);
		?>
		<?php if ( $kolofon_recent->have_posts() ) : ?>
			<section class="recent" itemscope="itemscope" itemtype="https://schema.org/ItemList">
				<div class="container">
					<?php
					if ( display_rule( 'section_chooser' ) ) {
						\Kolofon\render_section_chooser( 0 );
					}
					?>
					<h2 class="section-heading" itemprop="name"><?php esc_html_e( 'Recent Posts', 'kolofon' ); ?></h2>
					<ul class="<?php echo esc_attr( \Kolofon\post_list_classes( $kolofon_recent ) ); ?>">
						<?php
						while ( $kolofon_recent->have_posts() ) :
							$kolofon_recent->the_post();
							\Kolofon\post_list_item();
						endwhile;
						?>
					</ul>
				</div>
			</section>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>
	<?php endif; ?>
</main>

<?php
get_footer();
