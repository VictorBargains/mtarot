<?php
/**
 * Michael Tarot Card Settings
 **/
//require_once('mtarot_settings.php');

// Option Accessor
function tcard_option( $option ){ $o = get_option('tcard_options'); return $o[$option]; }

// Option Defaults
function mtarot_default_tcard_options(){
	return array(
		'image_path' => '/MichaelCards',
		'image_type' => 'jpg',
		'image_width' => '150',
		'image_height' => '251',
		'image_back' => '/wp-content/uploads/2011/03/cardback.jpg',
		'type_name' => 'tarot-card',
		'taxes_allowed' => mtarot_taxonomy_names(),
		'daily_post_id' => '',
		'daily_polarity' => '',
		'daily_desc_index' => 0,
		'daily_autogenerate' => 1,
		'daily_generate' => 0,
		'daily_date' => '',
		'daily_reset' => 0,
	);
}

// Admin Menu Definition
if( is_admin() ){ add_action( 'admin_menu', 'tcard_admin_menu' ); }
function tcard_admin_menu(){
	add_submenu_page( 'mtarot-options', 'Tarot Cards', 'Card Options', 'manage_options', 'tcard-options', 'mtarot_card_options_page' );
}

// Admin Menu Initialization
if( is_admin() ){ add_action( 'admin_init', 'tcard_admin_init' ); }
function tcard_admin_init(){
	register_setting( 'tcard_options', 'tcard_options', 'tcard_options_validate' );
	
	/* Post Type Section */
	add_settings_section( 'tcard_type_section', 'Custom Post Type', 'tcard_type_section_text', 'tcard_options_page' );	
	add_settings_field( 'tcard_type_name', 'Tarot Card Type:', 'mtarot_option_card_type_name', 'tcard_options_page', 'tcard_type_section' );	

	/* Appearance Section */
	add_settings_section( 'tcard_appearance_section', 'Tarot Card Images', 'tcard_image_section_text', 'tcard_options_page' );
	add_settings_field( 'tcard_image_back', 'Card Back Image URL:', 'mtarot_option_card_image_back', 'tcard_options_page', 'tcard_appearance_section' );
	add_settings_field( 'tcard_image_path', 'Card Face Image Directory:', 'mtarot_option_card_image_path', 'tcard_options_page', 'tcard_appearance_section' );
	add_settings_field( 'tcard_image_type', 'Image extension type:', 'mtarot_option_card_image_type', 'tcard_options_page', 'tcard_appearance_section' );
	add_settings_field( 'tcard_image_width', 'Card Image Width:', 'mtarot_option_card_image_width', 'tcard_options_page', 'tcard_appearance_section' );
	add_settings_field( 'tcard_image_height', 'Card Image Height:', 'mtarot_option_card_image_height', 'tcard_options_page', 'tcard_appearance_section' );

	/* Taxonomy Inclusion Section */
	add_settings_section( 'tcard_taxes_section', 'Custom Taxonomies', 'tcard_taxes_section_text', 'tcard_options_page' );	
	add_settings_field( 'tcard_taxes_allowed', 'Show Taxonomies in Details:', 'mtarot_option_card_taxes_allowed', 'tcard_options_page', 'tcard_taxes_section' );
	
	/* Card of the Day Section */
	add_settings_section( 'tcard_daily_section', 'Card of the Day', 'tcard_daily_section_text', 'tcard_options_page' );
	add_settings_field( 'tcard_daily_post_id', 'Post ID:', 'mtarot_option_daily_post_id', 'tcard_options_page', 'tcard_daily_section' );	
	add_settings_field( 'tcard_daily_polarity', 'Polarity:', 'mtarot_option_daily_polarity', 'tcard_options_page', 'tcard_daily_section' );	
	add_settings_field( 'tcard_daily_desc_index', 'Description Index:', 'mtarot_option_desc_index', 'tcard_options_page', 'tcard_daily_section' );	
	add_settings_field( 'tcard_daily_autogenerate', 'Auto-generate new card daily:', 'mtarot_option_daily_autogenerate', 'tcard_options_page', 'tcard_daily_section' );
	add_settings_field( 'tcard_daily_generate', 'Generate new card now:', 'mtarot_option_daily_generate', 'tcard_options_page', 'tcard_daily_section' );
	add_settings_field( 'tcard_daily_reset', 'Reset the daily card now:', 'mtarot_option_daily_reset', 'tcard_options_page', 'tcard_daily_section' );
	//add_settings_field( 'tcard_daily_date", "Daily Card Date:"', 'mtarot_option_daily_date', 'tcard_options_page', 'tcard_daily_section' );
}

