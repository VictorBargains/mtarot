<?php
/**
 * Michael Tarot Card
 **/
require_once('mtarot.php');

function MTAROT_ALLOWED_TAXONOMIES(){ //Taxonomies allowed as the lvalue in a filter argument.
	return array( 'category', 'overleaf', 'soul-evolution-stage', 'character-augment', 'life-option' );
}

function MTAROT_ALLOWED_PROPERTIES(){ //Tarot card properties allowed as lvalues in a filter argument.
	return array( 'polarity' );
}

function MTAROT_ALLOWED_POLARITIES(){ //Allowed rvalues for a `polarity` lvalue in a filter argument.
	return array( 'up', 'down' );
}

function MTAROT_ALLOWED_CUSTOMFIELDS(){ //Custom fields (post-meta) keys allowed as lvalues in a filter argument.
	return array( 'tarot-slug' );
}

function MTAROT_HIDE_TAXONOMIES(){ //These taxonomies will be hidden when the card is rendered to page.
	return array( 'category' );
}

// Validation
function tcard_options_validate( $input ){
	$input['image_back'] = mtarot_validate_url( $input['image_back'] );
	$input['image_path'] = mtarot_validate_url( $input['image_path'] );
	$input['image_width'] = mtarot_validate_number( $input['image_width'] );
	$input['image_height'] = mtarot_validate_number( $input['image_height'] );
	$input['type_name'] = mtarot_validate_slug( $input['type_name'] );
	
	if( !empty( $input['taxes_allowed'] ) ){
		$array_keys = array_keys($input['taxes_allowed']); 
		foreach( $array_keys as $key ){
			$input[$key] = mtarot_validate_slug( $input[$key] ); 
		}
	}
//	$input['type_slug'] = mtarot_validate_slug( $input['type_slug'] );

	$input['daily_autogenerate'] = mtarot_validate_checkbox( $input['daily_autogenerate'] );
	$input['daily_generate'] = mtarot_validate_checkbox( $input['daily_generate'] );

	if( $input['daily_generate'] == 1 ){
		mtarot_generate_daily();
		$input['daily_generate'] = 0;
	}
	return $input;
}


/* Data Accessors */
// Get unique name of tarot card (irrespective of index or polarity)
function mtarot_slug( $post_id ) {
	return get_post_meta( $post_id, 'tarot-slug', true );
}

// Get a random polarity
function mtarot_random_polarity(){
	$polarities = MTAROT_ALLOWED_POLARITIES();
	$pindex = array_rand($polarities);
	return $polarities[$pindex];
}

