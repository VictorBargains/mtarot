<?php
/**
 * Michael Tarot Stylesheets
 **/
require_once('mtarot.php');

//TODO: convert some hard coded style rules into options and dynamic CSS via PHP

function mtarot_style(){
	$url = WP_CONTENT_URL . '/plugins/mtarot/mtarot.css';
	echo '<link rel="stylesheet" type="text/css" href="' . $url . '" media="all" />';
}

add_action('wp_head', 'mtarot_style'); // Load michael-tarot stylesheet


?>