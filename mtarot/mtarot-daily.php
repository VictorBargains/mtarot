<?php 
/**
 * Michael Tarot Card of the Day
 *
 * Picks one card each new day, at random, including a polarity and a description index.
 */
 require_once('mtarot.php');
 
 	// This method will select a random card, set its ID in the options DB, and store a card-of-the-day meta token on it.
	// When all descriptions on all cards have matching card-of-the-day meta tokens, the tokens will be reset for the next cycle. 
	function mtarot_generate_daily( $new_id='random', $exclude=array() ){

		// by default, generate a daily card from the default deck
		if( $new_id == 'random' ){
	
			// create a placeholder for the result which will become the next card-of-the-day
			$chosen_cotd = NULL;
			
			// keep choosing a random card until we find one with an unused polarity/description pair
			while( $chosen_cotd == NULL ){
				
				// Once every description has been used once, we should reset the card-of-the-day metadata so we don't infinitely loop.
				if( count($exclude) == mtarot_option('deck_size') ){
					mtarot_reset_daily();
				}
				
				$new_id = mtarot_random_card_id( $exclude );
	
				// To process post meta data we need to get the post which is our new daily card
				$tcard = get_post( $new_id );
				
				// make a hypothetical card-of-the-day data structure for each possible polarity/description combination
				$possible_cotds = array();
				
				// find all positive and negative descriptions available
				$neg_descs = get_post_meta( $new_id, 'negative-description' );
				for( $i = 0; $i < count( $neg_descs ); $i++ ){
					$possible_cotds[] = array(
						'date' => split( " ", current_time( 'timestamp' ), 1 ),
						'polarity' => 'down',
						'desc_index' => $i
					);
				}
				
				$pos_descs = get_post_meta( $new_id, 'positive-description' );
				for( $i = 0; $i < count( $pos_descs ); $i++ ){
					$possible_cotds[] = array(
						'date' => split( " ", current_time( 'timestamp' ), 1 ),
						'polarity' => 'up',
						'desc_index' => $i
					);
				}
	
				// test to see if this card has been used as a card of the day before
				$cotd_metas = get_post_meta( $new_id, 'card-of-the-day' );
	
				// go through possible descs at random and keep the first one which hasn't already been used
				foreach( shuffle( $possible_cotds ) as $cotd ){
	
					// we will assume this $cotd can be used unless we find one with a matching polarity/description pair in the DB
					$keep_desc = true;
				
					// check each card-of-the-day meta field stored in the post to find a match
					foreach( $cotd_metas as $cotd_meta ){
						if( $cotd_meta['polarity'] == $cotd['polarity'] && $cotd_meta['desc_index'] == $cotd['desc_index'] ){
							// one match within the DB means the $cotd we are testing has already been used.
							$keep_desc = false;
							break;
						}
					}
					
					// if we have not invalidated this randomly chosen polarity/description combo, use it
					if( $keep_desc ){
						$chosen_cotd = $cotd;
						break;
					}
					
				}
				
				// since $keep_desc must be false and $chosen_cotd must be NULL to get to this point...
				// we should add the post id of this card to the exclusion list so it won't be picked by mtarot_random_card_id()
				$exclude[] = $new_id;	
			}
			
			// $chosen_cotd should have been set by now, so we should activate it...
			
			// pass the ID of the post to the system which stores it in the options
			$chosen_cotd['id'] = $new_id;
			mtarot_set_daily( $chosen_cotd );
			
		}
		
	}
	
	function mtarot_reset_daily( $wipe_post_meta=true ){
		// unset options		
		$tcard_opts = get_option('tcard_options');
		
		$tcard_opts['daily_post_id'] = '';
		$tcard_opts['daily_polarity'] = '';
		$tcard_opts['daily_desc_index'] = 0;
		$tcard_opts['daily_date'] = '';
		
		update_option( 'tcard_options', $tcard_opts );
	
		if( !$wipe_post_meta ){ return; }
		
		// get all posts which have been previously featured as a daily card.
		$cotd_posts = get_posts( array(	'meta_key' => 'card-of-the-day' ) );
	
		if( count( $exclude ) == count( $cotd_posts ) ){
			// number of cards of the day equals number of cards of the day, then we are fully saturated and should reset
			foreach( $cotd_posts as $post ){
				// remove all 'card-of-the-day' meta fields from each post
				delete_post_meta( $post->post_id, 'card-of-the-day' );
			}
		}
	}
	
	
	// Get the data describing the card of the day, auto-generating it if the option is set.
 	function mtarot_get_daily(){
		
		$current_date = split( " ", current_time('timestamp'), 1 );
		
		if( $current_date != tcard_option('daily_date') ){
			if( tcard_option('daily_autogenerate') ){
				mtarot_generate_daily();
			}	
		}
		
		$daily = array(
			'id'			 => tcard_option('daily_post_id'),
			'polarity'		 => tcard_option('daily_polarity'),
			'desc_index' 	 => tcard_option('daily_desc_index'),
			'date'			 => tcard_option('daily_date')
		);
		
		return $daily;
	}
 
	// persistently store the daily card info
 	function mtarot_set_daily( $daily ){
		if( isset( $daily['id'] ) ){
			$id = $daily['id'];
			
			$post = mtarot_get_tcard_post( $id );
			if( !empty( $post ) ){
				
				// set the global options data to identify this card as the card of the day
				$tcard_opts = get_option('tcard_options');
				
				$tcard_opts['daily_post_id'] = $daily['id'];
				$tcard_opts['daily_polarity'] = $daily['polarity'];
				$tcard_opts['daily_desc_index'] = $daily['desc_index'];
				$tcard_opts['daily_date'] = isset( $daily['date'] ) ? $daily['date'] : split( " ", current_time('timestamp'), 1 );
				
				update_option( 'tcard_options', $tcard_opts );
				
				// we don't need to store the id in the post's card-of-the-day record because it is redundant
				unset( $daily['id'] );
				
				// add the card-of-the-day metadata for this card with today's date and the chosen polarity/description
				add_post_meta( $id, 'card-of-the-day', $daily );
			}
		}
	}
 
 	function mtarot_daily_card_html(){
 
		$html = '';
 		
 		$daily = mtarot_get_daily();
		
//			$tcard = mtarot_get_tcard( $daily['id'] );		
		$tcard = get_post( $daily['id'] );
		
		if( !empty( $tcard ) ){

			// pass on polarity to display method.
			$polarity = empty( $daily['polarity'] ) ? mtarot_random_polarity() : $daily['polarity'];
		
			// get the description index
			$desc_index = empty( $daily['desc_index'] ) ? 0 : $daily['desc_index'];
			
			// TODO: add a parameter for the negative or positive description index
			$html .= mtarot_dealt_card_html( $tcard, $polarity, $desc_index );
		}
		
		else {
			$html .= "Error: post #" . $daily['id'] . " not found.";
		}
	
		return $html;
	
	
	}
	
	
 ?>