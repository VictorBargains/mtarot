<?php
/**
 * Tarot Card Slots
 *
 * Tarot Card Slots are specified by a `[tarot-card]` tag. When placed in a post, the tag will 
 * be replaced by HTML rendering the tarot card, including its name, properties, taxonomies,
 * image, and either a positive, negative, neutral, or no description (depending on the context).
 *
 * Defaults can be overridden by adding options to the tag as you would to an HTML tag like
 * `[tarot-card name="value"]`. Note: Unquoted values will be treated as garbage and ignored.
 **/

/* Validation */
function mtarot_validate_slotname( $slotname ){ return $slotname; } // TODO: Validate slot names
function mtarot_validate_slotmeaning( $slotmeaning ){ return mtarot_validate_slotname( $slotmeaning ); }
function mtarot_validate_argument( $value ){
 return in_array( $value, MTAROT_DEFAULT_SHORTCODE_ARGUMENTS() ); }

/* Slot Parsing */
// Search text for `[tarot-card]` tags and return an array of associative arrays
// which represent the slot:
//	$slot['fulltext'] is the full matched tag including brackets (`[` and `]`)
//	$slot['pageorder'] is the order in which the slots are found in $text
//	$slot['offset'] is the offset into $text where the tag occurs
//	$slot['toptions'] is the expanded associative array of card options for that slot
//	$slot['html'] is the content that the tag will be replaced with
function mtarot_slots( $text ){
	// TODO: re-engineer to use wordpress shortcode API rather than parsing '[tarot-card ...]' manually.

	$slots = array();
	// Find a `[tarot-card {arguments}]` tag in the post
	$regex = '/\[tarot-card\s*([^\]]*)]/i';
	/*
		PREG_PATTERN_ORDER
		Orders results so that $matches[0] is an array of full pattern matches, $matches[1] is an array of 
		strings matched by the first parenthesized subpattern, and so on.
		
		PREG_OFFSET_CAPTURE
		If this flag is passed, for every occurring match the appendant string offset will also be returned. 
		Note that this changes the value of matches into an array where every element is an array consisting 
		of the matched string at offset 0 and its string offset into subject at offset 1
	*/
	$flags = PREG_OFFSET_CAPTURE | PREG_SET_ORDER;
	$matches = array();
	preg_match_all( $regex, $text, /*&*/$matches, $flags );
	$pageorder = 0;
	foreach( $matches as $match ){ // each $match should have one element per catch group
		$fulltext = $match[0][0];
		$offset = $match[0][1];
		$arguments = $match[1][0];
		$slot = array(
			'fulltext' => $fulltext,
			'pageorder' => $pageorder,
			'offset' => $offset,
			'html' => '',
			'toptions' => mtarot_slot_options( $arguments )
			/*
			'index',
			'name',
			'meaning',
			'filters'
			*/
		);
		$slots[] = $slot;
		$pageorder++;
	}
	return $slots; 
}



/* Slot Argument Parsing */		
// Converts a string of `name="value"` arguments separated by spaces into an array
// of `toptions` type which defines the options for a tarot-card slot.
function mtarot_slot_options( $args ){
	// Load default tarot card options
	$toptions = array( 
		'name' => '',
		'meaning' => '',
		'index' => '',
		'filters' => array()
	);

	// Get card slot options (`name="quoted value" [name2="value2"]...`)
	$argrx = '/\\b([^=]+)\\s?=\\s?"([^"]*)"/';
	$argmatches = array();
	/*
		PREG_SET_ORDER
		Orders results so that $matches[0] is an array of first set of matches, 
		$matches[1] is an array of second set of matches, and so on.
	*/
	preg_match_all( $argrx, $args, $argmatches, PREG_SET_ORDER );
	
	// process arguments
	foreach( $argmatches as $argmatch ){

		$name = strtolower($argmatch[1]);
		$value = $argmatch[2];
		// process options with values
		if( !empty($value) ){
			switch( ($name) ){
				// directly load any scalar options
				case 'name':
					$slotname = mtarot_validate_slotname($value);	
					$toptions['name'] = $slotname;
					break;
				case 'meaning':
					$slotmeaning = mtarot_validate_slotmeaning($value);
					$toptions['meaning'] = $slotmeaning;
					break;
				case 'index':
					$toptions['index'] = $value;
					break;
				case 'polarity':
					$polarity = mtarot_validate_polarity($value);
					$toptions['polarity'] = $polarity;
					break;
				case 'filters':
					$filters = mtarot_unpack_filters($value);
					$toptions['filters'] = $filters; // For now don't do any fancy stuff to the filters after unpacking
//					mtarot_add_filters( $toptions['filters'], $filters );
					break;
			}
		}
	}

	return $toptions;
}

