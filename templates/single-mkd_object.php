<?php
/**
 * Single: Property
 * Template provided by cw-management-company plugin.
 * Override by creating single-mkd_object.php in your (child) theme.
 */

use CW\ManagementCompany\Admin\Metaboxes;
use CW\ManagementCompany\Documents;
use CW\ManagementCompany\Plugin;

get_header();

if ( function_exists( 'get_pageheader' ) ) {
	get_pageheader();
}

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	// ── Meta ─────────────────────────────────────────────────────────────────────
	$address         = get_post_meta( $post_id, '_mkd_address', true );
	$city            = get_post_meta( $post_id, '_mkd_city', true );
	$year_built      = get_post_meta( $post_id, '_mkd_year_built', true );
	$floors          = get_post_meta( $post_id, '_mkd_floors', true );
	$entrances       = get_post_meta( $post_id, '_mkd_entrances', true );
	$dwellings       = get_post_meta( $post_id, '_mkd_dwellings_count', true );
	$total_area      = get_post_meta( $post_id, '_mkd_total_area', true );
	$elevators       = get_post_meta( $post_id, '_mkd_elevators_count', true );
	$wear_pct        = get_post_meta( $post_id, '_mkd_wear_pct', true );
	$wall_material   = get_post_meta( $post_id, '_mkd_wall_material', true );
	$tariff          = get_post_meta( $post_id, '_mkd_tariff', true );
	$phone           = get_post_meta( $post_id, '_mkd_phone', true );
	$reception_hours = get_post_meta( $post_id, '_mkd_reception_hours', true );
	$responsible     = get_post_meta( $post_id, '_mkd_responsible_person', true );
	$contract_date   = get_post_meta( $post_id, '_mkd_contract_date', true );

	$tariff_rows_raw = get_post_meta( $post_id, '_mkd_tariff_rows', true );
	$tariff_rows     = ( $tariff_rows_raw ) ? json_decode( $tariff_rows_raw, true ) : [];
	if ( ! is_array( $tariff_rows ) ) {
		$tariff_rows = [];
	}

	$works_raw = get_post_meta( $post_id, '_mkd_works', true );
	$works     = ( $works_raw ) ? json_decode( $works_raw, true ) : [];
	if ( ! is_array( $works ) ) {
		$works = [];
	}

	$works_done = array_values( array_filter( $works, static fn( $w ) => 'done' === ( $w['type'] ?? '' ) ) );
	$works_plan = array_values( array_filter( $works, static fn( $w ) => 'plan' === ( $w['type'] ?? '' ) ) );

	$photo_yard_id     = (int) get_post_meta( $post_id, '_mkd_photo_yard', true );
	$photo_entrance_id = (int) get_post_meta( $post_id, '_mkd_photo_entrance', true );

	$status_terms = get_the_terms( $post_id, 'mkd_object_status' );
	$status_term  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0] : null;

	$documents_by_type   = Documents::group_by_type( Documents::query( $post_id ) );
	$document_years      = Documents::years_for_object( $post_id );
	$document_type_terms = get_terms( [ 'taxonomy' => 'mkd_document_type', 'hide_empty' => false ] );

	$wall_labels = [
		'panel'    => __( 'Panel', 'cw-management-company' ),
		'brick'    => __( 'Brick', 'cw-management-company' ),
		'monolith' => __( 'Monolith', 'cw-management-company' ),
		'block'    => __( 'Block', 'cw-management-company' ),
		'wood'     => __( 'Wood', 'cw-management-company' ),
		'other'    => __( 'Other', 'cw-management-company' ),
	];
	$wall_label = ! empty( $wall_material ) ? ( $wall_labels[ $wall_material ] ?? $wall_material ) : '';
	?>

	<?php /* ══════════════════════════════════════════════════════ HERO */ ?>
	<?php if ( has_post_thumbnail() ) : ?>
	<div class="position-relative overflow-hidden" style="max-height:520px;">
		<?php the_post_thumbnail( 'full', [ 'class' => 'w-100 object-fit-cover', 'style' => 'max-height:520px;object-fit:cover;display:block;' ] ); ?>
		<div class="position-absolute bottom-0 start-0 w-100 p-6 p-md-10"
			style="background:linear-gradient(to top,rgba(0,0,0,.65) 0%,transparent 100%);">
			<?php if ( $city || $status_term ) : ?>
			<div class="d-flex flex-wrap gap-2 mb-3">
				<?php if ( $city ) : ?>
				<span class="badge bg-white text-dark rounded-pill px-3 py-1"><?php echo esc_html( $city ); ?></span>
				<?php endif; ?>
				<?php if ( $status_term ) : ?>
				<span class="badge bg-primary rounded-pill px-3 py-1"><?php echo esc_html( $status_term->name ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<h1 class="text-white mb-0 fs-1 fw-bold">
				<?php echo wp_kses_post( get_the_title() ); ?>
			</h1>
			<?php if ( $address && get_the_title() !== $address ) : ?>
			<p class="text-white opacity-75 mb-0 mt-2"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php else : ?>
	<section class="wrapper bg-soft-primary">
		<div class="container py-10">
			<?php if ( $city || $status_term ) : ?>
			<div class="d-flex flex-wrap gap-2 mb-3">
				<?php if ( $city ) : ?>
				<span class="badge bg-white text-dark rounded-pill px-3 py-1"><?php echo esc_html( $city ); ?></span>
				<?php endif; ?>
				<?php if ( $status_term ) : ?>
				<span class="badge bg-primary rounded-pill px-3 py-1"><?php echo esc_html( $status_term->name ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<h1 class="display-4 fw-bold mb-2"><?php echo wp_kses_post( get_the_title() ); ?></h1>
			<?php if ( $address && get_the_title() !== $address ) : ?>
			<p class="lead text-muted mb-0"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ══════════════════════════════════════════════════════ GALLERY */ ?>
	<?php
	$gallery_slots = [];
	if ( $photo_yard_id > 0 )     $gallery_slots[] = [ 'id' => $photo_yard_id,     'label' => __( 'Yard', 'cw-management-company' ) ];
	if ( $photo_entrance_id > 0 ) $gallery_slots[] = [ 'id' => $photo_entrance_id, 'label' => __( 'Entrance', 'cw-management-company' ) ];

	if ( $gallery_slots ) :
		$col = count( $gallery_slots ) === 1 ? 'col-12' : 'col-6';
	?>
	<div class="container-fluid px-0">
		<div class="row g-2 mt-0">
			<?php foreach ( $gallery_slots as $slot ) : ?>
			<div class="<?php echo esc_attr( $col ); ?>">
				<div class="ratio ratio-16x9">
					<?php
					echo wp_get_attachment_image(
						$slot['id'],
						'large',
						false,
						[ 'class' => 'w-100 h-100 object-fit-cover', 'alt' => esc_attr( $slot['label'] ) ]
					);
					?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<section class="wrapper">
		<div class="container py-12 py-md-14">

			<?php /* ══════════════════════════════════════════════════════ SPECS GRID */ ?>
			<?php
			$spec_items = [];
			if ( $year_built ) $spec_items[] = [ 'label' => __( 'Year Built', 'cw-management-company' ),   'value' => $year_built ];
			if ( $floors )     $spec_items[] = [ 'label' => __( 'Floors', 'cw-management-company' ),       'value' => $floors ];
			if ( $entrances )  $spec_items[] = [ 'label' => __( 'Entrances', 'cw-management-company' ),    'value' => $entrances ];
			if ( $dwellings )  $spec_items[] = [ 'label' => __( 'Apartments', 'cw-management-company' ),   'value' => number_format( (int) $dwellings ) ];
			if ( $total_area ) $spec_items[] = [ 'label' => __( 'Total Area', 'cw-management-company' ),   'value' => number_format( (float) $total_area, 0, '.', ' ' ) . ' m²' ];
			if ( $elevators )  $spec_items[] = [ 'label' => __( 'Elevators', 'cw-management-company' ),    'value' => $elevators ];
			if ( $tariff )     $spec_items[] = [ 'label' => __( 'Tariff', 'cw-management-company' ),       'value' => number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' ];
			if ( $wear_pct )   $spec_items[] = [ 'label' => __( 'Wear', 'cw-management-company' ),         'value' => $wear_pct . '%' ];

			if ( $spec_items ) :
			?>
			<div class="row g-3 mb-12">
				<?php foreach ( $spec_items as $item ) : ?>
				<div class="col-6 col-sm-4 col-lg-3">
					<div class="card shadow-none border h-100 p-4 text-center">
						<div class="fs-3 fw-bold text-primary mb-1"><?php echo esc_html( $item['value'] ); ?></div>
						<div class="text-muted small"><?php echo esc_html( $item['label'] ); ?></div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ POST CONTENT */ ?>
			<?php if ( get_the_content() ) : ?>
			<div class="post-content mb-12">
				<?php the_content(); ?>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ TARIFF BREAKDOWN */ ?>
			<?php if ( $tariff && $tariff_rows ) : ?>
			<div class="mb-12">
				<div class="d-flex justify-content-between align-items-baseline flex-wrap gap-3 mb-6">
					<h2 class="h3 mb-0"><?php esc_html_e( 'Tariff Breakdown', 'cw-management-company' ); ?></h2>
					<span class="fs-5 fw-semibold text-primary">
						<?php echo esc_html( number_format( (float) $tariff, 2, '.', ' ' ) ); ?> ₽/m²
					</span>
				</div>
				<div class="d-flex flex-column gap-4">
					<?php foreach ( $tariff_rows as $trow ) :
						if ( '' === trim( $trow['name'] ?? '' ) ) continue;
						$pct_val     = (float) ( $trow['pct'] ?? 0 );
						$pct_display = min( 100, max( 0, $pct_val ) );
					?>
					<div>
						<div class="d-flex justify-content-between mb-2">
							<span class="fw-medium"><?php echo esc_html( $trow['name'] ); ?></span>
							<span class="text-muted small">
								<?php if ( ! empty( $trow['val'] ) ) : ?>
									<?php echo esc_html( $trow['val'] ); ?> ₽/m²<?php if ( ! empty( $trow['pct'] ) ) echo ' <span class="opacity-60">· ' . esc_html( $trow['pct'] ) . '%</span>'; ?>
								<?php elseif ( ! empty( $trow['pct'] ) ) : ?>
									<?php echo esc_html( $trow['pct'] ); ?>%
								<?php endif; ?>
							</span>
						</div>
						<?php if ( $pct_display > 0 ) : ?>
						<div class="progress" style="height:6px;">
							<div class="progress-bar bg-primary" role="progressbar"
								style="width:<?php echo esc_attr( $pct_display ); ?>%"
								aria-valuenow="<?php echo esc_attr( $pct_display ); ?>"
								aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ MANAGEMENT INFO */ ?>
			<?php
			$mgmt_items = [];
			if ( $responsible ) {
				$p = array_map( 'trim', explode( ',', $responsible, 2 ) );
				$mgmt_items[] = [ 'label' => __( 'Contact', 'cw-management-company' ), 'value' => implode( ', ', $p ) ];
			}
			if ( $contract_date ) {
				$mgmt_items[] = [ 'label' => __( 'Contract', 'cw-management-company' ), 'value' => mysql2date( get_option( 'date_format' ), $contract_date ) ];
			}
			if ( $phone ) {
				$mgmt_items[] = [ 'label' => __( 'Dispatcher', 'cw-management-company' ), 'value' => $phone, 'href' => 'tel:' . preg_replace( '/[^+\d]/', '', $phone ) ];
			}
			if ( $reception_hours ) {
				$mgmt_items[] = [ 'label' => __( 'Reception', 'cw-management-company' ), 'value' => $reception_hours ];
			}

			if ( $mgmt_items ) :
			?>
			<div class="card bg-pale-primary shadow-none p-6 p-md-8 mb-12">
				<h2 class="h4 mb-5"><?php esc_html_e( 'Management', 'cw-management-company' ); ?></h2>
				<div class="row g-4">
					<?php foreach ( $mgmt_items as $mi ) : ?>
					<div class="col-sm-6 col-md-4 col-lg-3">
						<div class="text-muted small mb-1"><?php echo esc_html( $mi['label'] ); ?></div>
						<?php if ( ! empty( $mi['href'] ) ) : ?>
						<a href="<?php echo esc_url( $mi['href'] ); ?>" class="fw-semibold text-dark"><?php echo esc_html( $mi['value'] ); ?></a>
						<?php else : ?>
						<div class="fw-semibold"><?php echo esc_html( $mi['value'] ); ?></div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ WORKS HISTORY */ ?>
			<?php if ( $works_done || $works_plan ) : ?>
			<div class="mb-12">
				<h2 class="h3 mb-6"><?php esc_html_e( 'Works', 'cw-management-company' ); ?></h2>

				<?php if ( $works_done && $works_plan ) : ?>
				<ul class="nav nav-tabs mb-6" id="cw-mc-works-tabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="cw-mc-tab-done" data-bs-toggle="tab"
							data-bs-target="#cw-mc-pane-done" type="button" role="tab">
							<?php esc_html_e( 'Completed', 'cw-management-company' ); ?>
							<span class="badge bg-secondary ms-1"><?php echo count( $works_done ); ?></span>
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="cw-mc-tab-plan" data-bs-toggle="tab"
							data-bs-target="#cw-mc-pane-plan" type="button" role="tab">
							<?php esc_html_e( 'Planned', 'cw-management-company' ); ?>
							<span class="badge bg-secondary ms-1"><?php echo count( $works_plan ); ?></span>
						</button>
					</li>
				</ul>
				<?php endif; ?>

				<div class="tab-content" id="cw-mc-works-content">
					<?php
					$both = $works_done && $works_plan;

					$render_works_pane = static function ( array $list, string $pane_id, bool $active ) use ( $both ): void {
						if ( ! $list ) return;
						?>
						<div class="tab-pane fade <?php echo $active ? 'show active' : ''; ?>"
							id="<?php echo esc_attr( $pane_id ); ?>" role="tabpanel">
							<div class="row g-4">
								<?php foreach ( $list as $w ) :
									if ( '' === trim( $w['title'] ?? '' ) ) continue;
									$is_done     = 'done' === ( $w['type'] ?? '' );
									$badge_class = $is_done ? 'bg-success' : 'bg-primary';
									$status_text = ! empty( $w['status'] )
										? $w['status']
										: ( $is_done
											? __( 'Completed', 'cw-management-company' )
											: __( 'Planned', 'cw-management-company' ) );
								?>
								<div class="col-md-6 col-lg-4">
									<div class="card shadow-none border h-100 p-5">
										<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
											<span class="badge <?php echo esc_attr( $badge_class ); ?>">
												<?php echo esc_html( $status_text ); ?>
											</span>
											<?php if ( ! empty( $w['date'] ) ) : ?>
											<span class="text-muted small"><?php echo esc_html( $w['date'] ); ?></span>
											<?php endif; ?>
										</div>
										<div class="fw-semibold mb-2"><?php echo esc_html( $w['title'] ); ?></div>
										<?php if ( ! empty( $w['detail'] ) ) : ?>
										<p class="text-muted small mb-auto"><?php echo esc_html( $w['detail'] ); ?></p>
										<?php endif; ?>
										<?php if ( ! empty( $w['cost'] ) ) : ?>
										<div class="text-primary fw-medium mt-3 small"><?php echo esc_html( $w['cost'] ); ?></div>
										<?php endif; ?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
					};

					$render_works_pane( $works_done ?: $works_plan, 'cw-mc-pane-done', true );
					if ( $both ) {
						$render_works_pane( $works_plan, 'cw-mc-pane-plan', false );
					}
					?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ DOCUMENTS */ ?>
			<?php if ( $documents_by_type || $document_years ) : ?>
			<div class="mb-12">
				<h2 class="h3 mb-6"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>

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
					<?php echo Documents::render_list( $documents_by_type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ══════════════════════════════════════════════════════ MAP */ ?>
			<?php if ( class_exists( 'Codeweber_Yandex_Maps' ) ) :
				$map_marker  = Plugin::build_map_marker( $post_id );
				$yandex_maps = Codeweber_Yandex_Maps::get_instance();
				if ( $map_marker && $yandex_maps->has_api_key() ) :
			?>
			<div class="mb-12">
				<h2 class="h3 mb-6"><?php esc_html_e( 'Location', 'cw-management-company' ); ?></h2>
				<?php
				echo $yandex_maps->render_map(
					[
						'height'                       => 420,
						'zoom'                         => 16,
						'center'                       => [ (float) $map_marker['latitude'], (float) $map_marker['longitude'] ],
						'auto_fit_bounds'              => false,
						'marker_open_balloon_on_click' => true,
					],
					[ $map_marker ]
				);
				?>
			</div>
			<?php
				endif;
			endif;
			?>

		</div>
	</section>

<?php endwhile; ?>

<?php get_footer(); ?>
