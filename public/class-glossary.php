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
    $this->settings = get_option( GT_SETTINGS . '-settings' );
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
		array( __( 'Glossary Term', GT_TEXTDOMAIN ), __( 'Glossary Terms', GT_TEXTDOMAIN ), 'glossary' ), $glossary_term_cpt
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
		array( __( 'Term Category', GT_TEXTDOMAIN ), __( 'Terms Categories', GT_TEXTDOMAIN ), 'glossary-cat' ), $glossary_term_tax, array( 'glossary' )
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

    if ( isset( $this->settings[ 'tax_archive' ] ) ) {
	add_action( 'pre_get_posts', array( $this, 'hide_taxonomy_frontend' ) );
    }

    add_filter( 'glossary-themes-url', array( $this, 'add_glossary_url' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
    if ( $query->is_search && !is_admin() ) {
	$post_types = $query->get( 'post_type' );
	if ( $post_types === 'post' ) {
	  $post_types = array();
	  $query->set( 'post_type', array_push( $post_types, $this->cpts ) );
	}
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
    $url_themes = apply_filters( 'glossary-themes-url', array() );
    wp_enqueue_style( GT_SETTINGS . '-hint', $url_themes[ $this->settings[ 'tooltip_style' ] ], array(), GT_VERSION );
  }

  /**
   * Add the path to the themes
   *
   * @param array $themes
   * @return array
   */
  public function add_glossary_url( $themes ) {
    $themes[ 'classic' ] = plugins_url( 'assets/css/tooltip-classic.css', __FILE__ );
    $themes[ 'box' ] = plugins_url( 'assets/css/tooltip-box.css', __FILE__ );
    $themes[ 'line' ] = plugins_url( 'assets/css/tooltip-line.css', __FILE__ );
    return $themes;
  }

  /**
   * Hide the taxonomy on the frontend
   *
   * @param object $query
   * @return object
   */
  public function hide_taxonomy_frontend( $query ) {
    if ( is_admin() ) {
	return;
    }

    if ( is_tax( 'glossary-cat' ) ) {
	$query->set_404();
    }
  }

	/**
	 * Add action hook for Glossary disable checkbox field on supported post types.
	 *
	 * @param string $post_type Post type.
	 */
	function add_meta_boxes( $post_type ) {
  		if ( ! in_array( $post_type, $this->settings['posttypes'], true ) ) {
  			return;
		}

		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submitbox_misc_actions' ) );
	}

	/**
	 * Add a checkbox for disabling Glossary term linking on a page.
	 */
	function post_submitbox_misc_actions() {
		$screen = get_current_screen();
		$post_id = 0;

		if ( 'add' !== $screen->action ) {
			$post_id = (int) wp_unslash( $_GET['post'] );
		}

		?>
		<div class="misc-pub-section glossary-disable">
			<?php wp_nonce_field( '_glossary_exclude_nonce', '_glossary_exclude_noncename' ); ?>
			<span><?php _e( 'Disable Glossary linking' ); ?>:</span>&nbsp;<input type="checkbox" name="_glossary_disable" id="_glossary_disable" <?php checked( (bool) get_post_meta( $post_id, '_glossary_disable', true ) ); ?> />
		</div>
		<?php
	}

	/**
	 * Save the Glossary disable post meta for supported post types.
	 *
	 * @param int $post_id Post ID.
	 * @param WP_Post $post WP_Post object.
	 */
	function save_post( $post_id, $post ) {
		if ( ! in_array( $post->post_type, $this->settings['posttypes'], true ) ) {
			return;
		}

		//Skip revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		//Users should have the ability to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['_glossary_exclude_noncename'] ) && wp_verify_nonce( $_POST['_glossary_exclude_noncename'], '_glossary_exclude_nonce' ) ) {
			$disable_glossary = (bool) $_POST['_glossary_disable'];

			if ( $disable_glossary ) {
				update_post_meta( $post_id, '_glossary_disable', 1 );
			} else {
				delete_post_meta( $post_id, '_glossary_disable' );
			}
		}
	}
}
