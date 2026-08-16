<?php

namespace CW\ManagementCompany;

class Documents {

	/**
	 * @param array $args { type?: string (mkd_document_type slug), year?: string (YYYY) }
	 * @return \WP_Post[]
	 */
	public static function query( int $object_id, array $args = [] ): array {
		$args = wp_parse_args( $args, [ 'type' => '', 'year' => '' ] );

		$meta_query = [
			[ 'key' => '_mkd_document_object', 'value' => $object_id, 'compare' => '=' ],
		];
		if ( $args['year'] && preg_match( '/^\d{4}$/', $args['year'] ) ) {
			$meta_query[] = [ 'key' => '_mkd_document_date', 'value' => $args['year'] . '-', 'compare' => 'LIKE' ];
		}

		$tax_query = [];
		if ( $args['type'] ) {
			$tax_query[] = [ 'taxonomy' => 'mkd_document_type', 'field' => 'slug', 'terms' => sanitize_key( $args['type'] ) ];
		}

		return get_posts( [
			'post_type'      => 'mkd_document',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => '_mkd_document_date',
			'order'          => 'DESC',
			'meta_query'     => $meta_query,
			'tax_query'      => $tax_query,
		] );
	}

	/**
	 * @param \WP_Post[] $docs
	 * @return array<string, array{label:string, items:\WP_Post[]}>
	 */
	public static function group_by_type( array $docs ): array {
		$groups = [];
		foreach ( $docs as $doc ) {
			$terms = get_the_terms( $doc->ID, 'mkd_document_type' );
			$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
			$key   = $term ? $term->slug : 'other';
			$label = $term ? $term->name : esc_html__( 'Other', 'cw-management-company' );

			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = [ 'label' => $label, 'items' => [] ];
			}
			$groups[ $key ]['items'][] = $doc;
		}
		return $groups;
	}

	/**
	 * Distinct years among a property's documents, newest first.
	 *
	 * @return string[]
	 */
	public static function years_for_object( int $object_id ): array {
		$doc_ids = get_posts( [
			'post_type'      => 'mkd_document',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => [
				[ 'key' => '_mkd_document_object', 'value' => $object_id, 'compare' => '=' ],
			],
		] );

		$years = [];
		foreach ( $doc_ids as $doc_id ) {
			$date = get_post_meta( $doc_id, '_mkd_document_date', true );
			if ( preg_match( '/^(\d{4})-/', (string) $date, $m ) ) {
				$years[ $m[1] ] = true;
			}
		}

		$years = array_keys( $years );
		rsort( $years );
		return $years;
	}

	/**
	 * @param array<string, array{label:string, items:\WP_Post[]}> $groups
	 */
	public static function render_list( array $groups ): string {
		ob_start();

		if ( ! $groups ) {
			echo '<p class="text-muted">' . esc_html__( 'No documents found.', 'cw-management-company' ) . '</p>';
		}

		foreach ( $groups as $group ) :
			?>
			<div class="mb-5">
				<h3 class="h6 text-uppercase text-muted mb-3"><?php echo esc_html( $group['label'] ); ?></h3>
				<ul class="list-unstyled mb-0">
					<?php foreach ( $group['items'] as $doc ) :
						$file_id  = (int) get_post_meta( $doc->ID, '_mkd_document_file', true );
						$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
						$date     = get_post_meta( $doc->ID, '_mkd_document_date', true );
						?>
						<li class="d-flex justify-content-between align-items-center border-bottom py-2 gap-3">
							<span>
								<?php echo esc_html( get_the_title( $doc ) ); ?>
								<?php if ( $date ) : ?>
									<span class="text-muted small ms-2">
										<?php echo esc_html( mysql2date( get_option( 'date_format' ), $date ) ); ?>
									</span>
								<?php endif; ?>
							</span>
							<?php if ( $file_url ) : ?>
							<a href="<?php echo esc_url( $file_url ); ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">
								<?php esc_html_e( 'Download', 'cw-management-company' ); ?>
							</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		endforeach;

		return ob_get_clean();
	}
}
