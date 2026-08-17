<?php
/**
 * Single: Property
 * Template provided by cw-management-company plugin.
 * Override by creating single-mkd_object.php in your (child) theme.
 */

use CW\ManagementCompany\Documents;

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	// ── Meta fields ───────────────────────────────────────────────────────────
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
	$series          = get_post_meta( $post_id, '_mkd_series', true );
	$tariff          = get_post_meta( $post_id, '_mkd_tariff', true );
	$phone           = get_post_meta( $post_id, '_mkd_phone', true );
	$reception_hours = get_post_meta( $post_id, '_mkd_reception_hours', true );
	$responsible     = get_post_meta( $post_id, '_mkd_responsible_person', true );
	$contract_date   = get_post_meta( $post_id, '_mkd_contract_date', true );
	$contract_number = get_post_meta( $post_id, '_mkd_contract_number', true );

	$tariff_rows_raw = get_post_meta( $post_id, '_mkd_tariff_rows', true );
	$tariff_rows     = $tariff_rows_raw ? json_decode( $tariff_rows_raw, true ) : [];
	if ( ! is_array( $tariff_rows ) ) { $tariff_rows = []; }

	$works_raw = get_post_meta( $post_id, '_mkd_works', true );
	$works     = $works_raw ? json_decode( $works_raw, true ) : [];
	if ( ! is_array( $works ) ) { $works = []; }

	$works_done = array_values( array_filter( $works, static fn( $w ) => 'done' === ( $w['type'] ?? '' ) ) );
	$works_plan = array_values( array_filter( $works, static fn( $w ) => 'plan' === ( $w['type'] ?? '' ) ) );

	$photo_yard_id     = (int) get_post_meta( $post_id, '_mkd_photo_yard', true );
	$photo_entrance_id = (int) get_post_meta( $post_id, '_mkd_photo_entrance', true );

	// ── Team members ──────────────────────────────────────────────────────────
	$team_raw     = get_post_meta( $post_id, '_mkd_team_members', true );
	$team_members = $team_raw ? json_decode( $team_raw, true ) : [];
	if ( ! is_array( $team_members ) ) { $team_members = []; }
	// Fallback to single _mkd_responsible_person field
	if ( empty( $team_members ) && $responsible ) {
		$p     = explode( ',', $responsible, 2 );
		$pname = trim( $p[0] );
		$prole = ! empty( $p[1] ) ? trim( $p[1] ) : '';
		$words = preg_split( '/\s+/', $pname );
		$ini   = mb_strtoupper( implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_filter( $words ) ) ) );
		$team_members = [[ 'initials' => mb_substr( $ini, 0, 2 ), 'name' => $pname, 'role' => $prole ]];
	}

	// ── Taxonomy / statuses ───────────────────────────────────────────────────
	$status_terms = get_the_terms( $post_id, 'mkd_object_status' );
	$status_term  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0] : null;

	// ── Documents ─────────────────────────────────────────────────────────────
	$documents_by_type   = class_exists( Documents::class ) ? Documents::group_by_type( Documents::query( $post_id ) ) : [];
	$document_years      = class_exists( Documents::class ) ? Documents::years_for_object( $post_id ) : [];
	$document_type_terms = get_terms( [ 'taxonomy' => 'mkd_document_type', 'hide_empty' => false ] );

	// ── Wall material labels ──────────────────────────────────────────────────
	$wall_labels = [
		'panel'    => __( 'Panel', 'cw-management-company' ),
		'brick'    => __( 'Brick', 'cw-management-company' ),
		'monolith' => __( 'Monolith', 'cw-management-company' ),
		'block'    => __( 'Block', 'cw-management-company' ),
		'wood'     => __( 'Wood', 'cw-management-company' ),
		'other'    => __( 'Other', 'cw-management-company' ),
	];
	$wall_label = ! empty( $wall_material ) ? ( $wall_labels[ $wall_material ] ?? $wall_material ) : '';

	// ── Spec items ────────────────────────────────────────────────────────────
	$spec_items = [];
	if ( $year_built ) $spec_items[] = [ 'k' => __( 'Year Built', 'cw-management-company' ),  'v' => $year_built ];
	if ( $floors )     $spec_items[] = [ 'k' => __( 'Floors', 'cw-management-company' ),      'v' => $floors ];
	if ( $entrances )  $spec_items[] = [ 'k' => __( 'Entrances', 'cw-management-company' ),   'v' => $entrances ];
	if ( $dwellings )  $spec_items[] = [ 'k' => __( 'Apartments', 'cw-management-company' ),  'v' => number_format( (int) $dwellings ) ];
	if ( $total_area ) $spec_items[] = [ 'k' => __( 'Total Area', 'cw-management-company' ),  'v' => number_format( (float) $total_area, 0, '.', ' ' ) . ' m²' ];
	if ( $elevators )  $spec_items[] = [ 'k' => __( 'Elevators', 'cw-management-company' ),   'v' => $elevators ];
	if ( $wear_pct )   $spec_items[] = [ 'k' => __( 'Wear', 'cw-management-company' ),        'v' => $wear_pct . '%' ];

	// ── Tariff rows (clean) ───────────────────────────────────────────────────
	$tariff_rows_clean = array_values( array_filter( $tariff_rows, static fn( $r ) => '' !== trim( $r['name'] ?? '' ) ) );

	// ── Works: date parsing helpers ───────────────────────────────────────────
	$parse_year = static fn( string $d ): string => ( preg_match( '/\b(20\d{2})\b/', $d, $m ) ? $m[1] : '' );
	$month_keys = [
		'январ' => 'январь', 'феврал' => 'февраль', 'март'    => 'март',    'апрел'   => 'апрель',
		'май'   => 'май',    'мая'    => 'май',      'июн'     => 'июнь',    'июл'     => 'июль',
		'август' => 'август', 'сентябр' => 'сентябрь', 'октябр' => 'октябрь',
		'ноябр' => 'ноябрь', 'декабр' => 'декабрь',
	];
	$parse_month = static function ( string $d ) use ( $month_keys ): string {
		$lower = mb_strtolower( $d );
		foreach ( $month_keys as $k => $v ) {
			if ( false !== mb_strpos( $lower, $k ) ) return $v;
		}
		return '';
	};

	$work_years        = [];
	$work_months_found = [];
	$months_order      = [ 'январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь' ];
	foreach ( $works as $w ) {
		$y = $parse_year( $w['date'] ?? '' );
		$m = $parse_month( $w['date'] ?? '' );
		if ( $y ) $work_years[ $y ]        = true;
		if ( $m ) $work_months_found[ $m ] = true;
	}
	$work_years_list  = array_keys( $work_years );
	rsort( $work_years_list );
	$work_months_list = array_values( array_filter( $months_order, static fn( $m ) => isset( $work_months_found[ $m ] ) ) );

	// ── Emergency card ────────────────────────────────────────────────────────
	$emergency_phone = get_post_meta( $post_id, '_mkd_emergency_phone', true );
	$emergency_note  = get_post_meta( $post_id, '_mkd_emergency_note', true );
	$emergency_raw   = get_post_meta( $post_id, '_mkd_emergency_items', true );
	$emergency_items = $emergency_raw ? json_decode( $emergency_raw, true ) : [];
	if ( ! is_array( $emergency_items ) ) { $emergency_items = []; }

	// ── Section visibility ────────────────────────────────────────────────────
	$show_tariff    = (bool) ( $tariff || $tariff_rows_clean );
	$show_contact   = (bool) ( $phone || $reception_hours || $team_members );
	$show_emergency = (bool) ( $emergency_phone || $emergency_items );
	?>

