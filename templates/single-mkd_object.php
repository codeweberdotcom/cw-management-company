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

	$has_facade   = has_post_thumbnail();
	$has_yard     = $photo_yard_id > 0;
	$has_entrance = $photo_entrance_id > 0;
	$has_photos   = $has_facade || $has_yard || $has_entrance;
	?>

	<div class="cw-mc-single">

	<?php /* ══════════════════════════════════════════════════ 1. HERO */ ?>
	<section class="cw-mc-section" style="padding-top:40px;padding-bottom:0">
		<div class="cw-mc-container">

			<?php /* Breadcrumb */ ?>
			<div style="font-size:14px;color:#6C7065;font-weight:600;margin-bottom:22px">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:#6C7065;text-decoration:none"><?php esc_html_e( 'Home', 'cw-management-company' ); ?></a>
				<span style="margin:0 6px">·</span>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mkd_object' ) ?: home_url( '/' ) ); ?>" style="color:#6C7065;text-decoration:none"><?php esc_html_e( 'Properties', 'cw-management-company' ); ?></a>
				<span style="margin:0 6px">·</span>
				<span><?php echo esc_html( get_the_title() ); ?></span>
			</div>

			<div class="cw-mc-hero-grid" style="display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,0.85fr);gap:48px;align-items:start">

				<?php /* Left: text */ ?>
				<div>
					<?php /* Badges */ ?>
					<?php
					$badges = [];
					if ( $city )   $badges[] = [ 'text' => $city,  'primary' => true ];
					if ( $wall_label || $series ) {
						$ws = implode( ', ', array_filter( [ $wall_label, $series ] ) );
						if ( $ws ) $badges[] = [ 'text' => $ws, 'primary' => false ];
					}
					if ( $status_term ) {
						$badges[] = [ 'text' => $status_term->name, 'primary' => false ];
					}
					if ( $contract_date ) {
						$formatted_date = mysql2date( get_option( 'date_format' ), $contract_date );
						/* translators: %s: contract date */
						$badges[] = [ 'text' => sprintf( __( 'In management since %s', 'cw-management-company' ), $formatted_date ), 'primary' => false ];
					}
					?>
					<?php if ( $badges ) : ?>
					<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px">
						<?php foreach ( $badges as $badge ) :
							$bg = $badge['primary'] ? '#E6EDE6' : '#EFEBE2';
							$color = $badge['primary'] ? '#1F5D3F' : '#6C7065';
							$fw = $badge['primary'] ? '700' : '600';
						?>
						<span style="font-size:12px;font-weight:<?php echo esc_attr( $fw ); ?>;color:<?php echo esc_attr( $color ); ?>;background:<?php echo esc_attr( $bg ); ?>;border-radius:999px;padding:5px 12px">
							<?php echo esc_html( $badge['text'] ); ?>
						</span>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<h1 class="cw-mc-h1" style="margin:0 0 14px">
						<?php echo wp_kses_post( get_the_title() ); ?>
					</h1>

					<?php if ( $address && get_the_title() !== $address ) : ?>
					<p style="font-size:14px;color:#6C7065;margin:0 0 14px">
						<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" style="vertical-align:-1px;margin-right:4px"><path d="M8 0a5 5 0 0 0-5 5c0 5.25 5 11 5 11s5-5.75 5-11A5 5 0 0 0 8 0zm0 7.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
						<?php echo esc_html( $address ); ?>
					</p>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
					<div class="cw-mc-desc" style="font-size:17px;line-height:1.6;color:#4A4F45;margin:0 0 28px;max-width:34em">
						<?php the_content(); ?>
					</div>
					<?php endif; ?>

					<?php /* Specs grid */ ?>
					<?php if ( $spec_items ) : ?>
					<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;padding-top:24px;border-top:1px solid #E2DDD2" class="cw-mc-specs">
						<?php foreach ( $spec_items as $s ) : ?>
						<div>
							<div style="font-size:13px;color:#6C7065;margin-bottom:4px"><?php echo esc_html( $s['k'] ); ?></div>
							<div style="font-size:18px;font-weight:700;letter-spacing:-0.3px"><?php echo esc_html( $s['v'] ); ?></div>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>

				<?php /* Right: photo grid */ ?>
				<?php if ( $has_photos ) : ?>
				<div style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:230px 160px;gap:12px">

					<?php /* Facade – spans 2 columns */ ?>
					<?php if ( $has_facade ) : ?>
					<div style="grid-column:span 2;border-radius:16px;overflow:hidden;height:230px">
						<?php the_post_thumbnail( 'large', [ 'style' => 'width:100%;height:100%;object-fit:cover;display:block', 'alt' => '' ] ); ?>
					</div>
					<?php else : ?>
					<div style="grid-column:span 2;height:230px;border-radius:16px;background:#EFEBE2"></div>
					<?php endif; ?>

					<?php /* Yard */ ?>
					<?php if ( $has_yard ) : ?>
					<div style="border-radius:12px;overflow:hidden;height:160px">
						<?php echo wp_get_attachment_image( $photo_yard_id, 'medium', false, [ 'style' => 'width:100%;height:100%;object-fit:cover;display:block', 'alt' => esc_attr__( 'Yard', 'cw-management-company' ) ] ); ?>
					</div>
					<?php else : ?>
					<div style="height:160px;border-radius:12px;background:#EFEBE2"></div>
					<?php endif; ?>

					<?php /* Entrance */ ?>
					<?php if ( $has_entrance ) : ?>
					<div style="border-radius:12px;overflow:hidden;height:160px">
						<?php echo wp_get_attachment_image( $photo_entrance_id, 'medium', false, [ 'style' => 'width:100%;height:100%;object-fit:cover;display:block', 'alt' => esc_attr__( 'Entrance', 'cw-management-company' ) ] ); ?>
					</div>
					<?php else : ?>
					<div style="height:160px;border-radius:12px;background:#EFEBE2"></div>
					<?php endif; ?>

				</div>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<?php /* ══════════════════════════════════════════════════ 2. TARIFF + CONTACT */ ?>
	<?php
	$tariff_rows_clean = array_filter( $tariff_rows, static fn( $r ) => '' !== trim( $r['name'] ?? '' ) );
	$show_tariff = $tariff && $tariff_rows_clean;
	$show_contact = $phone || $reception_hours || $responsible;
	if ( $show_tariff || $show_contact ) :
	?>
	<section class="cw-mc-section" id="tariff">
		<div class="cw-mc-container">
			<div style="display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:32px;align-items:start" class="cw-mc-2col">

				<?php /* Left: tariff breakdown */ ?>
				<?php if ( $show_tariff ) : ?>
				<div>
					<h2 class="cw-mc-h2" style="margin:0">
						<?php
						printf(
							/* translators: %s: tariff amount */
							esc_html__( 'Tariff Breakdown — %s', 'cw-management-company' ),
							esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m²' )
						);
						?>
					</h2>
					<?php if ( $contract_date ) : ?>
					<p style="font-size:15px;color:#6C7065;margin:10px 0 0">
						<?php
						printf(
							/* translators: %s: approval date */
							esc_html__( 'Approved at general meeting on %s', 'cw-management-company' ),
							esc_html( mysql2date( get_option( 'date_format' ), $contract_date ) )
						);
						?>
					</p>
					<?php endif; ?>
					<div style="background:#FFFFFF;border:1px solid #E7E2D8;border-radius:18px;margin-top:22px;overflow:hidden">
						<?php
						$first_row = true;
						foreach ( $tariff_rows_clean as $trow ) :
							$pct_val     = (float) ( $trow['pct'] ?? 0 );
							$pct_display = min( 100, max( 0, $pct_val ) );
						?>
						<div style="display:grid;grid-template-columns:minmax(0,1.4fr) 90px minmax(0,1fr);gap:20px;align-items:center;padding:16px 24px;border-top:<?php echo $first_row ? 'none' : '1px solid #EEEAE1'; ?>">
							<div style="font-size:16px;font-weight:600"><?php echo esc_html( $trow['name'] ); ?></div>
							<div style="font-size:16px;font-weight:700;font-variant-numeric:tabular-nums">
								<?php if ( ! empty( $trow['val'] ) ) echo esc_html( $trow['val'] ) . ' ₽'; ?>
							</div>
							<div style="height:8px;border-radius:999px;background:#EFEBE2;overflow:hidden">
								<div style="height:100%;border-radius:999px;background:#1F5D3F;width:<?php echo esc_attr( $pct_display ); ?>%"></div>
							</div>
						</div>
						<?php $first_row = false; endforeach; ?>
						<div style="display:flex;justify-content:space-between;padding:16px 24px;background:#EFEBE2;font-size:16px;font-weight:800">
							<span><?php esc_html_e( 'Total', 'cw-management-company' ); ?></span>
							<span><?php echo esc_html( number_format( (float) $tariff, 2, '.', ' ' ) . ' ₽/m² ' . __( 'per month', 'cw-management-company' ) ); ?></span>
						</div>
					</div>
				</div>
				<?php else : ?>
				<div></div>
				<?php endif; ?>

				<?php /* Right: dark contact card */ ?>
				<?php if ( $show_contact ) : ?>
				<div style="background:#12150F;color:#F5F2EC;border-radius:20px;padding:28px">
					<?php if ( $responsible ) :
						$person_parts = explode( ',', $responsible, 2 );
						$person_name  = trim( $person_parts[0] );
						$person_role  = ! empty( $person_parts[1] ) ? trim( $person_parts[1] ) : '';
					?>
					<div style="font-size:13px;font-weight:700;color:#7FB894;letter-spacing:0.6px;margin-bottom:20px">
						<?php esc_html_e( 'YOUR CONTACT', 'cw-management-company' ); ?>
					</div>
					<div style="display:flex;gap:14px;align-items:center;margin-bottom:24px">
						<?php
						$initials_raw = preg_split( '/[\s,]+/', $person_name );
						$initials = implode( '', array_map( static fn( $w ) => mb_strtoupper( mb_substr( $w, 0, 1 ) ), array_filter( $initials_raw ) ) );
						$initials = mb_substr( $initials, 0, 2 );
						?>
						<div style="flex:none;width:46px;height:46px;border-radius:50%;background:#2E3329;color:#7FB894;display:grid;place-items:center;font-size:15px;font-weight:700">
							<?php echo esc_html( $initials ); ?>
						</div>
						<div>
							<div style="font-size:16px;font-weight:700"><?php echo esc_html( $person_name ); ?></div>
							<?php if ( $person_role ) : ?>
							<div style="font-size:13px;color:#A8AC9F;margin-top:2px"><?php echo esc_html( $person_role ); ?></div>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>

					<div style="padding-top:20px;border-top:1px solid #2E3329;display:grid;gap:10px;font-size:15px">
						<?php if ( $phone ) : ?>
						<div style="color:#A8AC9F"><?php esc_html_e( 'Dispatcher', 'cw-management-company' ); ?></div>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>" style="color:#FFFFFF;font-weight:700;font-size:19px;text-decoration:none">
							<?php echo esc_html( $phone ); ?>
						</a>
						<?php endif; ?>
						<?php if ( $reception_hours ) : ?>
						<div style="color:#A8AC9F;font-size:14px;line-height:1.5;margin-top:4px">
							<?php echo esc_html( $reception_hours ); ?>
						</div>
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
	<section class="cw-mc-section" id="works">
		<div class="cw-mc-container">
			<div style="display:flex;align-items:flex-end;justify-content:space-between;gap:32px;flex-wrap:wrap;margin-bottom:20px">
				<h2 class="cw-mc-h2" style="margin:0"><?php esc_html_e( 'Completed Works & Plans', 'cw-management-company' ); ?></h2>
				<?php if ( $works_done && $works_plan ) : ?>
				<div style="display:flex;gap:8px;background:#EFEBE2;padding:6px;border-radius:12px" id="cw-mc-works-tabs">
					<button type="button" class="cw-mc-tab-btn cw-mc-tab-active" data-tab="done"
						style="border:none;border-radius:9px;padding:10px 16px;font-size:15px;font-weight:600;cursor:pointer;transition:background .15s,color .15s">
						<?php esc_html_e( 'Completed', 'cw-management-company' ); ?>
					</button>
					<button type="button" class="cw-mc-tab-btn" data-tab="plan"
						style="border:none;border-radius:9px;padding:10px 16px;font-size:15px;font-weight:600;cursor:pointer;transition:background .15s,color .15s;background:transparent;color:#3D4238">
						<?php esc_html_e( 'Planned', 'cw-management-company' ); ?>
					</button>
				</div>
				<?php endif; ?>
			</div>

			<?php
			$render_works_list = static function ( array $list, string $pane_id, bool $active ): void {
				if ( ! $list ) return;
				?>
				<div id="<?php echo esc_attr( $pane_id ); ?>" class="cw-mc-works-pane" style="display:<?php echo $active ? 'block' : 'none'; ?>">
					<div style="display:grid;gap:12px">
						<?php foreach ( $list as $w ) :
							if ( '' === trim( $w['title'] ?? '' ) ) continue;
							$is_done   = 'done' === ( $w['type'] ?? '' );
							$sc        = $is_done ? '#1F5D3F' : '#8A5A2B';
							$s_text    = ! empty( $w['status'] ) ? $w['status'] : ( $is_done ? __( 'Completed', 'cw-management-company' ) : __( 'Planned', 'cw-management-company' ) );
						?>
						<div style="background:#FFFFFF;border:1px solid #E7E2D8;border-radius:16px;padding:20px 24px;display:grid;grid-template-columns:130px minmax(0,1fr) 150px 140px;gap:24px;align-items:center" class="cw-mc-work-row">
							<div style="font-size:14px;font-weight:700;color:#6C7065"><?php echo esc_html( $w['date'] ?? '' ); ?></div>
							<div>
								<div style="font-size:18px;font-weight:700;letter-spacing:-0.3px"><?php echo esc_html( $w['title'] ); ?></div>
								<?php if ( ! empty( $w['detail'] ) ) : ?>
								<div style="font-size:14px;color:#55594F;margin-top:4px"><?php echo esc_html( $w['detail'] ); ?></div>
								<?php endif; ?>
							</div>
							<div style="font-size:15px;color:#55594F;white-space:nowrap"><?php echo esc_html( $w['cost'] ?? '' ); ?></div>
							<div style="font-size:14px;font-weight:700;color:<?php echo esc_attr( $sc ); ?>"><?php echo esc_html( $s_text ); ?></div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
			};

			$both = $works_done && $works_plan;
			$render_works_list( $works_done ?: $works_plan, 'cw-mc-pane-done', true );
			if ( $both ) {
				$render_works_list( $works_plan, 'cw-mc-pane-plan', false );
			}
			?>

		</div>
	</section>
	<?php endif; ?>

	<?php /* ══════════════════════════════════════════════════ 4. DOCUMENTS */ ?>
	<?php if ( $documents_by_type || $document_years ) : ?>
	<section class="cw-mc-section" id="docs">
		<div class="cw-mc-container">
			<h2 class="cw-mc-h2" style="margin:0 0 22px"><?php esc_html_e( 'Documents', 'cw-management-company' ); ?></h2>

			<?php if ( ( $document_type_terms && ! is_wp_error( $document_type_terms ) && count( $document_type_terms ) > 1 ) || count( $document_years ) > 1 ) : ?>
			<form id="cw-mc-document-filter" style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px">
				<?php if ( $document_type_terms && ! is_wp_error( $document_type_terms ) ) : ?>
				<select name="type" style="border:1px solid #D8D2C5;background:#FFFFFF;border-radius:999px;padding:8px 16px;font-size:14px;font-weight:600;color:#3D4238;outline:none">
					<option value=""><?php esc_html_e( 'All types', 'cw-management-company' ); ?></option>
					<?php foreach ( $document_type_terms as $type_term ) : ?>
					<option value="<?php echo esc_attr( $type_term->slug ); ?>"><?php echo esc_html( $type_term->name ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php endif; ?>
				<?php if ( $document_years ) : ?>
				<select name="year" style="border:1px solid #D8D2C5;background:#FFFFFF;border-radius:999px;padding:8px 16px;font-size:14px;font-weight:600;color:#3D4238;outline:none">
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
	<section class="cw-mc-section" style="padding-bottom:88px">
		<div class="cw-mc-container">
			<h2 class="cw-mc-h2" style="margin:0 0 22px"><?php esc_html_e( 'Location', 'cw-management-company' ); ?></h2>
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

	</div><?php /* .cw-mc-single */ ?>

	<?php /* ── Styles ─────────────────────────────────────────────────────────────── */ ?>
	<style>
	.cw-mc-single { font-family: Manrope, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #12150F; }
	.cw-mc-section { padding: 72px 0 0; }
	.cw-mc-container { max-width: 1240px; margin: 0 auto; padding: 0 40px; }
	.cw-mc-h1 { font-family: Bitter, Georgia, serif; font-weight: 600; font-size: 48px; line-height: 1.06; letter-spacing: -1.4px; }
	.cw-mc-h2 { font-family: Bitter, Georgia, serif; font-weight: 600; font-size: 32px; letter-spacing: -0.8px; }
	.cw-mc-desc > p { margin: 0; }
	.cw-mc-tab-btn.cw-mc-tab-active { background: #FFFFFF; color: #12150F; }
	.cw-mc-tab-btn:not(.cw-mc-tab-active) { background: transparent; color: #3D4238; }

	@media (max-width: 1023px) {
		.cw-mc-hero-grid, .cw-mc-2col { grid-template-columns: 1fr !important; }
		.cw-mc-specs { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
		.cw-mc-container { padding: 0 20px; }
		.cw-mc-h1 { font-size: 32px; letter-spacing: -0.8px; }
		.cw-mc-h2 { font-size: 26px; }
		.cw-mc-section { padding-top: 48px; }
		.cw-mc-work-row { grid-template-columns: 1fr !important; gap: 6px !important; padding: 16px !important; }
	}
	@media (max-width: 599px) {
		.cw-mc-specs { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
		.cw-mc-h1 { font-size: 26px; }
	}
	</style>

	<?php /* ── Tab JS ────────────────────────────────────────────────────────────── */ ?>
	<?php if ( $works_done && $works_plan ) : ?>
	<script>
	(function(){
		var tabs = document.querySelectorAll('#cw-mc-works-tabs .cw-mc-tab-btn');
		tabs.forEach(function(btn){
			btn.addEventListener('click', function(){
				tabs.forEach(function(b){ b.classList.remove('cw-mc-tab-active'); });
				btn.classList.add('cw-mc-tab-active');
				document.querySelectorAll('.cw-mc-works-pane').forEach(function(p){ p.style.display = 'none'; });
				var pane = document.getElementById('cw-mc-pane-' + btn.dataset.tab);
				if(pane) pane.style.display = 'block';
			});
		});
	})();
	</script>
	<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
