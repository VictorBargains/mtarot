<?php
/**
 * Michael Tarot Content Parsing and Dealing
 *
 * The Michael Tarot Deck integrates with WordPress via `[tarot-card]` tags inserted into
 * posts and pages in your blog. These functions control what content gets parsed for tags
 * and what the tags get replaced with.
 **/
require_once('mtarot.php');

function MTAROT_DEFAULT_WPQUERYARGS(){ //This array is passed to WP_Query to initialize a tarot card query.
	return array( 
		'post_type' => array( tcard_option('type_name') ), 
		'posts_per_page' => mtarot_option('deck_size'),
		'orderby' => 'date',
		'order' => 'asc'
	);
}

/* why is this here?
function get_tcards(){
	$args = MTAROT_DEFAULT_WPQUERYARGS();
	$wpquery = new WP_Query( $args );
	$deck = $wpquery->query( $args );
	return $deck;
}
*/

/* Manual Content Parsing */
add_action('the_content', 'mtarot_parse_layout'); // Parse content to turn [tarot-card] tags into HTML
//add_action('the_content_more_link', 'mtarot_layout_more_link'); // Add mtarot-form after more-links on layouts

// Parse a tarot-layout post
function mtarot_parse_layout( $content ){
	// BE CONSERVATIVE -- this is called a lot.
	$typename = tlayout_option('type_name');
	if( is_singular( $typename ) ){
		$content = mtarot_generate_layout_form( $content ); // Put layout form at the top of the layout, below the cut
		$slots = mtarot_slots( $content );	// Parse slots from post content
//		MECHO('mtarot_slots() found ' . count($slots) . ' slots.');
	
		$deck = mtarot_deck( $slots );		// Agregate slot definitions to get the whole deck
//		MECHO('mtarot_deck() found ' . count($deck) . ' cards.');
		
		mtarot_deal_slots( $deck, /*&*/$slots );	// Deal cards from the deck into the $slots array
		mtarot_deal_layout( /*&*/$content, $slots );// Deal cards from $slots into the layout content, replacing [tarot-card] tags.	

	}
	return $content;
}

// Return the union of all `toptions` in $slots as a new `toptions` array (DEPRECATED -VA 9/29/11)
/*
function mtarot_all_filters( $slots ){
	// For now, just add. No negations/removals.
	$allfilters = array();
	
	foreach( $slots as $slot ){
		if( is_array($slot) ){
			$slotfilters = $slot['toptions']['filters'];
			foreach( $slotfilters as $param => $value ){
				if( !array_key_exists($param, $allfilters) ){ $allfilters[$param] = array(); }		
				if( is_array($value) ){ $allfilters[$param] = array_unique( array_merge( $allfilters[$param], $value ) ); }
				else { $allfilters[$param] = array_unique( array_merge( $allfilters[$param], array($value) ) );	}
			}
		}
	}
	
	return $allfilters;
}
*/

function mtarot_layout_more_link( $content ){
	global $post;	
	$content += mtarot_question_form_asknew_html( $post );
	return $content;
}

// Build query arguments for a deck matching the restrictions of a set of slots
function mtarot_deck_queryargs( $slots ){
	$queryargs = MTAROT_DEFAULT_WPQUERYARGS();
	// Ignore filters at the deck level now that they are implemented at the subdeck level -VA 9/29/11
	/*
	$filters = mtarot_all_filters( $slots );
	MECHO('mtarot_all_filters() found ' . count($filters) . ' filters.');

	foreach( $filters as $filter ){
		$queryargs[] = mtarot_query_arguments( $filter );
	}
	*/
	return $queryargs;
}

function mtarot_deck( $slots ){
	// Get composite filters from $slots and build $deck query
	$args = mtarot_deck_queryargs( $slots );
	
	$wpquery = new WP_Query( $args );
	$deck = $wpquery->query( $args );
	return $deck;
}

