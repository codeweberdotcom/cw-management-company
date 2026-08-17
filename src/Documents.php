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
	 * Flat list of document rows: format badge, title, date.
	 *
	 * @param \WP_Post[] $docs
	 */
	public static function render_list( array $docs ): string {
		ob_start();

		if ( ! $docs ) {
			echo '<p class="text-muted mb-0">' . esc_html__( 'No documents found.', 'cw-management-company' ) . '</p>';
			return ob_get_clean();
		}
		?>
		<div class="d-flex flex-column gap-2">
			<?php foreach ( $docs as $doc ) :
				$file_id  = (int) get_post_meta( $doc->ID, '_mkd_document_file', true );
				$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
				$ext      = $file_url ? strtoupper( pathinfo( (string) wp_parse_url( $file_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';
				$date     = get_post_meta( $doc->ID, '_mkd_document_date', true );
				$tag      = $file_url ? 'a' : 'div';
				?>
				<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="card text-body text-decoration-none"<?php
					if ( $file_url ) {
						printf( ' href="%s" download', esc_url( $file_url ) );
					}
				?>>
					<div class="card-body py-3 px-4 d-flex align-items-center gap-3">
						<?php if ( $ext ) : ?>
						<span class="badge bg-pale-primary text-primary"><?php echo esc_html( $ext ); ?></span>
						<?php endif; ?>
						<span class="flex-fill fw-bold"><?php echo esc_html( get_the_title( $doc ) ); ?></span>
						<?php if ( $date ) : ?>
						<span class="fs-14 text-muted text-nowrap"><?php echo esc_html( mysql2date( 'd.m.Y', $date ) ); ?></span>
						<?php endif; ?>
					</div>
				</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php endforeach; ?>
		</div>
		<?php

		return ob_get_clean();
	}
}
