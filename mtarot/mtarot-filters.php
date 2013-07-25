<?php
/**
 * Tarot Card Filters 
 *
 * Filters are specified like an http query string and limit the card to the terms specified:
 * `[tarot-card filters="category=michael-tarot&overleaf=roles,goals"]` limits the card pool
 * dealt in that slot to those with the specified category and overleaf boxes checked.
 * Multiple terms can be specified for each filter separated by commas. Logical AND is applied
 * meaning that all filters must match for the card to be dealt.
 **/
require_once('mtarot.php');

function MTAROT_ALLOWED_FILTERS() {
	return array(
		'overleaf', 'category', 'tarot-slug', 'polarity'
	);	
}

/**
 * Filter Controls
 *
 * These functions accept a reference to a '$toptions' array which is expected to
 * have a key named 'filters' which itself is an array of filter arrays where each key
 * is a taxonomy or property name and each value is a term or property value.
 **/
function mtarot_add_filters( &$toptions, $filters ){
	$tf =& $toptions['filters']; // Existing filters are stored in toptions
	foreach( $filters as $f ){
		foreach( array_keys($f) as $k ){ // Keys represent filter names
			if( array_key_exists( $k, $tf ) ){ // Merge new terms into existing filter
				$new = $f[$k];
				if( !is_array($new) ){ $new = array($new); };
				$tf[$k] = array_unique( array_merge( $tf[$k], $new ) );
			} else $toptions['filters'][] = array( $k => $f[$k] ); // Add as new filter
		}
	}
}

function mtarot_remove_filters( &$toptions, $filters ){
	$tf =& $toptions['filters']; // Existing filters are stored in toptions
	foreach( $filters as $f ){ // Test each filter to see if it needs to be removed
		foreach( array_keys($f) as $k ){ // If a filter does exist, remove the specified terms
			if( !empty($tf[$k]) ){ $tf[$k] = array_diff( $tf[$k], $f[$k] ); }
		}
	}
}

function mtarot_reset_filters( &$toptions ){
	$toptions['filters'] = array();
}

/* WordPress Queries */
function mtarot_add_wpqueryarg_cat( &$args, $filter ){
	$param = $filter['param'];
	
	// Get the terms from the filter as an array and operate on them
	$terms = is_array($filter['$terms'])? $filter['terms'] : array($filter['terms']);
	foreach( $terms as $term ){
		if( $param  == 'category' && term_exists( $term, $param ) ){

			// Make sure filter refers to a valid category
			$cat = get_category($term);
			if( !empty($cat) ){ 
				// Convert into category ID for WP_Query
				$cat_id = $cat->category_id; 
			
				// If there are no existing category filters or this one isn't there yet, add it
				if( !isset($args['category__in']) ){
					$args['category__in'] = array($cat_id);
				} else if( !in_array( $cat_id, $args['category__in'] ) ){
					$args['category__in'][] = $cat_id;
				}
			}
		}
	}
}

function mtarot_add_wpqueryarg_tax( &$args, $filter ){
	$args['tax_query'][] = array(
		'taxonomy' => $param,
		'field' => 'slug',
		'terms' => $terms
	);
}

function mtarot_add_wpqueryarg_meta( &$args, $filter ){
	/* WP_Query needs a meta_query block to define a custom field query*/
	$args['meta_query'] = array(
		array(
			'key' => $param,
			'value' => $terms,
			'compare' => 'IN'
		)
	);
}

function mtarot_query_arguments( $filter ) {
	// convert tarot-card slot filters into WP_Query arguments
	$args = MTAROT_DEFAULT_WPQUERYARGS();
	// Some filters can be converted as-is, others need translation.
	foreach( $filter as $param => $terms ){
		if( $param == 'category' ){ 
			mtarot_add_wpqueryarg_cat( &$args, $filter );
		}
/*		else if( in_array( $param, MTAROT_ALLOWED_CUSTOMFIELDS() ) ){
			mtarot_add_wpqueryarg_meta( &$args, $filter );
		} 
		else if( !empty($param) && taxonomy_exists($param) ){
			mtarot_add_wpqueryarg_tax( &$args, $filter );		
		}
*/	}
	
	return $args;
}

/* why is this here?
function mtarot_query_posts( $args ){
	// Create a new instance
	$second_query = new WP_Query( $args );
	$posts = $second_query->query($args);
	return $posts;
}
/*

/* Serialization */
function mtarot_pack_filters( $array ){
	$f = '';
	foreach( keys($array) as $k ){
		$f .= (empty($filters)? '' : '&') . $k . '=' . implode( ',', $array[$k] );
	}
	return $f;
}
function mtarot_unpack_filters( $filters ){
//	MECHO( 'Unpacking filters: ', $filters );
	$f = array();
	$expressions = explode( '&', $filters );
	foreach( $expressions as $exp ){
		$expression = explode( '=', $exp, 2 );
		$lvalue = mtarot_validate_filter( $expression[0] );
		$rvalue = explode( ',', $expression[1] );
//		MECHO( 'Found filter lvalue '.$lvalue. ' and rvalue ', $rvalue );
		if( !empty($lvalue) ) $f[$lvalue] = $rvalue;
	}
	return $f;
}

/* Validation */
function mtarot_validate_filter( $value ){ //Check the value in `[tarot-card filters="foo=value"]`
	if( in_array( $value, MTAROT_ALLOWED_FILTERS() ) ){ return $value; }
	return '';
}


?>