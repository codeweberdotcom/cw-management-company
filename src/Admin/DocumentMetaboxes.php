<?php

namespace CW\ManagementCompany\Admin;

class DocumentMetaboxes {

	private const NONCE_ACTION = 'cw_mc_document_save_';
	private const NONCE_NAME   = 'cw_mc_document_nonce';

	public function init(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post_mkd_document', [ $this, 'save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'cw_mc_document_details',
			esc_html__( 'Document Details', 'cw-management-company' ),
			[ $this, 'render' ],
			'mkd_document',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION . $post->ID, self::NONCE_NAME );

		$object_id = (int) get_post_meta( $post->ID, '_mkd_document_object', true );
		$file_id   = (int) get_post_meta( $post->ID, '_mkd_document_file', true );
		$date      = get_post_meta( $post->ID, '_mkd_document_date', true );
		$cost      = get_post_meta( $post->ID, '_mkd_document_cost', true );

		$file_name = $file_id ? basename( get_attached_file( $file_id ) ?: '' ) : '';

		$properties = get_posts( [
			'post_type'      => 'mkd_object',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		?>
		<table class="form-table" role="presentation">

			<tr>
				<th><label for="cw_mc_document_object"><?php esc_html_e( 'Property', 'cw-management-company' ); ?></label></th>
				<td>
					<select id="cw_mc_document_object" name="_mkd_document_object">
						<option value=""><?php esc_html_e( '— select property —', 'cw-management-company' ); ?></option>
						<?php foreach ( $properties as $property ) : ?>
							<option value="<?php echo esc_attr( $property->ID ); ?>" <?php selected( $object_id, $property->ID ); ?>>
								<?php echo esc_html( get_the_title( $property ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<tr>
				<th><label><?php esc_html_e( 'File', 'cw-management-company' ); ?></label></th>
				<td>
					<input type="hidden" id="cw_mc_document_file" name="_mkd_document_file" value="<?php echo esc_attr( $file_id ?: '' ); ?>">
					<div id="cw_mc_document_file_preview" style="margin-bottom:8px;">
						<?php if ( $file_name ) : ?>
							<span><?php echo esc_html( $file_name ); ?></span>
						<?php endif; ?>
					</div>
					<button type="button" id="cw_mc_document_file_select" class="button">
						<?php esc_html_e( 'Select file', 'cw-management-company' ); ?>
					</button>
					<?php if ( $file_id ) : ?>
					<button type="button" id="cw_mc_document_file_remove" class="button" style="margin-left:4px;">
						<?php esc_html_e( 'Remove', 'cw-management-company' ); ?>
					</button>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th><label for="cw_mc_document_date"><?php esc_html_e( 'Date / Period', 'cw-management-company' ); ?></label></th>
				<td>
					<input type="date" id="cw_mc_document_date" name="_mkd_document_date" value="<?php echo esc_attr( $date ); ?>">
				</td>
			</tr>

			<tr>
				<th><label for="cw_mc_document_cost"><?php esc_html_e( 'Cost', 'cw-management-company' ); ?></label></th>
				<td>
					<input type="number" step="0.01" id="cw_mc_document_cost" name="_mkd_document_cost"
						value="<?php echo esc_attr( $cost ); ?>" class="regular-text">
				</td>
			</tr>

		</table>
		<?php
	}

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

		$object_id = absint( $_POST['_mkd_document_object'] ?? 0 );
		if ( $object_id && get_post_type( $object_id ) === 'mkd_object' ) {
			update_post_meta( $post_id, '_mkd_document_object', $object_id );
		} else {
			delete_post_meta( $post_id, '_mkd_document_object' );
		}

		$file_id = absint( $_POST['_mkd_document_file'] ?? 0 );
		if ( $file_id && get_post_type( $file_id ) === 'attachment' ) {
			update_post_meta( $post_id, '_mkd_document_file', $file_id );
		} else {
			delete_post_meta( $post_id, '_mkd_document_file' );
		}

		$date = $_POST['_mkd_document_date'] ?? '';
		$date = is_scalar( $date ) ? sanitize_text_field( wp_unslash( (string) $date ) ) : '';
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			update_post_meta( $post_id, '_mkd_document_date', $date );
		} else {
			delete_post_meta( $post_id, '_mkd_document_date' );
		}

		$cost = $_POST['_mkd_document_cost'] ?? '';
		$cost = is_scalar( $cost ) ? (string) $cost : '';
		if ( '' !== $cost ) {
			update_post_meta( $post_id, '_mkd_document_cost', (string) round( (float) $cost, 2 ) );
		} else {
			delete_post_meta( $post_id, '_mkd_document_cost' );
		}
	}

	public function enqueue_scripts( string $hook ): void {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'mkd_document' ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'cw-mc-admin',
			CW_MC_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			CW_MC_VERSION,
			true
		);
	}
}
