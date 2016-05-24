<?php
/**
 * Provide Import and Export of the settings of the plugin
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 */

class G_ImpExp {
		
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		$plugin = Glossary::get_instance();
		$this->setting_slug = $plugin->get_setting_slug();
		//Add the export settings method
		add_action( 'admin_init', array( $this, 'settings_export' ) );
		//Add the import settings method
		add_action( 'admin_init', array( $this, 'settings_import' ) );
	}
	
	/**
	 * Process a settings export from config
	 * @since    1.0.0
	 */
	public function settings_export() {

		if ( empty( $_POST[ 'g_action' ] ) || 'export_settings' != $_POST[ 'g_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'g_export_nonce' ], 'g_export_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings[ 0 ] = get_option( $this->setting_slug . '-settings' );

		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=g-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );
		if ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
			echo json_encode( $settings, JSON_PRETTY_PRINT );
		} else {
			echo json_encode( $settings );
		}
		exit;
	}

	/**
	 * Process a settings import from a json file
	 * @since    1.0.0
	 */
	public function settings_import() {

		if ( empty( $_POST[ 'g_action' ] ) || 'import_settings' != $_POST[ 'g_action' ] ) {
			return;
		}

		if ( !wp_verify_nonce( $_POST[ 'g_import_nonce' ], 'g_import_nonce' ) ) {
			return;
		}

		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		$extension = end( explode( '.', $_FILES[ 'import_file' ][ 'name' ] ) );

		if ( $extension != 'json' ) {
			wp_die( __( 'Please upload a valid .json file', $this->setting_slug ) );
		}

		$import_file = $_FILES[ 'import_file' ][ 'tmp_name' ];

		if ( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import', $this->setting_slug ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings = ( array ) json_decode( file_get_contents( $import_file ) );

		update_option( $this->plugin_slug . '-settings', get_object_vars( $settings[ 0 ] ) );

		wp_safe_redirect( admin_url( 'options-general.php?page=' . $this->setting_slug ) );
		exit;
	}
}

new G_ImpExp();