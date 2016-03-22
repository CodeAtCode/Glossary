<?php

//By Daniele Mte90 Scasciafratte
//render multicheck-posttype
add_action( 'cmb2_render_multicheck_posttype', 'ds_cmb_render_multicheck_posttype', 10, 5 );

function ds_cmb_render_multicheck_posttype( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
  $cpts = get_post_types();
  unset( $cpts[ 'nav_menu_item' ] );
  unset( $cpts[ 'revision' ] );
  $cpts = apply_filters( 'multicheck_posttype_' . $field->args[ '_id' ], $cpts );
  $options = '';
  $i = 1;
  $values = ( array ) $escaped_value;
  if ( $cpts ) {
    foreach ( $cpts as $cpt ) {
      $args = array(
          'value' => $cpt,
          'label' => $cpt,
          'type' => 'checkbox',
          'name' => $field->args[ '_name' ] . '[]',
      );
      if ( in_array( $cpt, $values ) ) {
        $args[ 'checked' ] = 'checked';
      }
      if ( version_compare( CMB2_VERSION, '2.2.2', '>=' ) ) {
        $options .= CMB2_Type_Multi_Base::list_input( $args, $i );
      } else {
        $options .= $field_type_object->list_input( $args, $i );
      }
      $i++;
    }
  }
  $classes = false === $field->args( 'select_all_button' ) ? 'cmb2-checkbox-list no-select-all cmb2-list' : 'cmb2-checkbox-list cmb2-list';
  echo $field_type_object->radio( array( 'class' => $classes, 'options' => $options ), 'multicheck_posttype' );
}

add_action( 'cmb2_render_text_number', 'sm_cmb_render_text_number', 10, 5 );

function sm_cmb_render_text_number( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
  echo $field_type_object->input( array( 'class' => 'cmb2-text-small', 'type' => 'number' ) );
}

// sanitize the field
add_filter( 'cmb2_sanitize_text_number', 'sm_cmb2_sanitize_text_number', 10, 2 );

function sm_cmb2_sanitize_text_number( $null, $new ) {
  $new = preg_replace( "/[^0-9]/", "", $new );
  return $new;
}
