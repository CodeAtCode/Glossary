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
  const VERSION = '1.0.6';

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
    register_via_cpt_core(
            array( __( 'Glossary Term', $this->get_plugin_slug() ), __( 'Glossary Terms', $this->get_plugin_slug() ), 'glossary' ), array(
        'taxonomies' => array( 'glossary-cat' ),
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-book-alt',
        'supports' => array( 'thumbnail', 'editor', 'title', 'genesis-seo', 'genesis-layouts', 'genesis-cpt-archive-settings' )
            )
    );

    add_filter( 'pre_get_posts', array( $this, 'filter_search' ) );

    register_via_taxonomy_core(
            array( __( 'Term Category', $this->get_plugin_slug() ), __( 'Terms Categories', $this->get_plugin_slug() ), 'glossary-cat' ), array(
        'public' => true,
        'capabilities' => array(
            'assign_terms' => 'edit_posts',
        )
            ), array( 'glossary' )
    );

    add_filter( 'the_content', array( $this, 'glossary_auto_link' ) );
    add_filter( 'the_excerpt', array( $this, 'glossary_auto_link' ) );
    add_action( 'genesis_entry_content', array( $this, 'genesis_content' ), 9 );

    require_once( plugin_dir_path( __FILE__ ) . '/includes/Glossary_a2z_Archive.php' );

    $this->settings = get_option( $this->get_plugin_slug() . '-settings' );

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
      $cpts = $query->get( 'post_type' );
      $cpts[] = $this->cpts;
      $query->set( 'post_type', $cpts );
    }
    return $query;
  }

  /**
   * AOrder the glossary terms alphabetically
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
    wp_enqueue_style( $this->get_plugin_slug() . '-hint', plugins_url( 'assets/css/tooltip-' . $this->settings[ 'tooltip_style' ] . '.css', __FILE__ ), array(), self::VERSION );
  }

  /**
   *
   * The magic function that add the glossary terms to your content
   *
   * @global object $post
   * @param string $text
   * @return string
   */
  public function glossary_auto_link( $text ) {
    if (
            $this->g_is_singular() ||
            $this->g_is_home() ||
            $this->g_is_category() ||
            $this->g_is_tag() ||
            $this->g_arc_glossary() ||
            $this->g_tax_glossary()
    ) {
      $gl_query = new WP_Query( array( 'post_type' => 'glossary', 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1 ) );
      while ( $gl_query->have_posts() ) : $gl_query->the_post();
        $link = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_url', true );
        $target = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_target', true );
        $nofollow = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_nofollow', true );
        $internal = false;
        //Get the post of the glossary loop
        if ( empty( $link ) ) {
          $link = get_the_permalink();
          $internal = true;
        }
        if ( !empty( $link ) && !empty( $target ) ) {
          $target = ' target="_blank"';
        }
        if ( !empty( $link ) && !empty( $nofollow ) ) {
          $nofollow = ' rel="nofollow"';
        }

        $words[] = $this->search_string( get_the_title() );
        if ( isset( $this->settings[ 'tooltip' ] ) ) {
          global $post;
          $links[] = $this->tooltip_html( $link, '$0', $post, $target, $nofollow, $internal );
        } else {
          $links[] = '<a href="' . $link . '"' . $target . $nofollow . '>$0</a>';
        }
        $related = $this->related_post_meta( get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_tag', true ) );
        if ( is_array( $related ) ) {
          foreach ( $related as $value ) {
            $words[] = $this->search_string( $value );
            if ( isset( $this->settings[ 'tooltip' ] ) ) {
              $links[] = $this->tooltip_html( $link, '$0', $post, $target, $nofollow, $internal );
            } else {
              $links[] = '<a href="' . $link . '"' . $target . $nofollow . '>$0</a>';
            }
          }
        }
      endwhile;
      if ( !empty( $words ) ) {
        if ( isset( $this->settings[ 'first_occurence' ] ) ) {
          $text = preg_replace( $words, $links, $text, 1 );
        } else {
          $text = preg_replace( $words, $links, $text );
        }
      }
      wp_reset_postdata();
    }

    return $text;
  }

  /**
   * Check the settings and if is a single page
   *
   * @return boolean
   */
  public function g_is_singular() {
    if ( isset( $this->settings[ 'posttypes' ] ) && is_singular( $this->settings[ 'posttypes' ] ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is the home page
   *
   * @return boolean
   */
  public function g_is_home() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'home', $this->settings[ 'is' ] ) && is_home() ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is a category page
   *
   * @return boolean
   */
  public function g_is_category() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'category', $this->settings[ 'is' ] ) && is_category() ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is tag
   *
   * @return boolean
   */
  public function g_is_tag() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'tag', $this->settings[ 'is' ] ) && is_tag() ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is an archive glossary
   *
   * @return boolean
   */
  public function g_arc_glossary() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'arc_glossary', $this->settings[ 'is' ] ) && is_post_type_archive( 'glossary' ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is a tax glossary page
   *
   * @return boolean
   */
  public function g_tax_glossary() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'tax_glossary', $this->settings[ 'is' ] ) && is_tax( 'glossary-cat' ) ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check the settings and if is a single page
   *
   * @return boolean
   */
  public function related_post_meta( $related ) {
    $value = array_map( 'trim', explode( ',', $related ) );
    if ( empty( $value[ 0 ] ) ) {
      $value = false;
    }
    return $value;
  }

  /**
   *
   * That method return the regular expression
   *
   * @param string $title Terms.
   * @return string
   */
  public function search_string( $title ) {
    return '/(?<!\w)((?i)' . $title . '(?-i))(?=[ \.\,\:\;\*\"\)\!\?\/\%\$\£\|\^\<\>])(?![^<]*(<\/a>|<\/span>|" \/>|>))/';
  }

  /**
   *
   * Get the excerpt by our limit
   *
   * @param object $post
   * @return string
   */
  public function get_the_excerpt( $post ) {
    if ( empty( $post->post_excerpt ) ) {
      $excerpt = apply_filters( 'glossary_excerpt', wp_strip_all_tags( $post->post_content ), $post );
    } else {
      $excerpt = apply_filters( 'glossary_excerpt', wp_strip_all_tags( $post->post_excerpt ), $post );
    }
    if ( strlen( $excerpt ) >= absint( $this->settings[ 'excerpt_limit' ] ) ) {
      return substr( $excerpt, 0, absint( $this->settings[ 'excerpt_limit' ] ) ) . '...';
    }
    return $excerpt;
  }

  /**
   * Add a tooltip for your terms
   *
   * @param string $link
   * @param string $title
   * @param object $post
   * @param string $target
   * @param string $nofollow
   * @param string $internal
   * @return string
   */
  public function tooltip_html( $link, $title, $post, $target, $nofollow, $internal ) {
    $link_tooltip = '<span class="glossary-tooltip">'
            . "\n" . '<span class="glossary-tooltip-item">'
            . "\n" . '<a href="' . $link . '"' . $target . $nofollow . '>' . $title . '</a>'
            . "\n" . '</span>'
            . "\n" . '<span class="glossary-tooltip-content clearfix">';
    $photo = get_the_post_thumbnail( $post->ID, 'thumbnail' );
    if ( !empty( $photo ) && !empty( $this->settings[ 't_image' ] ) ) {
      $link_tooltip .= $photo;
    }
    $readmore = '';
    if ( $internal ) {
      $readmore = ' <a href="' . get_the_permalink() . '">' . __( 'More' ) . '</a>';
    }
    $excerpt = $this->get_the_excerpt( $post );
    $link_tooltip .= "\n" . '<span class="glossary-tooltip-text">' . $excerpt . $readmore . '</span>'
            . "\n" . '</span>'
            . "\n" . '</span>';
    return apply_filters( 'glossary_tooltip_html', $link_tooltip, $title, $excerpt, $photo, $post, $target, $nofollow, $internal );
  }

  /**
   * Genesis hack to add the support for the archive content page
   *
   * @return void
   */
  public function genesis_content() {
    if ( !$this->g_arc_glossary() ) {
      return;
    }

    // Only display excerpt if not a teaser
    if ( !in_array( 'teaser', get_post_class() ) ) {
      remove_filter( 'the_content', array( $this, 'glossary_auto_link' ) );
      remove_filter( 'the_excerpt', array( $this, 'glossary_auto_link' ) );
      $excerpt = wp_strip_all_tags( get_the_excerpt() );
      echo '<p>' . $this->glossary_auto_link( $excerpt ) . ' <a href="' . get_the_permalink() . '">' . __( 'Read More' ) . '</a></p>';
      remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
    }
  }

}
