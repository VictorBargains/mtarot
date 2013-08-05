<?php
/**
 * Michael Tarot Question
 *
 * The Michael Tarot Deck requires a question that the user asks in order to get a reading
 * using a Tarot Layout.
 **/
require_once('mtarot.php');

/* Question Retrieval and Validation */
// Get the question whether it was provided via a GET or POST query (returns an empty string otherwise)
function mtarot_question(){
	$q = isset($_POST['question'])? $_POST['question'] : (isset($_GET['question'])? $_GET['question'] : '');
	return mtarot_validate_question( $q );
}

function mtarot_validate_question( $question ){
	$question = stripslashes($question);	// Unescape characters
	$question = preg_replace( '/( +$|^ +)/', '' , $question );	// Strip end or start spaces
	if( !empty($question) ){ $question = preg_replace( '/([^?])$/', '\\1?', $question ); } 	// Force `?` at end
	return $question;
}

/* Question Asked */
// Question styled and wrapped in quotes with a forced terminating question mark.
function mtarot_fancy_question_html( $question ){
	$qclass = mtarot_class('mtarot-question');
	$lclass = mtarot_class('mtarot-quote', 'left');
	$rclass = mtarot_class('mtarot-quote', 'right');
	$html = "<em {$lclass}>&ldquo;</em><div {$qclass}>{$question}</div><em {$rclass}>&rdquo;</em>";
	return $html;
}

/* Forms */

// Customizable Shortcode Form
function mtarot_shortcode_form_html( $args ){
	global $post;
	$html = '<form action="'.$args['action'].'" method="'.$args['method'].'" class="mtarot-form"><center>';
	$html .= '<div class="mtarot-layout-prompt">'.$args['layoutprompt'].'</div>';
	$html .= mtarot_layout_pulldown_html($post);
	$html .= '<div class="mtarot-question-prompt">'.$args['questionprompt'].'</div>';
	$html .= '<input type="text" id="question" name="question" value="'.$args['questionhint'].'"';
	$html .= ' class="mtarot-question" style="width:'.$args['width'].'px; display:block; text-align:center; align:center;" />';
	$html .= '<input class="mtarot-ask" type="submit" name="submit_ask" value="'.$args['submitbutton'].'" />';
	$html .= '</center></form><!-- End of mtarot form -->';
	return $html;
}

// New Question Form
function mtarot_question_form_asknew_html( $current_post ){
	$html = '<form class="mtarot-asknew" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';	
	$question = mtarot_question();
//	$qprompt = tlayout_option('form_questionPrompt');
	$qprompt = 'Get a Michael reading with this layout:';
	if( !empty($question) ){ $prompt = 'Type another question and choose a layout for a new reading...'; }
	$html .= '<center><table width="713" border="0" cellspacing="0" cellpadding="0">';
	$html .= '<tr><td colspan="3" align="center">';
	$html .= '<h6 class="mtarot-question-prompt">' . $qprompt . '</h6><!--/mtarot-question-prompt-->';
	$html .= '</td></tr><tr align="center" valign="middle">';
	$html .= '<td width="135"><label for="question">' . tlayout_option('form_questionLabel') . ' <i>(required)</i></label></td>';
	$html .= '<td width="443">' . mtarot_question_input_html( '' ) . '</td>';
	$html .= '<td width="135">' . mtarot_question_submit_ask_html( tlayout_option('form_submitButton') ) . '</td></tr>';
//	$html .= '<tr align="center" valign="middle"><td width="135"><label for="tlayout">' . tlayout_option('form_layoutLabel') . '</label></td>';
//	$html .= '<td colspan="2" align="left">' . mtarot_layout_pulldown_html( $current_post ) . tlayout_option('form_layoutHint') . '</td>';
//	$html .= '</tr>';
	$html .= '</table></center>';
	$html .= '</form>';
	// TODO: Load/Save/Share tarot readings.
	return $html; 
}

// Ask Again Form
function mtarot_question_form_askagain_html( $current_post, $question='' ){
	$action = $_SERVER['REQUEST_URI'];
	$html = '<form class="mtarot-askagain" action="' . $action . '" method="post">';
	$html .= mtarot_layout_pulldown_html( $current_post );
	$html .= mtarot_question_hidden_html( $question );
	$html .= mtarot_question_submit_ask_html( 'Ask Again' );
	$html .= "</form><!--/mtarot-askagain-->\n";
	return $html;
}

function mtarot_question_layout_form( $current_post ){
	$html = '<form class="mtarot-asklayout" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';	
	$question = mtarot_question();
	$html .= '<center><table width="713" border="0" cellspacing="0" cellpadding="0">';
    $html .= '<tr align="center" valign="middle">';
	$html .= '<td width="478">' . mtarot_question_input_html( '', 'Type your question for Michael here', '55' ) . '</td>';
	$html .= '<td width="200">' . mtarot_layout_pulldown_html( $current_post ) . '</td>';
	$html .= '<td width="135" rowspan="2">' . mtarot_question_submit_ask_html( tlayout_option('form_submitButton') ) . '</td></tr>';
	$html .= '<tr align="center" valign="top">';
	$html .= '<td><label for="question">Type your question for a tarot reading from Michael <i>(required)</i></label></td>';
	$html .= '<td><label for="tlayout">then choose a layout.</label></td>';
//	$html .= '<td></td>';
	$html .= '</table></center>';
	$html .= '</form>';
	return $html; 	
}

/* Question Form Pieces */
// Pulldown menu for specifying a tarot-layout (assumes a form context and optionally allows a page ID to specify the selected layout)
function mtarot_layout_pulldown_html( $current_post ){
	// Get all tlayouts
	$args = array( 'post_type' => 'tarot-layout', 'posts_per_page' => '30' );
	$layout_query = new WP_Query($args);
	$tlayouts = $layout_query->query($args);
	
	$html = "<select id='tlayout' name='tlayout' size='1'>\n";
	foreach( $tlayouts as $post_id ){
		$tlayout = get_post($post_id);
		
		$html .= '<option value="' . $tlayout->post_name . '"';
		if( $current_post->ID == $tlayout->ID ){ $html .= ' selected'; }
		$html .= '>' . $tlayout->post_title . "</option>\n";
	}
	$html .= "</select>\n";
	return $html;
}

// Question Text Input
function mtarot_question_input_html( $value, $hint='', $size='60' ){
	$html = '<input type="text" id="question" name="question" value="' . $value . '" title="' . $hint .'" size="' . $size . '" class="mtarot-question" />';
	return $html;
}

function mtarot_question_hidden_html( $question ){
	$html = '<input type="hidden" name="question" value="' . $question . '" />';
	return $html;
}

// "Ask" type Submit button
function mtarot_question_submit_ask_html( $button_text ){
	$html = '<input class="mtarot-ask" type="submit" name="submit_ask" value="' . $button_text . '" />';
	return $html;
}

?>