/**
 * Card Options Page 
 **/

function mtarot_card_options_page(){
//MECHO('tcard_options:', get_option('tcard_options'));
	mtarot_echo_options_page( 'Tarot Card Options', 'Adjust the settings for how tarot cards are stored, dealt, and shown.', 'tcard_options', 'tcard_options_page' );
}

/* Card Post Types Section */
function tcard_type_section_text(){ echo 'Select the custom post type you want to represent tarot cards in the deck.'; }
function mtarot_option_card_type_name(){ 
	echo mtarot_posttypes_pulldown_html( 'tcard_options[type_name]', tcard_option('type_name'), 'tcard_type_name' );
}

/* Card Appearance Section */
function tcard_image_section_text(){ echo 'Control how the image inside the border appears when a card is drawn on the page.'; }

function mtarot_option_card_image_back(){ 
	echo mtarot_option_text_html( 'tcard_options[image_back]', tcard_option('image_back'), 'tcard_image_back', 100 );
	echo '<br /><i>(this image will be displayed inside a card\'s border when it has been dealt face-down)</i>';
}

function mtarot_option_card_image_path(){ 
	echo mtarot_option_text_html( 'tcard_options[image_path]', tcard_option('image_path'), 'tcard_image_path', 100 ); 
	echo '<br /><i>(this directory will be searched for a file with the name `{tarot-slug}-{polarity}.{image-type}`, all lowercase, to be displayed when a card is dealt face up)</i>';
}

function mtarot_option_card_image_type(){ 
	echo mtarot_option_text_html( 'tcard_options[image_type]', tcard_option('image_type'), 'tcard_image_type', 10 ); 
}

function mtarot_option_card_image_width(){ 
	echo mtarot_option_text_html( 'tcard_options[image_width]', tcard_option('image_width'), 'tcard_image_width', 10 ); 
}

function mtarot_option_card_image_height(){	
	echo mtarot_option_text_html( 'tcard_options[image_height]', tcard_option('image_height'), 'tcard_image_height', 10 ); 
}

/* Card Taxonomies Section */
function tcard_taxes_section_text(){ echo 'If a taxonomy is checked here, any of its terms will be shown below cards to which they apply (on pages where full card details are shown, but not in thumbnails or layouts).'; }
function mtarot_option_card_taxes_allowed(){
	$allowed = tcard_option('taxes_allowed');
	$taxes = mtarot_taxonomy_names();
	foreach( $taxes as $tax ){
		$value = '';
		if( isset($allowed[$tax]) ){ $value = $allowed[$tax]; }
		
		$name = "tcard_options[taxes_allowed][{$tax}]";
		
		echo "<input type='checkbox' name='{$name}' value='1' ";
		checked( $value, '1' );
		echo " />{$tax}&nbsp; ";
	}
}


/* Daily Card Section */
function tcard_daily_section_text(){ echo 'Choose the card, polarity, and description featured in the Card of the Day.'; }

function mtarot_option_daily_post_id(){	
	echo mtarot_option_text_html( 'tcard_options[daily_post_id]', tcard_option('daily_post_id'), 'tcard_daily_post_id', 10 ); 
}

function mtarot_option_daily_polarity(){ 
	echo mtarot_polarities_pulldown_html( 'tcard_options[daily_polarity]', tcard_option('daily_polarity'), 'tcard_daily_polarity' );
}

function mtarot_option_desc_index(){
	echo mtarot_option_text_html( 'tcard_options[daily_desc_index]', tcard_option('daily_desc_index'), 'tcard_daily_desc_index', 10 );
}

function mtarot_option_daily_autogenerate(){
	echo '<input name="tcard_options[daily_autogenerate]" type="checkbox" value="1" '; 
	checked( '1', tcard_option( 'daily_autogenerate' ) );
	echo '>';
    echo '<label for="tcard_options[daily_autogenerate]">Generate daily</label>';
}

function mtarot_option_daily_generate(){
	echo '<input name="tcard_options[daily_generate]" type="checkbox" value="1" '; 
	checked( '1', tcard_option( 'daily_generate' ) );
	echo '>';
    echo '<label for="tcard_options[daily_generate]">Generate now</label>';
}

function mtarot_option_daily_reset(){
	echo '<input name="tcard_options[daily_reset]" type="checkbox" value="0" ';
	//checked( '1', tcard_option( 'daily_reset' ) ); // Do not persist this value.
	echo '>';
    echo '<label for="tcard_options[daily_generate]">Reset now (Use this if the card is stuck)</label>';
}

?>