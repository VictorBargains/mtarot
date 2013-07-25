<?php 
/*
	Plugin Name: 	Michael Tarot Deck
	Plugin URI: 	http://www.themichaelteaching.com/
	Description: 	Display Tarot Cards individually or as part of a reading which gets dealt randomly from the deck.
	Author: 		Victor Andersen
	Version: 		0.2
	Author URI: 	http://www.themichaelteaching.com/
*/

/* Respond to Plugin Activation */
register_activation_hook(__FILE__, 'mtarot_set_default_options');

/* Include Component Files */
require_once('mtarot-settings.php');	// Administrative settings panels and forms
require_once('mtarot-style.php');		// Static inclusion and dynamic generation of tarot card/layout stylesheets

require_once('mtarot-elements.php');	// HTML Primitives used for rendering content
require_once('mtarot-question.php');	// HTML forms and functions for initiating new tarot readings

require_once('mtarot-shortcodes.php');	// Official WordPress API for [tarot-card] and [tarot-form] replacement tags
require_once('mtarot-parsing.php');		// Parsing of content to replace [tarot-card] slots with rendered HTML
require_once('mtarot-layout.php');		// HTML primitives used to wrap tarot card layouts

require_once('mtarot-slot.php');		// HTML primitives and functions used for rendering tarot layout slots
require_once('mtarot-filters.php');		// Filters used to limit the cards dealt to a particular layout slot

require_once('mtarot-tcard.php');		// HTML primitives and functions used for rendering tarot cards
require_once('mtarot-widgets.php');		// Hooks into WordPress's widget API to use shortcode and HTML snippets inside a widget


/* Taxonomy Accessors */
function MTAROT_DEFAULT_TAXONOMIES_ARGS(){
	return array(
		'object_type' => array( tcard_option('type_name') ), 
		'public' => true, 
		'_builtin' => false 
	);
}
function mtarot_taxonomy_names() { return get_taxonomies( MTAROT_DEFAULT_TAXONOMIES_ARGS(), 'names' ); }
function mtarot_taxonomies() { return get_taxonomies( MTAROT_DEFAULT_TAXONOMIES_ARGS(), 'objects' ); }




/* Debug Functions  */
// Uncheck 'debug_messages' general option or style `div.mtarot-debug {display:none;}` to hide.
function MECHO( $label, $var='' ){ 
	if( mtarot_option('debug_messages') == '1' ){ 
		echo '<div class="mtarot-debug">' . $label; 
		if( !empty($var) ){ 
			if( is_array($var) ){ var_dump($var); } 
			else { print_r( $var ); }
		} 
		echo '</div>'; 
	} 
}

?>