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
  $atts = extract( shortcode_atts( array(
	'order' => 'asc',
	'num' => '100',
			), $atts ) );

  return get_glossary_terms_list( $atts[ 'order' ], $atts[ 'num' ] );
}
