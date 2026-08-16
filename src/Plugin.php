<?php

namespace CW\ManagementCompany;

class Plugin {

	public function init(): void {
		add_action( 'init', [ $this, 'register_cpt' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'init', [ $this, 'register_cpt_document' ] );
		add_action( 'init', [ $this, 'register_taxonomy_document_type' ] );
		add_action( 'init', [ $this, 'register_taxonomy_work_category' ] );

		add_filter( 'template_include', [ $this, 'template_include' ] );
		add_action( 'after_setup_theme', [ $this, 'register_card_templates' ] );
		add_filter( 'codeweber_post_type_template_map', [ $this, 'register_card_template_map' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_document_filter' ] );

		( new Admin\Metaboxes() )->init();
		( new Admin\DocumentMetaboxes() )->init();
		( new Admin\EventMetabox() )->init();
		( new Ajax\DocumentFilter() )->init();
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Document filter (AJAX)
	// ─────────────────────────────────────────────────────────────────────────

	public function enqueue_document_filter(): void {
		if ( ! is_singular( 'mkd_object' ) ) {
			return;
		}

		wp_enqueue_script(
			'cw-mc-document-filter',
			CW_MC_URL . 'assets/js/document-filter.js',
			[],
			CW_MC_VERSION,
			true
		);

		wp_localize_script( 'cw-mc-document-filter', 'cwMcDocFilter', [
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cw_mc_filter_documents' ),
			'objectId' => get_the_ID(),
		] );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Templates
	// ─────────────────────────────────────────────────────────────────────────

	public function template_include( string $template ): string {
		if ( is_post_type_archive( 'mkd_object' ) || is_tax( 'mkd_object_status' ) ) {
			$theme = locate_template( 'archive-mkd_object.php' );
			return $theme ?: CW_MC_DIR . 'templates/archive-mkd_object.php';
		}
		if ( is_singular( 'mkd_object' ) ) {
			$theme = locate_template( 'single-mkd_object.php' );
			return $theme ?: CW_MC_DIR . 'templates/single-mkd_object.php';
		}
		return $template;
	}

	/**
	 * Отдать теме папку с card-шаблонами плагина (Post Grid dropdown).
	 */
	public function register_card_templates(): void {
		if ( ! function_exists( 'cw_register_post_card_templates_dir' ) ) {
			return;
		}

		cw_register_post_card_templates_dir( CW_MC_DIR . 'templates/post-cards/', [
			'text_domain' => 'cw-management-company',
			'label'       => 'cw-management-company',
		] );
	}

	/**
	 * Без этого cw_render_post_card() резолвит mkd_object в папку 'post'
	 * (нет записи в базовой карте темы) и находит там чужой card.php раньше,
	 * чем сканер успевает подставить наш зарегистрированный шаблон.
	 */
	public function register_card_template_map( array $map ): array {
		$map['mkd_object'] = 'mkd_object';
		return $map;
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Yandex Maps marker builder — shared by archive (all properties) and
	// single (one property) templates.
	// ─────────────────────────────────────────────────────────────────────────

	public static function build_map_marker( int $object_id ): ?array {
		$lat = get_post_meta( $object_id, '_mkd_latitude', true );
		$lng = get_post_meta( $object_id, '_mkd_longitude', true );
		if ( '' === $lat || '' === $lng ) {
			return null;
		}

		$title   = get_the_title( $object_id );
		$address = get_post_meta( $object_id, '_mkd_address', true );
		$link    = get_permalink( $object_id );

		$status_terms = get_the_terms( $object_id, 'mkd_object_status' );
		$status_name  = ( $status_terms && ! is_wp_error( $status_terms ) ) ? $status_terms[0]->name : '';

		$balloon = '<div class="cw-mc-map-balloon">';
		$balloon .= '<strong>' . esc_html( $title ) . '</strong><br>';
		if ( $address ) {
			$balloon .= '<span>' . esc_html( $address ) . '</span><br>';
		}
		if ( $status_name ) {
			$balloon .= '<span class="text-muted">' . esc_html( $status_name ) . '</span><br>';
		}
		$balloon .= '<a href="' . esc_url( $link ) . '">' . esc_html__( 'View property', 'cw-management-company' ) . '</a>';
		$balloon .= '</div>';

		return [
			'id'                   => $object_id,
			'latitude'             => $lat,
			'longitude'            => $lng,
			'title'                => $title,
			'balloonContentHeader' => esc_html( $title ),
			'balloonContent'       => $balloon,
			'hintContent'          => $address ?: $title,
			'link'                 => $link,
			'address'              => $address,
		];
	}

	// ─────────────────────────────────────────────────────────────────────────
	// CPT
	// ─────────────────────────────────────────────────────────────────────────

	public function register_cpt(): void {
		$labels = [
			'name'               => esc_html__( 'Properties', 'cw-management-company' ),
			'singular_name'      => esc_html__( 'Property', 'cw-management-company' ),
			'menu_name'          => esc_html__( 'Properties', 'cw-management-company' ),
			'all_items'          => esc_html__( 'All Properties', 'cw-management-company' ),
			'add_new'            => esc_html__( 'Add New', 'cw-management-company' ),
			'add_new_item'       => esc_html__( 'Add New Property', 'cw-management-company' ),
			'edit_item'          => esc_html__( 'Edit Property', 'cw-management-company' ),
			'new_item'           => esc_html__( 'New Property', 'cw-management-company' ),
			'view_item'          => esc_html__( 'View Property', 'cw-management-company' ),
			'search_items'       => esc_html__( 'Search Properties', 'cw-management-company' ),
			'not_found'          => esc_html__( 'No properties found', 'cw-management-company' ),
			'not_found_in_trash' => esc_html__( 'No properties found in trash', 'cw-management-company' ),
		];

		register_post_type( 'mkd_object', [
			'label'               => esc_html__( 'Properties', 'cw-management-company' ),
			'labels'              => $labels,
			'description'         => 'Multi-apartment buildings managed by the company',
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'has_archive'         => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'delete_with_user'    => false,
			'exclude_from_search' => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'can_export'          => true,
			'rewrite'             => [ 'slug' => 'objects', 'with_front' => true ],
			'query_var'           => true,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions' ],
			'menu_icon'           => 'dashicons-building',
			'menu_position'       => 6,
			'show_in_graphql'     => false,
		] );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Taxonomies
	// ─────────────────────────────────────────────────────────────────────────

	public function register_taxonomies(): void {
		register_taxonomy( 'mkd_object_status', [ 'mkd_object' ], [
			'label'              => esc_html__( 'Status', 'cw-management-company' ),
			'labels'             => [
				'name'          => esc_html__( 'Statuses', 'cw-management-company' ),
				'singular_name' => esc_html__( 'Status', 'cw-management-company' ),
				'all_items'     => esc_html__( 'All Statuses', 'cw-management-company' ),
				'add_new_item'  => esc_html__( 'Add New Status', 'cw-management-company' ),
				'edit_item'     => esc_html__( 'Edit Status', 'cw-management-company' ),
				'new_item'      => esc_html__( 'New Status', 'cw-management-company' ),
				'search_items'  => esc_html__( 'Search Statuses', 'cw-management-company' ),
				'not_found'     => esc_html__( 'No statuses found', 'cw-management-company' ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => [ 'slug' => 'object-status', 'with_front' => true ],
		] );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// CPT: Documents (work acts, annual reports, meeting minutes)
	// ─────────────────────────────────────────────────────────────────────────

	public function register_cpt_document(): void {
		$labels = [
			'name'               => esc_html__( 'Documents', 'cw-management-company' ),
			'singular_name'      => esc_html__( 'Document', 'cw-management-company' ),
			'menu_name'          => esc_html__( 'Documents', 'cw-management-company' ),
			'all_items'          => esc_html__( 'All Documents', 'cw-management-company' ),
			'add_new'            => esc_html__( 'Add New', 'cw-management-company' ),
			'add_new_item'       => esc_html__( 'Add New Document', 'cw-management-company' ),
			'edit_item'          => esc_html__( 'Edit Document', 'cw-management-company' ),
			'new_item'           => esc_html__( 'New Document', 'cw-management-company' ),
			'search_items'       => esc_html__( 'Search Documents', 'cw-management-company' ),
			'not_found'          => esc_html__( 'No documents found', 'cw-management-company' ),
			'not_found_in_trash' => esc_html__( 'No documents found in trash', 'cw-management-company' ),
		];

		register_post_type( 'mkd_document', [
			'label'               => esc_html__( 'Documents', 'cw-management-company' ),
			'labels'              => $labels,
			'description'         => 'Work acts, annual reports and meeting minutes attached to a property',
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'delete_with_user'    => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'can_export'          => true,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => [ 'title', 'revisions' ],
			'menu_icon'           => 'dashicons-media-document',
			'menu_position'       => 7,
			'show_in_graphql'     => false,
		] );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Taxonomies: Documents
	// ─────────────────────────────────────────────────────────────────────────

	public function register_taxonomy_document_type(): void {
		register_taxonomy( 'mkd_document_type', [ 'mkd_document' ], [
			'label'              => esc_html__( 'Document Type', 'cw-management-company' ),
			'labels'             => [
				'name'          => esc_html__( 'Document Types', 'cw-management-company' ),
				'singular_name' => esc_html__( 'Document Type', 'cw-management-company' ),
				'all_items'     => esc_html__( 'All Types', 'cw-management-company' ),
				'add_new_item'  => esc_html__( 'Add New Type', 'cw-management-company' ),
				'edit_item'     => esc_html__( 'Edit Type', 'cw-management-company' ),
				'new_item'      => esc_html__( 'New Type', 'cw-management-company' ),
				'search_items'  => esc_html__( 'Search Types', 'cw-management-company' ),
				'not_found'     => esc_html__( 'No types found', 'cw-management-company' ),
			],
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'show_in_quick_edit' => true,
			'hierarchical'       => false,
			'rewrite'            => false,
			'query_var'          => false,
		] );
	}

	public function register_taxonomy_work_category(): void {
		register_taxonomy( 'mkd_work_category', [ 'mkd_document' ], [
			'label'              => esc_html__( 'Work Category', 'cw-management-company' ),
			'labels'             => [
				'name'          => esc_html__( 'Work Categories', 'cw-management-company' ),
				'singular_name' => esc_html__( 'Work Category', 'cw-management-company' ),
				'all_items'     => esc_html__( 'All Categories', 'cw-management-company' ),
				'add_new_item'  => esc_html__( 'Add New Category', 'cw-management-company' ),
				'edit_item'     => esc_html__( 'Edit Category', 'cw-management-company' ),
				'new_item'      => esc_html__( 'New Category', 'cw-management-company' ),
				'search_items'  => esc_html__( 'Search Categories', 'cw-management-company' ),
				'not_found'     => esc_html__( 'No categories found', 'cw-management-company' ),
			],
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'show_in_quick_edit' => true,
			'hierarchical'       => false,
			'rewrite'            => false,
			'query_var'          => false,
		] );
	}

	// ─────────────────────────────────────────────────────────────────────────
	// Activation: register taxonomies early and seed default terms
	// ─────────────────────────────────────────────────────────────────────────

	public static function activate(): void {
		$plugin = new self();
		$plugin->register_cpt();
		$plugin->register_taxonomies();
		$plugin->register_cpt_document();
		$plugin->register_taxonomy_document_type();
		$plugin->register_taxonomy_work_category();

		$statuses = [
			'in-management' => esc_html__( 'In Management', 'cw-management-company' ),
			'emergency'     => esc_html__( 'Emergency', 'cw-management-company' ),
			'removed'       => esc_html__( 'Removed from Management', 'cw-management-company' ),
		];
		foreach ( $statuses as $slug => $name ) {
			if ( ! term_exists( $slug, 'mkd_object_status' ) ) {
				wp_insert_term( $name, 'mkd_object_status', [ 'slug' => $slug ] );
			}
		}

		$document_types = [
			'work-act'        => esc_html__( 'Work Act', 'cw-management-company' ),
			'annual-report'   => esc_html__( 'Annual Report', 'cw-management-company' ),
			'meeting-minutes' => esc_html__( 'Meeting Minutes', 'cw-management-company' ),
			'other'           => esc_html__( 'Other', 'cw-management-company' ),
		];
		foreach ( $document_types as $slug => $name ) {
			if ( ! term_exists( $slug, 'mkd_document_type' ) ) {
				wp_insert_term( $name, 'mkd_document_type', [ 'slug' => $slug ] );
			}
		}

		$work_categories = [
			'maintenance'     => esc_html__( 'Common Property Maintenance', 'cw-management-company' ),
			'current-repair'  => esc_html__( 'Current Repair', 'cw-management-company' ),
			'capital-repair'  => esc_html__( 'Capital Repair', 'cw-management-company' ),
			'emergency-works' => esc_html__( 'Emergency Works', 'cw-management-company' ),
		];
		foreach ( $work_categories as $slug => $name ) {
			if ( ! term_exists( $slug, 'mkd_work_category' ) ) {
				wp_insert_term( $name, 'mkd_work_category', [ 'slug' => $slug ] );
			}
		}

		flush_rewrite_rules();
	}
}
