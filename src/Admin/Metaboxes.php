<?php

namespace CW\ManagementCompany\Admin;

class Metaboxes {

	private const NONCE_ACTION = 'cw_mc_object_save_';
	private const NONCE_NAME   = 'cw_mc_object_nonce';

	public function init(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_mkd_object', [ $this, 'save' ] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'cw_mc_address',
			esc_html__( 'Address & Identification', 'cw-management-company' ),
			[ $this, 'render_address' ],
			'mkd_object',
			'normal',
			'high'
		);
		add_meta_box(
			'cw_mc_technical',
			esc_html__( 'Technical Characteristics', 'cw-management-company' ),
			[ $this, 'render_technical' ],
			'mkd_object',
			'normal',
			'high'
		);
		add_meta_box(
			'cw_mc_location',
			esc_html__( 'Location', 'cw-management-company' ),
			[ $this, 'render_location' ],
			'mkd_object',
			'normal',
			'high'
		);
		add_meta_box(
			'cw_mc_management',
			esc_html__( 'Management', 'cw-management-company' ),
			[ $this, 'render_management' ],
			'mkd_object',
			'normal',
			'default'
		);
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Field definitions
	// ─────────────────────────────────────────────────────────────────────────

	private function address_fields(): array {
		return [
			'_mkd_address'          => [ 'label' => __( 'Address', 'cw-management-company' ), 'type' => 'text' ],
			'_mkd_cadastral_number' => [ 'label' => __( 'Cadastral Number', 'cw-management-company' ), 'type' => 'text' ],
			'_mkd_gis_code'         => [ 'label' => __( 'GIS ZHKH Code', 'cw-management-company' ), 'type' => 'text' ],
		];
	}

