<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @package   Glossary
 * @author    Codeat <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://codeat.com
 * @copyright 2016 GPL 2.0+
 */

// If uninstall not called from WordPress, then exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			delete_option('glossary-settings');
			restore_current_blog();
		}
	}
} else {
	delete_option('glossary-settings');
}