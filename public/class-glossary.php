<?php

/**
 * Glossary
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 */
class Glossary {

  /**
   * Plugin version, used for cache-busting of style and script file references.
   *
   * @since   1.0.0
   *
   * @var     string
   */
  const VERSION = '1.1.0';

  /**
   * Unique identifier for your plugin.
   *
   *
   * The variable name is used as the text domain when internationalizing strings
   * of text. Its value should match the Text Domain file header in the main
   * plugin file.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected static $plugin_slug = 'glossary-by-codeat';
  
  /**
   *
   * @since    1.1.0
   *
   * @var      string
   */
  protected static $setting_slug = 'glossary';

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Array of cpts of the plugin
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected $cpts = array( 'glossary' );
  protected $settings = null;

  /**
   * Initialize the plugin by setting localization and loading public scripts
   * and styles.
   *
   * @since     1.0.0
   */
  private function __construct() {
    $this->settings = get_option( $this->get_setting_slug() . '-settings' );
    $glossary_term_cpt = array(
	  'taxonomies' => array( 'glossary-cat' ),
	  'map_meta_cap' => true,
	  'menu_icon' => 'dashicons-book-alt',
	  'supports' => array( 'thumbnail', 'editor', 'title', 'genesis-seo', 'genesis-layouts', 'genesis-cpt-archive-settings' )
    );
    if ( !empty( $this->settings[ 'slug' ] ) ) {
	$glossary_term_cpt[ 'rewrite' ][ 'slug' ] = $this->settings[ 'slug' ];
    }
    if ( isset( $this->settings[ 'archive' ] ) ) {
	$glossary_term_cpt[ 'has_archive' ] = false;
    }
    register_via_cpt_core(
		array( __( 'Glossary Term', $this->get_plugin_slug() ), __( 'Glossary Terms', $this->get_plugin_slug() ), 'glossary' ), $glossary_term_cpt
    );
    $glossary_term_tax = array(
	  'public' => true,
	  'capabilities' => array(
		'assign_terms' => 'edit_posts',
	  )
    );
    if ( !empty( $this->settings[ 'slug-cat' ] ) ) {
	$glossary_term_tax[ 'rewrite' ][ 'slug' ] = $this->settings[ 'slug-cat' ];
    }
    register_via_taxonomy_core(
		array( __( 'Term Category', $this->get_plugin_slug() ), __( 'Terms Categories', $this->get_plugin_slug() ), 'glossary-cat' ), $glossary_term_tax, array( 'glossary' )
    );

    if ( isset( $this->settings[ 'search' ] ) ) {
	add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );
    }

    require_once( plugin_dir_path( __FILE__ ) . '/includes/Glossary_a2z_Archive.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/includes/Glossary_Tooltip_Engine.php' );

    if ( isset( $this->settings[ 'tooltip' ] ) ) {
	add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    if ( isset( $this->settings[ 'order_terms' ] ) ) {
	add_filter( 'pre_get_posts', array( $this, 'order_glossary' ) );
    }
  }

  /**
   * Return the plugin slug.
   *
   * @since    1.0.0
   *
   * @return    Plugin slug variable.
   */
  public function get_plugin_slug() {
    return self::$plugin_slug;
  }
  
  /**
   * Return the setting slug.
   *
   * @since    1.0.0
   *
   * @return    Plugin slug variable.
   */
  public function get_setting_slug() {
    return self::$setting_slug;
  }

  /**
   * Return the cpts
   *
   * @since    1.0.0
   *
   * @return    Cpts array
   */
  public function get_cpts() {
    return $this->cpts;
  }

  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {
    // If the single instance hasn't been set, set it now.
    if ( null == self::$instance ) {
	self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Add support for custom CPT on the search box
   *
   * @since    1.0.0
   *
   * @param    object    $query
   */
  public function filter_search( $query ) {
    if ( $query->is_search ) {
	//Mantain support for the post type available
	$cpt = $query->get( 'post_type' );
	if ( empty( $cpt ) ) {
	  $cpt = array();
	}
	$query->set( 'post_type', array_merge( $cpt, $this->cpts ) );
    }
    return $query;
  }

  /**
   * Order the glossary terms alphabetically
   *
   * @since    1.0.0
   *
   * @param    object    $query
   */
  public function order_glossary( $query ) {
    if ( is_admin() ) {
	return $query;
    }
    if ( isset( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] == 'glossary' ) {
	$query->set( 'orderby', 'post_title' );
	$query->set( 'order', 'ASC' );
    }
    return $query;
  }

  /**
   * Register and enqueue public-facing style sheet.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    wp_enqueue_style( $this->get_setting_slug() . '-hint', plugins_url( 'assets/css/tooltip-' . $this->settings[ 'tooltip_style' ] . '.css', __FILE__ ), array(), self::VERSION );
  }

}
