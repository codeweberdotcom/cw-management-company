<?php

namespace CW\ManagementCompany\Admin;

class EventMetabox {

	private const NONCE_ACTION = 'cw_mc_event_object_save_';
	private const NONCE_NAME   = 'cw_mc_event_object_nonce';

	public function init(): void {
		add_action( 'add_meta_boxes_events', [ $this, 'add_meta_box' ] );
		add_action( 'save_post_events', [ $this, 'save' ] );
	}

	public function add_meta_box(): void {
		add_meta_box(
			'cw_mc_event_object',
			esc_html__( 'Property', 'cw-management-company' ),
			[ $this, 'render' ],
			'events',
			'side',
			'default'
		);
	}

	public function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION . $post->ID, self::NONCE_NAME );

		$selected = (int) get_post_meta( $post->ID, '_mkd_event_object', true );

		$properties = get_posts( [
			'post_type'      => 'mkd_object',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );
		?>
		<select name="_mkd_event_object" style="width:100%;">
			<option value=""><?php esc_html_e( '— Not linked —', 'cw-management-company' ); ?></option>
			<?php foreach ( $properties as $property ) : ?>
				<option value="<?php echo esc_attr( $property->ID ); ?>" <?php selected( $selected, $property->ID ); ?>>
					<?php echo esc_html( get_the_title( $property ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Link this event to a property — e.g. an owners meeting for a specific building.', 'cw-management-company' ); ?>
		</p>
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

		$object_id = absint( $_POST['_mkd_event_object'] ?? 0 );
		if ( $object_id && get_post_type( $object_id ) === 'mkd_object' ) {
			update_post_meta( $post_id, '_mkd_event_object', $object_id );
		} else {
			delete_post_meta( $post_id, '_mkd_event_object' );
		}
	}
}
