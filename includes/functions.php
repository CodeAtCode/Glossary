<?php

function get_glossary_terms_list( $order, $num, $tax = '' ) {
  if ( $order === 'asc' ) {
    $order = 'ASC';
  }

  $args = array( 'post_type' => 'glossary', 'order' => $order, 'orderby' => 'title', 'posts_per_page' => $num, 'update_post_meta_cache' => false, 'fields' => 'ids' );

  if ( !empty( $tax ) ) {
    $args[ 'tax_query' ] = array(
	  array(
		'taxonomy' => 'glossary-cat',
		'terms' => $tax,
		'field' => 'slug',
	  ),
    );
  }

  $glossary = new WP_Query( $args );
  if ( $glossary->have_posts() ) {
    $out = '<dl class="glossary-terms-list">';
    while ( $glossary->have_posts() ) : $glossary->the_post();
	$out .= '<dt><a href="' . get_glossary_term_url( get_the_ID() ) . '">' . get_the_title() . '</a></dt>';
    endwhile;
    $out .= '</dl>';
    wp_reset_query();

    return $out;
  }
}

function get_glossary_term_url( $id = '' ) {
  if ( empty( $id ) ) {
    $id = get_the_ID();
  }
  $type = get_post_meta( $id, GT_SETTINGS . '_link_type', true );
  $link = get_post_meta( $id, GT_SETTINGS . '_url', true );
  $cpt = get_post_meta( $id, GT_SETTINGS . '_cpt', true );
  if ( empty( $link ) && empty( $cpt ) ) {
    return get_the_permalink( $id );
  }
  if ( $type === 'external' || empty( $type ) ) {
    return $link;
  }
  if ( $type === 'internal' ) {
    return get_the_permalink( $cpt );
  }
}

function get_glossary_cats_list( $order = 'DESC', $num = '0' ) {
  $taxs = get_terms( 'glossary-cat', array(
	'hide_empty' => false,
	'order' => $order,
	'number' => $num,
	'orderby' => 'name'
	    ) );

  $out = '<dl class="glossary-terms-list">';
  if ( !empty( $taxs ) && !is_wp_error( $taxs ) ) {
    foreach ( $taxs as $tax ) {
	$out .= '<dt><a href="' . esc_url( get_term_link( $tax ) ) . '">' . $tax->name . '</a></dt>';
    }
    $out .= '</dl>';
    return $out;
  }
}
