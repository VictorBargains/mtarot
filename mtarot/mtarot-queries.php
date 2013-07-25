<?php
/**
 * Michael Tarot Database Queries
 **/
require_once('mtarot.php');


/** TCARD QUERIES **/
// Used by shortcodes and widgets

function mtarot_get_tcard_post( $args ){
	
	// Set the arguments used for the post query
	$qargs = array(
		'post_type'		=> tcard_option('type-name'),
		'orderby' 		=> 'desc',
	);
	
	if( !is_empty( $args['tarot-slug'] ) ){
		// get cards where custom field "tarot-slug" matches $args['tarot-slug']
		$qargs['meta_key'] = 'tarot-slug';
		$qargs['meta_value'] = $args['tarot-slug'];
		$qargs['numberposts'] = 1;
	} else {
		$qargs['orderby'] = 'rand';
	}
	
	// Filter by category
	if( !is_empty( $args['category'] ) ){
		$qargs['category'] = $args['category'];
	}
	
	// Filter by overleaf
	if( !is_empty( $args['overleaf'] ) ){
		$qargs['overleaf'] = $args['overleaf'];
	}
	
	MECHO( 'qargs: ' . $qargs );
	
	// Get list of card IDs which match query
	$posts_array = get_posts( $qargs );
	
	if( count( $posts_array ) > 0 ){
		return $posts_array[0];
	}
	return NULL; 
}


?>