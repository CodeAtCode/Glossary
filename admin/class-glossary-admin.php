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
 * administrative side of the WordPress site.
 *
 */
class Glossary_Admin {

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Slug of the plugin screen.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_screen_hook_suffix = null;

  /**
   * Initialize the plugin by loading admin scripts & styles and adding a
   * settings page and menu.
   *
   * @since     1.0.0
   */
  private function __construct() {
    $plugin = Glossary::get_instance();
    $this->plugin_slug = $plugin->get_plugin_slug();
    $this->setting_slug = $plugin->get_setting_slug();
    $this->cpts = $plugin->get_cpts();

    // Load admin style sheet and JavaScript.
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    // Load admin style in dashboard for the At glance widget
    add_action( 'admin_head-index.php', array( $this, 'enqueue_admin_styles' ) );

    // At Glance Dashboard widget for your cpts
    add_filter( 'dashboard_glance_items', array( $this, 'cpt_glance_dashboard_support' ), 10, 1 );
    // Activity Dashboard widget for your cpts
    add_filter( 'dashboard_recent_posts_query_args', array( $this, 'cpt_activity_dashboard_support' ), 10, 1 );

    // Add the options page and menu item.
    add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

    // Add an action link pointing to the options page.
    $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->setting_slug . '.php' );
    add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

    /*
     * CMB 2 for metabox and many other cool things!
     * https://github.com/WebDevStudios/CMB2
     */
    require_once( plugin_dir_path( __FILE__ ) . '/includes/CMB2/init.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2-extra.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/includes/cmb2-post-search-field.php' );
    add_filter( 'multicheck_posttype_posttypes', array( $this, 'hide_glossary' ) );
    /*
     * Add metabox
     */
    add_action( 'cmb2_init', array( $this, 'cmb_glossary' ) );

    /*
     * Import Export settings
     */
    require_once( plugin_dir_path( __FILE__ ) . 'includes/impexp.php' );
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
   * Register and enqueue admin-specific style sheet.
   *
   * @since     1.0.0
   *
   * @return    void    Return early if no settings page is registered.
   */
  public function enqueue_admin_styles() {
    $screen = get_current_screen();
    if ( $this->plugin_screen_hook_suffix == $screen->id || strpos( $_SERVER[ 'REQUEST_URI' ], 'index.php' ) || strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/' ) !== -1 ) {
	wp_enqueue_style( $this->setting_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array( 'dashicons' ), Glossary::VERSION );
    }
  }

  /**
   * Register and enqueue admin-specific JavaScript.
   *
   *
   * @since     1.0.0
   *
   * @return    void    Return early if no settings page is registered.
   */
  public function enqueue_admin_scripts() {
    $screen = get_current_screen();
    if ( $screen->post_type === 'glossary' ) {
	wp_enqueue_script( $this->setting_slug . '-admin-pt-script', plugins_url( 'assets/js/pt.js', __FILE__ ), array( 'jquery' ), Glossary::VERSION );
    }

    if ( !isset( $this->plugin_screen_hook_suffix ) ) {
	return;
    }

    if ( $this->plugin_screen_hook_suffix === $screen->id ) {
	wp_enqueue_script( $this->setting_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs' ), Glossary::VERSION );
    }
  }

  /**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   * 
   * @return void
   */
  public function add_plugin_admin_menu() {
    /*
     * Settings page in the menu
     * 
     */
    $this->plugin_screen_hook_suffix = add_submenu_page( 'edit.php?post_type=glossary', __( 'Settings', $this->plugin_slug ), __( 'Settings', $this->plugin_slug ), 'manage_options', $this->setting_slug, array( $this, 'display_plugin_admin_page' ));
  }

  /**
   * Render the settings page for this plugin.
   *
   * @since    1.0.0
   */
  public function display_plugin_admin_page() {
    include_once( 'views/admin.php' );
  }

  /**
   * Add settings action link to the plugins page.
   *
   * @since    1.0.0
   */
  public function add_action_links( $links ) {
    return array_merge(
		array(
	  'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings' ) . '</a>',
		), $links
    );
  }

