<?php
/**
 * Archive: Properties
 * Template provided by cw-management-company plugin.
 * Override by creating archive-mkd_object.php in your (child) theme.
 */

get_header();

if ( function_exists( 'get_pageheader' ) ) {
	get_pageheader();
}

$grid_gap = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'grid-gap' ) : 'gx-md-8 gy-10 gy-md-13';
?>

<section id="content-wrapper" class="wrapper">
	<div class="container py-14 py-md-16">

		<?php if ( class_exists( 'Codeweber_Yandex_Maps' ) ) :
			$all_object_ids = get_posts( [
				'post_type'      => 'mkd_object',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			] );
			$markers = array_values( array_filter( array_map(
				[ '\CW\ManagementCompany\Plugin', 'build_map_marker' ],
				$all_object_ids
			) ) );

			$yandex_maps = Codeweber_Yandex_Maps::get_instance();
			if ( $markers && $yandex_maps->has_api_key() ) :
				?>
				<div class="mb-10">
					<?php
					echo $yandex_maps->render_map( [
						'height'                       => 450,
						'auto_fit_bounds'              => true,
						'clusterer'                    => true,
						'route_button'                 => false,
						'marker_open_balloon_on_click' => true,
					], $markers );
					?>
				</div>
				<?php
			endif;
		endif;
		?>

		<?php if ( have_posts() ) : ?>
		<div class="row <?php echo esc_attr( $grid_gap ); ?>">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( function_exists( 'cw_render_post_card' ) ) {
					echo cw_render_post_card( get_the_ID(), 'card' );
				}
			endwhile;
			?>
		</div>
		<?php
		if ( function_exists( 'codeweber_posts_pagination' ) ) {
			codeweber_posts_pagination( [ 'nav_class' => 'd-flex justify-content-center mt-10' ] );
		} else {
			the_posts_pagination( [ 'mid_size' => 2 ] );
		}
		?>
		<?php else : ?>
		<p class="text-muted"><?php esc_html_e( 'No properties found.', 'cw-management-company' ); ?></p>
		<?php endif; ?>

	</div>
</section>

<?php get_footer(); ?>
