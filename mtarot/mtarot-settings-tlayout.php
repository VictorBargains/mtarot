<?php
/**
 * Michael Tarot Layout Settings
 **/
//require_once('mtarot_settings.php');

// Option Accessor
function tlayout_option( $option ){ $o = get_option('tlayout_options'); return stripslashes($o[$option]); }

// Option Defaults
function mtarot_default_tlayout_options(){
	return array(
		'type_name' => 'tarot-layout',
		'form_questionPrompt' => 'Want a personal tarot reading from Michael? Type a question below...',
		'form_questionHint' => 'Type your question here',
		'form_questionLabel' => 'Your Question:',
		'form_layoutPrompt' => '',
		'form_layoutHint' => '<i>(affects the intepretation of the cards &mdash; <a href="/tlayout/" target="_blank">View Layout Descriptions</a>&nbsp;)</i>',
		'form_layoutLabel' => 'Spread&nbsp;/ Layout:',
		'form_submitButton' => 'Ask Michael',
	);
}

// Admin Menu Definition
if( is_admin() ){ add_action( 'admin_menu', 'tlayout_admin_menu' ); }
function tlayout_admin_menu(){
	add_submenu_page( 'mtarot-options', 'Tarot Layouts', 'Layout Options', 'manage_options', 'tlayout-options', 'mtarot_layout_options_page' );
}

// Admin Menu Initialization
if( is_admin() ){ add_action( 'admin_init', 'tlayout_admin_init' ); }
function tlayout_admin_init(){
	register_setting( 'tarot_layout_options', 'tlayout_options', 'tlayout_options_validate' );
	add_settings_section( 'tlayout_type_section', 'Custom Post Type', 'tlayout_type_section_text', 'tlayout_options_page' );	
	add_settings_field( 'tlayout_type_name', 'Tarot Layout Type:', 'tlayout_option_type_name', 'tlayout_options_page', 'tlayout_type_section' );
	add_settings_section( 'tlayout_form_section', 'Question and Layout Form', 'tlayout_form_section_text', 'tlayout_options_page' );
	add_settings_field( 'tlayout_form_questionPrompt', 'Question Prompt:', 'tlayout_option_form_questionPrompt', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_questionHint', 'Question Hint:', 'tlayout_option_form_questionHint', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_questionLabel', 'Question Label:', 'tlayout_option_form_questionLabel', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_layoutPrompt', 'Layout Prompt:', 'tlayout_option_form_layoutPrompt', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_layoutHint', 'Layout Hint:', 'tlayout_option_form_layoutHint', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_layoutLabel', 'Layout Label:', 'tlayout_option_form_layoutLabel', 'tlayout_options_page', 'tlayout_form_section' );
	add_settings_field( 'tlayout_form_submitButton', 'Submit Button Text:', 'tlayout_option_form_submitButton', 'tlayout_options_page', 'tlayout_form_section' );
}

// Option Validation
function tlayout_options_validate( $input ){
	$input['type_name'] = mtarot_validate_slug( $input['type_name'] );
	return $input;
}

/* HTML Form Elements */

// Layout Options Page
function mtarot_layout_options_page(){
	if( !current_user_can('manage_options') ){
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	echo '<div class="wrap"><h2>Michael Tarot Layout Options</h2>';
	echo '<form method="post" action="options.php">';
	settings_fields( 'tarot_layout_options' );
	do_settings_sections( 'tlayout_options_page' );
	echo '<p class="submit"><input type="submit" class="button-primary" value="';
	_e('Save Changes');
	echo '" /></p></form></div>';
}

// Layout Post Types Section
function tlayout_type_section_text(){ echo 'Set the database names of the custom post type you are using to represent tarot layouts.'; }

function tlayout_option_type_name(){
	$options = get_option('tlayout_options');
	echo "<input id='tlayout_type_name' name='tlayout_options[type_name]' size='30' type='text' value='{$options['type_name']}' />";
}

// Layout Form Section
function tlayout_form_section_text(){ echo 'Set the Prompts (shown before) and Hints (shown after) for the Question and Layout elements that make up the Tarot Form (called via the [mtarot-form] shortcode)'; }

function tlayout_option_form_questionPrompt(){
	echo '<input id="tlayout_form_questionPrompt" name="tlayout_options[form_questionPrompt]" size="80" type="text" value="' . tlayout_option('form_questionPrompt') . '" />';
}

function tlayout_option_form_questionHint(){
	echo '<input id="tlayout_form_questionHint" name="tlayout_options[form_questionHint]" size="80" type="text" value="' . tlayout_option('form_questionHint') . '" />';
}

function tlayout_option_form_questionLabel(){
	echo '<input id="tlayout_form_questionLabel" name="tlayout_options[form_questionLabel]" size="40" type="text" value="' . tlayout_option('form_questionLabel') . '" />';
}

function tlayout_option_form_layoutPrompt(){
	echo '<input id="tlayout_form_layoutPrompt" name="tlayout_options[form_layoutPrompt]" size="80" type="text" value="' . tlayout_option('form_layoutPrompt') . '" />';
}

function tlayout_option_form_layoutHint(){
	echo '<input id="tlayout_form_layoutHint" name="tlayout_options[form_layoutHint]" size="80" type="text" value="' . tlayout_option('form_layoutHint') . '" />';
}

function tlayout_option_form_layoutLabel(){
	echo '<input id="tlayout_form_layoutLabel" name="tlayout_options[form_layoutLabel]" size="40" type="text" value="' . tlayout_option('form_layoutLabel') . '" />';
}

function tlayout_option_form_submitButton(){
	echo '<input id="tlayout_form_submitButton" name="tlayout_options[form_submitButton]" size="40" type="text" value="' . tlayout_option('form_submitButton') . '" />';
}

?>