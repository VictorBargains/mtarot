<?php
/**
 * Michael Tarot Database Queries
 **/
require_once('mtarot.php');


/** TCARD QUERIES **/
// Used by shortcodes and widgets

/**
 * get a single tarot-card post via custom arguments
 */
function mtarot_get_tcard_post( $args ){
	$tcards = mtarot_get_tcard_posts( $args );
	return empty( $tcards ) ? NULL : $tcards[0];
}

/**
 * get a single tarot-card post by id
 */
function mtarot_get_tcard( $post_id ){
	return mtarot_get_tcard_posts( array( 'id' => $post_id ) );
}

/**
 * get all ids of all tarot-card posts in date order
 */
function mtarot_get_tcard_ids(){
	$results = $wpdb->get_results("
		SELECT post_id 
		FROM $wpdb->posts 
		WHERE post_type = " . tcard_option('type-name')  . "
		ORDER BY post_date
	");
	return $results;
}

/**
 * get a random ID of a tarot-card post, with an exclusion list
 */
function mtarot_random_card_id( $exclude = array() ){
	//1. get all tarot card post IDs
	$ids = mtarot_get_tcard_ids();
	
	//2. remove any IDs which match $exclude
	$x_ids = array_diff( $ids, $exclude );
	
	//3. return a remaining ID at random
	$r_id = array_rand( $x_ids );
	
	return $x_ids[$r_id]; 
}

/**
 * Workhorse function which inputs mtarot card arguments, 
 * turns it into a query arguments list, and then 
 * returns the wordpress posts fetched with those arguments.
 */
function mtarot_get_tcard_posts( $args ){

	/**
	 * 'id' argument
	 * - finds a specific card by post_id
	 */
	if( !empty( $args['id'] ) ){
		$tcard = get_post( $args['id'] );
		if( !empty( $tcard ) ){
			// if the single result specified by the ID is found, return it immediately
			return array( $tcard );
		}
	}

	// If an ID is not specified, we need to issue a WP_Query to handle the lookup...
	
	// By default, fetch all custom post types matching the tarot-card type, and "shuffle" the deck by ordering them randomly.
	$qargs = array(
		'post_type'		=> tcard_option('type-name'),
		'orderby' 		=> 'random',
	);
	
	
	/**
	 * 'tarot-slug' argument
	 */
	if( !empty( $args['tarot-slug'] ) ){
		// get cards where custom field "tarot-slug" matches $args['tarot-slug']
		$qargs['meta_key'] = 'tarot-slug';
		$qargs['meta_value'] = $args['tarot-slug'];
	} 
	
	
	/**
	 * 'category' argument
	 * - adds a category requirement to the post query -- can be used to filter by deck
	 */
	if( !empty( $args['category'] ) ){
		$qargs['category'] = $args['category'];
	}
	
	
	/**
	 * 'overleaf' argument
	 * - restricts the query to posts which belong to a specific overleaf taxonomy
	 */
	if( !empty( $args['overleaf'] ) ){
		$qargs['overleaf'] = $args['overleaf'];
	}
	
	MECHO( 'qargs: ' . $qargs );
	
	// return list of card IDs which match query
	return get_posts( $qargs );
}


?>