// Check to see if a card from the deck matches the filters as specified in the slot options
function /* boolean */ mtarot_card_matches_filters( $card, $filters ){
	//MECHO( 'Does card ' . $card->ID . ' match filters:', $filters );
	
	foreach( $filters as $filter => $value ){

		// Find each filter and, depending on type, decide whether it matches the card
		switch( $filter ){

			// Overleaf filters are a taxonomy check
			case 'overleaf':
				// Query all overleaf taxonomy terms from the post
				$terms = get_the_terms( $card->ID, 'overleaf' );

				// If we do not get an array, this post has no terms and fails this filter
				if( !is_array( $terms ) ) return false;
				
				// Value should be an array of valid overleaf term names
				$match = false;
				foreach( $value as $overleaf ){
					// Terms is an array of objects; compare against name value
					foreach( $terms as $term ){
						if( $term->slug == $overleaf ){
//							MECHO( 'Found ' . $overleaf . ' in ', $terms );
							$match = true;
							break;
						}
					}
				}
				
				// Unless one of the terms matched, this filter fails
				if( !$match ) return false;
				break;

			// Category filters are straight up category checks
			case 'category':
				// Iterate on post categories, comparing slug names to value
				$match = false;
				foreach( get_the_category( $card->ID ) as $cat ){
					if( $cat->category_nicename == $value ){
						 $match = true;
						 break; // once matched, bail from foreach
					}
				}

				// Unless one of the categories matched, this filter fails
				if( !$match ) return false;
				break;

			// Tarot Slug filters are a Custom Field (post metadata) check
			case 'tarot-slug':
				if( get_post_meta( $card->ID, 'tarot-slug', true ) != $value ) return false;
				break;

			// Polarity isn't really a filter, so there's nothing to check here.
			case 'polarity':
				break;
		}
	}
	
	// If card passes all filter tests, return a match
	return true;
}


// Populates slot definitions with HTML representing cards from the deck
function /* void */ mtarot_deal_slots( $deck, &$slots ){

	// Keep track of tcard IDs dealt to avoid duplicates
	$dealt = array();		

	// In order specified by slot `toptions` index, deal either face-down placeholders or polar cards from the deck
	usort( $slots, 'mtarot_slot_index_compare' );
	$slotindex = 0;
	foreach( $slots as $slot ){
		if( is_array($slot) ){

			// 1. Find subset of deck to deal from based on filters and previous dealings
			$subdeck = array();
			foreach( $deck as $card ){
				
				// Exclude from subdeck if it has already been dealt.
				if( in_array( $card->ID, $dealt ) ) continue;
				
				// Exclude from subdeck if it doesn't match the filters (if any) for this slot
				if( !mtarot_card_matches_filters( $card, $slot['toptions']['filters'] ) ) continue;
				
				
				
				// Card has passed all tests. Add to subdeck.
				$subdeck[] = $card;
			}
			
			// 2. Select random card from subdeck that has not yet been dealt
			$tcard = '';
			$tries = 0; 
			$deckSize = count( $subdeck );

			do {
				$tindex = array_rand( $subdeck ); 
				$tcard = $subdeck[$tindex]; 
				$tries++; 
			} 
			while( 
				in_array($tcard->ID, $dealt) && 
				$tries < $deckSize 
			);
				
			if( !empty( $tcard ) ){
				// 3. Record the dealt status of the selected card
				$dealt[] = $tcard->ID;
		
				// If a polarity was not specified, randomly choose one:
				$tpolarity = mtarot_validate_polarity( $slot['toptions']['filters']['polarity'] );
				$polarity = empty($tpolarity)? mtarot_random_polarity() : $tpolarity;
				
	
				// Set the HTML representing the slot and card which will be output later
				$slots[$slotindex]['html'] = mtarot_slot_html( $tcard, $polarity, $slot['toptions'] );
			} else {
				MECHO( 'Error: Slot '.$slotindex.' not dealt (no card found matching filters).' );
			}
				
		}
		
		// Move on to next slot.
		$slotindex++;
	}
}

function mtarot_generate_layout_form( $content ){
// search for <!--more--> tag.
	global $post;
	$question = mtarot_question();
	if( empty( $question ) ){
	
		$pattern = '/\\<table/i';
		$replacement = '<hr /><p>' . mtarot_question_form_asknew_html($post) . '</p>';
//	preg_replace( $pattern, $replacement, $content, 1 );	
//mixed preg_replace ( mixed $pattern , mixed $replacement , mixed $subject [, int $limit = -1 [, int &$count ]] )	
//int preg_match_all ( string $pattern , string $subject [, array &$matches [, int $flags = PREG_PATTERN_ORDER [, int $offset = 0 ]]] )
		$matches = array();
		$count = preg_match_all( $pattern, $content, /*&*/$matches, PREG_OFFSET_CAPTURE );
		if( $count > 0 ){	
			$offset = $matches[0][0][1];
			$content = substr_replace( $content, $replacement, $offset, 0 );
		}
		
		$content .= $replacement;
	}

	return $content;
}

// Given a populated slots definition, deals cards into a layout
function mtarot_deal_layout( &$content, $slots ){
	// In reverse pageorder, search and replace to 'deal' cards
	usort( $slots, 'mtarot_slot_pageorder_compare_desc' );
	$count = 0;
	foreach( $slots as $slot ){
		if( is_array($slot) ){ mtarot_deal_card( /*&*/$content, $slot ); }
	}
}


// Deal a card into a slot from a deck
function mtarot_deal_card( &$content, $slot ){
	if( is_array($slot) ){
		$content = substr_replace( $content, $slot['html'], $slot['offset'], strlen( $slot['fulltext'] ) );
	}
}



?>