<<<<<<< Updated upstream
// Get a random polar description of the card (if no polarity specified, returns post content)
function mtarot_card_description( $post, $polarity='random', $index='random' ){
	if( $polarity == 'random' ){ $polarity = mtarot_random_polarity(); }
	
=======
// Get an array of all descriptions, positive and negative, for a given card
function mtarot_card_descriptions( $post, $polarity='' ){
	
	// If a random polarity is desired, make it decide here
	if( $polarity == 'random' ){ $polarity = mtarot_random_polarity(); }
		
	// Pick the description from the database depending on which polarity was chosen
>>>>>>> Stashed changes
	$meta = '';
	switch( $polarity ){
		case 'up': $meta = 'positive-description'; break;
		case 'down': $meta = 'negative-description'; break;
		default:	return array( $post->post_excerpt );
	}
	
	$descs = get_post_meta( $post->ID, $meta, false );

<<<<<<< Updated upstream
=======
	return $descs;
}

// Get a random polar description of the card (if no polarity specified, returns post content)
function mtarot_card_description( $post, $polarity='', $index='random' ){
	
	$descs = mtarot_card_descriptions( $post, $polarity );
	
	// When no polarity is specified, use the excerpt as a description and ignore $
>>>>>>> Stashed changes
	if( empty($descs) ){ return $post->post_excerpt; }
	
	$dindex = ($index == 'random') ? array_rand($descs) : ($index % count($descs) );
	
	return $descs[$dindex];
}

<<<<<<< Updated upstream
=======
// Helper function to make a JSON style array out of a provided PHP array. //TODO: move elsewhere or replace with actual JSON library method
function javascript_array( $input_array ){
	$html = "[";
	for( $i = 0; $i < count($input_array); $i++ ){
		$html .= "'" . addslashes($input_array[$i]) . "'";
		if( $i < count($input_array) - 1 ){ $html .= ", \n"; }
	}
	$html .= "]";
	return $html;	
}

// Display controls which allow the description to change dynamically
function mtarot_card_description_controls_html( $post ){
	$updescs = mtarot_card_descriptions( $post, 'up' );
	$downdescs = mtarot_card_descriptions( $post, 'down' );
	$genericdescs = mtarot_card_descriptions( $post );
	$hint_message = "Click the + or - buttons to cycle through the various interpretations of this card when it is dealt in the Illuminated (+) or Shadow (-) position. Click this text again to return to the general description.";
	
	$html = "<script language='javascript'>\n";
	$html .= "var desc_polarity = 'none';\n";
	$html .= "var desc_indices = { 'up' : 0, 'down' : 0, 'none' : 0 };\n";
	$html .= "var card_descriptions = { 'up' : " . javascript_array($updescs) . ", 'down' : " . javascript_array($downdescs) . ", 'none' : " . javascript_array($genericdescs) . " };\n";
	$html .= "var card_image_urls = { 'up' : '" . mtarot_card_img_url($post, 'up') . "', 'down' : '" . mtarot_card_img_url($post, 'down') . "' };\n";

	$html .= "function showNextDescription(polarity) {\n";
	$html .= "  if( polarity === desc_polarity && polarity === 'none' ){ alert('" . addslashes($hint_message) . "'); }\n";
	$html .= "  desc_polarity = polarity;\n";
	$html .= "  desc_indices[polarity] = (desc_indices[polarity] + 1)  % card_descriptions[polarity].length;\n";
	$html .= "  document.getElementById('tcard-description-" . $post->ID . "').innerHTML = card_descriptions[polarity][desc_indices[polarity]];\n";
	$html .= "  if( polarity != 'down' ){ polarity = 'up'; }\n";
	$html .= "  document.getElementById('tcard-face-" . $post->ID . "').src = card_image_urls[polarity];\n";
	$html .= "}\n";

	$html .= "</script>\n";
	
	$html .= '<div id="tcard-desc-controls">';
	$html .= '<span id="tcard-desc-control-buttons">';
	$html .= '<a href="#" onclick="showNextDescription(\'up\'); return false;" title="Click to show an Illuminated meaning of this card...">';
	$html .= mtarot_card_polarity_html( $post, 'up' );
	$html .= '</a>';
	$html .= '<a href="#" onclick="showNextDescription(\'down\'); return false;" title="Click to show a Shadow meaning of this card...">';
	$html .= mtarot_card_polarity_html( $post, 'down' );
	$html .= '</a>';
	$html .= '</span>';
	$html .= '<a href="#" onclick="showNextDescription(\'none\'); return false;" title="Click to show the generic meaning of this card...">See other meanings</a>:';
	$html .= '</div>';
	return $html;
}

>>>>>>> Stashed changes
function mtarot_card_desc_up( $post, $index='random' ){
	return mtarot_card_description( $post, 'up', $index );/*
	$desc = get_post_meta( $post->ID, 'positive-description', false );
	$dindex = ($index == 'random') ? array_rand($desc) : $index;
	return $desc[$dindex];*/
}

function mtarot_card_desc_down( $post, $index='random' ){
	return mtarot_card_description( $post, 'down', $index );/*
	$desc = get_post_meta( $post->ID, 'negative-description', false );
	$dindex = ($index == 'random') ? array_rand($desc) : $index;
	return $desc[$dindex];*/
}

/* Show entire card */
<<<<<<< Updated upstream
function mtarot_card( $post, $polarity='random', $desc_index='random' ){
=======
function mtarot_card( $post, $polarity='', $desc_index=-1 ){
>>>>>>> Stashed changes
	echo mtarot_card_html( $post, $polarity, $desc_index );
}

// Must be in The Loop
function mtarot_the_card(){
	global $post;
<<<<<<< Updated upstream
	mtarot_card($post, 'up', 'random');
}

/* Card HTML Components */
function mtarot_card_html( $post, $polarity='random', $desc_index='random' ){
=======
	mtarot_card($post/*, 'up', 'random'*/); // commenting out to use function defaults
}

/* Card HTML Components */
function mtarot_card_html( $post, $polarity='', $desc_index='' ){
	// Polarity and description index should be defined at this level so they will be consistent among the pieces:
	
>>>>>>> Stashed changes
	$html = mtarot_div( $post, 'tcard', $polarity );
	
	$html .= mtarot_card_face_html( $post, $polarity );
	$html .= mtarot_card_polarity_html( $post, $polarity );
	$html .= mtarot_card_label_html( $post, $polarity );
<<<<<<< Updated upstream
	/*
	if( $desc_index < 0 ){
		// Only show non-negative descriptions
	} else*/ {
		$html .= mtarot_card_description_html( $post, $polarity, $desc_index );
	}
	
=======

	$html .= mtarot_card_description_html( $post, $polarity, $desc_index );
	
	$html .= mtarot_card_description_controls_html( $post );
	
	$html .= '<!-- taxonomies: -->';
>>>>>>> Stashed changes
	$html .= mtarot_card_taxonomies_html( $post );
	$html .= "</div><!--/tcard-->\n";
	return $html;
}

function mtarot_card_polarity_html( $post, $polarity='' ){
	$html =  mtarot_div( $post, 'tcard-polarity', $polarity );
	switch( $polarity ){
		case 'up': $html .= '+'; 	break;
		case 'down': $html .= '-'; 	break;
		default: 			break;
	}
	$html .= '</div><!--/tcard-polarity-->';
	return $html;
}

function mtarot_card_label_html( $post, $polarity='' ){
	$html = mtarot_div( $post, 'tcard-label' );
	$html .= $post->post_title . '</div>';
	return $html;
}

function mtarot_card_img_url( $post, $polarity ){
	$imgName = mtarot_slug($post->ID) . '-' . (empty($polarity)? 'up' : $polarity) ;
	$url = tcard_option('image_path') . '/' . $imgName . '.' . tcard_option('image_type');
<<<<<<< Updated upstream
=======
	return $url;
}

function mtarot_card_img_html( $post, $polarity='' ){
	$url = mtarot_card_img_url( $post, $polarity );
>>>>>>> Stashed changes
	$class = mtarot_class( 'tcard-face', $polarity );
	$id = mtarot_id( $post, 'tcard-face', $polarity );
	$dimensions = 'width="' . tcard_option('image_width') . '" height="' . tcard_option('image_height') . '"';
	return '<img ' . $class . ' ' . $id . ' src="' . $url . '" ' . $dimensions . ' />';
}

function mtarot_card_backimg_html( $post, $toptions ){
	$class = mtarot_class('tcard-back');
	$id = 'id="tcard-back-' . $toptions['pageorder'] . '"';
	$dimensions = 'width="' . tcard_option('image_width') . '" height="' . tcard_option('image_height') . '"';
	return '<img ' . $class . ' ' . $id . ' src="' . tcard_option('image_back') . '" ' . $dimensions . ' />';
}

function mtarot_card_face_html( $post, $polarity='' ) {
	$html = mtarot_div( $post, 'tcard-face-container', $polarity ) . mtarot_card_img_html( $post, $polarity ) . '</div>';
	return $html;
}

function mtarot_card_back_html( $post, $toptions ){
	$html = mtarot_div( $post, 'tcard-back-container' );
	$html .= mtarot_card_backimg_html( $post, $polarity ); 
	$html .= '</div>';
	return $html;
}

function mtarot_card_description_html( $post, $polarity='random', $desc_index='random' ){
	$html = mtarot_div( $post, 'tcard-description', $polarity );
	$html .= mtarot_card_description( $post, $polarity, $desc_index );
/*	switch( $polarity ){
		case 'up': $html .= mtarot_card_desc_up($post);		break;
		case 'down': $html .= mtarot_card_desc_down($post); 	break;
		default: $html .= $post->post_excerpt; 			break;
	}*/
	$html .= "</div><!--/tcard-description-->\n";
	return $html;
}

function mtarot_card_term_html( $post, $term, $taxonomyURL ){
	$termURL = $taxonomyURL. '/' . $term->slug;
	$html = mtarot_div( $post, 'term',  mtarot_slug($post->ID) );
	$html .= '<a href="' . $termURL . '">' . $term->name . '</a>';
	$html .= '</div><!--/tcard-term-->';
	return $html;
}

function mtarot_card_taxonomy_html( $post, $taxonomy ){
	$html = '';
	if( in_array( $taxonomy, MTAROT_HIDE_TAXONOMIES() ) ){ return $html; }

	$taxTerms = get_the_terms( $post->ID, $taxonomy );
	MECHO("Tax Terms: ", $taxTerms);
	if( !empty($taxTerms) ){
		$tax = get_taxonomy($taxonomy);
		$taxURL = get_option('siteurl') . '/' . $tax->name;

		$html .= mtarot_div( $post, 'taxonomy', mtarot_slug($post->ID) );
		$html .= $tax->label . ': ';

		foreach( $taxTerms as $term ){
			$html .= mtarot_card_term_html( $post, $term, $taxURL );
		}
		
		$html .= "</div><!--/tcard-taxonomy-->\n";
	}				
	return $html;
}

function mtarot_card_taxonomies_html( $post ){
	$html = mtarot_div( $post, 'taxonomies', 'tarot-card' );
	MECHO("Found taxonomies: ", mtarot_taxonomy_names() );
	foreach( mtarot_taxonomy_names() as $taxonomy ){
		MECHO("Taxonomy: ", $taxonomy);
		$html .= mtarot_card_taxonomy_html( $post, $taxonomy );
	}
	$html .= "</div><!--/tcard-taxonomies-->\n";
	return $html;
}

/* Validation */
function mtarot_validate_polarity( $value ){
	if( in_array( $value, MTAROT_ALLOWED_POLARITIES() ) ){ return $value; }
	return '';
}

//-----------------------------------

//	add_settings_field( 'tcard_type_slug', 'Custom Post Type Slug', 'mtarot_option_card_type_slug', 'tcard_options_page', 'tcard_posttypes_section' );	

/*	add_settings_section( 'tcard_style_section', 'Tarot Card Style', 'tcard_style_section_text', 'tcard_options_page' );
	add_settings_field( 'tcard_faceborder_color', 'Card Face Border Color', 'mtarot_option_card_faceborder_color', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_faceborder_size', 'Card Face Border Size', 'mtarot_option_card_faceborder_size', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_faceoutline_color', 'Card Face Outline Color', 'mtarot_option_card_faceoutline_color', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_faceoutline_size', 'Card Face Outline Size', 'mtarot_option_card_faceoutline_size', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_backborder_color', 'Card Back Border Color', 'mtarot_option_card_backborder_color', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_backborder_size', 'Card Back Border Size', 'mtarot_option_card_backborder_size', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_backoutline_color', 'Card Back Outline Color', 'mtarot_option_card_backoutline_color', 'tcard_options_page', 'tcard_style_section' );
	add_settings_field( 'tcard_backoutline_size', 'Card Back Outline Size', 'mtarot_option_card_backoutline_size', 'tcard_options_page', 'tcard_style_section' ); */


?>