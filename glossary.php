<?php

/**
 * The WordPress Plugin Boilerplate.
 *
 * A foundation off of which to build well-documented WordPress plugins that
 * also follow WordPress Coding Standards and PHP best practices.
 *
 * @package   Glossary
 * @author    Codeat <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://codeat.com
 * @copyright 2016 GPL 2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Glossary
 * Plugin URI:        @TODO
 * Description:       @TODO
 * Version:           1.0.0
 * Author:            Codeat
 * Author URI:        http://codeat.com
 * Text Domain:       glossary
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * WordPress-Plugin-Boilerplate-Powered: v1.1.7
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

/*
 * ------------------------------------------------------------------------------
 * Public-Facing Functionality
 * ------------------------------------------------------------------------------
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/load_textdomain.php' );

/*
 * Load library for simple and fast creation of Taxonomy and Custom Post Type
 */

require_once( plugin_dir_path( __FILE__ ) . 'includes/Taxonomy_Core/Taxonomy_Core.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/CPT_Core/CPT_Core.php' );

/*
 * Load Widgets Helper
 */

require_once( plugin_dir_path( __FILE__ ) . 'includes/Widgets-Helper/wph-widget-class.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/last_glossary.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/categories.php' );

require_once( plugin_dir_path( __FILE__ ) . 'public/class-glossary.php' );
/*
 * - 9999 is used for load the plugin as last for resolve some
 *   problems when the plugin use API of other plugins, remove
 *   if you don' want this
 */

add_action( 'plugins_loaded', array( 'Glossary', 'get_instance' ), 9999 );

/*
 * -----------------------------------------------------------------------------
 * Dashboard and Administrative Functionality
 * -----------------------------------------------------------------------------
 */

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 */

if ( is_admin() && (!defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'admin/class-glossary-admin.php' );
    add_action( 'plugins_loaded', array( 'Glossary_Admin', 'get_instance' ) );
}
