<?php

class a2z_Glossary_Widget extends WPH_Widget {

  function __construct() {

    $plugin = Glossary::get_instance();
    $this->plugin_slug = $plugin->get_plugin_slug();

    $args = array(
	  'label' => __( 'List of alphabet taxonomies for glossary terms', $this->plugin_slug ),
	  'description' => __( 'List of alphabet taxonomies for glossary terms', $this->plugin_slug ),
    );

    $args[ 'fields' ] = array(
	  array(
		'name' => __( 'Title', $this->plugin_slug ),
		'desc' => __( 'Enter the widget title.', $this->plugin_slug ),
		'id' => 'title',
		'type' => 'text',
		'class' => 'widefat',
		'validate' => 'alpha_dash',
		'filter' => 'strip_tags|esc_attr'
	  ),
	  array(
		'name' => __( 'Show Counts' ),
		'id' => 'show_counts',
		'type' => 'checkbox',
	  ),
    );

    $this->create_widget( $args );
  }

  function widget( $args, $instance ) {
    $out = $args[ 'before_widget' ];
    // And here do whatever you want
    $out .= $args[ 'before_title' ];
    $out .= $instance[ 'title' ];
    $out .= $args[ 'after_title' ];

    global $wpdb;
    $count_col = '';
    if ( ( bool ) $instance[ 'show_counts' ] ) {
	$count_col = ", count( substring( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ), 1, 1) ) as counts";
    }
    $querystr = "SELECT DISTINCT substring( TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) ), 1, 1) as initial" . $count_col . " FROM $wpdb->posts WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'glossary' GROUP BY initial ORDER BY TRIM( LEADING 'A ' FROM TRIM( LEADING 'AN ' FROM TRIM( LEADING 'THE ' FROM UPPER( $wpdb->posts.post_title ) ) ) );";
    $pt_initials = $wpdb->get_results( $querystr, ARRAY_A );
    $initial_arr = array();
    $base_url = get_post_type_archive_link( 'glossary' );
    if ( !$base_url ) {
	$base_url = esc_url( home_url( '/' ) );
	if ( get_option( 'show_on_front' ) == 'page' ) {
	  $base_url = esc_url( get_permalink( get_option( 'page_for_posts' ) ) );
	}
    }
    foreach ( $pt_initials AS $pt_rec ) {
	$link = add_query_arg( 'az', $pt_rec[ 'initial' ], $base_url );
	if ( ( bool ) $instance[ 'show_counts' ] ) {
	  $item = '<li class="count"><a href="' . $link . '">' . $pt_rec[ 'initial' ] . ' <span>(' . $pt_rec[ 'counts' ] . ')</span>' . '</a></li>';
	} else {
	  $item = '<li><a href="' . $link . '">' . $pt_rec[ 'initial' ] . '</a></li>';
	}
	$initial_arr[] = $item;
    }
    $out .= '<ul>' . implode( '', $initial_arr ) . '</ul>';

    $out .= $args[ 'after_widget' ];
    echo $out;
  }

}

// Register widget
if ( !function_exists( 'glossary_a2z_register_widget' ) ) {

  function glossary_a2z_register_widget() {
    register_widget( 'a2z_Glossary_Widget' );
  }

  add_action( 'widgets_init', 'glossary_a2z_register_widget', 1 );
}
