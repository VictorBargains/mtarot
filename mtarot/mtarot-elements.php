<?php
/**
 * Michael Tarot HTML Element Generation 
 *
 * These functions return HTML strings and fragments which are customized to specific
 * stylesheet classes and IDs. A variant of the class can also be specified for an
 * additional level of selector specificity.
 **/
require_once('mtarot.php');

/**
 * Style-able Div Elements
 *
 * <div class="foo foo-variant" id="foo-1337">...</div>
 **/

// class attributes for tarot elements
function mtarot_class( $class, $variant='' ){
	$vClass = ' ' . $variant . '-' . $class;
	$attr = 'class="' . $class . (($variant != '')? $vClass : '') . '"';
	return $attr;
}

// id attributes for tarot elements
function mtarot_id( $post, $class, $variant='' ){
	$t_id = $class . '-' . $post->ID;
	$attr = 'id="' . $t_id . '"';/* . (empty( $variant )? '' : $v_id) . '"';*/
	return $attr;
}

// div using class and id for tarot elements
function mtarot_div( $post, $class, $variant='' ){
	$c_attr = mtarot_class( $class, $variant );
	$i_attr = mtarot_id( $post, $class, $variant );
	return '<div ' . $c_attr . ' ' . $i_attr . '>';
}

function mtarot_wrap_div( $innerHTML, $class='', $id='', $variant='' ){
	$html = '<div';
	if( !empty($class) ){ 
		$html .= " class='{$class}";
		if( !empty($variant) ){ $html .= " {$class}-{$variant}"; }
		$html .= "'";
	}
	if( !empty($id) ){ $html .= " id='{$class}-{$id}'"; }
	$html .= ">{$innerHTML}</div><!--/{$class}-->\n";
	return $html;
}

/**
 * Tarot Header
 **/
function mtarot_head_html(){
	$html = '';
	// if tarot category
	if( is_category('michael-tarot') ){
		// show tarot title
		$html .= '<h1><a href="/tarot/">The Michael Tarot</a></h1>';
		// show tarot navigation
		
		// if layout or card
			// show next/prev post links
	}
	return mtarot_wrap_div( $html, 'mtarot-head' );
}


/**
 * Tarot Navigation
 **/
 
function mtarot_navigation_html(){
	$links = array( // name => url, ...
		'View Cards' => '/tcards',
		'Card List' => '/tarot/cards',
		'Get a Reading' => '/tarot/reading',
	);
}

// 


/**
 * Option Form Primitives
 **/
 
/* Pulldown or Select Box */
// Select Name: name used when submitting form; also, defines WPDB field used to store option
// Item Values: array giving the values associated with those options
// Select ID (optional): id used as selector on form page for <select> element (also used in registration of settings)
// Item Names (optional): array of names with indices matching `$item_values` that will appear in the drop-down menu (or as items in a select box)
// Select Size (optional): number of elements displayed in the select input. 1 = dropdown. >1 = select box x items tall (remainder will scroll)
// Selected Item (optional): index of the item in $item_values or $item_names which should be selected by default
function mtarot_option_select_html( $select_name, $item_values, $item_names='', $select_id='', $select_class='', $select_size=1, $selected_value='' ){
	$html = "<select size='{$select_size}' name='{$select_name}'";
	if( !empty($select_class) ){ $html .= " class='{$select_class}'"; }
	if( !empty($select_id) ){ $html .= " id='{$select_id}'"; }
	$html .= ">\n";
	
	$vindex = 0;
	foreach( $item_values as $value ){
		$name = is_array($item_names)? $item_names[$vindex] : $value;
		$html .= "<option value='{$value}'";
		if( !empty($selected_value) && $selected_value == $value){ $html .= " selected"; }
		$html .= ">{$name}</option>\n";
		$vindex++;
	}
	$html .= "</select>";
	return $html;
}

function mtarot_option_text_html( $name, $value, $id='', $size=40 ){
	$html = "<input size='{$size}' name='{$name}' value='{$value}'";
	if( !empty($id) ){ $html .= " id='{$id}'"; }
	$html .= ">\n";
	return $html;
}

// single-item select box with custom post types
function mtarot_posttypes_pulldown_html( $name, $value, $id='' ){
	$args = array(
		'public' => true,
		'_builtin' => false
	);
	$types = get_post_types( $args, 'objects', 'and' );

	$names = array();
	$values = array();
	$index = 0;
	$selected = '';
	foreach( $types as $type ){
		$names[] = $type->labels->singular_name;
		$values[] = $type->name;
		if( $value == $type->name ){ $selected = $value; }
		$index++;
	}
	return mtarot_option_select_html( $name, $values, $names, $id, 'select-posttype', 1, $selected );
}

// TODO: Move and extend form generation functions here

// TODO: Move and extend options page generation functions here


?>