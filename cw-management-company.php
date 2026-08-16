<?php
/**
 * Plugin Name: CW Management Company
 * Description: Multi-apartment building objects, documents (work acts, annual reports, meeting minutes) and events for a management company website.
 * Version:     0.1.0
 * Author:      CodeWeber
 * Text Domain: cw-management-company
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'CW_MC_VERSION', '0.1.0' );
define( 'CW_MC_FILE', __FILE__ );
define( 'CW_MC_DIR', plugin_dir_path( __FILE__ ) );
define( 'CW_MC_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( function ( $class ) {
	$prefix   = 'CW\\ManagementCompany\\';
	$base_dir = CW_MC_DIR . 'src/';

	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, strlen( $prefix ) );
	$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Plugin bootstrap (CPTs, taxonomies, admin) will be wired up in a later step.
