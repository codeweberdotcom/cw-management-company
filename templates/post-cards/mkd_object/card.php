<?php
/**
 * Card Template: Card
 * Description: Property card with photo, address, key stats and status badge
 * Supports: title
 * Order: 10
 *
 * @param array $post_data        Post data from cw_get_post_card_data().
 * @param array $display_settings Display settings.
 * @param array $template_args    Extra arguments.
 */

if ( ! isset( $post_data ) || ! $post_data ) {
	return;
}

$display = function_exists( 'cw_get_post_card_display_settings' )
	? cw_get_post_card_display_settings( $display_settings ?? [] )
	: wp_parse_args( $display_settings ?? [], [
		'show_title' => true,
	] );

$post_id     = (int) $post_data['id'];
$card_radius = class_exists( 'Codeweber_Options' ) ? Codeweber_Options::style( 'card-radius' ) : 'rounded';

$address    = get_post_meta( $post_id, '_mkd_address', true );
$year_built = get_post_meta( $post_id, '_mkd_year_built', true );
$floors     = get_post_meta( $post_id, '_mkd_floors', true );

$status_terms = get_the_terms( $post_id, 'mkd_object_status' );
$status_term  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0] : null;
?>
<div class="col-md-6 col-lg-4">
	<div class="card h-100 overflow-hidden <?php echo esc_attr( $card_radius ); ?>">

		<div class="position-relative">
			<a href="<?php echo esc_url( $post_data['link'] ); ?>" class="d-block">
				<?php if ( $post_data['image_url'] ) : ?>
					<img src="<?php echo esc_url( $post_data['image_url'] ); ?>"
						alt="<?php echo esc_attr( $post_data['image_alt'] ); ?>"
						class="card-img-top" style="height:220px;object-fit:cover;">
				<?php else : ?>
					<div style="height:220px;background:#f1f3f5;"></div>
				<?php endif; ?>
			</a>
			<?php if ( $status_term ) : ?>
			<span class="badge bg-primary position-absolute top-0 start-0 m-2">
				<?php echo esc_html( $status_term->name ); ?>
			</span>
			<?php endif; ?>
		</div>

		<div class="card-body d-flex flex-column">
			<?php if ( ! empty( $display['show_title'] ) ) : ?>
			<h3 class="h5 mb-2">
				<a href="<?php echo esc_url( $post_data['link'] ); ?>" class="link-dark text-decoration-none">
					<?php echo esc_html( $post_data['title'] ); ?>
				</a>
			</h3>
			<?php endif; ?>

			<?php if ( $address ) : ?>
			<p class="text-muted small mb-3"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>

			<div class="mt-auto d-flex flex-wrap gap-2">
				<?php if ( $year_built ) : ?>
				<span class="badge bg-soft-ash text-ash">
					<?php
					/* translators: %s: year built */
					echo esc_html( sprintf( __( 'Built %s', 'cw-management-company' ), $year_built ) );
					?>
				</span>
				<?php endif; ?>
				<?php if ( $floors ) : ?>
				<span class="badge bg-soft-ash text-ash">
					<?php
					echo esc_html( sprintf(
						/* translators: %s: number of floors */
						_n( '%s floor', '%s floors', (int) $floors, 'cw-management-company' ),
						$floors
					) );
					?>
				</span>
				<?php endif; ?>
			</div>
		</div>

	</div>
</div>
