<?php

/**
 * Glossary_Tooltip_Engine
 * Engine system that add the tooltips
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2015 GPL
 */
class Glossary_Tooltip_Engine {
	/**
	 * The single instance of the class.
	 *
	 * @since 1.3.0
	 */
	protected static $instance = null;

	/**
	 * Main Glossary_Tooltip_Engine.
	 *
	 * Ensure only one instance of Glossary_Tooltip_Engine is loaded.
	 *
	 * @static
	 *
	 * @since 1.3.0
	 *
	 * @return Glossary_Tooltip_Engine - Main instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    $this->settings = get_option( GT_SETTINGS . '-settings' );
    add_filter( 'the_content', array( $this, 'glossary_auto_link' ) );
    add_filter( 'the_excerpt', array( $this, 'glossary_auto_link' ) );
    add_action( 'genesis_entry_content', array( $this, 'genesis_content' ), 9 );
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
	$gl_query = new WP_Query( array( 'post_type' => 'glossary', 'order' => 'ASC', 'orderby' => 'title', 'posts_per_page' => -1, 'no_found_rows' => true, 'update_post_term_cache' => false ) );
	while ( $gl_query->have_posts() ) : $gl_query->the_post();
	  $url = get_post_meta( get_the_ID(), GT_SETTINGS . '_url', true );
	  $type = get_post_meta( get_the_ID(), GT_SETTINGS . '_link_type', true );
	  $link = get_glossary_term_url();
	  $target = get_post_meta( get_the_ID(), GT_SETTINGS . '_target', true );
	  $nofollow = get_post_meta( get_the_ID(), GT_SETTINGS . '_nofollow', true );
	  $internal = false;
	  //Get the post of the glossary loop
	  if ( empty( $url ) && empty( $type ) || $type === 'internal' ) {
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
	  $related = $this->related_post_meta( get_post_meta( get_the_ID(), GT_SETTINGS . '_tag', true ) );
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
    }
    return false;
  }

  /**
   * Check the settings and if is the home page
   *
   * @return boolean
   */
  public function g_is_home() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'home', $this->settings[ 'is' ] ) && is_home() ) {
	return true;
    }
    return false;
  }

  /**
   * Check the settings and if is a category page
   *
   * @return boolean
   */
  public function g_is_category() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'category', $this->settings[ 'is' ] ) && is_category() ) {
	return true;
    }
    return false;
  }

  /**
   * Check the settings and if is tag
   *
   * @return boolean
   */
  public function g_is_tag() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'tag', $this->settings[ 'is' ] ) && is_tag() ) {
	return true;
    }
    return false;
  }

  /**
   * Check the settings and if is an archive glossary
   *
   * @return boolean
   */
  public function g_arc_glossary() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'arc_glossary', $this->settings[ 'is' ] ) && is_post_type_archive( 'glossary' ) ) {
	return true;
    }
    return false;
  }

  /**
   * Check the settings and if is a tax glossary page
   *
   * @return boolean
   */
  public function g_tax_glossary() {
    if ( isset( $this->settings[ 'is' ] ) && in_array( 'tax_glossary', $this->settings[ 'is' ] ) && is_tax( 'glossary-cat' ) ) {
	return true;
    }
    return false;
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
    return apply_filters( 'glossary-regex', '/(?<!\w)((?i)' . preg_quote( $title ) . '(?-i))(?=[ \.\,\:\;\*\"\)\!\?\/\%\$\£\|\^\<\>])(?![^<]*(<\/a>|<\/span>|" \/>|>))/', preg_quote( $title ) );
  }

  /**
   *
   * Get the excerpt by our limit
   *
   * @param object $post
   * @return string
   */
  public function get_the_excerpt( $post, $internal = false ) {
    $readmore = '';
    if ( empty( $post->post_excerpt ) ) {
	$excerpt = apply_filters( 'glossary_excerpt', wp_strip_all_tags( $post->post_content ), $post );
    } else {
	$excerpt = apply_filters( 'glossary_excerpt', wp_strip_all_tags( $post->post_excerpt ), $post );
    }

    if ( $internal ) {
	$readmore = ' <a href="' . get_the_permalink() . '">' . __( 'More' ) . '</a>';
    }

    if ( isset( $this->settings[ 'excerpt_words' ] ) && $this->settings[ 'excerpt_words' ] ) {
	$char_limit = absint( $this->settings[ 'excerpt_limit' ] );
	if ( strlen( $excerpt ) >= $char_limit ) {
	  $excerpt = wp_trim_words( $excerpt, $char_limit, '' ) . '...' . $readmore;
	}
    } else {
	if ( strlen( $excerpt ) >= absint( $this->settings[ 'excerpt_limit' ] ) ) {
	  $excerpt = substr( $excerpt, 0, absint( $this->settings[ 'excerpt_limit' ] ) ) . '...' . $readmore;
	}
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
		. '<span class="glossary-tooltip-item">'
		. '<a href="' . $link . '"' . $target . $nofollow . '>' . $title . '</a>'
		. '</span>'
		. '<span class="glossary-tooltip-content clearfix">';
    $photo = get_the_post_thumbnail( $post->ID, 'thumbnail' );
    if ( !empty( $photo ) && !empty( $this->settings[ 't_image' ] ) ) {
	$link_tooltip .= $photo;
    }
    $readmore = '';
    $excerpt = $this->get_the_excerpt( $post, $internal );
    $link_tooltip .= '<span class="glossary-tooltip-text">' . $excerpt . $readmore . '</span>'
		. '</span>'
		. '</span>';
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

/**
 * Main instance of Glossary_Tooltip_Engine.
 *
 * Returns the main instance of Glossary_Tooltip_Engine to prevent the need to use globals.
 *
 * @since 1.3.0
 *
 * @return Glossary_Tooltip_Engine
 */
function Glossary_Tooltip_Engine() {
	return Glossary_Tooltip_Engine::get_instance();
}

Glossary_Tooltip_Engine();
