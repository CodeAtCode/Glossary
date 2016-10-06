<?php

/**
 * Load the plugin text domain for translation.
 *
 * @since    1.0.0
 */
function g_load_plugin_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), GT_TEXTDOMAIN );

	load_textdomain( GT_TEXTDOMAIN, trailingslashit( WP_LANG_DIR ) . GT_TEXTDOMAIN . '/' . GT_TEXTDOMAIN . '-' . $locale . '.mo' );
	load_plugin_textdomain( GT_TEXTDOMAIN, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'g_load_plugin_textdomain', 1 );
