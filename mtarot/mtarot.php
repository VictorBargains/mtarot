<?php 
/*
	Plugin Name: 	Michael Tarot Deck
	Plugin URI: 	http://www.themichaelteaching.com/
	Description: 	Display Tarot Cards individually or as part of a reading which gets dealt randomly from the deck.
	Author: 		Victor Andersen
	Version: 		0.3
	Author URI: 	http://www.themichaelteaching.com/
*/

/* Respond to Plugin Activation */
register_activation_hook(__FILE__, 'mtarot_activate');
register_deactivation_hook(__FILE__, 'mtarot_deactivate');

/* Include Component Files */
require_once('mtarot-settings.php');	// Administrative settings panels and forms
require_once('mtarot-style.php');		// Static inclusion and dynamic generation of tarot card/layout stylesheets

require_once('mtarot-queries.php');		// Support for querying the wordpress database
require_once('mtarot-daily.php');		// Support for a "card of the day" feature

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

/* PLUGIN ACTIVTATION EVENT */
function mtarot_activate() {
	$tcard_opts = get_option('tcard_options');
	if( empty( $tcard_opts ) ){
		mtarot_set_default_options();	
	}
	else {
		// TODO: check to see if plugin has added new options we should be aware of
		
		// for now, leave old options intact and hope new options can handle null values.
		mtarot_set_default_options();
	}
}

/* PLUGIN DEACTIVATION EVENT */
function mtarot_deactivate() {
	// TODO: reset the card-of-the-day tokens if the user has set some option to do so on deactivation
}


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