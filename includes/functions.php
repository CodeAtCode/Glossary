<?php

function get_glossary_terms_list( $order, $num ) {
  $order = 'DESC';
  if ( $order === 'asc' ) {
    $order = 'ASC';
  }

  $glossary = new WP_Query( array( 'post_type' => 'glossary', 'order' => $order, 'orderby' => 'title', 'posts_per_page' => $num, 'update_post_meta_cache' => false, 'fields' => 'ids' ) );
  if ( $glossary->have_posts() ) {
    $out .= '<dl class="glossary-terms-list">';
    while ( $glossary->have_posts() ) : $glossary->the_post();
	$out .= '<dt><a href="' . get_glossary_term_url( get_the_ID() ) . '">' . get_the_title() . '</a></dt>';
    endwhile;
    $out .= '</dl>';
    wp_reset_query();
  }

  return $out;
}

function get_glossary_term_url( $id = '' ) {
  $plugin = Glossary::get_instance();
  if ( empty( $id ) ) {
    $id = get_the_ID();
  }
  $link = get_post_meta( $id, $plugin->get_plugin_slug() . '_url', true );
  if ( empty( $link ) ) {
    $link = get_the_permalink();
  }
  return $link;
}
