<?php
/**
 * The home template.
 *
 * @package Kolofon
 */

use function Kolofon\opt;

get_header();
?>

<main class="home">
	<?php do_action( 'kolofon_before_hero' ); ?>

	<section class="hero">
		<div class="container">
			<div class="hero-inner">
				<div class="hero-copy">
					<?php $eyebrow = opt( 'hero_eyebrow' ); ?>
					<?php if ( $eyebrow ) : ?>
						<p class="hero-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
					<?php endif; ?>

					<h1 class="hero-heading">
						<?php echo wp_kses( opt( 'hero_heading' ), \Kolofon\allowed_heading_html() ); ?>
					</h1>

					<?php $body = opt( 'hero_body' ); ?>
					<?php if ( $body ) : ?>
						<div class="hero-body"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
					<?php endif; ?>

					<?php \Kolofon\render_social_icons(); ?>

					<?php if ( is_active_sidebar( 'intro' ) ) : ?>
						<div class="intro"><?php dynamic_sidebar( 'intro' ); ?></div>
					<?php endif; ?>
				</div>

				<?php
				$portrait = opt( 'hero_portrait' ) ?: \Kolofon\default_portrait_url();
				$pstyle   = sanitize_html_class( opt( 'portrait_style' ) );
				?>
				<div class="hero-portrait is-<?php echo esc_attr( $pstyle ); ?>">
					<img src="<?php echo esc_url( $portrait ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( opt( 'hero_heading' ) ) ); ?>" fetchpriority="high" decoding="async" width="<?php echo esc_attr( intval( opt( 'portrait_size' ) ) ); ?>" height="<?php echo esc_attr( intval( opt( 'portrait_size' ) ) ); ?>" />
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'kolofon_after_hero' ); ?>

	<?php if ( intval( opt( 'show_recent' ) ) === 1 ) : ?>
		<?php
		$recent = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => intval( opt( 'recent_count' ) ),
				'no_found_rows'  => true,
			)
		);
		?>
		<?php if ( $recent->have_posts() ) : ?>
			<section class="recent">
				<div class="container">
					<?php
					if ( 1 === intval( opt( 'show_section_chooser' ) ) ) {
						\Kolofon\render_section_chooser( 0 );
					}
					?>
					<h2 class="section-heading"><?php esc_html_e( 'Recent Posts', 'kolofon' ); ?> <a class="section-link" href="<?php echo esc_url( \Kolofon\get_blog_index_url() ); ?>"><?php esc_html_e( 'View all', 'kolofon' ); ?></a></h2>
					<ul class="<?php echo esc_attr( \Kolofon\post_list_classes( $recent ) ); ?>">
						<?php
						while ( $recent->have_posts() ) :
							$recent->the_post();
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
