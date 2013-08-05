<?php
/**
 * Michael Tarot Shortcodes
 **/
require_once('mtarot.php');

function MTAROT_SHORTCODE_ALLOWED_ARGUMENTS(){ //Arguments allowed inside `[tarot-card]` tags.
	return array( 'name', 'meaning', 'index', 'filters' );
}
 
/* [tarot-card] 
 *	OPTIONAL ARGUMENTS
 *	
 */
 
/* [tarot-form]
 *	Valid Arguments:
 *		style: 'light', 'dark', 'narrow'
 *		method: 'POST', 'GET'
 *		action: any valid URL
 *		questionType: 'new', 'asked'
 *		showAsked: 'true', 'false'
 */
 
require_once('mtarot-question.php');

/** TAROT FORM SHORTCODE **/

function MTAROT_FORM_DEFAULT_SHORTCODE_ATTS(){
	return array(
		'width' => '440',
		'style' => 'light',
		'method' => 'POST',
		'action' => '/tarot-layout/michaels-thought/',	// This should be pulled dynamically, or a specific page for a reading should be made
		'questionType' => 'new',
		'showAsked' => 'true',
		'questionprompt' => tlayout_option('form_questionPrompt'),
		'questionhint' => tlayout_option('form_questionHint'),
		'questionlabel' => tlayout_option('form_questionLabel'),
		'layoutprompt' => tlayout_option('form_layoutPrompt'),
		'layouthint' => tlayout_option('form_layoutHint'),
		'layoutlabel' => tlayout_option('form_layoutLabel'),
		'submitbutton' => tlayout_option('form_submitButton')
	);
}
 
function mtarot_form_shortcode( $atts ){
	$atts = shortcode_atts( MTAROT_FORM_DEFAULT_SHORTCODE_ATTS(), $atts );
	global $post;
	if( $atts['questionType'] == 'new' ){
//		echo mtarot_question_form_asknew_html($post);
		
		return mtarot_shortcode_form_html($atts);
	} else if( $atts['questionType'] == 'again' ){
		return mtarot_question_form_askagain_html($post);
	}
}
add_shortcode( 'mtarot-form', 'mtarot_form_shortcode' );

/** TAROT CARD SHORTCODE **/

/* Shortcode Content Parsing*/
function MTAROT_DEFAULT_SHORTCODE_ATTS(){ // This array specifies the default arguments and values for the shortcode
	$args = array(
		'name' => '',		// name of the position where the card will be dealt
		'meaning' => '',	// meaning of the card dealt in that position
		'polarity' => '',	// force either 'up' or 'down' polarity (chosen randomly if left blank)
		'tarot-slug' => '',	// force a particular card to be dealt, specified by its slug
		'category' => '',	// force the card to be dealt from a particular category
		'overleaf' => '',	// force the card to be dealt from a particular overleaf
		
		/* index is now deprecated. cards dealt using [tcard] will be dealt in page order. */
		'index' => '',		// order in which the card will be dealt from the deck
		/* filters is now deprecated. use 'overleaf=foo' or add custom taxonomies to get intended behavior. */
		'filters' => ''		// legacy tarot-card filters. never really worked right anyway
	);
//	foreach( mtarot_taxonomy_names() as $name ){ $args[$name] = ''; }
	return $args;
}


/* Entry point for [tcard] shortcodes */
function tcard_shortcode( $toptions, $content=null ){
	
	// Merge specified arguments with defaults
	$args = shortcode_atts( MTAROT_DEFAULT_SHORTCODE_ATTS(), $toptions ); 
	MECHO( $args );

	// Initialize output
	$html = '';

/*

	// Set the arguments used for the post query
	$qargs = array(
		'post_type'		=> tcard_option('type-name'),
		'orderby' 		=> 'desc',
	);
	
	if( !empty( $args['tarot-slug'] ) ){
		// get cards where custom field "tarot-slug" matches $args['tarot-slug']
		$qargs['meta_key'] = 'tarot-slug';
		$qargs['meta_value'] = $args['tarot-slug'];
		$qargs['numberposts'] = 1;
	} else {
		$qargs['orderby'] = 'rand';
	}
	
	// Filter by category
	if( !empty( $args['category'] ) ){
		$qargs['category'] = $args['category'];
	}
	
	// Filter by overleaf
	if( !empty( $args['overleaf'] ) ){
		$qargs['overleaf'] = $args['overleaf'];
	}
	
	MECHO( 'qargs: ' . $qargs );
	
	// Get list of card IDs which match query
	$posts_array = get_posts( $qargs );
	
	*/
	
	$posts_array = mtarot_get_tcard_posts( $args );
	
	// Pick a random polarity unless one was specified
	$polarity = empty( $args['polarity'] ) ? mtarot_random_polarity() : $args['polarity'];
	
	// Append HTML for slot with dealt card if no error
	if( empty( $posts_array ) ){
		$html += '<div class="error">Error: cannot deal card (no posts matching query).</div>';
	} else {
		
		// TODO: Offer a selection mode to choose which html method is called to render the card
		// TODO: Introduce shortcode argument to manage selection mode
		// TODO: Add widget which offers parameters to duplicate shortcode output
		
		$html += mtarot_dealt_card_html( $posts_array[0], $polarity );
	}
	
	// Output HTML
	return $html;
}

add_shortcode( 'tcard', 'tcard_shortcode' );


 ?>