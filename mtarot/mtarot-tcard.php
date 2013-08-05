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

// Get a random polar description of the card (if no polarity specified, returns post content)
function mtarot_card_description( $post, $polarity='random', $index='random' ){
	if( $polarity == 'random' ){ $polarity = mtarot_random_polarity(); }
	
	$meta = '';
	switch( $polarity ){
		case 'up': $meta = 'positive-description'; break;
		case 'down': $meta = 'negative-description'; break;
	}
	
	$descs = get_post_meta( $post->ID, $meta, false );

	if( empty($descs) ){ return $post->post_excerpt; }
	
	$dindex = ($index == 'random') ? array_rand($descs) : ($index % count($descs) );
	
	return $descs[$dindex];
}

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
function mtarot_card( $post, $polarity='random', $desc_index='random' ){
	echo mtarot_card_html( $post, $polarity, $desc_index );
}

// Must be in The Loop
function mtarot_the_card(){
	global $post;
	mtarot_card($post, 'up', -1);
}

/* Card HTML Components */
function mtarot_card_html( $post, $polarity='random', $desc_index=0 ){
	$html = mtarot_div( $post, 'tcard', $polarity );
	$html .= mtarot_card_face_html( $post, $polarity );
	$html .= mtarot_card_polarity_html( $post, $polarity );
	$html .= mtarot_card_label_html( $post, $polarity );
	
	if( $desc_index < 0 ){
		// Only show non-negative descriptions
	} else {
		$html .= mtarot_card_description_html( $post, $polarity, $desc_index );
	}
	
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

function mtarot_card_img_html( $post, $polarity='' ){
	$imgName = mtarot_slug($post->ID) . '-' . (empty($polarity)? 'up' : $polarity) ;
	$url = tcard_option('image_path') . '/' . $imgName . '.jpg';
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
	foreach( mtarot_taxonomy_names() as $taxonomy ){
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