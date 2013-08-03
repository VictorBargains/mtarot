<?php
/**
 * Michael Tarot General Settings and Administration
 **/
require_once('mtarot.php');

/* Admin Page Generation */
function mtarot_echo_options_page( $title, $description, $fields, $sections ){
	if( !current_user_can('manage_options') ){
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo "<div class='wrap'><h2>{$title}</h2><p>{$description}</p>";
	echo "<form method='post' action='options.php'>";
	settings_fields( $fields );
	do_settings_sections( $sections );
	echo "<p class='submit'><input type='submit' class='button-primary' value='" . __('Save Changes') . "'></p>";
	echo "</form></div>";
}

require_once('mtarot-settings-general.php');
require_once('mtarot-settings-tcard.php');
require_once('mtarot-settings-tlayout.php');

/**
 * Option Defaults
 **/
function mtarot_should_clobber_options(){ return true; } // TODO: make an option

function mtarot_set_default_options(){
	// Get Defaults
	$mtarot_options = mtarot_default_options();
	$tcard_options = mtarot_default_tcard_options();
	$tlayout_options = mtarot_default_tlayout_options();

	// Make options if not exist
	add_option('mtarot_options', $mtarot_options, '', 'yes');
	add_option('tcard_options', $tcard_options, '', 'yes');
	add_option('tlayout_options', $tlayout_options, '', 'yes');

	if( mtarot_should_clobber_options() ){
		update_option('mtarot_options', $mtarot_options);
		update_option('tcard_options', $tcard_options);
		update_option('tlayout_options', $tlayout_options);
	}
	// TODO: merge existing options (if any) with defaults
}

/**
 * Validation Functions
 **/

function mtarot_validate_url( $url ){
	// Strip leading and trailing spaces
	$url = preg_replace( '/($\\s*|\\s*^)/', '', $url );
	return $url;
}

function mtarot_validate_number( $number ){
	// Strip non-numerals
	$number = preg_replace( '/[^0-9]/', '', $number );
	return $number;
}

function mtarot_validate_slug( $slug ){
	// make lowercase and replace spaces with dashes
	$slug = preg_replace( '/\\s/', '-', strtolower($slug) );
	return $slug;
}

function mtarot_validate_checkbox( $bool ){
	// turn a boolean value into a 1 or 0
	return $bool ? 1 : 0;
}
?>