  /**
   * Add the counter of your CPTs in At Glance widget in the dashboard<br>
   * NOTE: add in $post_types your cpts, remember to edit the css style (admin/assets/css/admin.css) for change the dashicon<br>
   *
   *        Reference:  http://wpsnipp.com/index.php/functions-php/wordpress-post-types-dashboard-at-glance-widget/
   *
   * @since    1.0.0
   * 
   * @return array
   */
  public function cpt_glance_dashboard_support( $items = array() ) {
    $post_types = $this->cpts;
    foreach ( $post_types as $type ) {
	if ( !post_type_exists( $type ) ) {
	  continue;
	}
	$num_posts = wp_count_posts( $type );
	if ( $num_posts ) {
	  $published = intval( $num_posts->publish );
	  $post_type = get_post_type_object( $type );
	  $text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, $this->plugin_slug );
	  $text = sprintf( $text, number_format_i18n( $published ) );
	  if ( current_user_can( $post_type->cap->edit_posts ) ) {
	    $items[] = '<a class="' . $post_type->name . '-count" href="edit.php?post_type=' . $post_type->name . '">' . sprintf( '%2$s', $type, $text ) . "</a>\n";
	  } else {
	    $items[] = sprintf( '%2$s', $type, $text ) . "\n";
	  }
	}
    }
    return $items;
  }

  /**
   * Add the recents post type in the activity widget<br>
   * NOTE: add in $post_types your cpts
   *
   * @since    1.0.0
   * 
   * @return array
   */
  function cpt_activity_dashboard_support( $query_args ) {
    if ( !is_array( $query_args[ 'post_type' ] ) ) {
	//Set default post type
	$query_args[ 'post_type' ] = array( 'page' );
    }
    $query_args[ 'post_type' ] = array_merge( $query_args[ 'post_type' ], $this->cpts );
    return $query_args;
  }

  /**
   * Hide glossary post type from settings
   *
   * @since    1.0.0
   * @return array
   */
  function hide_glossary( $cpts ) {
    unset( $cpts[ 'attachment' ] );
    return $cpts;
  }

  /**
   * NOTE:     Your metabox on Demo CPT
   *
   * @since    1.0.0
   * 
   * @return void
   */
  public function cmb_glossary() {
    // Start with an underscore to hide fields from custom fields list
    $cmb_demo = new_cmb2_box( array(
	  'id' => 'glossary_metabox',
	  'title' => __( 'Glossary auto-link settings', $this->plugin_slug ),
	  'object_types' => $this->cpts,
	  'context' => 'normal',
	  'priority' => 'high',
	  'show_names' => true,
		) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Additional search terms', $this->plugin_slug ),
	  'desc' => __( 'Case-Insensitive! More than one: Comma Separated Values', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_tag',
	  'type' => 'text'
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'What type of link?', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_link_type',
	  'type' => 'radio',
	  'default' => 'external',
	  'options' => array(
		'external' => 'External URL',
		'internal' => 'Internal Post Type'
	  )
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'External URL', $this->plugin_slug ),
	  'desc' => __( 'Redirects links to an external/affliate URL', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_url',
	  'type' => 'text_url',
	  'protocols' => array( 'http', 'https' ),
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Internal Post type', $this->plugin_slug ),
	  'desc' => __( 'Select a post type of your site', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_cpt',
	  'type' => 'post_search_text',
	  'select_type' => 'radio',
	  'onlyone' => true
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Open external link in a new window', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_target',
	  'type' => 'checkbox'
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'No Follow link', $this->plugin_slug ),
	  'desc' => __( 'Put rel="nofollow" in the link for SEO purposes', $this->plugin_slug ),
	  'id' => $this->setting_slug . '_nofollow',
	  'type' => 'checkbox'
    ) );
  }

}
