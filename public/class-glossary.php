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

        register_via_cpt_core(
                array( __( 'Glossary Term', $this->get_plugin_slug() ), __( 'Glossary Terms', $this->get_plugin_slug() ), 'glossary' ), array(
            'taxonomies' => array( 'glossary-cat' ),
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-book-alt',
            'supports' => array( 'thumbnail', 'editor', 'title' )
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

        add_filter( 'the_content', array( $this, 'codeat_glossary_auto_link' ) );
        add_filter( 'the_excerpt', array( $this, 'codeat_glossary_auto_link' ) );
	  add_action( 'genesis_entry_content', array( $this, 'genesis_content' ), 9 );

        require_once( plugin_dir_path( __FILE__ ) . '/includes/Glossary_a2z_Archive.php' );

        $this->settings = get_option( $this->get_plugin_slug() . '-settings' );

        if ( isset( $this->settings[ 'tooltip' ] ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
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
            $this->cpts[] = 'post';
            $query->set( 'post_type', $this->cpts );
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

            while ( $gl_query->have_posts() ) : $gl_query->the_post();
                $link = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_url', true );
                $target = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_target', true );
                $nofollow = get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_nofollow', true );
                //Get the post of the glossary loop
                if ( empty( $link ) ) {
                    $link = get_the_permalink();
                }
                if ( !empty( $link ) && !empty( $target )){
                    $target = ' target="_blank"';
                }
                if ( !empty ( $link ) && !empty( $nofollow )){
                    $nofollow = ' rel="nofollow"';
                }

                $words[] = $this->search_string( get_the_title() );
                if ( isset( $this->settings[ 'tooltip' ] ) ) {
                    global $post;
                    $links[] = $this->tooltip_html( $link, get_the_title(), $post );
                } else {
                    $links[] = '<a href="' . $link . '"' . $target . $nofollow .'>' . get_the_title() . '</a>';
                }
                $related = $this->related_post_meta( get_post_meta( get_the_ID(), $this->get_plugin_slug() . '_tag', true ) );
                if ( is_array( $related ) ) {
                    foreach ( $related as $value ) {
                        $words[] = $this->search_string( $value );
                        if ( isset( $this->settings[ 'tooltip' ] ) ) {
                            $links[] = $this->tooltip_html( $link, $value, $post );
                        } else {
                            $links[] = '<a href="' . $link . '"' . $target . $nofollow .'>' . $value . '</a>';
                        }
                    }
                }
            endwhile;
            if ( isset( $this->settings[ 'first_occurence' ] ) ) {
                $text = preg_replace( $words, $links, $text, 1 );
            } else {
                $text = preg_replace( $words, $links, $text );
            }
            wp_reset_postdata();
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

    public function related_post_meta( $related ) {
        $value = array_map( 'trim', explode( ',', $related ) );
        if ( empty( $value[ 0 ] ) ) {
            $value = false;
        }
        return $value;
    }

    public function search_string( $title ) {
        return '/((?i)' . $title . '(?-i))(?![^<]*(<\/a>|<\/span>|" \/>))/';
    }

    public function get_the_excerpt( $post ) {
        if ( empty( $post->post_excerpt ) ) {
            return substr( $post->post_content, 0, intval( $this->settings[ 'excerpt_limit' ] ) );
        } else {
            return substr( $post->post_excerpt, 0, intval( $this->settings[ 'excerpt_limit' ] ) );
        }
    }

    public function tooltip_html( $link, $title, $post ) {
        $link_tooltip = '<span class="tooltip">'
                . "\n" . '<span class="tooltip-item">'
                . "\n" . '<a href="' . $link . '">' . $title . '</a>'
                . "\n" . '</span>'
                . "\n" . '<span class="tooltip-content clearfix">';
        $photo = wp_get_attachment_image( $post->ID, 'small' );
        if ( !empty( $photo ) ) {
            $link_tooltip .= $photo;
        }
        $link_tooltip .= "\n" . '<span class="tooltip-text">' . $this->get_the_excerpt( $post ) . ' ...</span>'
                . "\n" . '</span>'
                . "\n" . '</span>';
        return $link_tooltip;
    }
    
    public function be_grid_content() {
		if ( !$this->g_arc_glossary() ) {
			return;
		}

		// Only display excerpt if not a teaser
		if ( !in_array( 'teaser', get_post_class() ) ) {
			$excerpt = substr(get_the_excerpt( ),0,-10) . '<a href="' . get_the_permalink() . '">' . __( 'Read More' ) . '</a>';
			echo '<p>' . $this->codeat_glossary_auto_link( $excerpt ) . '</p>';
			remove_action( 'genesis_entry_content', 'genesis_do_post_content' );
		}
    }

}
