<?php

/**
 * Provide CMB
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 */
class GT_CMB {

  /**
   * Initialize the class
   *
   * @since     1.0.0
   */
  public function __construct() {
    $plugin = Glossary::get_instance();
    $this->cpts = $plugin->get_cpts();
    /*
     * CMB 2 for metabox and many other cool things!
     * https://github.com/WebDevStudios/CMB2
     */
    require_once( plugin_dir_path( __FILE__ ) . '/CMB2/init.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/cmb2-extra.php' );
    require_once( plugin_dir_path( __FILE__ ) . '/cmb2-post-search-field.php' );
    add_filter( 'multicheck_posttype_posttypes', array( $this, 'hide_glossary' ) );
    /*
     * Add metabox
     */
    add_action( 'cmb2_init', array( $this, 'cmb_glossary' ) );
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
	  'title' => __( 'Glossary auto-link settings', GT_TEXTDOMAIN ),
	  'object_types' => $this->cpts,
	  'context' => 'normal',
	  'priority' => 'high',
	  'show_names' => true,
		) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Additional search terms', GT_TEXTDOMAIN ),
	  'desc' => __( 'Case-Insensitive! More than one: Comma Separated Values', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_tag',
	  'type' => 'text'
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'What type of link?', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_link_type',
	  'type' => 'radio',
	  'default' => 'external',
	  'options' => array(
		'external' => 'External URL',
		'internal' => 'Internal Post Type'
	  )
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'External URL', GT_TEXTDOMAIN ),
	  'desc' => __( 'Redirects links to an external/affliate URL', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_url',
	  'type' => 'text_url',
	  'protocols' => array( 'http', 'https' ),
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Internal Post type', GT_TEXTDOMAIN ),
	  'desc' => __( 'Select a post type of your site', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_cpt',
	  'type' => 'post_search_text',
	  'select_type' => 'radio',
	  'onlyone' => true
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'Open external link in a new window', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_target',
	  'type' => 'checkbox'
    ) );
    $cmb_demo->add_field( array(
	  'name' => __( 'No Follow link', GT_TEXTDOMAIN ),
	  'desc' => __( 'Put rel="nofollow" in the link for SEO purposes', GT_TEXTDOMAIN ),
	  'id' => GT_SETTINGS . '_nofollow',
	  'type' => 'checkbox'
    ) );
  }

}

new GT_CMB();