/* HTML Elements */
function mtarot_undealt_card_html( $post, $toptions ){
	$html .= mtarot_div( $post, 'tcard', 'undealt' );
	$html .= mtarot_card_back_html( $post, $toptions );
	$html .= '</div><!--/tcard-->';
	return $html;
}

function mtarot_dealt_card_html( $post, $polarity, $desc_index='random' ){
	$html .= mtarot_div( $post, 'tcard', $polarity );
	$html .= '<a target="_blank" href="/tcard/' . $post->post_name . '">' . $post->post_title;
	$html .= mtarot_card_face_html($post, $polarity) . '</a>';
	$html .= mtarot_card_polarity_html($post, $polarity);
	$html .= mtarot_card_description_html($post, $polarity, $desc_index);
	$html .= '</div><!--/tcard-->';
	return $html;
}

/* Tarot Layout Slots */
function mtarot_undealt_slot_html( $post, $toptions ) {
	$html = mtarot_div( $post, 'tcard-slot', 'undealt' );
	$html .= mtarot_toptions_js_html( $toptions );
	$html .= mtarot_undealt_card_html( $post, $toptions );
	$html .= '</div><!--/tcard-slot-->';
	return $html;
}

// Will deal the card face down unless a question has been provided as a query argument or a specific card is forced in `toptions`;
function mtarot_slot_html( $post, $polarity, $toptions ) {
	// If no question has been asked, deal cards face down and let JavaScript/jQuery/AJAX handle the dealing
 	$question = mtarot_question();

	$variant = empty($question)? 'undealt' : '';

	$html = mtarot_div( $post, 'tcard-slot', $variant );

	$html .= mtarot_div( $post, 'tcard-slot-name' );
	$html .= $toptions['name'];
	$html .= '</div><!--/tcard-slot-name-->';

	$html .= mtarot_div( $post, 'tcard-slot-meaning' );
	$html .= $toptions['meaning'];
	$html .= '</div><!--/tcard-slot-meaning-->';

	if( empty($question) ){ $html .= mtarot_undealt_card_html( $post, $toptions ); }
	else { $html .= mtarot_dealt_card_html( $post, $polarity ); }
	
	$html .= '</div><!--/tcard-slot-->';
	return $html;
}

/* Slot Sort Callback Functions */
function mtarot_slot_index_compare( $a, $b ){
	if( !is_array($a) || !is_array($b) ){ return 0; }
	if( $a['toptions']['index'] == $b['toptions']['index'] ){ return 0; }
	return ( $a['toptions']['index'] < $b['toptions']['index'] )? -1 : 1;
}

function mtarot_slot_pageorder_compare_desc( $a, $b ){
	if( !is_array($a) || !is_array($b) ){ return 0; }
	if( $a['pageorder'] == $b['pageorder'] ){ return 0; }
	return ( $a['pageorder'] > $b['pageorder'] )? -1 : 1;
}


/**
 * Tarot Slot Options
 *
 * Multiple options can be separated by spaces like `[tarot-card name1="value1" name2="value2]`.
 *
 * The following options are available:
 *  
 *	 `name` is the optional title of the card slot that will be displayed to the user
 *	   as part of the card's layout. This is not the title of the card itself, but the
 *	   name given to the slot which contains it (i.e. You, Me, Positive, etc...).
 *
 *	 `meaning` is the optional descriptive text matching the slot `name`. It can be used to
 *	   describe the purpose of the card slot within the layout and any additional ways to
 *	   interpret the card given its context and purpose.
 *  
 *	 - `index` is the optional order in which that card will be dealt (default: 0).
 *	   Slots with the same index will be dealt in an undefined order, which may be the order
 *	   the slots appear in the layout. Negative numbers are fine and will mean a slot gets
 *	   dealt a card before the default slots.
 *  
 *	 - `filters` is an optional list of name/value pairs of the form "name=value".
 * 	   Multiple filters can be separated by ampersands (`&`) like "name1=value1&name2=value2".
 *	   Multiple values can be separated by commas (`,`) like "name=value1,value2,value3".
 *	   Filter names can be any category, taxonomy, custom field, or tarot card property.
 *  
 *  	   Filter Examples:
 *		Category filter: 		[tarot-card filters="category=overleaves-deck"]
 *		Taxonomy filter: 		[tarot-card filters="overleaf=roles,goals"]
 *		Custom Field filter: 		[tarot-card filters="tarot-slug=tao"]
 *		Tarot Card Property filter: 	[tarot-card filters="polarity=up"]
 **/

// Encapsulation of slot options in javascript to allow AJAX hooks
function mtarot_toptions_js_html( $toptions ){
	$html = '<script language="JavaScript">' ."\n";
	$html .= '	var toptions = ' . json_encode($toptions) .';' ."\n";
	$html .= '	slot[toptions.pageorder].toptions = toptions;' ."\n";
	$html .= '</script>';
	return $html;
}



?>