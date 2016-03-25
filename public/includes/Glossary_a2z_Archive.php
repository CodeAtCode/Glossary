<?php

/**
 * A-Z Archive system
 * Based on https://github.com/NowellVanHoesen/a2z-alphabetical-archive-links/
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @copyright 2016 GPL 2.0+
 */
class G_a2z_Archive {

  /**
   * Initialize the plugin by setting localization and loading public scripts
   * and styles.
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_filter( 'query_vars', array( $this, 'query_vars' ) );
    add_action( 'pre_get_posts', array( $this, 'check_qv' ) );
  }

  /**
   * Add our value
   * 
   * @param array $query_vars
   * @return array
   */
  public function query_vars( $query_vars ) {
    array_push( $query_vars, 'az' );
    return $query_vars;
  }

  /**
   * Check our value
   * 
   * @global object $wp_query
   * @param object $query
   */
  public function check_qv( $query ) {
    if ( $query->is_main_query() && $query->is_archive() && isset( $query->query_vars[ 'az' ] ) ) {
      // if we are on the main query and the query var 'a2z' exists, modify the where/orderby statements
      add_filter( 'posts_where', array( $this, 'modify_query_where' ) );
      add_filter( 'posts_orderby', array( $this, 'modify_query_orderby' ) );
    }
  }

  /**
   * Alter the SQL
   * 
   * @global object $wp_query
   * @global object $wpdb
   * @param string $where
   * @return string
   */
  public function modify_query_where( $where ) {
    global $wp_query, $wpdb;
    $where .= " AND substring( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ), 1, 1) = '" . $wp_query->query_vars[ 'az' ] . "'";
    remove_filter( 'posts_where', array( $this, 'modify_query_where' ) );
    return $where;
  }

  /**
   * Alter the SQL
   * 
   * @global object $wpdb
   * @param string $orderby
   * @return string
   */
  public function modify_query_orderby( $orderby ) {
    global $wpdb;
    $orderby = "( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ) )";

    remove_filter( 'posts_orderby', array( $this, 'modify_query_orderby' ) );
    return $orderby;
  }

}

new G_a2z_Archive();
