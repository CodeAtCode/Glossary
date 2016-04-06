<?php

/**
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 *
 * Plugin Name:       Glossary
 * Plugin URI:        http://codeat.co/glossary
 * Description:       Easily add and manage a glossary with auto-link, tooltips and more. Improve your internal link building for a better SEO.
 * Version:           1.0.6
 * Author:            Codeat
 * Author URI:        http://codeat.co
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
require_once( plugin_dir_path( __FILE__ ) . 'includes/widgets/a2z.php' );

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

if ( is_admin() && (!defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
    require_once( plugin_dir_path( __FILE__ ) . 'admin/class-glossary-admin.php' );
    add_action( 'plugins_loaded', array( 'Glossary_Admin', 'get_instance' ) );
}
