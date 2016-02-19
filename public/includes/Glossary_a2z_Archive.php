<?php

/**
 * A2Z Archive system
 * Based on https://github.com/NowellVanHoesen/a2z-alphabetical-archive-links/
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
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

    public function query_vars( $query_vars ) {
        array_push( $query_vars, 'az' );
        return $query_vars;
    }

    public function check_qv( $query ) {
        global $wp_query;
        if ( $query->is_main_query() && isset( $wp_query->query_vars[ 'az' ] ) ) {
            // if we are on the main query and the query var 'a2z' exists, modify the where/orderby statements
            add_filter( 'posts_where', array( $this, 'modify_query_where' ) );
            add_filter( 'posts_orderby', array( $this, 'modify_query_orderby' ) );
        }
    }

    public function modify_query_where( $where ) {
        global $wp_query, $wpdb;
        $where .= " AND substring( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ), 1, 1) = '" . $wp_query->query_vars[ 'az' ] . "'";
        return $where;
    }

    public function modify_query_orderby( $orderby ) {
        global $wpdb;
        $orderby = "( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ) )";
        return $orderby;
    }

}

new G_a2z_Archive();
