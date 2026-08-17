<?php
/**
 * Single: Property
 * Template provided by cw-management-company plugin.
 * Override by creating single-mkd_object.php in your (child) theme.
 */

use CW\ManagementCompany\Admin\Metaboxes;
use CW\ManagementCompany\Documents;

get_header();

if ( function_exists( 'get_pageheader' ) ) {
	get_pageheader();
}

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	$status_terms = get_the_terms( $post_id, 'mkd_object_status' );
	$status_term  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0] : null;

	// Documents attached to this property, grouped by document type.
	$documents_by_type  = Documents::group_by_type( Documents::query( $post_id ) );
	$document_years     = Documents::years_for_object( $post_id );
	$document_type_terms = get_terms( [ 'taxonomy' => 'mkd_document_type', 'hide_empty' => false ] );

	// Events linked to this property (e.g. owners meetings).
	$events = get_posts( [
		'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'      => [
			[ 'key' => '_mkd_event_object', 'value' => $post_id, 'compare' => '=' ],
		],
	] );
	?>

	<section class="wrapper">
		<div class="container py-14 py-md-16">
			<div class="row gx-md-8 gy-10">

				<div class="col-lg-8">

					<?php if ( has_post_thumbnail() ) : ?>
					<div class="mb-8">
						<?php the_post_thumbnail( 'large', [ 'class' => 'img-fluid rounded w-100' ] ); ?>
					</div>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
					<div class="post-content mb-8">
						<?php the_content(); ?>
					</div>
					<?php endif; ?>

					<?php if ( class_exists( 'Codeweber_Yandex_Maps' ) ) :
						$map_marker  = \CW\ManagementCompany\Plugin::build_map_marker( $post_id );
						$yandex_maps = Codeweber_Yandex_Maps::get_instance();
						if ( $map_marker && $yandex_maps->has_api_key() ) :
							?>
							<div class="mb-8">
								<h2 class="h4 mb-4"><?php esc_html_e( 'Location', 'cw-management-company' ); ?></h2>
								<?php
								echo $yandex_maps->render_map( [
									'height'                       => 350,
									'zoom'                         => 16,
									'center'                       => [ (float) $map_marker['latitude'], (float) $map_marker['longitude'] ],
									'auto_fit_bounds'              => false,
									'route_button'                 => false,
									'marker_open_balloon_on_click' => true,
								], [ $map_marker ] );
								?>
							</div>
							<?php
						endif;
					endif;
					?>

					<?php if ( $documents_by_type || $document_years ) : ?>
					<div class="mb-8">
						<h2 class="h4 mb-4"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>

						<?php if ( ( $document_type_terms && ! is_wp_error( $document_type_terms ) && count( $document_type_terms ) > 1 ) || count( $document_years ) > 1 ) : ?>
						<form id="cw-mc-document-filter" class="d-flex flex-wrap gap-3 mb-4">
							<?php if ( $document_type_terms && ! is_wp_error( $document_type_terms ) ) : ?>
							<select name="type" class="form-select w-auto">
								<option value=""><?php esc_html_e( 'All types', 'cw-management-company' ); ?></option>
								<?php foreach ( $document_type_terms as $type_term ) : ?>
								<option value="<?php echo esc_attr( $type_term->slug ); ?>">
									<?php echo esc_html( $type_term->name ); ?>
								</option>
								<?php endforeach; ?>
							</select>
							<?php endif; ?>

							<?php if ( $document_years ) : ?>
							<select name="year" class="form-select w-auto">
								<option value=""><?php esc_html_e( 'All years', 'cw-management-company' ); ?></option>
								<?php foreach ( $document_years as $year ) : ?>
								<option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php endif; ?>
						</form>
						<?php endif; ?>

						<div id="cw-mc-documents-list">
							<?php echo Documents::render_list( $documents_by_type ); // phpcs:ignore WordPress.Security.EscapeOutput -- already escaped inside render_list() ?>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( $events ) : ?>
					<div class="mb-8">
						<h2 class="h4 mb-4"><?php esc_html_e( 'Events', 'cw-management-company' ); ?></h2>
						<ul class="list-unstyled mb-0">
							<?php foreach ( $events as $event ) :
								$date_start = get_post_meta( $event->ID, '_event_date_start', true );
								?>
								<li class="d-flex justify-content-between align-items-center border-bottom py-2 gap-3">
									<a href="<?php echo esc_url( get_permalink( $event ) ); ?>">
										<?php echo esc_html( get_the_title( $event ) ); ?>
									</a>
									<?php if ( $date_start ) : ?>
									<span class="text-muted small flex-shrink-0">
										<?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $date_start ) ); ?>
									</span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>

				</div>

				<?php
				$field_groups = [];
				foreach ( Metaboxes::field_groups() as $group_key => $group_def ) {
					$rows = [];
					foreach ( $group_def['fields'] as $meta_key => $field ) {
						$raw       = get_post_meta( $post_id, $meta_key, true );
						$formatted = Metaboxes::format_value( $field, (string) $raw );
						if ( '' !== $formatted ) {
							$rows[] = [ 'key' => $meta_key, 'label' => $field['label'], 'value' => $formatted ];
						}
					}
					if ( $rows ) {
						$field_groups[ $group_key ] = [ 'label' => $group_def['label'], 'rows' => $rows ];
					}
				}

				$render_group_rows = static function ( array $rows ): void {
					foreach ( $rows as $row ) :
						$is_contact = ( '_mkd_responsible_person' === $row['key'] && false !== strpos( $row['value'], ',' ) );
						?>
						<li class="d-flex justify-content-between border-bottom py-2 gap-3">
							<span class="text-muted"><?php echo esc_html( $row['label'] ); ?></span>
							<?php if ( $is_contact ) :
								[ $person_name, $person_contact ] = array_map( 'trim', explode( ',', $row['value'], 2 ) );
								?>
								<strong class="text-end">
									<?php echo esc_html( $person_name ); ?><br>
									<span class="text-muted fw-normal"><?php echo esc_html( $person_contact ); ?></span>
								</strong>
							<?php else : ?>
								<strong class="text-end"><?php echo esc_html( $row['value'] ); ?></strong>
							<?php endif; ?>
						</li>
					<?php endforeach;
				};
				?>
				<aside class="col-lg-4">
					<div class="card bg-pale-primary p-6 sticky-top" style="top:80px;">

						<?php if ( $status_term ) : ?>
						<span class="badge bg-primary mb-4"><?php echo esc_html( $status_term->name ); ?></span>
						<?php endif; ?>

						<?php foreach ( $field_groups as $group_key => $group ) :
							if ( 'technical' === $group_key ) :
								?>
								<div class="accordion accordion-wrapper mt-4">
									<div class="card plain accordion-item">
										<div class="card-header" id="cw-mc-heading-technical">
											<button class="accordion-button collapsed"
												data-bs-toggle="collapse"
												data-bs-target="#cw-mc-collapse-technical"
												aria-expanded="false"
												aria-controls="cw-mc-collapse-technical">
												<?php echo esc_html( $group['label'] ); ?>
											</button>
										</div>
										<div id="cw-mc-collapse-technical"
											class="accordion-collapse collapse"
											aria-labelledby="cw-mc-heading-technical">
											<div class="card-body">
												<ul class="list-unstyled mb-0">
													<?php $render_group_rows( $group['rows'] ); ?>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<?php
							else :
								?>
								<div class="mt-4 first-group">
									<h3 class="h6 text-uppercase text-muted mb-2"><?php echo esc_html( $group['label'] ); ?></h3>
									<ul class="list-unstyled mb-0">
										<?php $render_group_rows( $group['rows'] ); ?>
									</ul>
								</div>
								<?php
							endif;
						endforeach;
						?>

					</div>
				</aside>

			</div>
		</div>
	</section>

<?php endwhile; ?>

<?php get_footer(); ?>
