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

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

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

	$tariff_rows_raw = get_post_meta( $post_id, '_mkd_tariff_rows', true );
	$tariff_rows     = ( $tariff_rows_raw ) ? json_decode( $tariff_rows_raw, true ) : [];
	if ( ! is_array( $tariff_rows ) ) { $tariff_rows = []; }

	$works_raw = get_post_meta( $post_id, '_mkd_works', true );
	$works     = ( $works_raw ) ? json_decode( $works_raw, true ) : [];
	if ( ! is_array( $works ) ) { $works = []; }

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

	$spec_items = [];
	if ( $year_built ) $spec_items[] = [ 'k' => __( 'Year Built', 'cw-management-company' ),   'v' => $year_built ];
	if ( $floors )     $spec_items[] = [ 'k' => __( 'Floors', 'cw-management-company' ),       'v' => $floors ];
	if ( $entrances )  $spec_items[] = [ 'k' => __( 'Entrances', 'cw-management-company' ),    'v' => $entrances ];
	if ( $dwellings )  $spec_items[] = [ 'k' => __( 'Apartments', 'cw-management-company' ),   'v' => number_format( (int) $dwellings ) ];
	if ( $total_area ) $spec_items[] = [ 'k' => __( 'Total Area', 'cw-management-company' ),   'v' => number_format( (float) $total_area, 0, '.', ' ' ) . ' m²' ];
	if ( $elevators )  $spec_items[] = [ 'k' => __( 'Elevators', 'cw-management-company' ),    'v' => $elevators ];
	if ( $tariff )     $spec_items[] = [ 'k' => __( 'Tariff', 'cw-management-company' ),       'v' => number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' ];
	if ( $wear_pct )   $spec_items[] = [ 'k' => __( 'Wear', 'cw-management-company' ),         'v' => $wear_pct . '%' ];

	$tariff_rows_clean = array_values( array_filter( $tariff_rows, static fn( $r ) => '' !== trim( $r['name'] ?? '' ) ) );
	?>

<div class="cw-mc-page">

	<?php /* ══════════════════════════════════════════════════ 1. HERO */ ?>
	<section class="cw-mc-s-hero">
		<div class="cw-mc-wrap">

			<div class="cw-mc-breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'cw-management-company' ); ?></a>
				<span class="cw-mc-breadcrumb__sep">·</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mkd_object' ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'Properties', 'cw-management-company' ); ?></a>
				<span class="cw-mc-breadcrumb__sep">·</span>
				<?php echo esc_html( get_the_title() ); ?>
			</div>

			<div class="cw-mc-hero-grid">

				<div>
					<?php /* Badges */ ?>
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
								mysql2date( get_option( 'date_format' ), $contract_date )
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
					<p class="cw-mc-address-hint text-muted small mb-3"><?php echo esc_html( $address ); ?></p>
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
						<?php endif; ?>
					</div>
					<div class="cw-mc-photo-grid__secondary">
						<?php if ( $photo_yard_id > 0 ) : ?>
						<?php echo wp_get_attachment_image( $photo_yard_id, 'medium', false, [ 'alt' => esc_attr__( 'Yard', 'cw-management-company' ) ] ); ?>
						<?php endif; ?>
					</div>
					<div class="cw-mc-photo-grid__secondary">
						<?php if ( $photo_entrance_id > 0 ) : ?>
						<?php echo wp_get_attachment_image( $photo_entrance_id, 'medium', false, [ 'alt' => esc_attr__( 'Entrance', 'cw-management-company' ) ] ); ?>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</section>

	<?php /* ══════════════════════════════════════════════════ 2. TARIFF + CONTACT */ ?>
	<?php
	$show_tariff  = (bool) ( $tariff && $tariff_rows_clean );
	$show_contact = (bool) ( $phone || $reception_hours || $responsible );
	if ( $show_tariff || $show_contact ) :
	?>
	<section class="cw-mc-s" id="tariff">
		<div class="cw-mc-wrap">
			<div class="cw-mc-2col">

				<?php if ( $show_tariff ) : ?>
				<div>
					<h2 class="cw-mc-h2">
						<?php
						printf(
							/* translators: %s: tariff amount */
							esc_html__( 'Tariff Breakdown — %s', 'cw-management-company' ),
							esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' )
						);
						?>
					</h2>
					<?php if ( $contract_date ) : ?>
					<p class="cw-mc-tariff-meta">
						<?php
						printf(
							/* translators: %s: date */
							esc_html__( 'Approved at general meeting on %s', 'cw-management-company' ),
							esc_html( mysql2date( get_option( 'date_format' ), $contract_date ) )
						);
						?>
					</p>
					<?php endif; ?>
					<div class="cw-mc-tariff-card">
						<?php foreach ( $tariff_rows_clean as $trow ) :
							$pct = min( 100, max( 0, (float) ( $trow['pct'] ?? 0 ) ) );
						?>
						<div class="cw-mc-tariff-row">
							<div class="cw-mc-tariff-row__name"><?php echo esc_html( $trow['name'] ); ?></div>
							<div class="cw-mc-tariff-row__val">
								<?php if ( ! empty( $trow['val'] ) ) echo esc_html( $trow['val'] ) . ' ₽'; ?>
							</div>
							<div class="progressbar line primary" data-value="<?php echo esc_attr( $pct ); ?>"></div>
						</div>
						<?php endforeach; ?>
						<div class="cw-mc-tariff-total">
							<span><?php esc_html_e( 'Total', 'cw-management-company' ); ?></span>
							<span><?php echo esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m² ' . __( 'per month', 'cw-management-company' ) ); ?></span>
						</div>
					</div>
				</div>
				<?php else : ?>
				<div></div>
				<?php endif; ?>

				<?php if ( $show_contact ) : ?>
				<div class="cw-mc-contact-card">
					<?php if ( $responsible ) :
						$p     = explode( ',', $responsible, 2 );
						$pname = trim( $p[0] );
						$prole = ! empty( $p[1] ) ? trim( $p[1] ) : '';
						$words = preg_split( '/[\s,]+/', $pname );
						$ini   = mb_strtoupper( implode( '', array_map( static fn( $w ) => mb_substr( $w, 0, 1 ), array_filter( $words ) ) ) );
						$ini   = mb_substr( $ini, 0, 2 );
					?>
					<div class="cw-mc-contact-card__eyebrow"><?php esc_html_e( 'YOUR CONTACT', 'cw-management-company' ); ?></div>
					<div class="cw-mc-contact-card__person">
						<div class="cw-mc-avatar"><?php echo esc_html( $ini ); ?></div>
						<div>
							<div class="cw-mc-contact-card__name"><?php echo esc_html( $pname ); ?></div>
							<?php if ( $prole ) : ?>
							<div class="cw-mc-contact-card__role"><?php echo esc_html( $prole ); ?></div>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>

					<div class="cw-mc-contact-card__footer">
						<?php if ( $phone ) : ?>
						<div class="cw-mc-contact-card__sub"><?php esc_html_e( 'Dispatcher', 'cw-management-company' ); ?></div>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>" class="cw-mc-contact-card__phone">
							<?php echo esc_html( $phone ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $reception_hours ) : ?>
						<div class="cw-mc-contact-card__hours"><?php echo esc_html( $reception_hours ); ?></div>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ══════════════════════════════════════════════════ 3. WORKS */ ?>
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

			<?php
			$render_pane = static function ( array $list, string $id, bool $active ): void {
				if ( ! $list ) return;
				?>
				<div id="<?php echo esc_attr( $id ); ?>" class="cw-mc-works-pane <?php echo $active ? '' : 'd-none'; ?>">
					<div class="cw-mc-works-list">
						<?php foreach ( $list as $w ) :
							if ( '' === trim( $w['title'] ?? '' ) ) continue;
							$is_done   = 'done' === ( $w['type'] ?? '' );
							$s_text    = ! empty( $w['status'] )
								? $w['status']
								: ( $is_done ? __( 'Completed', 'cw-management-company' ) : __( 'Planned', 'cw-management-company' ) );
							$s_mod     = $is_done ? 'done' : 'plan';
						?>
						<div class="cw-mc-work-row">
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

		</div>
	</section>
	<?php endif; ?>

	<?php /* ══════════════════════════════════════════════════ 4. DOCUMENTS */ ?>
	<?php if ( $documents_by_type || $document_years ) : ?>
	<section class="cw-mc-s" id="docs">
		<div class="cw-mc-wrap">
			<h2 class="cw-mc-h2 mb-5"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>

			<?php if ( ( $document_type_terms && ! is_wp_error( $document_type_terms ) && count( $document_type_terms ) > 1 ) || count( $document_years ) > 1 ) : ?>
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
			<?php endif; ?>

			<div id="cw-mc-documents-list">
				<?php echo Documents::render_list( $documents_by_type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ══════════════════════════════════════════════════ 5. MAP */ ?>
	<?php if ( class_exists( 'Codeweber_Yandex_Maps' ) ) :
		$map_marker  = Plugin::build_map_marker( $post_id );
		$yandex_maps = Codeweber_Yandex_Maps::get_instance();
		if ( $map_marker && $yandex_maps->has_api_key() ) :
	?>
	<section class="cw-mc-s cw-mc-s-last">
		<div class="cw-mc-wrap">
			<h2 class="cw-mc-h2 mb-5"><?php esc_html_e( 'Location', 'cw-management-company' ); ?></h2>
			<?php
			echo $yandex_maps->render_map(
				[
					'height'                       => 420,
					'zoom'                         => 16,
					'center'                       => [ (float) $map_marker['latitude'], (float) $map_marker['longitude'] ],
					'auto_fit_bounds'              => false,
					'marker_open_balloon_on_click' => true,
					'border_radius'                => 20,
				],
				[ $map_marker ]
			);
			?>
		</div>
	</section>
	<?php
		endif;
	endif;
	?>

</div><?php /* .cw-mc-page */ ?>

<?php if ( $works_done && $works_plan ) : ?>
<script>
(function(){
	var btns = document.querySelectorAll('#cw-mc-works-tabs .cw-mc-tab-btn');
	btns.forEach(function(btn){
		btn.addEventListener('click', function(){
			btns.forEach(function(b){ b.classList.remove('is-active'); });
			btn.classList.add('is-active');
			document.querySelectorAll('.cw-mc-works-pane').forEach(function(p){ p.classList.add('d-none'); });
			var pane = document.getElementById('cw-mc-pane-' + btn.dataset.tab);
			if (pane) pane.classList.remove('d-none');
		});
	});
})();
</script>
<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
