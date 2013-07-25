<?php 
/**
 * Michael Tarot Card of the Day
 */
 
 	function mtarot_get_daily(){
		// For now, fake daily data. In future, store in a database or determine via algorithm.
		
		$daily = array(
			'id'			 => '1917',		// 1917 = '76 Cycle Off'
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
 
 		$daily = mtarot_get_daily();
		$post = mtarot_get_tcard( $daily['id'] );
		
		// pass on polarity to display method.
		$polarity = empty( $daily['polarity'] ) ? array_random('up', 'down') : $daily['polarity'];
		
		echo mtarot_dealt_card_html( $post, $polarity );
	}
	
	
 ?>