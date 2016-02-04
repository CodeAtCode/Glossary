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
	/* @TODO: delete all transient, options and files you may have added
	  delete_transient( 'TRANSIENT_NAME' );
	  delete_option('OPTION_NAME');
	  //info: remove custom file directory for main site
	  $upload_dir = wp_upload_dir();
	  $directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "CUSTOM_DIRECTORY_NAME" . DIRECTORY_SEPARATOR;
	  if (is_dir($directory)) {
	  foreach(glob($directory.'*.*') as $v){
	  unlink($v);
	  }
	  rmdir($directory);
	  }
	 */
	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog[ 'blog_id' ] );
			/* @TODO: delete all transient, options and files you may have added
			  delete_transient( 'TRANSIENT_NAME' );
			  delete_option('OPTION_NAME');
			  remove_role( 'advanced' );
			  //info: remove custom file directory for main site
			  $upload_dir = wp_upload_dir();
			  $directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "CUSTOM_DIRECTORY_NAME" . DIRECTORY_SEPARATOR;
			  if (is_dir($directory)) {
			  foreach(glob($directory.'*.*') as $v){
			  unlink($v);
			  }
			  rmdir($directory);
			  }
			  // Delete post meta data
			  $posts = get_posts(array('posts_per_page' => -1));
			  foreach ($posts as $post) {
			    $post_meta = get_post_meta($post->ID);
			    delete_post_meta($post->ID, 'your-post-meta');
			  }
			  // Delete user meta data
			  $users = get_users();
			  foreach ($users as $user) {
			    delete_user_meta($user->ID, 'your-user-meta');
			  }
			  //info: remove and optimize tables
			  $GLOBALS['wpdb']->query("DROP TABLE `".$GLOBALS['wpdb']->prefix."TABLE_NAME`");
			  $GLOBALS['wpdb']->query("OPTIMIZE TABLE `" .$GLOBALS['wpdb']->prefix."options`");
			 */
			restore_current_blog();
		}
	}
} else {
	/* @TODO: delete all transient, options and files you may have added
	  delete_transient( 'TRANSIENT_NAME' );
	  delete_option('OPTION_NAME');
	  remove_role('advanced');
	  //info: remove custom file directory for main site
	  $upload_dir = wp_upload_dir();
	  $directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . "CUSTOM_DIRECTORY_NAME" . DIRECTORY_SEPARATOR;
	  if (is_dir($directory)) {
	  foreach(glob($directory.'*.*') as $v){
	  unlink($v);
	  }
	  rmdir($directory);
	  }
	  // Delete post meta data
	  $posts = get_posts(array('posts_per_page' => -1));
	  foreach ($posts as $post) {
	      $post_meta = get_post_meta($post->ID);
	      delete_post_meta($post->ID, 'your-post-meta');
	  }
	  // Delete user meta data
	  $users = get_users();
	  foreach ($users as $user) {
	    delete_user_meta($user->ID, 'your-user-meta');
	  }
	  //info: remove and optimize tables
	  $GLOBALS['wpdb']->query("DROP TABLE `".$GLOBALS['wpdb']->prefix."TABLE_NAME`");
	  $GLOBALS['wpdb']->query("OPTIMIZE TABLE `" .$GLOBALS['wpdb']->prefix."options`");
	 */

}