	private function technical_fields(): array {
		$wall_materials = [
			'panel'    => __( 'Panel', 'cw-management-company' ),
			'brick'    => __( 'Brick', 'cw-management-company' ),
			'monolith' => __( 'Monolith', 'cw-management-company' ),
			'block'    => __( 'Block', 'cw-management-company' ),
			'wood'     => __( 'Wood', 'cw-management-company' ),
			'other'    => __( 'Other', 'cw-management-company' ),
		];
		$supply_options = [
			'central'    => __( 'Central', 'cw-management-company' ),
			'autonomous' => __( 'Autonomous', 'cw-management-company' ),
			'none'       => __( 'None', 'cw-management-company' ),
		];
		$gas_options = [
			'central' => __( 'Central', 'cw-management-company' ),
			'bottled' => __( 'Bottled', 'cw-management-company' ),
			'none'    => __( 'None', 'cw-management-company' ),
		];
		$ventilation_options = [
			'natural'        => __( 'Natural', 'cw-management-company' ),
			'supply_exhaust' => __( 'Supply & Exhaust', 'cw-management-company' ),
			'none'           => __( 'None', 'cw-management-company' ),
		];

		return [
			'_mkd_year_built'      => [ 'label' => __( 'Year Built', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_wall_material'   => [ 'label' => __( 'Wall Material', 'cw-management-company' ), 'type' => 'select', 'options' => $wall_materials ],
			'_mkd_series'          => [ 'label' => __( 'Building Series', 'cw-management-company' ), 'type' => 'text' ],
			'_mkd_floors'          => [ 'label' => __( 'Floors', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_entrances'       => [ 'label' => __( 'Entrances', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_dwellings_count' => [ 'label' => __( 'Dwelling Units', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_nonliving_count' => [ 'label' => __( 'Non-Residential Units', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_total_area'      => [ 'label' => __( 'Total Area, m²', 'cw-management-company' ), 'type' => 'decimal' ],
			'_mkd_living_area'     => [ 'label' => __( 'Living Area, m²', 'cw-management-company' ), 'type' => 'decimal' ],
			'_mkd_common_area'     => [ 'label' => __( 'Common Property Area, m²', 'cw-management-company' ), 'type' => 'decimal' ],
			'_mkd_land_area'       => [ 'label' => __( 'Land Plot Area, m²', 'cw-management-company' ), 'type' => 'decimal' ],
			'_mkd_elevators_count' => [ 'label' => __( 'Elevators', 'cw-management-company' ), 'type' => 'number' ],
			'_mkd_garbage_chute'   => [ 'label' => __( 'Garbage Chute', 'cw-management-company' ), 'type' => 'checkbox' ],
			'_mkd_heating_type'    => [ 'label' => __( 'Heating', 'cw-management-company' ), 'type' => 'select', 'options' => $supply_options ],
			'_mkd_hot_water'       => [ 'label' => __( 'Hot Water Supply', 'cw-management-company' ), 'type' => 'select', 'options' => $supply_options ],
			'_mkd_cold_water'      => [ 'label' => __( 'Cold Water Supply', 'cw-management-company' ), 'type' => 'select', 'options' => $supply_options ],
			'_mkd_sewage'          => [ 'label' => __( 'Sewage', 'cw-management-company' ), 'type' => 'select', 'options' => $supply_options ],
			'_mkd_electricity'     => [ 'label' => __( 'Electricity Supply', 'cw-management-company' ), 'type' => 'select', 'options' => $supply_options ],
			'_mkd_gas'             => [ 'label' => __( 'Gas Supply', 'cw-management-company' ), 'type' => 'select', 'options' => $gas_options ],
			'_mkd_ventilation'     => [ 'label' => __( 'Ventilation', 'cw-management-company' ), 'type' => 'select', 'options' => $ventilation_options ],
		];
	}

	private function management_fields(): array {
		return [
			'_mkd_contract_number'    => [ 'label' => __( 'Management Contract Number', 'cw-management-company' ), 'type' => 'text' ],
			'_mkd_contract_date'      => [ 'label' => __( 'Contract Date', 'cw-management-company' ), 'type' => 'date' ],
			'_mkd_contract_term'      => [ 'label' => __( 'Contract Term', 'cw-management-company' ), 'type' => 'text' ],
			'_mkd_management_method'  => [
				'label'   => __( 'Management Method', 'cw-management-company' ),
				'type'    => 'select',
				'options' => [
					'management_company' => __( 'Management Company', 'cw-management-company' ),
					'hoa'                 => __( 'Homeowners Association (HOA)', 'cw-management-company' ),
					'direct'              => __( 'Direct Management by Owners', 'cw-management-company' ),
				],
			],
			'_mkd_responsible_person' => [ 'label' => __( 'Building Representative (Contact)', 'cw-management-company' ), 'type' => 'text' ],
		];
	}

	private function all_fields(): array {
		return $this->address_fields() + $this->technical_fields() + $this->management_fields();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Public accessors — reused by frontend templates so field labels/options
	// stay in sync with the admin metaboxes (single source of truth).
	// ─────────────────────────────────────────────────────────────────────────

	public static function field_groups(): array {
		$instance = new self();
		return [
			esc_html__( 'Address & Identification', 'cw-management-company' )  => $instance->address_fields(),
			esc_html__( 'Technical Characteristics', 'cw-management-company' ) => $instance->technical_fields(),
			esc_html__( 'Management', 'cw-management-company' )                => $instance->management_fields(),
		];
	}

	public static function format_value( array $field, string $value ): string {
		if ( '' === $value ) {
			return '';
		}
		switch ( $field['type'] ) {
			case 'select':
				return $field['options'][ $value ] ?? $value;
			case 'checkbox':
				return '1' === $value ? esc_html__( 'Yes', 'cw-management-company' ) : esc_html__( 'No', 'cw-management-company' );
			default:
				return $value;
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Render
	// ─────────────────────────────────────────────────────────────────────────

	public function render_address( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION . $post->ID, self::NONCE_NAME );
		$this->render_fields( $post->ID, $this->address_fields() );
	}

	public function render_technical( \WP_Post $post ): void {
		$this->render_fields( $post->ID, $this->technical_fields() );
	}

	public function render_management( \WP_Post $post ): void {
		$this->render_fields( $post->ID, $this->management_fields() );
	}

	public function render_location( \WP_Post $post ): void {
		global $opt_name;
		if ( empty( $opt_name ) ) {
			$opt_name = 'redux_demo';
		}
		$yandex_api_key = class_exists( 'Redux' ) ? Redux::get_option( $opt_name, 'yandexapi' ) : '';

		$latitude  = get_post_meta( $post->ID, '_mkd_latitude', true );
		$longitude = get_post_meta( $post->ID, '_mkd_longitude', true );
		$zoom      = get_post_meta( $post->ID, '_mkd_zoom', true );
		$address   = get_post_meta( $post->ID, '_mkd_yandex_address', true );

		if ( empty( $latitude ) ) { $latitude = '55.7558'; }
		if ( empty( $longitude ) ) { $longitude = '37.6173'; }
		if ( empty( $zoom ) ) { $zoom = '15'; }
		?>
		<p class="description" style="margin-bottom:10px;">
			<?php esc_html_e( 'Click on the map to set the property location, or use the search field.', 'cw-management-company' ); ?>
		</p>

		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<div style="position:relative;margin-bottom:12px;">
			<input type="text" id="cw-mc-map-search" placeholder="<?php esc_attr_e( 'Search address...', 'cw-management-company' ); ?>"
				style="width:100%;padding:8px;border:1px solid #8c8f94;border-radius:4px;box-sizing:border-box;">
		</div>
		<?php endif; ?>
		<div id="cw-mc-yandex-map" style="width:100%;height:400px;margin-bottom:15px;"></div>

		<?php if ( ! empty( $yandex_api_key ) ) : ?>
		<script src="https://api-maps.yandex.ru/v3/?apikey=<?php echo esc_attr( $yandex_api_key ); ?>&lang=ru_RU"></script>
		<script>
		(function() {
			var apiKey = '<?php echo esc_js( $yandex_api_key ); ?>';
			var geocodeUrl = 'https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&lang=ru_RU';
			ymaps3.ready.then(function() {
				var YMap = ymaps3.YMap, YMapDefaultSchemeLayer = ymaps3.YMapDefaultSchemeLayer,
				    YMapDefaultFeaturesLayer = ymaps3.YMapDefaultFeaturesLayer,
				    YMapMarker = ymaps3.YMapMarker, YMapListener = ymaps3.YMapListener;

				var latField     = document.getElementById('_mkd_latitude');
				var lngField     = document.getElementById('_mkd_longitude');
				var zoomField    = document.getElementById('_mkd_zoom');
				var addressField = document.getElementById('_mkd_yandex_address');
				var searchInput  = document.getElementById('cw-mc-map-search');

				var lat  = parseFloat(latField  && latField.value  ? latField.value  : '55.7558') || 55.7558;
				var lng  = parseFloat(lngField  && lngField.value  ? lngField.value  : '37.6173') || 37.6173;
				var zoom = parseInt(zoomField && zoomField.value ? zoomField.value : '15') || 15;

				var map = new YMap(document.getElementById('cw-mc-yandex-map'), {
					location: { center: [lng, lat], zoom: zoom }
				});
				map.addChild(new YMapDefaultSchemeLayer());
				map.addChild(new YMapDefaultFeaturesLayer());

				var el = document.createElement('div');
				el.style.cssText = 'cursor:grab;width:28px;height:28px;transform:translate(-50%,-100%)';
				el.innerHTML = '<svg viewBox="0 0 24 24" fill="#d63638" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>';

				var marker = new YMapMarker({
					coordinates: [lng, lat],
					draggable: true,
					onDragEnd: function(coords) { syncFields(coords[1], coords[0]); }
				}, el);
				map.addChild(marker);

				map.addChild(new YMapListener({
					onClick: function(obj, event) {
						var coords = event && event.coordinates ? event.coordinates : null;
						if (!coords) return;
						marker.update({ coordinates: coords });
						syncFields(coords[1], coords[0]);
					}
				}));

				map.addChild(new YMapListener({
					onActionEnd: function() {
						if (zoomField) { zoomField.value = Math.round(map.zoom); }
					}
				}));

				function syncFields(latVal, lngVal) {
					if (latField)  { latField.value  = latVal.toFixed(6); }
					if (lngField)  { lngField.value  = lngVal.toFixed(6); }
					if (zoomField) { zoomField.value = Math.round(map.zoom); }
					if (addressField) {
						fetch('https://geocode-maps.yandex.ru/1.x/?apikey=' + encodeURIComponent(apiKey) + '&format=json&geocode=' + lngVal + ',' + latVal + '&results=1&lang=ru_RU')
							.then(function(r) { return r.json(); })
							.then(function(d) {
								var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
								if (fm && fm.length) { addressField.value = fm[0].GeoObject.metaDataProperty.GeocoderMetaData.text; }
							});
					}
				}

				function geocodeAndMove(query) {
					if (!query) return;
					fetch(geocodeUrl + '&geocode=' + encodeURIComponent(query) + '&results=1')
						.then(function(r) { return r.json(); })
						.then(function(d) {
							var fm = d.response && d.response.GeoObjectCollection && d.response.GeoObjectCollection.featureMember;
							if (!fm || !fm.length) return;
							var pos = fm[0].GeoObject.Point.pos.split(' ');
							var fLng = parseFloat(pos[0]), fLat = parseFloat(pos[1]);
							if (isNaN(fLat) || isNaN(fLng)) return;
							marker.update({ coordinates: [fLng, fLat] });
							map.update({ location: { center: [fLng, fLat], zoom: 15 } });
							syncFields(fLat, fLng);
						}).catch(function() {});
				}

				function initSuggest(input) {
					var wrap = input.parentNode;
					var drop = document.createElement('div');
					drop.style.cssText = 'display:none;position:absolute;z-index:99999;left:0;right:0;top:100%;background:#fff;border:1px solid #c3c4c7;border-top:none;border-radius:0 0 4px 4px;box-shadow:0 4px 8px rgba(0,0,0,.12);max-height:220px;overflow-y:auto;font-size:13px;';
					wrap.appendChild(drop);
					var timer, active = -1;
					function hide() { drop.style.display = 'none'; active = -1; }
					function hl(i) { active = i; Array.from(drop.children).forEach(function(c,j){c.style.background=j===i?'#f0f7ff':'';}); }
					function pick(t, s) { input.value = t + (s ? ', '+s : ''); hide(); geocodeAndMove(input.value); }
					function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
					input.addEventListener('input', function() {
						clearTimeout(timer);
						var q = input.value.trim();
						if (q.length < 2) { hide(); return; }
						timer = setTimeout(function() {
							ymaps3.suggest({ text: q, lang: 'ru_RU', results: 5 })
								.then(function(items) {
									drop.innerHTML = '';
									items = (items || []).filter(function(r) { return r.title && r.title.text; });
									if (!items.length) { hide(); return; }
									items.forEach(function(r, i) {
										var t = r.title.text, s = r.subtitle && r.subtitle.text ? r.subtitle.text : '';
										var div = document.createElement('div');
										div.style.cssText = 'padding:7px 12px;cursor:pointer;border-bottom:1px solid #f0f0f1;line-height:1.3;';
										div.innerHTML = '<span style="font-weight:600">'+esc(t)+'</span>'+(s?'<br><span style="color:#777;font-size:12px">'+esc(s)+'</span>':'');
										div.addEventListener('mousedown', function(e) { e.preventDefault(); pick(t, s); });
										div.addEventListener('mouseover', function() { hl(i); });
										drop.appendChild(div);
									});
									drop.style.display = 'block';
								}).catch(function() {});
						}, 250);
					});
					input.addEventListener('keydown', function(e) {
						if (e.key === 'ArrowDown') { e.preventDefault(); hl(Math.min(active+1, drop.children.length-1)); }
						else if (e.key === 'ArrowUp') { e.preventDefault(); hl(Math.max(active-1, 0)); }
						else if (e.key === 'Enter') {
							e.preventDefault();
							if (active >= 0 && drop.children[active]) drop.children[active].dispatchEvent(new MouseEvent('mousedown',{bubbles:true}));
							else geocodeAndMove(input.value.trim());
							hide();
						} else if (e.key === 'Escape') { hide(); }
					});
					input.addEventListener('blur', function() { setTimeout(hide, 200); });
				}

				if (searchInput) initSuggest(searchInput);
			});
		})();
		</script>
		<?php else : ?>
		<p style="color:#d32f2f;background:#ffebee;padding:10px;border-radius:4px;">
			<?php esc_html_e( 'Yandex API key is not set. Please configure it in Theme Options > API > Yandex API Key.', 'cw-management-company' ); ?>
		</p>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
			<div>
				<label for="_mkd_latitude" style="display:block;margin-bottom:5px;font-weight:bold;">
					<?php esc_html_e( 'Latitude', 'cw-management-company' ); ?>
				</label>
				<input type="number" step="any" id="_mkd_latitude" name="_mkd_latitude"
					value="<?php echo esc_attr( $latitude ); ?>" style="width:100%;padding:8px;" placeholder="55.7558">
			</div>
			<div>
				<label for="_mkd_longitude" style="display:block;margin-bottom:5px;font-weight:bold;">
					<?php esc_html_e( 'Longitude', 'cw-management-company' ); ?>
				</label>
				<input type="number" step="any" id="_mkd_longitude" name="_mkd_longitude"
					value="<?php echo esc_attr( $longitude ); ?>" style="width:100%;padding:8px;" placeholder="37.6173">
			</div>
			<div>
				<label for="_mkd_zoom" style="display:block;margin-bottom:5px;font-weight:bold;">
					<?php esc_html_e( 'Zoom', 'cw-management-company' ); ?>
				</label>
				<input type="number" step="1" min="1" max="19" id="_mkd_zoom" name="_mkd_zoom"
					value="<?php echo esc_attr( $zoom ); ?>" style="width:100%;padding:8px;" placeholder="15">
			</div>
			<div>
				<label for="_mkd_yandex_address" style="display:block;margin-bottom:5px;font-weight:bold;">
					<?php esc_html_e( 'Address (from map)', 'cw-management-company' ); ?>
				</label>
				<input type="text" id="_mkd_yandex_address" name="_mkd_yandex_address"
					value="<?php echo esc_attr( $address ); ?>" style="width:100%;padding:8px;" readonly>
			</div>
		</div>
		<?php
	}

	private function render_fields( int $post_id, array $fields ): void {
		echo '<table class="form-table" role="presentation"><tbody>';

		foreach ( $fields as $key => $field ) {
			$value = get_post_meta( $post_id, $key, true );
			$id    = 'cw_mc_' . ltrim( $key, '_' );

			echo '<tr><th><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

			switch ( $field['type'] ) {
				case 'select':
					echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
					echo '<option value="">' . esc_html__( '— not specified —', 'cw-management-company' ) . '</option>';
					foreach ( $field['options'] as $opt_value => $opt_label ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $opt_value ),
							selected( $value, $opt_value, false ),
							esc_html( $opt_label )
						);
					}
					echo '</select>';
					break;

				case 'checkbox':
					printf(
						'<input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s>',
						esc_attr( $id ),
						esc_attr( $key ),
						checked( $value, '1', false )
					);
					break;

				case 'number':
					printf(
						'<input type="number" step="1" id="%1$s" name="%2$s" value="%3$s" class="small-text">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				case 'decimal':
					printf(
						'<input type="number" step="0.01" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				case 'date':
					printf(
						'<input type="date" id="%1$s" name="%2$s" value="%3$s">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
					break;

				default:
					printf(
						'<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">',
						esc_attr( $id ),
						esc_attr( $key ),
						esc_attr( $value )
					);
			}

			echo '</td></tr>';
		}

		echo '</tbody></table>';
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Save
	// ─────────────────────────────────────────────────────────────────────────

	public function save( int $post_id ): void {
		if ( ! isset( $_POST[ self::NONCE_NAME ] )
			|| ! wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION . $post_id )
		) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $this->all_fields() as $key => $field ) {
			$raw = $_POST[ $key ] ?? '';
			$raw = is_scalar( $raw ) ? (string) $raw : '';

			switch ( $field['type'] ) {
				case 'select':
					$value = array_key_exists( $raw, $field['options'] ) ? $raw : '';
					break;

				case 'checkbox':
					$value = ( '' !== $raw ) ? '1' : '';
					break;

				case 'number':
					$value = ( '' === $raw ) ? '' : (string) absint( $raw );
					break;

				case 'decimal':
					$value = ( '' === $raw ) ? '' : (string) round( (float) $raw, 2 );
					break;

				case 'date':
					$value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
					break;

				default:
					$value = sanitize_text_field( wp_unslash( $raw ) );
			}

			if ( '' !== $value ) {
				update_post_meta( $post_id, $key, $value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}

		$location_fields = [ '_mkd_latitude', '_mkd_longitude', '_mkd_zoom', '_mkd_yandex_address' ];
		foreach ( $location_fields as $key ) {
			$raw = $_POST[ $key ] ?? '';
			$raw = is_scalar( $raw ) ? sanitize_text_field( wp_unslash( (string) $raw ) ) : '';
			if ( '' !== $raw ) {
				update_post_meta( $post_id, $key, $raw );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}
}
