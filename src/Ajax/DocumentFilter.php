<?php

namespace CW\ManagementCompany\Ajax;

use CW\ManagementCompany\Documents;

class DocumentFilter {

	public function init(): void {
		add_action( 'wp_ajax_cw_mc_filter_documents', [ $this, 'handle' ] );
		add_action( 'wp_ajax_nopriv_cw_mc_filter_documents', [ $this, 'handle' ] );
	}

	public function handle(): void {
		check_ajax_referer( 'cw_mc_filter_documents', 'nonce' );

		$object_id = absint( $_POST['object_id'] ?? 0 );
		if ( ! $object_id || get_post_type( $object_id ) !== 'mkd_object' ) {
			wp_send_json_error( [ 'message' => __( 'Invalid property.', 'cw-management-company' ) ], 400 );
		}

		$type = sanitize_key( $_POST['type'] ?? '' );
		$year = ( isset( $_POST['year'] ) && preg_match( '/^\d{4}$/', $_POST['year'] ) ) ? $_POST['year'] : '';

		$docs   = Documents::query( $object_id, [ 'type' => $type, 'year' => $year ] );
		$groups = Documents::group_by_type( $docs );
		$html   = Documents::render_list( $groups );

		wp_send_json_success( [
			'html'  => $html,
			'count' => count( $docs ),
		] );
	}
}