<div class="cw-mc-page">

	<?php /* ════════════════════════════════════════ 1. HERO */ ?>
	<section class="cw-mc-s-hero">
		<div class="cw-mc-wrap">

			<nav class="cw-mc-breadcrumb" aria-label="breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'cw-management-company' ); ?></a>
				<span class="cw-mc-breadcrumb__sep" aria-hidden="true">·</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mkd_object' ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Properties', 'cw-management-company' ); ?></a>
				<span class="cw-mc-breadcrumb__sep" aria-hidden="true">·</span>
				<?php echo esc_html( get_the_title() ); ?>
			</nav>

			<div class="cw-mc-hero-grid">

				<div>
					<?php
					$badges = [];
					if ( $city ) $badges[] = [ 'text' => $city, 'mod' => 'primary' ];
					$ws = implode( ', ', array_filter( [ $wall_label, $series ] ) );
					if ( $ws ) $badges[] = [ 'text' => $ws, 'mod' => 'muted' ];
					if ( $status_term ) $badges[] = [ 'text' => $status_term->name, 'mod' => 'muted' ];
					if ( $contract_date ) {
						$badges[] = [
							'text' => sprintf(
								/* translators: %s: date */
								__( 'In management since %s', 'cw-management-company' ),
								mysql2date( 'F Y', $contract_date )
							),
							'mod' => 'muted',
						];
					}
					?>
					<?php if ( $badges ) : ?>
					<div class="cw-mc-badges">
						<?php foreach ( $badges as $b ) : ?>
						<span class="cw-mc-badge cw-mc-badge--<?php echo esc_attr( $b['mod'] ); ?>">
							<?php echo esc_html( $b['text'] ); ?>
						</span>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<h1 class="cw-mc-h1"><?php echo wp_kses_post( get_the_title() ); ?></h1>

					<?php if ( $address && get_the_title() !== $address ) : ?>
					<p class="text-muted small mb-3"><?php echo esc_html( $address ); ?></p>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
					<div class="cw-mc-desc"><?php the_content(); ?></div>
					<?php endif; ?>

					<?php if ( $spec_items ) : ?>
					<div class="cw-mc-specs">
						<?php foreach ( $spec_items as $s ) : ?>
						<div>
							<div class="cw-mc-spec__label"><?php echo esc_html( $s['k'] ); ?></div>
							<div class="cw-mc-spec__value"><?php echo esc_html( $s['v'] ); ?></div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>

				<div class="cw-mc-photo-grid">
					<div class="cw-mc-photo-grid__facade">
						<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large', [ 'alt' => '' ] ); ?>
						<?php else : ?>
						<div class="cw-mc-photo-grid__placeholder">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="m3 16 5-5 4 4 3-2 6 7"/><circle cx="8" cy="8" r="1.5"/></svg>
							<?php esc_html_e( 'Building Facade', 'cw-management-company' ); ?>
						</div>
						<?php endif; ?>
					</div>
					<div class="cw-mc-photo-grid__secondary">
						<?php if ( $photo_yard_id > 0 ) : ?>
						<?php echo wp_get_attachment_image( $photo_yard_id, 'medium', false, [ 'alt' => esc_attr__( 'Yard', 'cw-management-company' ) ] ); ?>
						<?php else : ?>
						<div class="cw-mc-photo-grid__placeholder">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="m3 16 5-5 4 4 3-2 6 7"/><circle cx="8" cy="8" r="1.5"/></svg>
							<?php esc_html_e( 'Yard', 'cw-management-company' ); ?>
						</div>
						<?php endif; ?>
					</div>
					<div class="cw-mc-photo-grid__secondary">
						<?php if ( $photo_entrance_id > 0 ) : ?>
						<?php echo wp_get_attachment_image( $photo_entrance_id, 'medium', false, [ 'alt' => esc_attr__( 'Entrance', 'cw-management-company' ) ] ); ?>
						<?php else : ?>
						<div class="cw-mc-photo-grid__placeholder">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="m3 16 5-5 4 4 3-2 6 7"/><circle cx="8" cy="8" r="1.5"/></svg>
							<?php esc_html_e( 'Entrance', 'cw-management-company' ); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</section>

	<?php /* ════════════════════════════════════════ 2. TARIFF + CONTACT */ ?>
	<?php if ( $show_tariff || $show_contact ) : ?>
	<section class="cw-mc-s" id="tariff">
		<div class="cw-mc-wrap">
			<div class="cw-mc-2col">

				<?php if ( $show_tariff ) : ?>
				<div>
					<h2 class="cw-mc-h2">
						<?php if ( $tariff ) :
							printf(
								/* translators: %s: tariff amount with unit */
								esc_html__( 'Tariff Breakdown — %s', 'cw-management-company' ),
								esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' )
							);
						else :
							esc_html_e( 'Tariff Breakdown', 'cw-management-company' );
						endif; ?>
					</h2>
					<?php if ( $contract_date ) : ?>
					<p class="cw-mc-tariff-meta">
						<?php
						$meta_str = sprintf(
							/* translators: %s: meeting date */
							__( 'Approved at general meeting on %s', 'cw-management-company' ),
							mysql2date( get_option( 'date_format' ), $contract_date )
						);
						if ( $contract_number ) {
							$meta_str .= ', ' . sprintf(
								/* translators: %s: protocol number */
								__( 'protocol № %s', 'cw-management-company' ),
								$contract_number
							);
						}
						echo esc_html( $meta_str ) . '.';
						?>
					</p>
					<?php endif; ?>
					<?php if ( $tariff_rows_clean ) : ?>
					<div class="cw-mc-tariff-card">
						<?php foreach ( $tariff_rows_clean as $trow ) :
							$pct = min( 100, max( 0, (float) ( $trow['pct'] ?? 0 ) ) );
						?>
						<div class="cw-mc-tariff-row">
							<div class="cw-mc-tariff-row__name"><?php echo esc_html( $trow['name'] ); ?></div>
							<div class="cw-mc-tariff-row__val">
								<?php if ( ! empty( $trow['val'] ) ) echo esc_html( $trow['val'] ) . ' ₽'; ?>
							</div>
							<div class="cw-mc-tariff-row__track">
								<div class="cw-mc-tariff-row__fill" style="--w:<?php echo esc_attr( $pct ); ?>%"></div>
							</div>
						</div>
						<?php endforeach; ?>
						<?php if ( $tariff ) : ?>
						<div class="cw-mc-tariff-total">
							<span><?php esc_html_e( 'Total', 'cw-management-company' ); ?></span>
							<span><?php echo esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m² ' . __( 'per month', 'cw-management-company' ) ); ?></span>
						</div>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php else : ?>
				<div></div>
				<?php endif; ?>

				<?php if ( $show_contact ) : ?>
				<div class="cw-mc-contact-card">
					<?php if ( $team_members ) : ?>
					<div class="cw-mc-contact-card__eyebrow"><?php esc_html_e( 'ASSIGNED TEAM', 'cw-management-company' ); ?></div>
					<div class="cw-mc-team-list">
						<?php foreach ( $team_members as $member ) : ?>
						<div class="cw-mc-team-member">
							<div class="cw-mc-avatar"><?php echo esc_html( $member['initials'] ?? '?' ); ?></div>
							<div>
								<div class="cw-mc-team-member__name"><?php echo esc_html( $member['name'] ?? '' ); ?></div>
								<?php if ( ! empty( $member['role'] ) ) : ?>
								<div class="cw-mc-team-member__role"><?php echo esc_html( $member['role'] ); ?></div>
								<?php endif; ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<?php if ( $phone || $reception_hours ) : ?>
					<div class="cw-mc-contact-card__footer">
						<?php if ( $phone ) : ?>
						<div class="cw-mc-contact-card__sub"><?php esc_html_e( 'Building Dispatcher', 'cw-management-company' ); ?></div>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>" class="cw-mc-contact-card__phone">
							<?php echo esc_html( $phone ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $reception_hours ) : ?>
						<div class="cw-mc-contact-card__hours"><?php echo esc_html( $reception_hours ); ?></div>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ════════════════════════════════════════ 3. WORKS */ ?>
	<?php if ( $works_done || $works_plan ) : ?>
	<section class="cw-mc-s" id="works">
		<div class="cw-mc-wrap">

			<div class="cw-mc-works-header">
				<h2 class="cw-mc-h2"><?php esc_html_e( 'Completed Works & Plans', 'cw-management-company' ); ?></h2>
				<?php if ( $works_done && $works_plan ) : ?>
				<div class="cw-mc-tabs" id="cw-mc-works-tabs">
					<button type="button" class="cw-mc-tab-btn is-active" data-tab="done">
						<?php esc_html_e( 'Completed', 'cw-management-company' ); ?>
					</button>
					<button type="button" class="cw-mc-tab-btn" data-tab="plan">
						<?php esc_html_e( 'Planned', 'cw-management-company' ); ?>
					</button>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( $work_years_list || $work_months_list ) : ?>
			<div class="cw-mc-works-meta">
				<?php if ( $work_years_list ) : ?>
				<select class="cw-mc-select" id="cw-mc-filter-year" aria-label="<?php esc_attr_e( 'Filter by year', 'cw-management-company' ); ?>">
					<option value=""><?php esc_html_e( 'All years', 'cw-management-company' ); ?></option>
					<?php foreach ( $work_years_list as $wy ) : ?>
					<option value="<?php echo esc_attr( $wy ); ?>"><?php echo esc_html( $wy . ' ' . __( 'year', 'cw-management-company' ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
				<?php if ( $work_months_list ) : ?>
				<select class="cw-mc-select" id="cw-mc-filter-month" aria-label="<?php esc_attr_e( 'Filter by month', 'cw-management-company' ); ?>">
					<option value=""><?php esc_html_e( 'All months', 'cw-management-company' ); ?></option>
					<?php foreach ( $work_months_list as $wm ) : ?>
					<option value="<?php echo esc_attr( $wm ); ?>"><?php echo esc_html( $wm ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
				<span class="cw-mc-works-count" id="cw-mc-works-count" aria-live="polite"></span>
			</div>
			<?php endif; ?>

			<?php
			$render_pane = static function ( array $list, string $id, bool $active ) use ( $parse_year, $parse_month ) {
				if ( ! $list ) return;
				?>
				<div id="<?php echo esc_attr( $id ); ?>" class="cw-mc-works-pane<?php echo $active ? '' : ' d-none'; ?>">
					<div class="cw-mc-works-list">
						<?php foreach ( $list as $w ) :
							if ( '' === trim( $w['title'] ?? '' ) ) continue;
							$is_done = 'done' === ( $w['type'] ?? '' );
							$s_text  = ! empty( $w['status'] )
								? $w['status']
								: ( $is_done ? __( 'Completed', 'cw-management-company' ) : __( 'Planned', 'cw-management-company' ) );
							$s_mod   = $is_done ? 'done' : 'plan';
							$wy      = $parse_year( $w['date'] ?? '' );
							$wm      = $parse_month( $w['date'] ?? '' );
						?>
						<div class="cw-mc-work-row"
							data-year="<?php echo esc_attr( $wy ); ?>"
							data-month="<?php echo esc_attr( $wm ); ?>"
							data-cost="<?php echo esc_attr( $w['cost'] ?? '' ); ?>">
							<div class="cw-mc-work-row__date"><?php echo esc_html( $w['date'] ?? '' ); ?></div>
							<div>
								<div class="cw-mc-work-row__title"><?php echo esc_html( $w['title'] ); ?></div>
								<?php if ( ! empty( $w['detail'] ) ) : ?>
								<div class="cw-mc-work-row__detail"><?php echo esc_html( $w['detail'] ); ?></div>
								<?php endif; ?>
							</div>
							<div class="cw-mc-work-row__cost"><?php echo esc_html( $w['cost'] ?? '' ); ?></div>
							<div class="cw-mc-work-row__status cw-mc-work-row__status--<?php echo esc_attr( $s_mod ); ?>">
								<?php echo esc_html( $s_text ); ?>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			};

			$both = $works_done && $works_plan;
			$render_pane( $works_done ?: $works_plan, 'cw-mc-pane-done', true );
			if ( $both ) {
				$render_pane( $works_plan, 'cw-mc-pane-plan', false );
			}
			?>

			<p class="cw-mc-works-note">
				<?php esc_html_e( 'Full list of works for all management years — in annual reports in the', 'cw-management-company' ); ?>
				<?php if ( $documents_by_type || $document_years ) : ?>
				<a href="#docs"><?php esc_html_e( 'documents section', 'cw-management-company' ); ?></a>
				<?php else : ?>
				<?php esc_html_e( 'documents section', 'cw-management-company' ); ?>
				<?php endif; ?>.
			</p>

		</div>
	</section>
	<?php endif; ?>

	<?php /* ════════════════════════════════════════ 4. DOCUMENTS + EMERGENCY */ ?>
	<?php if ( $documents_by_type || $document_years || $show_emergency ) : ?>
	<section class="cw-mc-s" id="docs">
		<div class="cw-mc-wrap">
			<?php
			$has_docs     = (bool) ( $documents_by_type || $document_years );
			$use_2col     = $has_docs && $show_emergency;

			$render_doc_filter = static function () use ( $document_type_terms, $document_years ) {
				if ( ( $document_type_terms && ! is_wp_error( $document_type_terms ) && count( $document_type_terms ) > 1 ) || count( $document_years ) > 1 ) {
					?>
					<form id="cw-mc-document-filter" class="cw-mc-doc-filter">
						<?php if ( $document_type_terms && ! is_wp_error( $document_type_terms ) ) : ?>
						<select name="type" class="cw-mc-select">
							<option value=""><?php esc_html_e( 'All types', 'cw-management-company' ); ?></option>
							<?php foreach ( $document_type_terms as $type_term ) : ?>
							<option value="<?php echo esc_attr( $type_term->slug ); ?>"><?php echo esc_html( $type_term->name ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php endif; ?>
						<?php if ( $document_years ) : ?>
						<select name="year" class="cw-mc-select">
							<option value=""><?php esc_html_e( 'All years', 'cw-management-company' ); ?></option>
							<?php foreach ( $document_years as $year ) : ?>
							<option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php endif; ?>
					</form>
					<?php
				}
			};

			$render_emergency_card = static function () use ( $emergency_phone, $emergency_items, $emergency_note ) {
				?>
				<div class="cw-mc-emergency-card">
					<div class="cw-mc-emergency-card__header">
						<h3 class="cw-mc-emergency-card__title"><?php esc_html_e( 'What to Do in an Emergency', 'cw-management-company' ); ?></h3>
						<?php if ( $emergency_phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $emergency_phone ) ); ?>" class="cw-mc-emergency-card__phone">
							<?php echo esc_html( $emergency_phone ); ?>
						</a>
						<?php endif; ?>
					</div>
					<?php if ( $emergency_items ) : ?>
					<div class="cw-mc-emergency-list">
						<?php foreach ( $emergency_items as $item ) : ?>
						<div class="cw-mc-emergency-item">
							<div class="cw-mc-emergency-item__title"><?php echo esc_html( $item['title'] ?? '' ); ?></div>
							<?php if ( ! empty( $item['desc'] ) ) : ?>
							<div class="cw-mc-emergency-item__desc"><?php echo nl2br( esc_html( $item['desc'] ) ); ?></div>
							<?php endif; ?>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<?php if ( $emergency_note ) : ?>
					<div class="cw-mc-emergency-card__note"><?php echo esc_html( $emergency_note ); ?></div>
					<?php endif; ?>
				</div>
				<?php
			};
			?>

			<?php if ( $use_2col ) : ?>
			<div class="cw-mc-docs-grid">
				<div>
					<h2 class="cw-mc-h2 mb-4"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>
					<?php $render_doc_filter(); ?>
					<div id="cw-mc-documents-list">
						<?php echo Documents::render_list( $documents_by_type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</div>
				<div><?php $render_emergency_card(); ?></div>
			</div>

			<?php elseif ( $has_docs ) : ?>
			<h2 class="cw-mc-h2 mb-5"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>
			<?php $render_doc_filter(); ?>
			<div id="cw-mc-documents-list">
				<?php echo Documents::render_list( $documents_by_type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>

			<?php else : ?>
			<?php $render_emergency_card(); ?>
			<?php endif; ?>

		</div>
	</section>
	<?php endif; ?>

</div><?php /* .cw-mc-page */ ?>

<script>
(function () {
	// ── Works tabs ────────────────────────────────────────────────────────────
	var tabBtns = document.querySelectorAll('#cw-mc-works-tabs .cw-mc-tab-btn');
	if (tabBtns.length) {
		tabBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				tabBtns.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				document.querySelectorAll('.cw-mc-works-pane').forEach(function (p) { p.classList.add('d-none'); });
				var pane = document.getElementById('cw-mc-pane-' + btn.dataset.tab);
				if (pane) pane.classList.remove('d-none');
				applyFilter();
			});
		});
	}

	// ── Works filter ─────────────────────────────────────────────────────────
	var yearSel  = document.getElementById('cw-mc-filter-year');
	var monthSel = document.getElementById('cw-mc-filter-month');
	var counter  = document.getElementById('cw-mc-works-count');

	function parseCost(str) {
		if (!str) return 0;
		var n = parseFloat(str.replace(/[^\d]/g, ''));
		return isNaN(n) ? 0 : n;
	}

	function plural(n, one, few, many) {
		var mod10 = n % 10, mod100 = n % 100;
		if (mod10 === 1 && mod100 !== 11) return one;
		if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
		return many;
	}

	function applyFilter() {
		var year  = yearSel  ? yearSel.value  : '';
		var month = monthSel ? monthSel.value : '';

		document.querySelectorAll('.cw-mc-work-row').forEach(function (row) {
			var ok = (!year || row.dataset.year === year) && (!month || row.dataset.month === month);
			row.classList.toggle('cw-mc-hidden', !ok);
		});

		if (!counter) return;
		var activePane = document.querySelector('.cw-mc-works-pane:not(.d-none)');
		if (!activePane) return;
		var visible = activePane.querySelectorAll('.cw-mc-work-row:not(.cw-mc-hidden)');
		var count = visible.length;
		var total = 0;
		visible.forEach(function (r) { total += parseCost(r.dataset.cost || ''); });

		if (!count) { counter.textContent = ''; return; }
		var label = plural(count, 'работа', 'работы', 'работ');
		var text  = count + ' ' + label;
		if (total > 0) {
			var tStr = total >= 1000000
				? (Math.round(total / 100000) / 10) + ' млн. ₽'
				: total >= 1000
				? Math.round(total / 1000) + ' тыс. ₽'
				: total + ' ₽';
			text += ' · ' + tStr;
		}
		counter.textContent = text;
	}

	if (yearSel)  yearSel.addEventListener('change',  applyFilter);
	if (monthSel) monthSel.addEventListener('change', applyFilter);

	applyFilter();
})();
</script>

<?php endwhile; ?>
<?php get_footer(); ?>
