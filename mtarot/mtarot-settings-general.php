<?php
/**
 * Michael Tarot General Settings
 **/
//require_once('mtarot_settings.php');

// Option Accessor
function mtarot_option( $option ){ $o = get_option('mtarot_options'); return $o[$option]; }

// Option Defaults
function mtarot_default_options(){
	return array(
		'deck_size' => '76',
		'debug_messages' => false
	);
}

// Option Validation
function mtarot_options_validate( $input ){
	$input['deck_size'] = mtarot_validate_number( $input['deck_size'] );
	return $input;
}

// Admin Menu Definition
if( is_admin() ){ add_action( 'admin_menu', 'mtarot_admin_menu' ); }
function mtarot_admin_menu(){
	add_menu_page(
		'Tarot Deck', 
		'Tarot Deck Options', 
		'manage_options', 
		'mtarot-options', 
		'mtarot_options_page', 
		WP_CONTENT_URL . '/plugins/mtarot/mtarot-icon.png', 
		0 
	);
}

// Admin Menu Initialization
if( is_admin() ){ add_action( 'admin_init', 'mtarot_admin_init' ); }
function mtarot_admin_init(){
	register_setting( 'mtarot_options', 'mtarot_options', 'mtarot_options_validate' );
	add_settings_section( 'mtarot_general_section',		'General Tarot Settings',	'mtarot_general_section_text',	'mtarot_options_page' );
	add_settings_field( 'mtarot_deck_size', 'Maximum Deck Size', 'mtarot_option_deck_size', 'mtarot_options_page', 'mtarot_general_section' );
	add_settings_field( 'mtarot_debug_messages', 'Show Debug messages', 'mtarot_option_debug_messages', 'mtarot_options_page', 'mtarot_general_section' );
}

/* HTML Form Elements */

// General Options Page
function mtarot_options_page(){
	mtarot_echo_options_page( 'Tarot Deck Options', 
							  'This page controls the general settings for how the Michael Tarot Deck plugin behaves. The "Card Options" and "Layout Options" sub-pages contain more options.',
							  'mtarot_options',
							  'mtarot_options_page' );
}

// General Section
function mtarot_general_section_text(){ echo 'These settings affect all tarot cards and layouts.'; }

function mtarot_option_deck_size(){
	$options = get_option('mtarot_options');
	echo "<input id='mtarot_deck_size' name='mtarot_options[deck_size]' size='10' type='text' value='{$options['deck_size']}' />\n";
}

function mtarot_option_debug_messages(){
	echo "<input type='checkbox' value='1' name='mtarot_options[debug_messages]' ";
	checked( mtarot_option('debug_messages'), '1' );
	echo " > <i>(useful to find out where a problem is happening, but will cause red text to be written to the page)</i>";
}

?>