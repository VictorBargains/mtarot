<?php
/**
 * Michael Tarot Layouts
 **/
require_once('mtarot.php');


/**
 * Layout HTML Elements
 **/

// Output a box to show a question asked by the user (if any), along with a form to ask a new one
function mtarot_before_layout_html( $current_post, $question='' ){
	if( !empty($question) ){
		$html = mtarot_div( $current_post, 'mtarot-question-container' );
		$html .= mtarot_question_asked_html( $question ); 
//		$html .= mtarot_question_form_asknew_html( $current_post, $question );
		$html .= "</div><!--/mtarot-question-container-->\n";
	}
	return $html;
}

// Let the user pick a new layout to ask this question (or get a different reading using the same layout/question)
function mtarot_after_layout_html( $current_post, $question='' ){
	if( !empty($question) ){
		$html = mtarot_div( $current_post, 'mtarot-askagain-container' );
		$html .= "<h5>Ask this question again using the layout of your choice: </h5>\n";
		$html .= mtarot_question_form_askagain_html( $current_post, $question );
		$html .= "</div><!--/mtarot-askagain-container-->\n";
		return $html;
	}
}

// Question the user asked
function mtarot_question_asked_html( $question ){
	$html = '<div class="mtarot-question-asked">You asked, ' . mtarot_fancy_question_html( $question );
	$html .= " and can find your reading <a href='#content' style='text-decoration:underline'>below</a>.\n";
	$html .= "</div><!--/mtarot-question-asked-->\n";
	return $html;
}

?>