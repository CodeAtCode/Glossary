<?php

class Last_Glossary_Widget extends WPH_Widget {

  function __construct() {

    $plugin = Glossary::get_instance();
    $this->plugin_slug = $plugin->get_plugin_slug();

    $args = array(
	  'label' => __( 'Latest Glossary Terms', $this->plugin_slug ),
	  'description' => __( 'List of latest Glossary Terms', $this->plugin_slug ),
    );

    $args[ 'fields' ] = array(
	  array(
		'name' => __( 'Title', $this->plugin_slug ),
		'desc' => __( 'Enter the widget title.', $this->plugin_slug ),
		'id' => 'title',
		'type' => 'text',
		'class' => 'widefat',
		'std' => __( 'Latest Glossary Terms', $this->plugin_slug ),
		'validate' => 'alpha_dash',
		'filter' => 'strip_tags|esc_attr'
	  ),
	  array(
		'name' => __( 'Number' ),
		'desc' => __( 'Select how many glossary to show.', $this->plugin_slug ),
		'id' => 'number',
		'type' => 'text',
		'validate' => 'numeric',
		'std' => 5,
		'filter' => 'strip_tags|esc_attr',
	  ),
	  array(
		'name' => __( 'Taxonomy' ),
		'desc' => __( 'Select the taxonomy.', $this->plugin_slug ),
		'id' => 'tax',
		'type' => 'taxonomyterm',
		'taxonomy' => 'glossary-cat',
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
    $out .= '<div class="widget-glossary-terms-list">';
    $out .= get_glossary_terms_list( 'ASC', $instance[ 'number' ], $instance[ 'tax' ] );
    $out .= '</div>';
    $out .= $args[ 'after_widget' ];
    echo $out;
  }

}

// Register widget
if ( !function_exists( 'glossary_last_register_widget' ) ) {

  function glossary_last_register_widget() {
    register_widget( 'Last_Glossary_Widget' );
  }

  add_action( 'widgets_init', 'glossary_last_register_widget', 1 );
}
