<?php
/**
 * The home template.
 *
 * @package Kolofon
 */

use function Kolofon\opt;

get_header();
?>

<main class="home" itemprop="mainEntity" itemscope="itemscope" itemtype="https://schema.org/Person">
	<?php do_action( 'kolofon_before_hero' ); ?>

	<section class="hero">
		<div class="container">
			<div class="hero-inner">
				<div class="hero-copy">
					<?php $kolofon_eyebrow = opt( 'hero_eyebrow' ); ?>
					<?php if ( $kolofon_eyebrow ) : ?>
						<p class="hero-eyebrow" itemprop="jobTitle"><?php echo esc_html( $kolofon_eyebrow ); ?></p>
					<?php endif; ?>

					<h1 class="hero-heading" itemprop="name">
						<?php echo wp_kses( opt( 'hero_heading' ), \Kolofon\allowed_heading_html() ); ?>
					</h1>

					<?php $kolofon_body = opt( 'hero_body' ); ?>
					<?php if ( $kolofon_body ) : ?>
						<div class="hero-body" itemprop="description"><?php echo wp_kses_post( wpautop( $kolofon_body ) ); ?></div>
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
					<img itemprop="image" src="<?php echo esc_url( $kolofon_portrait ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( opt( 'hero_heading' ) ) ); ?>" fetchpriority="high" decoding="async" width="<?php echo esc_attr( intval( opt( 'portrait_size' ) ) ); ?>" height="<?php echo esc_attr( intval( opt( 'portrait_size' ) ) ); ?>" />
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'kolofon_after_hero' ); ?>

	<?php if ( intval( opt( 'show_recent' ) ) === 1 ) : ?>
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
					if ( 1 === intval( opt( 'show_section_chooser' ) ) ) {
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
