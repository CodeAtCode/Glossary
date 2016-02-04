<?php

/**
 * Glossary
 *
 * @package   Glossary
 * @author    Codeat <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://codeat.com
 * @copyright 2016 GPL 2.0+
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-glossary-admin.php`
 *
 * @package Glossary
 * @author  Codeat <mte90net@gmail.com>
 */
class Glossary {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

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
	protected static $plugin_slug = 'glossary';

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected static $plugin_name = 'Glossary';

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

		// Create Custom Post Type https://github.com/jtsternberg/CPT_Core/blob/master/README.md
		register_via_cpt_core(
			array( __( 'Glossary', $this->get_plugin_slug() ), __( 'Glossary', $this->get_plugin_slug() ), 'glossary' ), array(
		    'taxonomies' => array( 'glossary-cat' ),
		    'map_meta_cap' => true
			)
		);

		add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );

		// Create Custom Taxonomy https://github.com/jtsternberg/Taxonomy_Core/blob/master/README.md
		register_via_taxonomy_core(
			array( __( 'Category', $this->get_plugin_slug() ), __( 'Categories', $this->get_plugin_slug() ), 'glossary-cat' ), array(
		    'public' => true,
		    'capabilities' => array(
			'assign_terms' => 'edit_posts',
		    )
			), array( 'glossary' )
		);

		add_filter( 'the_content', array( $this, 'codeat_glossary_auto_link' ) );
		add_filter( 'the_excerpt', array( $this, 'codeat_glossary_auto_link' ) );

		$this->settings = get_option( $this->get_plugin_slug() . '-settings' );
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
	 * Return the plugin name.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin name variable.
	 */
	public function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Return the version
	 *
	 * @since    1.0.0
	 *
	 * @return    Version const.
	 */
	public function get_plugin_version() {
		return self::VERSION;
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
			//Mantain support for post
			$this->cpts[] = 'post';
			$query->set( 'post_type', $this->cpts );
		}
		return $query;
	}

	public function codeat_glossary_auto_link( $text ) {
		if (
			$this->g_is_singular() ||
			$this->g_is_home() ||
			$this->g_is_category() ||
			$this->g_is_tag() ||
			$this->g_arc_glossary() ||
			$this->g_tax_glossary()
		) {
			$gl_query = new WP_Query( array( 'post_type' => 'glossary', 'order' => 'ASC', 'orderby' => 'title' ) );

			if ( $gl_query->have_posts() ) {
				while ( $gl_query->have_posts() ) : $gl_query->the_post();
					$words[] = '/((?i)' . get_the_title() . '(?-i))(?![^<]*(<\/a>|" \/>))/';
					$links[] = '<a href="' . get_the_permalink() . '">' . get_the_title() . '</a>';
				endwhile;

				$text = preg_replace( $words, $links, $text );
			}
		}

		return $text;
	}

	public function g_is_singular() {
		if ( isset( $this->settings[ 'posttypes' ] ) && is_singular( $this->settings[ 'posttypes' ] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function g_is_home() {
		if ( isset( $this->settings[ 'is' ] ) && in_array( 'home', $this->settings[ 'is' ] ) && is_home() ) {
			return true;
		} else {
			return false;
		}
	}

	public function g_is_category() {
		if ( isset( $this->settings[ 'is' ] ) && in_array( 'category', $this->settings[ 'is' ] ) && is_category() ) {
			return true;
		} else {
			return false;
		}
	}

	public function g_is_tag() {
		if ( isset( $this->settings[ 'is' ] ) && in_array( 'tag', $this->settings[ 'is' ] ) && is_tag() ) {
			return true;
		} else {
			return false;
		}
	}

	public function g_arc_glossary() {
		if ( isset( $this->settings[ 'is' ] ) && in_array( 'arc_glossary', $this->settings[ 'is' ] ) && is_post_type_archive( 'glossary' ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function g_tax_glossary() {
		if ( isset( $this->settings[ 'is' ] ) && in_array( 'tax_glossary', $this->settings[ 'is' ] ) && is_tax( 'glossary-cat' ) ) {
			return true;
		} else {
			return false;
		}
	}

}
