<?php

add_shortcode( 'glossary-terms', 'glossary_terms_list_shortcode' );

/**
 * Shortcode for generate list of glossary terms
 *
 * @since    1.1.0
 *
 * @return list of glossary terms
 */
function glossary_terms_list_shortcode( $atts ) {
  $atts = shortcode_atts( array(
	'order' => 'asc',
	'num' => '100',
	'tax'=> ''
	    ), $atts );

  return get_glossary_terms_list( $atts[ 'order' ], $atts[ 'num' ], $atts[ 'tax' ] );
}

add_shortcode( 'glossary-cats', 'glossary_cat_list_shortcode' );

/**
 * Shortcode for generate list of glossary cat
 *
 * @since    1.1.0
 *
 * @return list of glossary cats
 */
function glossary_cat_list_shortcode( $atts ) {
  $atts = shortcode_atts( array(
	'order' => 'asc',
	'num' => '100',
	    ), $atts );

  return get_glossary_cats_list( $atts[ 'order' ], $atts[ 'num' ] );
}
