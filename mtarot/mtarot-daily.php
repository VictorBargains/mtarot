<?php 
/**
 * Michael Tarot Card of the Day
 */
 require_once('mtarot.php');

 	function mtarot_get_daily(){
		// For now, fake daily data. In future, store in a database or determine via algorithm.
		
		$daily = array(
			'id'			 => 1917,		// 1917 = '76 Cycle Off'
			'polarity'		 => 'up',		// positive
			'negative-index' => 0,			// index of the 'negative-description' meta field to use
			'positive-index' => 0,			// index of the 'positive-description' meta field to use
		);
		
		return $daily;
	}
 
 // persistently store the daily card info
 	function mtarot_set_daily( $daily ){
	
	
	}
 
 	function mtarot_daily_card_html(){
 
		$html = '';
 		
 		$daily = mtarot_get_daily();
		
//			$tcard = mtarot_get_tcard( $daily['id'] );		
		$tcard = get_post( $daily['id'] );
		
		if( !empty( $tcard ) ){

			// pass on polarity to display method.
			$polarity = empty( $daily['polarity'] ) ? array_random('up', 'down') : $daily['polarity'];
		
			// TODO: add a parameter for the negative or positive description index
			$html .= mtarot_dealt_card_html( $tcard, $polarity );
		}
		
		else {
			$html .= "Error: post #" . $daily['id'] . " not found.";
		}
	
		return $html;
	
	
	}
	
	
 ?>