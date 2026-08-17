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

	$works_done = array_filter( $works, static fn( $w ) => 'done' === ( $w['type'] ?? '' ) );
	$works_plan = array_filter( $works, static fn( $w ) => 'plan' === ( $w['type'] ?? '' ) );

	$photo_yard_id     = (int) get_post_meta( $post_id, '_mkd_photo_yard', true );
	$photo_entrance_id = (int) get_post_meta( $post_id, '_mkd_photo_entrance', true );

	$status_terms = get_the_terms( $post_id, 'mkd_object_status' );
	$status_term  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0] : null;

	$documents_by_type   = Documents::group_by_type( Documents::query( $post_id ) );
	$document_years      = Documents::years_for_object( $post_id );
	$document_type_terms = get_terms( [ 'taxonomy' => 'mkd_document_type', 'hide_empty' => false ] );

	// ── Helper: format area ───────────────────────────────────────────────────
	$fmt_area = static function( string $v ): string {
		return number_format( (float) $v, 2, '.', ' ' ) . ' m²';
	};

	// ── Wall material label ───────────────────────────────────────────────────
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

	<?php /* ═══════════════════════════════════════════ SECTION 1: OBJECT HEADER */ ?>
	<section class="wrapper">
		<div class="container py-10 py-md-12">

			<?php if ( $city || $status_term ) : ?>
			<div class="d-flex flex-wrap gap-2 mb-4">
				<?php if ( $city ) : ?>
				<span class="badge bg-soft-primary text-primary rounded-pill">
					<?php echo esc_html( $city ); ?>
				</span>
				<?php endif; ?>
				<?php if ( $status_term ) : ?>
				<span class="badge bg-primary rounded-pill">
					<?php echo esc_html( $status_term->name ); ?>
				</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<h1 class="display-4 fw-bold mb-4">
				<?php echo wp_kses_post( get_the_title() ); ?>
			</h1>

			<?php if ( $address && get_the_title() !== $address ) : ?>
			<p class="lead text-muted mb-4">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 0a5 5 0 0 0-5 5c0 5.25 5 11 5 11s5-5.75 5-11A5 5 0 0 0 8 0zm0 7.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
				<?php echo esc_html( $address ); ?>
			</p>
			<?php endif; ?>

			<?php if ( $phone || $reception_hours ) : ?>
			<div class="d-flex flex-wrap gap-5">
				<?php if ( $phone ) : ?>
				<div>
					<span class="text-muted small d-block"><?php esc_html_e( 'Dispatcher', 'cw-management-company' ); ?></span>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>" class="fw-semibold text-dark">
						<?php echo esc_html( $phone ); ?>
					</a>
				</div>
				<?php endif; ?>
				<?php if ( $reception_hours ) : ?>
				<div>
					<span class="text-muted small d-block"><?php esc_html_e( 'Reception', 'cw-management-company' ); ?></span>
					<span class="fw-semibold"><?php echo esc_html( $reception_hours ); ?></span>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div>
	</section>

	<?php /* ═══════════════════════════════════════════ SECTION 2: GALLERY */ ?>
	<?php
	$has_facade   = has_post_thumbnail();
	$has_yard     = $photo_yard_id > 0;
	$has_entrance = $photo_entrance_id > 0;
	$gallery_cols = array_filter( [ $has_facade, $has_yard, $has_entrance ] );
	$col_count    = count( $gallery_cols );
	if ( $col_count > 0 ) :
		$col_class = 1 === $col_count ? 'col-12' : ( 2 === $col_count ? 'col-6' : 'col-4' );
	?>
	<section class="wrapper bg-light">
		<div class="container-fluid px-0">
			<div class="row g-2">
				<?php if ( $has_facade ) : ?>
				<div class="<?php echo esc_attr( $col_class ); ?>">
					<div class="ratio ratio-16x9">
						<?php the_post_thumbnail( 'large', [ 'class' => 'w-100 h-100 object-fit-cover' ] ); ?>
					</div>
				</div>
				<?php endif; ?>
				<?php if ( $has_yard ) : ?>
				<div class="<?php echo esc_attr( $col_class ); ?>">
					<div class="ratio ratio-16x9">
						<?php
						echo wp_get_attachment_image(
							$photo_yard_id,
							'large',
							false,
							[ 'class' => 'w-100 h-100 object-fit-cover', 'alt' => esc_attr__( 'Yard', 'cw-management-company' ) ]
						);
						?>
					</div>
				</div>
				<?php endif; ?>
				<?php if ( $has_entrance ) : ?>
				<div class="<?php echo esc_attr( $col_class ); ?>">
					<div class="ratio ratio-16x9">
						<?php
						echo wp_get_attachment_image(
							$photo_entrance_id,
							'large',
							false,
							[ 'class' => 'w-100 h-100 object-fit-cover', 'alt' => esc_attr__( 'Entrance', 'cw-management-company' ) ]
						);
						?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="wrapper">
		<div class="container py-12 py-md-14">

			<?php /* ═══════════════════════════════════════════ SECTION 3: SPECS GRID */ ?>
			<?php
			$spec_items = [];
			if ( $year_built )  $spec_items[] = [ 'label' => __( 'Year Built', 'cw-management-company' ),   'value' => $year_built ];
			if ( $floors )      $spec_items[] = [ 'label' => __( 'Floors', 'cw-management-company' ),       'value' => $floors ];
			if ( $entrances )   $spec_items[] = [ 'label' => __( 'Entrances', 'cw-management-company' ),    'value' => $entrances ];
			if ( $dwellings )   $spec_items[] = [ 'label' => __( 'Apartments', 'cw-management-company' ),   'value' => number_format( (int) $dwellings ) ];
			if ( $total_area )  $spec_items[] = [ 'label' => __( 'Total Area', 'cw-management-company' ),   'value' => $fmt_area( $total_area ) ];
			if ( $elevators )   $spec_items[] = [ 'label' => __( 'Elevators', 'cw-management-company' ),    'value' => $elevators ];
			if ( $wall_label )  $spec_items[] = [ 'label' => __( 'Walls', 'cw-management-company' ),        'value' => $wall_label ];
			if ( $tariff )      $spec_items[] = [ 'label' => __( 'Tariff', 'cw-management-company' ),       'value' => number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' ];
			if ( $wear_pct )    $spec_items[] = [ 'label' => __( 'Wear', 'cw-management-company' ),         'value' => $wear_pct . '%' ];

			if ( $spec_items ) :
			?>
			<div class="mb-10 mb-md-12">
				<h2 class="h3 mb-6"><?php esc_html_e( 'Building Parameters', 'cw-management-company' ); ?></h2>
				<div class="row g-3">
					<?php foreach ( $spec_items as $item ) : ?>
					<div class="col-6 col-md-4 col-lg-3">
						<div class="card h-100 p-4 text-center shadow-none border">
							<div class="fs-4 fw-bold text-primary mb-1"><?php echo esc_html( $item['value'] ); ?></div>
							<div class="text-muted small"><?php echo esc_html( $item['label'] ); ?></div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="row gx-md-10 gy-8">
				<div class="col-lg-8">

					<?php /* ══════════════════════════════════════════ SECTION 4: TARIFF BREAKDOWN */ ?>
					<?php if ( $tariff && $tariff_rows ) : ?>
					<div class="mb-10">
						<h2 class="h3 mb-2"><?php esc_html_e( 'Tariff Breakdown', 'cw-management-company' ); ?></h2>
						<p class="text-muted mb-6">
							<?php
							printf(
								/* translators: %s: tariff rate */
								esc_html__( 'Total management tariff: %s', 'cw-management-company' ),
								'<strong>' . esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' ) . '</strong>'
							);
							?>
						</p>
						<div class="d-flex flex-column gap-3">
							<?php foreach ( $tariff_rows as $trow ) :
								if ( '' === trim( $trow['name'] ?? '' ) ) continue;
								$pct_val = (float) ( $trow['pct'] ?? 0 );
								$pct_display = min( 100, max( 0, $pct_val ) );
								?>
							<div>
								<div class="d-flex justify-content-between mb-1">
									<span class="fw-medium"><?php echo esc_html( $trow['name'] ); ?></span>
									<span class="text-muted">
										<?php if ( ! empty( $trow['val'] ) ) echo esc_html( $trow['val'] ) . ' ₽/m²'; ?>
										<?php if ( ! empty( $trow['pct'] ) ) echo ' <span class="opacity-75">(' . esc_html( $trow['pct'] ) . '%)</span>'; ?>
									</span>
								</div>
								<?php if ( $pct_display > 0 ) : ?>
								<div class="progress" style="height:6px;">
									<div class="progress-bar bg-primary" role="progressbar"
										style="width:<?php echo esc_attr( $pct_display ); ?>%"
										aria-valuenow="<?php echo esc_attr( $pct_display ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<?php endif; ?>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

					<?php /* ══════════════════════════════════════════ SECTION 5: WORKS HISTORY */ ?>
					<?php if ( $works_done || $works_plan ) : ?>
					<div class="mb-10">
						<h2 class="h3 mb-6"><?php esc_html_e( 'Works', 'cw-management-company' ); ?></h2>

						<?php if ( $works_done && $works_plan ) : ?>
						<ul class="nav nav-tabs mb-6" id="cw-mc-works-tabs" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="cw-mc-tab-done" data-bs-toggle="tab"
									data-bs-target="#cw-mc-pane-done" type="button" role="tab"
									aria-controls="cw-mc-pane-done" aria-selected="true">
									<?php esc_html_e( 'Completed', 'cw-management-company' ); ?>
									<span class="badge bg-secondary ms-1"><?php echo count( $works_done ); ?></span>
								</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="cw-mc-tab-plan" data-bs-toggle="tab"
									data-bs-target="#cw-mc-pane-plan" type="button" role="tab"
									aria-controls="cw-mc-pane-plan" aria-selected="false">
									<?php esc_html_e( 'Planned', 'cw-management-company' ); ?>
									<span class="badge bg-secondary ms-1"><?php echo count( $works_plan ); ?></span>
								</button>
							</li>
						</ul>
						<?php endif; ?>

						<div class="tab-content" id="cw-mc-works-content">
							<?php
							$render_works = static function ( array $list, string $pane_id, bool $active ): void {
								if ( ! $list ) return;
								?>
								<div class="tab-pane fade <?php echo $active ? 'show active' : ''; ?>"
									id="<?php echo esc_attr( $pane_id ); ?>" role="tabpanel">
									<div class="d-flex flex-column gap-3">
										<?php foreach ( $list as $w ) :
											if ( '' === trim( $w['title'] ?? '' ) ) continue;
											$is_done = 'done' === ( $w['type'] ?? '' );
											$badge_class = $is_done ? 'bg-success' : 'bg-primary';
											$status_text = ! empty( $w['status'] ) ? $w['status'] : ( $is_done ? __( 'Completed', 'cw-management-company' ) : __( 'Planned', 'cw-management-company' ) );
											?>
											<div class="card shadow-none border p-4">
												<div class="d-flex justify-content-between align-items-start gap-3 mb-2 flex-wrap">
													<div>
														<?php if ( ! empty( $w['date'] ) ) : ?>
														<span class="text-muted small me-2"><?php echo esc_html( $w['date'] ); ?></span>
														<?php endif; ?>
														<span class="fw-semibold"><?php echo esc_html( $w['title'] ); ?></span>
													</div>
													<span class="badge <?php echo esc_attr( $badge_class ); ?> flex-shrink-0">
														<?php echo esc_html( $status_text ); ?>
													</span>
												</div>
												<?php if ( ! empty( $w['detail'] ) ) : ?>
												<p class="text-muted small mb-2"><?php echo esc_html( $w['detail'] ); ?></p>
												<?php endif; ?>
												<?php if ( ! empty( $w['cost'] ) ) : ?>
												<div class="text-primary fw-medium small"><?php echo esc_html( $w['cost'] ); ?></div>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
								<?php
							};

							$both_tabs = $works_done && $works_plan;
							$render_works( $works_done ? array_values( $works_done ) : array_values( $works_plan ), 'cw-mc-pane-done', true );
							if ( $both_tabs ) {
								$render_works( array_values( $works_plan ), 'cw-mc-pane-plan', false );
							}
							?>
						</div>
					</div>
					<?php endif; ?>

					<?php /* ══════════════════════════════════════════ SECTION 6: POST CONTENT */ ?>
					<?php if ( get_the_content() ) : ?>
					<div class="post-content mb-10">
						<?php the_content(); ?>
					</div>
					<?php endif; ?>

					<?php /* ══════════════════════════════════════════ SECTION 7: DOCUMENTS */ ?>
					<?php if ( $documents_by_type || $document_years ) : ?>
					<div class="mb-10">
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

					<?php /* ══════════════════════════════════════════ SECTION 8: MAP */ ?>
					<?php if ( class_exists( 'Codeweber_Yandex_Maps' ) ) :
						$map_marker  = Plugin::build_map_marker( $post_id );
						$yandex_maps = Codeweber_Yandex_Maps::get_instance();
						if ( $map_marker && $yandex_maps->has_api_key() ) :
					?>
					<div class="mb-10">
						<h2 class="h3 mb-6"><?php esc_html_e( 'Location', 'cw-management-company' ); ?></h2>
						<?php
						echo $yandex_maps->render_map(
							[
								'height'                       => 380,
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

				<?php /* ══════════════════════════════════════════ SIDEBAR */ ?>
				<aside class="col-lg-4">
					<div class="sticky-top" style="top:90px;">

						<?php if ( $responsible || $contract_date || $tariff ) : ?>
						<div class="card bg-pale-primary shadow-none p-6 mb-4">
							<h3 class="h6 text-uppercase text-muted mb-4"><?php esc_html_e( 'Management', 'cw-management-company' ); ?></h3>
							<ul class="list-unstyled mb-0">

								<?php if ( $tariff ) : ?>
								<li class="d-flex justify-content-between border-bottom py-2 gap-3">
									<span class="text-muted"><?php esc_html_e( 'Tariff', 'cw-management-company' ); ?></span>
									<strong><?php echo esc_html( number_format( (float) $tariff, 2, '.', ' ' ) ); ?> ₽/m²</strong>
								</li>
								<?php endif; ?>

								<?php if ( $responsible ) :
									$person_parts = explode( ',', $responsible, 2 );
									$person_name  = trim( $person_parts[0] );
									$person_extra = ! empty( $person_parts[1] ) ? trim( $person_parts[1] ) : '';
								?>
								<li class="d-flex justify-content-between border-bottom py-2 gap-3">
									<span class="text-muted"><?php esc_html_e( 'Contact', 'cw-management-company' ); ?></span>
									<strong class="text-end">
										<?php echo esc_html( $person_name ); ?>
										<?php if ( $person_extra ) : ?>
										<br><span class="text-muted fw-normal small"><?php echo esc_html( $person_extra ); ?></span>
										<?php endif; ?>
									</strong>
								</li>
								<?php endif; ?>

								<?php if ( $contract_date ) : ?>
								<li class="d-flex justify-content-between border-bottom py-2 gap-3">
									<span class="text-muted"><?php esc_html_e( 'Contract', 'cw-management-company' ); ?></span>
									<strong><?php echo esc_html( mysql2date( get_option( 'date_format' ), $contract_date ) ); ?></strong>
								</li>
								<?php endif; ?>

								<?php if ( $phone ) : ?>
								<li class="d-flex justify-content-between border-bottom py-2 gap-3">
									<span class="text-muted"><?php esc_html_e( 'Dispatcher', 'cw-management-company' ); ?></span>
									<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>" class="fw-semibold text-dark">
										<?php echo esc_html( $phone ); ?>
									</a>
								</li>
								<?php endif; ?>

								<?php if ( $reception_hours ) : ?>
								<li class="d-flex justify-content-between py-2 gap-3">
									<span class="text-muted"><?php esc_html_e( 'Reception', 'cw-management-company' ); ?></span>
									<strong class="text-end"><?php echo esc_html( $reception_hours ); ?></strong>
								</li>
								<?php endif; ?>

							</ul>
						</div>
						<?php endif; ?>

						<?php
						// Technical characteristics accordion in sidebar.
						$tech_rows = [];
						$tech_fields = Metaboxes::field_groups()['technical']['fields'] ?? [];
						foreach ( $tech_fields as $meta_key => $field ) {
							// Skip fields already shown in specs grid above.
							if ( in_array( $meta_key, [ '_mkd_year_built', '_mkd_floors', '_mkd_entrances', '_mkd_dwellings_count', '_mkd_total_area', '_mkd_elevators_count', '_mkd_wall_material', '_mkd_wear_pct' ], true ) ) {
								continue;
							}
							$raw       = get_post_meta( $post_id, $meta_key, true );
							$formatted = Metaboxes::format_value( $field, (string) $raw );
							if ( '' !== $formatted ) {
								$tech_rows[] = [ 'label' => $field['label'], 'value' => $formatted ];
							}
						}
						if ( $tech_rows ) :
						?>
						<div class="accordion accordion-wrapper mb-4">
							<div class="card plain accordion-item shadow-none border">
								<div class="card-header" id="cw-mc-heading-tech">
									<button class="accordion-button collapsed" type="button"
										data-bs-toggle="collapse"
										data-bs-target="#cw-mc-collapse-tech"
										aria-expanded="false"
										aria-controls="cw-mc-collapse-tech">
										<?php esc_html_e( 'Technical Info', 'cw-management-company' ); ?>
									</button>
								</div>
								<div id="cw-mc-collapse-tech" class="accordion-collapse collapse"
									aria-labelledby="cw-mc-heading-tech">
									<div class="card-body">
										<ul class="list-unstyled mb-0">
											<?php foreach ( $tech_rows as $row ) : ?>
											<li class="d-flex justify-content-between border-bottom py-2 gap-3">
												<span class="text-muted"><?php echo esc_html( $row['label'] ); ?></span>
												<strong class="text-end"><?php echo esc_html( $row['value'] ); ?></strong>
											</li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<?php
						// Address & Management fields in sidebar.
						$sidebar_groups = [ 'address', 'management' ];
						foreach ( $sidebar_groups as $gk ) {
							$group_def = Metaboxes::field_groups()[ $gk ] ?? null;
							if ( ! $group_def ) continue;
							$rows = [];
							$skip = [ '_mkd_tariff', '_mkd_phone', '_mkd_reception_hours', '_mkd_responsible_person', '_mkd_contract_date' ];
							foreach ( $group_def['fields'] as $meta_key => $field ) {
								if ( in_array( $meta_key, $skip, true ) ) continue;
								$raw       = get_post_meta( $post_id, $meta_key, true );
								$formatted = Metaboxes::format_value( $field, (string) $raw );
								if ( '' !== $formatted ) {
									$rows[] = [ 'label' => $field['label'], 'value' => $formatted ];
								}
							}
							if ( ! $rows ) continue;
							?>
							<div class="card shadow-none border p-5 mb-4">
								<h3 class="h6 text-uppercase text-muted mb-3"><?php echo esc_html( $group_def['label'] ); ?></h3>
								<ul class="list-unstyled mb-0">
									<?php foreach ( $rows as $row ) : ?>
									<li class="d-flex justify-content-between border-bottom py-2 gap-3">
										<span class="text-muted"><?php echo esc_html( $row['label'] ); ?></span>
										<strong class="text-end"><?php echo esc_html( $row['value'] ); ?></strong>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
							<?php
						}
						?>

					</div>
				</aside>

			</div>
		</div>
	</section>

<?php endwhile; ?>

<?php get_footer(); ?>
